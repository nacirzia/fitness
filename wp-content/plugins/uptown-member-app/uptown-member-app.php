<?php
/**
 * Plugin Name: Uptown Member App
 * Description: Member accounts, profiles, monthly workout/diet plans, and REST APIs for the Uptown Fitness mobile app.
 * Version: 1.0.0
 * Author: Uptown Fitness
 * Text Domain: uptown-member-app
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UPTOWN_APP_VERSION', '1.0.0' );
define( 'UPTOWN_APP_FILE', __FILE__ );
define( 'UPTOWN_APP_DIR', plugin_dir_path( __FILE__ ) );

require_once UPTOWN_APP_DIR . 'includes/class-db.php';
require_once UPTOWN_APP_DIR . 'includes/class-auth.php';
require_once UPTOWN_APP_DIR . 'includes/class-plans.php';
require_once UPTOWN_APP_DIR . 'includes/class-rest.php';

register_activation_hook(
	__FILE__,
	static function () {
		Uptown_App_DB::install();
		Uptown_App_DB::seed_demo();
	}
);

add_action(
	'plugins_loaded',
	static function () {
		Uptown_App_DB::maybe_upgrade();
		Uptown_App_REST::init();
	}
);

add_action(
	'admin_menu',
	static function () {
		add_menu_page(
			'Member App',
			'Member App',
			'manage_options',
			'uptown-member-app',
			static function () {
				$url = home_url( '/member-admin/' );
				echo '<div class="wrap"><h1>Uptown Member App</h1>';
				echo '<p>Manage members, profiles and monthly plans in the Tailwind admin panel.</p>';
				echo '<p><a class="button button-primary" href="' . esc_url( $url ) . '">Open admin panel</a></p>';
				echo '<p class="description">Demo login for the mobile app: <code>demo@uptown-fitness.de</code> / <code>Uptown2026!</code></p>';
				echo '</div>';
			},
			'dashicons-smartphone',
			59
		);
	}
);
