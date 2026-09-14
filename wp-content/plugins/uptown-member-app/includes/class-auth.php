<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_App_Auth {

	public static function login( $email, $password ) {
		global $wpdb;
		$email = sanitize_email( $email );
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . Uptown_App_DB::members_table() . ' WHERE email = %s LIMIT 1',
				$email
			)
		);

		if ( ! $row || 'active' !== $row->status || ! wp_check_password( $password, $row->password ) ) {
			return new WP_Error( 'uf_login', 'Invalid email or password.', array( 'status' => 401 ) );
		}

		$token = bin2hex( random_bytes( 32 ) );
		$wpdb->insert(
			Uptown_App_DB::tokens_table(),
			array(
				'member_id'  => (int) $row->id,
				'token_hash' => hash( 'sha256', $token ),
				'expires_at' => gmdate( 'Y-m-d H:i:s', time() + 30 * DAY_IN_SECONDS ),
			)
		);

		return array(
			'token'  => $token,
			'member' => self::public_member( $row ),
		);
	}

	public static function member_from_request( WP_REST_Request $request ) {
		$header = $request->get_header( 'authorization' );
		$token  = '';
		if ( $header && preg_match( '/Bearer\s+(.+)/i', $header, $m ) ) {
			$token = trim( $m[1] );
		}
		if ( ! $token ) {
			$token = (string) $request->get_param( 'token' );
		}
		return self::member_from_token( $token );
	}

	public static function member_from_token( $token ) {
		global $wpdb;
		$token = trim( (string) $token );
		if ( strlen( $token ) < 20 ) {
			return null;
		}
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT m.* FROM ' . Uptown_App_DB::members_table() . ' m
				INNER JOIN ' . Uptown_App_DB::tokens_table() . ' t ON t.member_id = m.id
				WHERE t.token_hash = %s AND t.expires_at > UTC_TIMESTAMP() AND m.status = %s
				LIMIT 1',
				hash( 'sha256', $token ),
				'active'
			)
		);
		return $row ?: null;
	}

	public static function public_member( $row ) {
		return array(
			'id'           => (int) $row->id,
			'email'        => $row->email,
			'display_name' => $row->display_name,
			'phone'        => $row->phone,
			'status'       => $row->status,
		);
	}

	public static function get_profile( $member_id ) {
		global $wpdb;
		$profile = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . Uptown_App_DB::profiles_table() . ' WHERE member_id = %d',
				$member_id
			),
			ARRAY_A
		);
		if ( ! $profile ) {
			return array(
				'member_id'         => (int) $member_id,
				'height_cm'         => null,
				'weight_kg'         => null,
				'target_weight_kg'  => null,
				'age'               => null,
				'gender'            => '',
				'goal'              => 'general',
				'activity_level'    => 'moderate',
				'experience'        => 'beginner',
				'injuries'          => '',
				'notes'             => '',
			);
		}
		return $profile;
	}

	public static function save_profile( $member_id, $data ) {
		global $wpdb;
		$fields = array(
			'member_id'        => (int) $member_id,
			'height_cm'        => self::num_or_null( $data['height_cm'] ?? null ),
			'weight_kg'        => self::num_or_null( $data['weight_kg'] ?? null ),
			'target_weight_kg' => self::num_or_null( $data['target_weight_kg'] ?? null ),
			'age'              => isset( $data['age'] ) && '' !== $data['age'] ? (int) $data['age'] : null,
			'gender'           => sanitize_text_field( $data['gender'] ?? '' ),
			'goal'             => sanitize_key( $data['goal'] ?? 'general' ),
			'activity_level'   => sanitize_key( $data['activity_level'] ?? 'moderate' ),
			'experience'       => sanitize_key( $data['experience'] ?? 'beginner' ),
			'injuries'         => sanitize_textarea_field( $data['injuries'] ?? '' ),
			'notes'            => sanitize_textarea_field( $data['notes'] ?? '' ),
			'updated_at'       => current_time( 'mysql' ),
		);
		$wpdb->replace( Uptown_App_DB::profiles_table(), $fields );
		return self::get_profile( $member_id );
	}

	private static function num_or_null( $value ) {
		if ( null === $value || '' === $value ) {
			return null;
		}
		return round( (float) $value, 1 );
	}
}
