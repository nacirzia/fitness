<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_Booking_Mail {

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

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		wp_mail( $booking['guest_email'], 'Dein Uptown Termin: ' . $type->title, $guest_body, $headers );
		if ( ! empty( $type->host_email ) ) {
			wp_mail( $type->host_email, 'Neuer Termin: ' . $booking['guest_name'], $host_body, $headers );
		}
	}

	public static function send_cancelled( $booking, $type ) {
		$when = wp_date( 'l, d. F Y · H:i', strtotime( $booking->starts_at ) );
		$body = sprintf(
			"Der Termin am %s (%s) wurde abgesagt.\n\nUptown Fitness\n",
			$when,
			$type->title
		);
		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		wp_mail( $booking->guest_email, 'Termin abgesagt', $body, $headers );
		if ( ! empty( $type->host_email ) ) {
			wp_mail( $type->host_email, 'Termin abgesagt: ' . $booking->guest_name, $body, $headers );
		}
	}
}
