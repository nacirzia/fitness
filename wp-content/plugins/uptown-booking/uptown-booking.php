<?php
/**
 * Plugin Name: Uptown Booking
 * Description: Calendly-style appointment booking on your site. Visitors pick an agenda, a team member, and a time slot.
 * Version: 1.0.1
 * Author: Uptown Fitness
 * Text Domain: uptown-booking
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UPTOWN_BOOKING_VERSION', '1.0.1' );
define( 'UPTOWN_BOOKING_FILE', __FILE__ );
define( 'UPTOWN_BOOKING_DIR', plugin_dir_path( __FILE__ ) );
define( 'UPTOWN_BOOKING_URL', plugin_dir_url( __FILE__ ) );

require_once UPTOWN_BOOKING_DIR . 'includes/class-db.php';
require_once UPTOWN_BOOKING_DIR . 'includes/class-availability.php';
require_once UPTOWN_BOOKING_DIR . 'includes/class-mail.php';
require_once UPTOWN_BOOKING_DIR . 'includes/class-rest.php';
require_once UPTOWN_BOOKING_DIR . 'includes/class-admin.php';
require_once UPTOWN_BOOKING_DIR . 'includes/class-frontend.php';

register_activation_hook(
	__FILE__,
	static function () {
		Uptown_Booking_DB::install();
		Uptown_Booking_DB::seed();
		Uptown_Booking_Frontend::ensure_page();
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	static function () {
		flush_rewrite_rules();
	}
);

add_action(
	'plugins_loaded',
	static function () {
		Uptown_Booking_DB::maybe_upgrade();
		Uptown_Booking_REST::init();
		Uptown_Booking_Admin::init();
		Uptown_Booking_Frontend::init();
	}
);
