<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_Booking_REST {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function routes() {
		$ns = 'uptown-booking/v1';

		register_rest_route(
			$ns,
			'/catalog',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'catalog' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$ns,
			'/slots',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'slots' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$ns,
			'/book',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'book' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function catalog() {
		$cats  = Uptown_Booking_DB::categories();
		$types = Uptown_Booking_DB::active_types();
		$out   = array();
		foreach ( $cats as $key => $meta ) {
			$items = array();
			foreach ( $types as $type ) {
				if ( $type->category !== $key ) {
					continue;
				}
				$items[] = self::type_payload( $type );
			}
			if ( ! $items ) {
				continue;
			}
			$out[] = array(
				'id'    => $key,
				'title' => $meta['de'],
				'blurb' => $meta['blurb'],
				'types' => $items,
			);
		}
		return rest_ensure_response( array( 'categories' => $out ) );
	}

	public static function slots( WP_REST_Request $request ) {
		$slug = sanitize_title( (string) $request->get_param( 'type' ) );
		$type = Uptown_Booking_DB::get_type_by_slug( $slug );
		if ( ! $type ) {
			return new WP_Error( 'ubk_type', __( 'Meeting type not found.', 'uptown-booking' ), array( 'status' => 404 ) );
		}

		$month = sanitize_text_field( (string) $request->get_param( 'month' ) );
		if ( ! preg_match( '/^\d{4}-\d{2}$/', $month ) ) {
			$month = wp_date( 'Y-m' );
		}
		$from  = $month . '-01';
		$start = DateTimeImmutable::createFromFormat( 'Y-m-d', $from, wp_timezone() );
		$to    = $start ? $start->modify( 'last day of this month' )->format( 'Y-m-d' ) : $from;

		return rest_ensure_response(
			array(
				'type'  => self::type_payload( $type ),
				'month' => $month,
				'days'  => Uptown_Booking_Availability::month_slots( $type, $from, $to ),
			)
		);
	}

	public static function book( WP_REST_Request $request ) {
		$nonce = $request->get_header( 'X-WP-Nonce' );
		if ( ! wp_verify_nonce( (string) $nonce, 'wp_rest' ) ) {
			return new WP_Error( 'ubk_nonce', __( 'Please reload the page and try again.', 'uptown-booking' ), array( 'status' => 403 ) );
		}

		if ( self::rate_limited() ) {
			return new WP_Error( 'ubk_rate', __( 'Please wait a moment before booking again.', 'uptown-booking' ), array( 'status' => 429 ) );
		}

		$slug = sanitize_title( (string) $request->get_param( 'type' ) );
		$type = Uptown_Booking_DB::get_type_by_slug( $slug );
		if ( ! $type ) {
			return new WP_Error( 'ubk_type', __( 'Meeting type not found.', 'uptown-booking' ), array( 'status' => 404 ) );
		}

		$date = sanitize_text_field( (string) $request->get_param( 'date' ) );
		$time = sanitize_text_field( (string) $request->get_param( 'time' ) );
		$start = Uptown_Booking_Availability::slot_datetime( $date, $time );
		if ( ! $start ) {
			return new WP_Error( 'ubk_slot', __( 'That time is not valid.', 'uptown-booking' ), array( 'status' => 400 ) );
		}
		$end = $start->modify( '+' . (int) $type->duration_min . ' minutes' );

		$name  = sanitize_text_field( (string) $request->get_param( 'name' ) );
		$email = sanitize_email( (string) $request->get_param( 'email' ) );
		$phone = sanitize_text_field( (string) $request->get_param( 'phone' ) );
		$notes = sanitize_textarea_field( (string) $request->get_param( 'notes' ) );

		if ( strlen( $name ) < 2 || ! $email ) {
			return new WP_Error( 'ubk_guest', __( 'Please enter your name and a valid email.', 'uptown-booking' ), array( 'status' => 400 ) );
		}

		$days = Uptown_Booking_Availability::month_slots( $type, $date, $date );
		if ( empty( $days[ $date ] ) || ! in_array( $time, $days[ $date ], true ) ) {
			return new WP_Error( 'ubk_taken', __( 'That slot is no longer available.', 'uptown-booking' ), array( 'status' => 409 ) );
		}

		if ( ! Uptown_Booking_Availability::is_free( $type->host_id, $start, $end ) ) {
			return new WP_Error( 'ubk_taken', __( 'That slot is no longer available.', 'uptown-booking' ), array( 'status' => 409 ) );
		}

		global $wpdb;
		$row = array(
			'type_id'      => (int) $type->id,
			'host_id'      => (int) $type->host_id,
			'starts_at'    => $start->format( 'Y-m-d H:i:s' ),
			'ends_at'      => $end->format( 'Y-m-d H:i:s' ),
			'guest_name'   => $name,
			'guest_email'  => $email,
			'guest_phone'  => $phone,
			'guest_notes'  => $notes,
			'status'       => 'confirmed',
			'cancel_token' => wp_generate_password( 32, false, false ),
			'created_at'   => current_time( 'mysql' ),
		);
		$ok = $wpdb->insert( Uptown_Booking_DB::bookings_table(), $row );
		if ( ! $ok ) {
			return new WP_Error( 'ubk_save', __( 'Could not save the booking.', 'uptown-booking' ), array( 'status' => 500 ) );
		}

		$row['id'] = (int) $wpdb->insert_id;
		Uptown_Booking_Mail::send_confirmation( $row, $type );

		return rest_ensure_response(
			array(
				'ok'      => true,
				'booking' => array(
					'id'       => $row['id'],
					'title'    => $type->title,
					'host'     => $type->host_name,
					'role'     => $type->host_role,
					'location' => $type->location,
					'starts'   => $start->format( 'c' ),
					'when'     => wp_date( 'l, d. F Y · H:i', $start->getTimestamp() ),
					'end'      => $end->format( 'H:i' ),
					'duration' => (int) $type->duration_min,
				),
			)
		);
	}

	private static function type_payload( $type ) {
		return array(
			'id'          => (int) $type->id,
			'slug'        => $type->slug,
			'title'       => $type->title,
			'description' => $type->description,
			'duration'    => (int) $type->duration_min,
			'location'    => $type->location,
			'category'    => $type->category,
			'host'        => array(
				'id'   => (int) $type->host_id,
				'name' => $type->host_name,
				'role' => $type->host_role,
				'bio'  => isset( $type->host_bio ) ? $type->host_bio : '',
			),
		);
	}

	private static function rate_limited() {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
		$key = 'ubk_rl_' . md5( $ip );
		$n   = (int) get_transient( $key );
		if ( $n >= 12 ) {
			return true;
		}
		set_transient( $key, $n + 1, 10 * MINUTE_IN_SECONDS );
		return false;
	}
}
