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
							'temperature'     => 0.55,
							'maxOutputTokens' => 512,
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
		$text = trim( wp_strip_all_tags( (string) $text ) );

		if ( '' === $text ) {
			return new WP_Error( 'ucb_empty', __( 'The assistant returned an empty reply.', 'uptown-chatbot' ), array( 'status' => 502 ) );
		}

		return $text;
	}

	private static function system_prompt( $settings, $lang ) {
		$lang_label = 'de' === $lang ? 'German' : 'English';
		$name       = $settings['bot_name'] ? $settings['bot_name'] : 'Uptown';
		$site       = home_url( '/' );

		return "You are {$name}, the official chatbot for Uptown Fitness, a 24/7 smart gym.
Reply only in {$lang_label}. Be concise, friendly, and practical. Use short paragraphs.
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
