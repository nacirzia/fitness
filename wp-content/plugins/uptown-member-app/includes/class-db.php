<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_App_DB {

	public static function members_table() {
		global $wpdb;
		return $wpdb->prefix . 'uf_members';
	}

	public static function profiles_table() {
		global $wpdb;
		return $wpdb->prefix . 'uf_profiles';
	}

	public static function plans_table() {
		global $wpdb;
		return $wpdb->prefix . 'uf_plans';
	}

	public static function workouts_table() {
		global $wpdb;
		return $wpdb->prefix . 'uf_workouts';
	}

	public static function meals_table() {
		global $wpdb;
		return $wpdb->prefix . 'uf_meals';
	}

	public static function targets_table() {
		global $wpdb;
		return $wpdb->prefix . 'uf_targets';
	}

	public static function tokens_table() {
		global $wpdb;
		return $wpdb->prefix . 'uf_tokens';
	}

	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();

		dbDelta(
			'CREATE TABLE ' . self::members_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				email varchar(190) NOT NULL,
				password varchar(255) NOT NULL,
				display_name varchar(120) NOT NULL,
				phone varchar(40) NOT NULL DEFAULT '',
				status varchar(20) NOT NULL DEFAULT 'active',
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY email (email)
			) $charset;"
		);

		dbDelta(
			'CREATE TABLE ' . self::profiles_table() . " (
				member_id bigint(20) unsigned NOT NULL,
				height_cm decimal(5,1) DEFAULT NULL,
				weight_kg decimal(5,1) DEFAULT NULL,
				target_weight_kg decimal(5,1) DEFAULT NULL,
				age smallint(5) unsigned DEFAULT NULL,
				gender varchar(20) NOT NULL DEFAULT '',
				goal varchar(40) NOT NULL DEFAULT 'general',
				activity_level varchar(40) NOT NULL DEFAULT 'moderate',
				experience varchar(40) NOT NULL DEFAULT 'beginner',
				injuries text,
				notes text,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (member_id)
			) $charset;"
		);

		dbDelta(
			'CREATE TABLE ' . self::plans_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				member_id bigint(20) unsigned NOT NULL,
				month char(7) NOT NULL,
				title varchar(180) NOT NULL,
				summary text,
				calories_target int(11) NOT NULL DEFAULT 0,
				status varchar(20) NOT NULL DEFAULT 'published',
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY member_month (member_id, month)
			) $charset;"
		);

		dbDelta(
			'CREATE TABLE ' . self::workouts_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				plan_id bigint(20) unsigned NOT NULL,
				day_number tinyint(3) unsigned NOT NULL,
				title varchar(180) NOT NULL,
				focus varchar(80) NOT NULL DEFAULT '',
				duration_min smallint(5) unsigned NOT NULL DEFAULT 45,
				exercises longtext NOT NULL,
				PRIMARY KEY  (id),
				KEY plan_day (plan_id, day_number)
			) $charset;"
		);

		dbDelta(
			'CREATE TABLE ' . self::meals_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				plan_id bigint(20) unsigned NOT NULL,
				day_number tinyint(3) unsigned NOT NULL,
				meal_type varchar(20) NOT NULL,
				title varchar(180) NOT NULL,
				items longtext NOT NULL,
				calories smallint(5) unsigned NOT NULL DEFAULT 0,
				PRIMARY KEY  (id),
				KEY plan_day (plan_id, day_number)
			) $charset;"
		);

		dbDelta(
			'CREATE TABLE ' . self::targets_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				plan_id bigint(20) unsigned NOT NULL,
				label varchar(120) NOT NULL,
				current_value varchar(40) NOT NULL DEFAULT '',
				target_value varchar(40) NOT NULL,
				unit varchar(20) NOT NULL DEFAULT '',
				PRIMARY KEY  (id),
				KEY plan_id (plan_id)
			) $charset;"
		);

		dbDelta(
			'CREATE TABLE ' . self::tokens_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				member_id bigint(20) unsigned NOT NULL,
				token_hash char(64) NOT NULL,
				expires_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY token_hash (token_hash),
				KEY member_id (member_id)
			) $charset;"
		);

		update_option( 'uptown_app_db_version', '1.0.0' );
	}

	public static function maybe_upgrade() {
		if ( get_option( 'uptown_app_db_version' ) !== '1.0.0' ) {
			self::install();
		}
		self::seed_demo();
	}

	public static function seed_demo() {
		global $wpdb;
		$email = 'demo@uptown-fitness.de';
		$existing = $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . self::members_table() . ' WHERE email = %s', $email ) );
		if ( $existing ) {
			return (int) $existing;
		}

		$wpdb->insert(
			self::members_table(),
			array(
				'email'        => $email,
				'password'     => wp_hash_password( 'Uptown2026!' ),
				'display_name' => 'Lea Sommer',
				'phone'        => '+49 30 555 012 70',
				'status'       => 'active',
				'created_at'   => current_time( 'mysql' ),
			)
		);
		$member_id = (int) $wpdb->insert_id;

		$wpdb->replace(
			self::profiles_table(),
			array(
				'member_id'         => $member_id,
				'height_cm'         => 168.0,
				'weight_kg'         => 64.0,
				'target_weight_kg'  => 60.0,
				'age'               => 29,
				'gender'            => 'female',
				'goal'              => 'fat_loss',
				'activity_level'    => 'moderate',
				'experience'        => 'intermediate',
				'injuries'          => '',
				'notes'             => 'Prefers morning sessions and 45–50 minute workouts.',
				'updated_at'        => current_time( 'mysql' ),
			)
		);

		Uptown_App_Plans::generate_for_member( $member_id, gmdate( 'Y-m' ) );
		return $member_id;
	}
}
