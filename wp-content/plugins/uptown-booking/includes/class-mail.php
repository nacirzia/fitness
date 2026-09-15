<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_Booking_Mail {

	public static function from_email() {
		$s = Uptown_Booking_Availability::settings();
		$email = sanitize_email( $s['from_email'] ?? '' );
		return $email ? $email : 'fitness@technativelabs.com';
	}

	public static function from_name() {
		$s = Uptown_Booking_Availability::settings();
		$name = sanitize_text_field( $s['from_name'] ?? '' );
		return $name ? $name : 'Uptown Fitness';
	}

	public static function headers( $reply_to = '' ) {
		$from = sprintf( '%s <%s>', self::from_name(), self::from_email() );
		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'From: ' . $from,
		);
		if ( $reply_to && is_email( $reply_to ) ) {
			$headers[] = 'Reply-To: ' . $reply_to;
		}
		return $headers;
	}

	public static function send( $to, $subject, $body, $reply_to = '' ) {
		add_filter( 'wp_mail_from', array( __CLASS__, 'from_email' ) );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'from_name' ) );
		$ok = wp_mail( $to, $subject, $body, self::headers( $reply_to ) );
		remove_filter( 'wp_mail_from', array( __CLASS__, 'from_email' ) );
		remove_filter( 'wp_mail_from_name', array( __CLASS__, 'from_name' ) );
		return $ok;
	}

	public static function send_confirmation( $booking, $type ) {
		$when = wp_date( 'l, d. F Y · H:i', strtotime( $booking['starts_at'] ) );
		$end  = wp_date( 'H:i', strtotime( $booking['ends_at'] ) );
		$cancel = home_url( '/buchen/?cancel=' . rawurlencode( $booking['cancel_token'] ) );

		$guest_body = sprintf(
			"Hallo %s,\n\nvielen Dank. Dein Termin ist bestätigt.\n\nThema: %s\nMit: %s (%s)\nZeit: %s–%s\nOrt: %s\n\nAbsagen: %s\n\nUptown Fitness\n",
			$booking['guest_name'],
			$type->title,
			$type->host_name,
			$type->host_role,
			$when,
			$end,
			$type->location ? $type->location : 'wird bestätigt',
			$cancel
		);

		$host_body = sprintf(
			"Neuer Termin\n\nThema: %s\nGast: %s\nE-Mail: %s\nTelefon: %s\nZeit: %s–%s\nNotiz: %s\n",
			$type->title,
			$booking['guest_name'],
			$booking['guest_email'],
			$booking['guest_phone'] ? $booking['guest_phone'] : '–',
			$when,
			$end,
			$booking['guest_notes'] ? $booking['guest_notes'] : '–'
		);

		self::send( $booking['guest_email'], 'Dein Uptown Termin: ' . $type->title, $guest_body, self::from_email() );
		if ( ! empty( $type->host_email ) ) {
			self::send( $type->host_email, 'Neuer Termin: ' . $booking['guest_name'], $host_body, $booking['guest_email'] );
		}
	}

	public static function send_cancelled( $booking, $type ) {
		$when = wp_date( 'l, d. F Y · H:i', strtotime( $booking->starts_at ) );
		$body = sprintf(
			"Der Termin am %s (%s) wurde abgesagt.\n\nUptown Fitness\n",
			$when,
			$type->title
		);
		self::send( $booking->guest_email, 'Termin abgesagt', $body );
		if ( ! empty( $type->host_email ) ) {
			self::send( $type->host_email, 'Termin abgesagt: ' . $booking->guest_name, $body );
		}
	}
}
