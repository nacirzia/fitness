<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_Chatbot_API {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function routes() {
		register_rest_route(
			'uptown-chatbot/v1',
			'/chat',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'chat' ),
				'permission_callback' => array( __CLASS__, 'public_nonce' ),
			)
		);

		register_rest_route(
			'uptown-chatbot/v1',
			'/test',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'test' ),
				'permission_callback' => static function () {
					return current_user_can( 'manage_options' );
				},
			)
		);
	}

	public static function public_nonce() {
		$nonce = '';
		if ( isset( $_SERVER['HTTP_X_WP_NONCE'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WP_NONCE'] ) );
		}
		return (bool) wp_verify_nonce( $nonce, 'wp_rest' );
	}

	public static function chat( WP_REST_Request $request ) {
		$settings = Uptown_Chatbot_Settings::get();

		if ( '1' !== $settings['enabled'] ) {
			return new WP_Error( 'ucb_disabled', __( 'The chatbot is currently turned off.', 'uptown-chatbot' ), array( 'status' => 403 ) );
		}

		if ( self::rate_limited() ) {
			return new WP_Error( 'ucb_rate', __( 'Please wait a moment before sending another message.', 'uptown-chatbot' ), array( 'status' => 429 ) );
		}

		$lang     = self::normalize_lang( $request->get_param( 'lang' ) );
		$message  = sanitize_text_field( (string) $request->get_param( 'message' ) );
		$history  = $request->get_param( 'history' );
		$history  = is_array( $history ) ? $history : array();

		if ( strlen( $message ) < 1 || strlen( $message ) > 1000 ) {
			return new WP_Error( 'ucb_message', __( 'Please enter a message (max. 1000 characters).', 'uptown-chatbot' ), array( 'status' => 400 ) );
		}

		$faq = self::faq_reply( $lang, $message );
		if ( $faq ) {
			return rest_ensure_response( array( 'reply' => $faq ) );
		}

		$result = self::generate( $settings, $lang, $message, $history );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( array( 'reply' => $result ) );
	}

	public static function test() {
		$settings = Uptown_Chatbot_Settings::get();
		$result   = self::generate( $settings, 'en', 'Reply with the single word OK.', array() );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response(
			array(
				'ok'      => true,
				'message' => __( 'Gemini connection works.', 'uptown-chatbot' ),
			)
		);
	}

	private static function generate( $settings, $lang, $message, $history ) {
		if ( empty( $settings['api_key'] ) ) {
			return new WP_Error( 'ucb_key', __( 'Gemini API key is missing. Add it in Chatbot settings.', 'uptown-chatbot' ), array( 'status' => 400 ) );
		}

		$contents = array();
		foreach ( array_slice( $history, -8 ) as $item ) {
			if ( empty( $item['role'] ) || empty( $item['text'] ) ) {
				continue;
			}
			$role = 'assistant' === $item['role'] || 'model' === $item['role'] ? 'model' : 'user';
			$contents[] = array(
				'role'  => $role,
				'parts' => array(
					array( 'text' => sanitize_text_field( substr( (string) $item['text'], 0, 1000 ) ) ),
				),
			);
		}

		$contents[] = array(
			'role'  => 'user',
			'parts' => array( array( 'text' => $message ) ),
		);

		$model = preg_replace( '/[^a-z0-9._-]/i', '', $settings['model'] );
		$url   = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent';

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 25,
				'headers' => array(
					'Content-Type'   => 'application/json',
					'x-goog-api-key' => $settings['api_key'],
				),
				'body'    => wp_json_encode(
					array(
						'systemInstruction' => array(
							'parts' => array(
								array( 'text' => self::system_prompt( $settings, $lang ) ),
							),
						),
						'contents'          => $contents,
						'generationConfig'  => array(
							'temperature'     => 0.4,
							'maxOutputTokens' => 2048,
							'thinkingConfig'  => array(
								'thinkingBudget' => 0,
							),
						),
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'ucb_http', __( 'Could not reach Gemini. Please try again.', 'uptown-chatbot' ), array( 'status' => 502 ) );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code >= 400 ) {
			$detail = '';
			if ( isset( $body['error']['message'] ) ) {
				$detail = ' ' . sanitize_text_field( $body['error']['message'] );
			}
			return new WP_Error( 'ucb_gemini', trim( __( 'Gemini request failed.', 'uptown-chatbot' ) . $detail ), array( 'status' => 502 ) );
		}

		$text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
		$text = self::plain_text( (string) $text );

		if ( '' === $text ) {
			return new WP_Error( 'ucb_empty', __( 'The assistant returned an empty reply.', 'uptown-chatbot' ), array( 'status' => 502 ) );
		}

		return $text;
	}

	private static function faq_reply( $lang, $message ) {
		$q  = strtolower( $message );
		$de = 'de' === $lang;

		if ( preg_match( '/andere frage|different question|other question/i', $q ) ) {
			return null;
		}

		if ( preg_match( '/price|pricing|cost|membership|fee|beitrag|preis|kostet|tarif|mitgliedschaft|flex|smart|complete/i', $q ) ) {
			return $de
				? "Wir haben drei Mitgliedschaften:\n\nFLEX 39,90 € / Monat: monatlich kündbar, Home-Club, App und digitaler Startplan.\nSMART 29,90 € / Monat: 12 Monate Laufzeit, Home-Club, App, Check-up und Startplan.\nCOMPLETE 49,90 € / Monat: alle Clubs, Kurse, monatlicher Coach-Check und erweiterte App-Analysen.\n\nEinmalige Startgebühr: 29 €. Vor Vertragsabschluss ist ein kostenloses Probetraining möglich. Mehr unter /preise/"
				: "We offer three membership plans:\n\nFLEX 39.90 € / month: monthly cancellation, home club, app, and a digital start plan.\nSMART 29.90 € / month: 12-month term, home club, app, check-up, and start plan.\nCOMPLETE 49.90 € / month: all clubs, regular classes, monthly coach check, and extended app analytics.\n\nOne-time start fee: 29 €. A free trial workout is available before you sign. More details: /preise/";
		}

		if ( preg_match( '/open|hours|opening|zeit|geöffnet|öffnungs/i', $q ) ) {
			return $de
				? "Unsere Clubs sind für aktive Mitglieder rund um die Uhr (24/7) per App-Check-in geöffnet.\n\nBetreute Zeiten: Montag–Freitag 08:00–20:00, Samstag 10:00–16:00.\nKontakt: hallo@uptown-fitness.de / +49 30 555 012 70."
				: "Clubs are open 24/7 for active members via app check-in.\n\nStaffed hours: Monday–Friday 08:00–20:00, Saturday 10:00–16:00.\nContact: hallo@uptown-fitness.de / +49 30 555 012 70.";
		}

		if ( preg_match( '/trial|probe|probetraining|test.?train/i', $q ) ) {
			return $de
				? "Du kannst vor Vertragsabschluss kostenlos Probe trainieren. Buche deinen Termin unter /probetraining/ oder schreib uns an hallo@uptown-fitness.de."
				: "You can try a free workout before signing. Book a slot at /probetraining/ or email hallo@uptown-fitness.de.";
		}

		return null;
	}

	private static function plain_text( $text ) {
		$text = wp_strip_all_tags( $text );
		$text = preg_replace( '/\*\*(.+?)\*\*/s', '$1', $text );
		$text = preg_replace( '/__(.+?)__/s', '$1', $text );
		$text = preg_replace( '/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '$1', $text );
		$text = preg_replace( '/^#{1,6}\s+/m', '', $text );
		$text = preg_replace( '/^\s*[-*]\s+/m', '• ', $text );
		return trim( (string) $text );
	}

	private static function system_prompt( $settings, $lang ) {
		$lang_label = 'de' === $lang ? 'German' : 'English';
		$name       = $settings['bot_name'] ? $settings['bot_name'] : 'Uptown';
		$site       = home_url( '/' );

		return "You are {$name}, the official chatbot for Uptown Fitness, a 24/7 smart gym.
Reply only in {$lang_label}. Be concise, friendly, and practical. Use short paragraphs and plain text only — no markdown, no asterisks, no bold.
Always finish the full answer. If you list memberships, include every plan with its complete price and a one-line description.
Answer from the gym knowledge below. If something is missing, say so and point the visitor to {$site}kontakt/ or hallo@uptown-fitness.de.
Do not invent prices, legal claims, or medical advice. Do not mention Gemini or that you are an AI model unless asked.
When useful, mention relevant site pages using root-relative paths such as /preise/ or /probetraining/.

GYM KNOWLEDGE:
{$settings['knowledge']}";
	}

	private static function normalize_lang( $lang ) {
		$lang = strtolower( (string) $lang );
		return ( 0 === strpos( $lang, 'de' ) ) ? 'de' : 'en';
	}

	private static function rate_limited() {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
		$key = 'ucb_rl_' . md5( $ip );
		$n   = (int) get_transient( $key );
		if ( $n >= 20 ) {
			return true;
		}
		set_transient( $key, $n + 1, 10 * MINUTE_IN_SECONDS );
		return false;
	}
}
