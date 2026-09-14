<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_App_REST {

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'cors' ), 15 );
	}

	public static function cors() {
		remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
		add_filter(
			'rest_pre_serve_request',
			static function ( $value ) {
				header( 'Access-Control-Allow-Origin: *' );
				header( 'Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS' );
				header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
				header( 'Access-Control-Allow-Credentials: true' );
				return $value;
			}
		);
	}

	public static function routes() {
		$ns = 'uptown-app/v1';

		register_rest_route(
			$ns,
			'/login',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'login' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$ns,
			'/me',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'me' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$ns,
			'/profile',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'get_profile' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'PUT, POST',
					'callback'            => array( __CLASS__, 'save_profile' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		register_rest_route(
			$ns,
			'/plan',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'plan' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$ns,
			'/plan/(?P<month>\d{4}-\d{2})',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'plan' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function login( WP_REST_Request $request ) {
		$result = Uptown_App_Auth::login(
			(string) $request->get_param( 'email' ),
			(string) $request->get_param( 'password' )
		);
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$profile = Uptown_App_Auth::get_profile( $result['member']['id'] );
		$plan    = Uptown_App_Plans::get_plan( $result['member']['id'] );
		return rest_ensure_response(
			array(
				'token'   => $result['token'],
				'member'  => $result['member'],
				'profile' => $profile,
				'plan'    => $plan,
			)
		);
	}

	public static function me( WP_REST_Request $request ) {
		$member = self::need_member( $request );
		if ( is_wp_error( $member ) ) {
			return $member;
		}
		return rest_ensure_response(
			array(
				'member'  => Uptown_App_Auth::public_member( $member ),
				'profile' => Uptown_App_Auth::get_profile( $member->id ),
				'plan'    => Uptown_App_Plans::get_plan( $member->id ),
			)
		);
	}

	public static function get_profile( WP_REST_Request $request ) {
		$member = self::need_member( $request );
		if ( is_wp_error( $member ) ) {
			return $member;
		}
		return rest_ensure_response( Uptown_App_Auth::get_profile( $member->id ) );
	}

	public static function save_profile( WP_REST_Request $request ) {
		$member = self::need_member( $request );
		if ( is_wp_error( $member ) ) {
			return $member;
		}
		$saved = Uptown_App_Auth::save_profile( $member->id, $request->get_json_params() ?: $request->get_params() );
		return rest_ensure_response( $saved );
	}

	public static function plan( WP_REST_Request $request ) {
		$member = self::need_member( $request );
		if ( is_wp_error( $member ) ) {
			return $member;
		}
		$month = $request->get_param( 'month' );
		$plan  = Uptown_App_Plans::get_plan( $member->id, $month );
		if ( ! $plan ) {
			return new WP_Error( 'uf_plan', 'No plan published for this month yet.', array( 'status' => 404 ) );
		}
		return rest_ensure_response( $plan );
	}

	private static function need_member( WP_REST_Request $request ) {
		$member = Uptown_App_Auth::member_from_request( $request );
		if ( ! $member ) {
			return new WP_Error( 'uf_auth', 'Please log in.', array( 'status' => 401 ) );
		}
		return $member;
	}
}
