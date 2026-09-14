<?php
/**
 * Plugin Name: Uptown Chatbot
 * Description: Bilingual gym chatbot (DE/EN) powered by Gemini, with a Tawk-style widget and theme settings.
 * Version: 1.0.0
 * Author: Uptown Fitness
 * Text Domain: uptown-chatbot
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UPTOWN_CHATBOT_VERSION', '1.0.0' );
define( 'UPTOWN_CHATBOT_FILE', __FILE__ );
define( 'UPTOWN_CHATBOT_DIR', plugin_dir_path( __FILE__ ) );
define( 'UPTOWN_CHATBOT_URL', plugin_dir_url( __FILE__ ) );

require_once UPTOWN_CHATBOT_DIR . 'includes/class-settings.php';
require_once UPTOWN_CHATBOT_DIR . 'includes/class-api.php';
require_once UPTOWN_CHATBOT_DIR . 'includes/class-frontend.php';

add_action(
	'plugins_loaded',
	static function () {
		Uptown_Chatbot_Settings::init();
		Uptown_Chatbot_API::init();
		Uptown_Chatbot_Frontend::init();
	}
);

register_activation_hook(
	__FILE__,
	static function () {
		if ( false === get_option( 'uptown_chatbot_settings', false ) ) {
			add_option( 'uptown_chatbot_settings', Uptown_Chatbot_Settings::defaults() );
		}
	}
);
