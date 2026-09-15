<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_Booking_Frontend {

	public static function init() {
		add_shortcode( 'uptown_booking', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'init', array( __CLASS__, 'maybe_cancel' ) );
	}

	public static function ensure_page() {
		$page = get_page_by_path( 'buchen' );
		if ( $page ) {
			return (int) $page->ID;
		}
		return wp_insert_post(
			array(
				'post_title'   => 'Termin buchen',
				'post_name'    => 'buchen',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '[uptown_booking]',
				'meta_input'   => array(
					'_uptown_eyebrow' => 'KALENDER',
					'_uptown_lead'    => 'Wähle dein Anliegen, ein Teammitglied und einen freien Termin. Alles direkt auf unserer Website.',
					'_uptown_image'   => 'trainer',
				),
			)
		);
	}

	public static function assets() {
		if ( ! self::has_shortcode() ) {
			return;
		}
		wp_enqueue_style(
			'uptown-booking',
			UPTOWN_BOOKING_URL . 'assets/css/booking.css',
			array(),
			UPTOWN_BOOKING_VERSION
		);
		wp_enqueue_script(
			'uptown-booking',
			UPTOWN_BOOKING_URL . 'assets/js/booking.js',
			array(),
			UPTOWN_BOOKING_VERSION,
			true
		);
		wp_localize_script(
			'uptown-booking',
			'UptownBooking',
			array(
				'restUrl' => esc_url_raw( rest_url( 'uptown-booking/v1/' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'agenda'  => isset( $_GET['agenda'] ) ? sanitize_key( wp_unslash( $_GET['agenda'] ) ) : '',
			)
		);
	}

	public static function shortcode( $atts ) {
		$atts = shortcode_atts( array( 'agenda' => '' ), $atts );
		$notice = '';
		if ( isset( $_GET['cancelled'] ) ) {
			$notice = '<div class="ubk-flash">Dein Termin wurde abgesagt.</div>';
		}
		ob_start();
		?>
		<div class="ubk" data-ubk data-agenda="<?php echo esc_attr( $atts['agenda'] ); ?>">
			<?php echo $notice; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div class="ubk-shell" data-ubk-root>
				<p class="ubk-loading">Kalender wird geladen…</p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function maybe_cancel() {
		if ( empty( $_GET['cancel'] ) || is_admin() ) {
			return;
		}
		$token = sanitize_text_field( wp_unslash( $_GET['cancel'] ) );
		if ( strlen( $token ) < 16 ) {
			return;
		}
		global $wpdb;
		$booking = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . Uptown_Booking_DB::bookings_table() . ' WHERE cancel_token = %s',
				$token
			)
		);
		if ( ! $booking || 'cancelled' === $booking->status ) {
			wp_safe_redirect( home_url( '/buchen/?cancelled=1' ) );
			exit;
		}
		$wpdb->update(
			Uptown_Booking_DB::bookings_table(),
			array( 'status' => 'cancelled' ),
			array( 'id' => (int) $booking->id )
		);
		$type = Uptown_Booking_DB::get_type_by_slug(
			$wpdb->get_var( $wpdb->prepare( 'SELECT slug FROM ' . Uptown_Booking_DB::types_table() . ' WHERE id = %d', $booking->type_id ) )
		);
		if ( $type ) {
			Uptown_Booking_Mail::send_cancelled( $booking, $type );
		}
		wp_safe_redirect( home_url( '/buchen/?cancelled=1' ) );
		exit;
	}

	private static function has_shortcode() {
		if ( ! is_singular() ) {
			return false;
		}
		$post = get_post();
		return $post && has_shortcode( $post->post_content, 'uptown_booking' );
	}
}
