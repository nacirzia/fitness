<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_Booking_DB {

	public static function hosts_table() {
		global $wpdb;
		return $wpdb->prefix . 'uf_book_hosts';
	}

	public static function types_table() {
		global $wpdb;
		return $wpdb->prefix . 'uf_book_types';
	}

	public static function hours_table() {
		global $wpdb;
		return $wpdb->prefix . 'uf_book_hours';
	}

	public static function bookings_table() {
		global $wpdb;
		return $wpdb->prefix . 'uf_book_bookings';
	}

	public static function maybe_upgrade() {
		if ( get_option( 'uptown_booking_db_version' ) !== '1.0.0' ) {
			self::install();
			self::seed();
			if ( class_exists( 'Uptown_Booking_Frontend' ) ) {
				Uptown_Booking_Frontend::ensure_page();
			}
		}
	}

	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();

		dbDelta(
			'CREATE TABLE ' . self::hosts_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(120) NOT NULL,
				email varchar(190) NOT NULL,
				role varchar(80) NOT NULL DEFAULT '',
				slug varchar(80) NOT NULL,
				bio text,
				timezone varchar(80) NOT NULL DEFAULT 'Europe/Berlin',
				status varchar(20) NOT NULL DEFAULT 'active',
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY slug (slug)
			) $charset;"
		);

		dbDelta(
			'CREATE TABLE ' . self::types_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				host_id bigint(20) unsigned NOT NULL,
				category varchar(40) NOT NULL,
				title varchar(160) NOT NULL,
				slug varchar(80) NOT NULL,
				description text,
				duration_min smallint(5) unsigned NOT NULL DEFAULT 30,
				buffer_min smallint(5) unsigned NOT NULL DEFAULT 0,
				location varchar(160) NOT NULL DEFAULT '',
				status varchar(20) NOT NULL DEFAULT 'active',
				sort_order smallint(5) unsigned NOT NULL DEFAULT 0,
				PRIMARY KEY  (id),
				UNIQUE KEY slug (slug),
				KEY host_id (host_id),
				KEY category (category)
			) $charset;"
		);

		dbDelta(
			'CREATE TABLE ' . self::hours_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				host_id bigint(20) unsigned NOT NULL,
				weekday tinyint(1) unsigned NOT NULL,
				start_time time NOT NULL,
				end_time time NOT NULL,
				PRIMARY KEY  (id),
				KEY host_week (host_id, weekday)
			) $charset;"
		);

		dbDelta(
			'CREATE TABLE ' . self::bookings_table() . " (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				type_id bigint(20) unsigned NOT NULL,
				host_id bigint(20) unsigned NOT NULL,
				starts_at datetime NOT NULL,
				ends_at datetime NOT NULL,
				guest_name varchar(120) NOT NULL,
				guest_email varchar(190) NOT NULL,
				guest_phone varchar(40) NOT NULL DEFAULT '',
				guest_notes text,
				status varchar(20) NOT NULL DEFAULT 'confirmed',
				cancel_token varchar(64) NOT NULL,
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY cancel_token (cancel_token),
				KEY host_time (host_id, starts_at),
				KEY status (status)
			) $charset;"
		);

		update_option( 'uptown_booking_db_version', '1.0.0' );
	}

	public static function categories() {
		return array(
			'fitness'   => array(
				'title' => __( 'Fitness program', 'uptown-booking' ),
				'de'    => 'Fitnessprogramm',
				'blurb' => 'Trainingsplan, Ziele und persönliches Coaching.',
			),
			'business'  => array(
				'title' => __( 'Business meeting', 'uptown-booking' ),
				'de'    => 'Business-Gespräch',
				'blurb' => 'Partnerschaften, Franchise und Zusammenarbeit.',
			),
			'marketing' => array(
				'title' => __( 'Marketing', 'uptown-booking' ),
				'de'    => 'Marketing',
				'blurb' => 'Kampagnen, Content und Markenauftritt.',
			),
			'sales'     => array(
				'title' => __( 'Sales & membership', 'uptown-booking' ),
				'de'    => 'Vertrieb & Mitgliedschaft',
				'blurb' => 'Tarife, Firmenfitness und Vertragsfragen.',
			),
		);
	}

	public static function seed() {
		global $wpdb;
		if ( (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . self::hosts_table() ) > 0 ) {
			return;
		}

		$now  = current_time( 'mysql' );
		$seed = array(
			array(
				'name'  => 'Jonas Weber',
				'email' => 'jonas@uptown-fitness.de',
				'role'  => 'Owner',
				'slug'  => 'jonas-weber',
				'bio'   => 'Gründer von Uptown Fitness. Gespräche zu Partnerschaft, Clubs und Zusammenarbeit.',
				'types' => array(
					array( 'business', 'Business-Gespräch mit dem Owner', 'business-owner', 'Ideen, Kooperationen und strategische Themen direkt mit Jonas.', 45, 'Video oder Club' ),
				),
			),
			array(
				'name'  => 'Lea Sommer',
				'email' => 'lea@uptown-fitness.de',
				'role'  => 'Head Coach',
				'slug'  => 'lea-sommer',
				'bio'   => 'Coaching, Trainingspläne und Ziele. Persönlich und klar.',
				'types' => array(
					array( 'fitness', 'Fitnessprogramm besprechen', 'fitness-coach', 'Ziele, Plan und nächste Schritte für dein Training.', 30, 'Club oder Video' ),
					array( 'fitness', 'Start-Check / Probetraining', 'fitness-trial', 'Clubtour, Zonen und ein erster Plan für die ersten Wochen.', 45, 'Im Club' ),
				),
			),
			array(
				'name'  => 'Mira Klein',
				'email' => 'mira@uptown-fitness.de',
				'role'  => 'Marketing',
				'slug'  => 'mira-klein',
				'bio'   => 'Kampagnen, Kooperationen und Content für die Marke Uptown.',
				'types' => array(
					array( 'marketing', 'Marketing-Gespräch', 'marketing-team', 'Kampagnen, Influencer, Content und Markenauftritt.', 30, 'Video' ),
				),
			),
			array(
				'name'  => 'Tom Richter',
				'email' => 'tom@uptown-fitness.de',
				'role'  => 'Sales',
				'slug'  => 'tom-richter',
				'bio'   => 'Mitgliedschaften, Firmenfitness und Tarifberatung.',
				'types' => array(
					array( 'sales', 'Mitgliedschaft & Vertrieb', 'sales-membership', 'Tarife, Firmenangebote und Vertragsfragen.', 20, 'Telefon oder Video' ),
				),
			),
		);

		foreach ( $seed as $host ) {
			$wpdb->insert(
				self::hosts_table(),
				array(
					'name'       => $host['name'],
					'email'      => $host['email'],
					'role'       => $host['role'],
					'slug'       => $host['slug'],
					'bio'        => $host['bio'],
					'timezone'   => 'Europe/Berlin',
					'status'     => 'active',
					'created_at' => $now,
				)
			);
			$host_id = (int) $wpdb->insert_id;

			foreach ( array( 1, 2, 3, 4, 5 ) as $day ) {
				$wpdb->insert(
					self::hours_table(),
					array(
						'host_id'    => $host_id,
						'weekday'    => $day,
						'start_time' => '09:00:00',
						'end_time'   => '17:00:00',
					)
				);
			}

			$order = 0;
			foreach ( $host['types'] as $type ) {
				$wpdb->insert(
					self::types_table(),
					array(
						'host_id'      => $host_id,
						'category'     => $type[0],
						'title'        => $type[1],
						'slug'         => $type[2],
						'description'  => $type[3],
						'duration_min' => $type[4],
						'buffer_min'   => 10,
						'location'     => $type[5],
						'status'       => 'active',
						'sort_order'   => $order++,
					)
				);
			}
		}
	}

	public static function get_host( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::hosts_table() . ' WHERE id = %d', $id ) );
	}

	public static function get_type_by_slug( $slug ) {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT t.*, h.name AS host_name, h.email AS host_email, h.role AS host_role, h.bio AS host_bio, h.timezone
				FROM ' . self::types_table() . ' t
				INNER JOIN ' . self::hosts_table() . ' h ON h.id = t.host_id
				WHERE t.slug = %s AND t.status = %s AND h.status = %s',
				$slug,
				'active',
				'active'
			)
		);
	}

	public static function active_types( $category = '' ) {
		global $wpdb;
		$sql = 'SELECT t.*, h.name AS host_name, h.role AS host_role, h.slug AS host_slug
			FROM ' . self::types_table() . ' t
			INNER JOIN ' . self::hosts_table() . " h ON h.id = t.host_id
			WHERE t.status = 'active' AND h.status = 'active'";
		$args = array();
		if ( $category ) {
			$sql   .= ' AND t.category = %s';
			$args[] = $category;
		}
		$sql .= ' ORDER BY t.sort_order ASC, t.title ASC';
		if ( $args ) {
			$sql = $wpdb->prepare( $sql, $args );
		}
		return $wpdb->get_results( $sql );
	}
}
