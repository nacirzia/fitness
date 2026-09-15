<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_Booking_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_uptown_booking_save_host', array( __CLASS__, 'save_host' ) );
		add_action( 'admin_post_uptown_booking_save_type', array( __CLASS__, 'save_type' ) );
		add_action( 'admin_post_uptown_booking_save_hours', array( __CLASS__, 'save_hours' ) );
		add_action( 'admin_post_uptown_booking_save_settings', array( __CLASS__, 'save_settings' ) );
		add_action( 'admin_post_uptown_booking_cancel', array( __CLASS__, 'cancel_booking' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function assets( $hook ) {
		if ( strpos( (string) $hook, 'uptown-booking' ) === false ) {
			return;
		}
		wp_enqueue_style(
			'uptown-booking-admin',
			UPTOWN_BOOKING_URL . 'assets/css/admin.css',
			array(),
			UPTOWN_BOOKING_VERSION
		);
	}

	public static function menu() {
		add_menu_page(
			'Bookings',
			'Bookings',
			'manage_options',
			'uptown-booking',
			array( __CLASS__, 'page_bookings' ),
			'dashicons-calendar-alt',
			57
		);
		add_submenu_page( 'uptown-booking', 'Bookings', 'Bookings', 'manage_options', 'uptown-booking', array( __CLASS__, 'page_bookings' ) );
		add_submenu_page( 'uptown-booking', 'Event types', 'Event types', 'manage_options', 'uptown-booking-types', array( __CLASS__, 'page_types' ) );
		add_submenu_page( 'uptown-booking', 'Team', 'Team', 'manage_options', 'uptown-booking-team', array( __CLASS__, 'page_team' ) );
		add_submenu_page( 'uptown-booking', 'Settings', 'Settings', 'manage_options', 'uptown-booking-settings', array( __CLASS__, 'page_settings' ) );
	}

	public static function page_bookings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		global $wpdb;
		$rows = $wpdb->get_results(
			'SELECT b.*, t.title AS type_title, h.name AS host_name
			FROM ' . Uptown_Booking_DB::bookings_table() . ' b
			LEFT JOIN ' . Uptown_Booking_DB::types_table() . ' t ON t.id = b.type_id
			LEFT JOIN ' . Uptown_Booking_DB::hosts_table() . ' h ON h.id = b.host_id
			ORDER BY b.starts_at DESC
			LIMIT 200'
		);
		echo '<div class="wrap ubk-admin"><h1>Bookings</h1>';
		echo '<p>Public calendar: <a href="' . esc_url( home_url( '/buchen/' ) ) . '" target="_blank">' . esc_html( home_url( '/buchen/' ) ) . '</a></p>';
		echo '<table class="widefat striped"><thead><tr><th>When</th><th>Guest</th><th>Agenda</th><th>Host</th><th>Status</th><th></th></tr></thead><tbody>';
		if ( ! $rows ) {
			echo '<tr><td colspan="6">No bookings yet.</td></tr>';
		}
		foreach ( $rows as $row ) {
			echo '<tr>';
			echo '<td>' . esc_html( wp_date( 'd.m.Y H:i', strtotime( $row->starts_at ) ) ) . '</td>';
			echo '<td>' . esc_html( $row->guest_name ) . '<br><a href="mailto:' . esc_attr( $row->guest_email ) . '">' . esc_html( $row->guest_email ) . '</a></td>';
			echo '<td>' . esc_html( $row->type_title ) . '</td>';
			echo '<td>' . esc_html( $row->host_name ) . '</td>';
			echo '<td>' . esc_html( $row->status ) . '</td>';
			echo '<td>';
			if ( 'confirmed' === $row->status ) {
				echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
				wp_nonce_field( 'uptown_booking_cancel' );
				echo '<input type="hidden" name="action" value="uptown_booking_cancel">';
				echo '<input type="hidden" name="id" value="' . (int) $row->id . '">';
				echo '<button class="button">Cancel</button></form>';
			}
			echo '</td></tr>';
		}
		echo '</tbody></table></div>';
	}

	public static function page_types() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		global $wpdb;
		$edit = isset( $_GET['edit'] ) ? (int) $_GET['edit'] : 0;
		$row  = $edit ? $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Uptown_Booking_DB::types_table() . ' WHERE id = %d', $edit ) ) : null;
		$hosts = $wpdb->get_results( 'SELECT * FROM ' . Uptown_Booking_DB::hosts_table() . ' ORDER BY name ASC' );
		$types = $wpdb->get_results(
			'SELECT t.*, h.name AS host_name FROM ' . Uptown_Booking_DB::types_table() . ' t
			LEFT JOIN ' . Uptown_Booking_DB::hosts_table() . ' h ON h.id = t.host_id
			ORDER BY t.sort_order ASC, t.title ASC'
		);
		$cats = Uptown_Booking_DB::categories();

		echo '<div class="wrap ubk-admin"><h1>Event types</h1>';
		echo '<div class="ubk-admin-grid">';
		echo '<form class="ubk-card" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'uptown_booking_save_type' );
		echo '<input type="hidden" name="action" value="uptown_booking_save_type">';
		echo '<input type="hidden" name="id" value="' . (int) $edit . '">';
		echo '<h2>' . ( $row ? 'Edit event type' : 'New event type' ) . '</h2>';
		self::field( 'title', 'Title', $row ? $row->title : '' );
		self::field( 'slug', 'Slug', $row ? $row->slug : '', 'text', 'Leave empty to auto-generate' );
		echo '<p><label>Agenda<br><select name="category">';
		foreach ( $cats as $key => $meta ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $row ? $row->category : '', $key, false ) . '>' . esc_html( $meta['de'] ) . '</option>';
		}
		echo '</select></label></p>';
		echo '<p><label>Assigned team member<br><select name="host_id">';
		foreach ( $hosts as $host ) {
			echo '<option value="' . (int) $host->id . '" ' . selected( $row ? $row->host_id : 0, $host->id, false ) . '>' . esc_html( $host->name . ' · ' . $host->role ) . '</option>';
		}
		echo '</select></label></p>';
		echo '<p><label>Description<br><textarea name="description" rows="3" class="large-text">' . esc_textarea( $row ? $row->description : '' ) . '</textarea></label></p>';
		self::field( 'duration_min', 'Duration (minutes)', $row ? $row->duration_min : 30, 'number' );
		self::field( 'buffer_min', 'Buffer after (minutes)', $row ? $row->buffer_min : 10, 'number' );
		self::field( 'location', 'Location / format', $row ? $row->location : 'Video' );
		echo '<p><label><input type="checkbox" name="status" value="active" ' . checked( $row ? $row->status : 'active', 'active', false ) . '> Active</label></p>';
		submit_button( $row ? 'Update event type' : 'Create event type' );
		echo '</form>';

		echo '<div class="ubk-card"><h2>All event types</h2><table class="widefat striped"><thead><tr><th>Title</th><th>Agenda</th><th>Host</th><th>Min</th><th></th></tr></thead><tbody>';
		foreach ( $types as $type ) {
			echo '<tr><td>' . esc_html( $type->title ) . '</td><td>' . esc_html( $type->category ) . '</td><td>' . esc_html( $type->host_name ) . '</td><td>' . (int) $type->duration_min . '</td>';
			echo '<td><a class="button" href="' . esc_url( admin_url( 'admin.php?page=uptown-booking-types&edit=' . (int) $type->id ) ) . '">Edit</a></td></tr>';
		}
		echo '</tbody></table></div></div></div>';
	}

	public static function page_team() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		global $wpdb;
		$edit  = isset( $_GET['edit'] ) ? (int) $_GET['edit'] : 0;
		$host  = $edit ? Uptown_Booking_DB::get_host( $edit ) : null;
		$hosts = $wpdb->get_results( 'SELECT * FROM ' . Uptown_Booking_DB::hosts_table() . ' ORDER BY name ASC' );
		$hours = $edit ? Uptown_Booking_Availability::hours_for_host( $edit ) : array();
		$by_day = array();
		foreach ( $hours as $hour ) {
			$by_day[ (int) $hour->weekday ] = $hour;
		}
		$days = array( 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday' );

		echo '<div class="wrap ubk-admin"><h1>Team</h1><div class="ubk-admin-grid">';
		echo '<form class="ubk-card" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'uptown_booking_save_host' );
		echo '<input type="hidden" name="action" value="uptown_booking_save_host">';
		echo '<input type="hidden" name="id" value="' . (int) $edit . '">';
		echo '<h2>' . ( $host ? 'Edit team member' : 'New team member' ) . '</h2>';
		self::field( 'name', 'Name', $host ? $host->name : '' );
		self::field( 'email', 'Email', $host ? $host->email : '', 'email' );
		self::field( 'role', 'Role', $host ? $host->role : '' );
		self::field( 'slug', 'Slug', $host ? $host->slug : '', 'text', 'Leave empty to auto-generate' );
		echo '<p><label>Bio<br><textarea name="bio" rows="3" class="large-text">' . esc_textarea( $host ? $host->bio : '' ) . '</textarea></label></p>';
		echo '<p><label><input type="checkbox" name="status" value="active" ' . checked( $host ? $host->status : 'active', 'active', false ) . '> Active</label></p>';
		submit_button( $host ? 'Update member' : 'Create member' );
		echo '</form>';

		echo '<div>';
		echo '<div class="ubk-card"><h2>All members</h2><table class="widefat striped"><thead><tr><th>Name</th><th>Role</th><th>Email</th><th></th></tr></thead><tbody>';
		foreach ( $hosts as $item ) {
			echo '<tr><td>' . esc_html( $item->name ) . '</td><td>' . esc_html( $item->role ) . '</td><td>' . esc_html( $item->email ) . '</td>';
			echo '<td><a class="button" href="' . esc_url( admin_url( 'admin.php?page=uptown-booking-team&edit=' . (int) $item->id ) ) . '">Edit & hours</a></td></tr>';
		}
		echo '</tbody></table></div>';

		if ( $host ) {
			echo '<form class="ubk-card" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
			wp_nonce_field( 'uptown_booking_save_hours' );
			echo '<input type="hidden" name="action" value="uptown_booking_save_hours">';
			echo '<input type="hidden" name="host_id" value="' . (int) $host->id . '">';
			echo '<h2>Weekly hours · ' . esc_html( $host->name ) . '</h2>';
			echo '<table class="widefat"><thead><tr><th>Day</th><th>Open</th><th>From</th><th>To</th></tr></thead><tbody>';
			foreach ( $days as $num => $label ) {
				$open = isset( $by_day[ $num ] );
				$start = $open ? substr( $by_day[ $num ]->start_time, 0, 5 ) : '09:00';
				$end   = $open ? substr( $by_day[ $num ]->end_time, 0, 5 ) : '17:00';
				echo '<tr><td>' . esc_html( $label ) . '</td>';
				echo '<td><input type="checkbox" name="open[' . $num . ']" value="1" ' . checked( $open, true, false ) . '></td>';
				echo '<td><input type="time" name="start[' . $num . ']" value="' . esc_attr( $start ) . '"></td>';
				echo '<td><input type="time" name="end[' . $num . ']" value="' . esc_attr( $end ) . '"></td></tr>';
			}
			echo '</tbody></table>';
			submit_button( 'Save hours' );
			echo '</form>';
		}
		echo '</div></div></div>';
	}

	public static function page_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s = Uptown_Booking_Availability::settings();
		echo '<div class="wrap ubk-admin"><h1>Booking settings</h1>';
		echo '<form class="ubk-card" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="max-width:560px">';
		wp_nonce_field( 'uptown_booking_save_settings' );
		echo '<input type="hidden" name="action" value="uptown_booking_save_settings">';
		self::field( 'min_notice_hours', 'Minimum notice (hours)', $s['min_notice_hours'], 'number' );
		self::field( 'max_days_ahead', 'Bookable days ahead', $s['max_days_ahead'], 'number' );
		self::field( 'from_name', 'Email from name', $s['from_name'] );
		self::field( 'from_email', 'Email from address', $s['from_email'], 'email' );
		echo '<p class="description">Booking mail is sent as this name and address instead of wordpress@… Timezone: ' . esc_html( wp_timezone_string() ) . '</p>';
		submit_button( 'Save settings' );
		echo '</form></div>';
	}

	public static function save_host() {
		self::guard( 'uptown_booking_save_host' );
		global $wpdb;
		$id     = (int) $_POST['id'];
		$name   = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$role   = sanitize_text_field( wp_unslash( $_POST['role'] ?? '' ) );
		$slug   = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) );
		$bio    = sanitize_textarea_field( wp_unslash( $_POST['bio'] ?? '' ) );
		$status = empty( $_POST['status'] ) ? 'inactive' : 'active';
		if ( ! $slug ) {
			$slug = sanitize_title( $name );
		}
		$data = array(
			'name'   => $name,
			'email'  => $email,
			'role'   => $role,
			'slug'   => $slug,
			'bio'    => $bio,
			'status' => $status,
		);
		if ( $id ) {
			$wpdb->update( Uptown_Booking_DB::hosts_table(), $data, array( 'id' => $id ) );
		} else {
			$data['timezone']   = 'Europe/Berlin';
			$data['created_at'] = current_time( 'mysql' );
			$wpdb->insert( Uptown_Booking_DB::hosts_table(), $data );
			$id = (int) $wpdb->insert_id;
			foreach ( array( 1, 2, 3, 4, 5 ) as $day ) {
				$wpdb->insert(
					Uptown_Booking_DB::hours_table(),
					array(
						'host_id'    => $id,
						'weekday'    => $day,
						'start_time' => '09:00:00',
						'end_time'   => '17:00:00',
					)
				);
			}
		}
		wp_safe_redirect( admin_url( 'admin.php?page=uptown-booking-team&edit=' . $id . '&saved=1' ) );
		exit;
	}

	public static function save_type() {
		self::guard( 'uptown_booking_save_type' );
		global $wpdb;
		$id    = (int) $_POST['id'];
		$title = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
		$slug  = sanitize_title( wp_unslash( $_POST['slug'] ?? '' ) );
		if ( ! $slug ) {
			$slug = sanitize_title( $title );
		}
		$data = array(
			'host_id'      => (int) ( $_POST['host_id'] ?? 0 ),
			'category'     => sanitize_key( wp_unslash( $_POST['category'] ?? 'fitness' ) ),
			'title'        => $title,
			'slug'         => $slug,
			'description'  => sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) ),
			'duration_min' => max( 10, (int) ( $_POST['duration_min'] ?? 30 ) ),
			'buffer_min'   => max( 0, (int) ( $_POST['buffer_min'] ?? 0 ) ),
			'location'     => sanitize_text_field( wp_unslash( $_POST['location'] ?? '' ) ),
			'status'       => empty( $_POST['status'] ) ? 'inactive' : 'active',
		);
		if ( $id ) {
			$wpdb->update( Uptown_Booking_DB::types_table(), $data, array( 'id' => $id ) );
		} else {
			$wpdb->insert( Uptown_Booking_DB::types_table(), $data );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=uptown-booking-types&saved=1' ) );
		exit;
	}

	public static function save_hours() {
		self::guard( 'uptown_booking_save_hours' );
		$host_id = (int) $_POST['host_id'];
		$rows    = array();
		foreach ( range( 1, 7 ) as $day ) {
			if ( empty( $_POST['open'][ $day ] ) ) {
				continue;
			}
			$rows[] = array(
				'weekday'    => $day,
				'start_time' => self::as_time( wp_unslash( $_POST['start'][ $day ] ?? '09:00' ) ),
				'end_time'   => self::as_time( wp_unslash( $_POST['end'][ $day ] ?? '17:00' ) ),
			);
		}
		Uptown_Booking_Availability::replace_hours( $host_id, $rows );
		wp_safe_redirect( admin_url( 'admin.php?page=uptown-booking-team&edit=' . $host_id . '&saved=1' ) );
		exit;
	}

	public static function save_settings() {
		self::guard( 'uptown_booking_save_settings' );
		update_option(
			'uptown_booking_settings',
			array(
				'min_notice_hours' => max( 0, (int) ( $_POST['min_notice_hours'] ?? 2 ) ),
				'max_days_ahead'   => max( 1, (int) ( $_POST['max_days_ahead'] ?? 28 ) ),
				'from_name'        => sanitize_text_field( wp_unslash( $_POST['from_name'] ?? 'Uptown Fitness' ) ),
				'from_email'       => sanitize_email( wp_unslash( $_POST['from_email'] ?? 'fitness@technativelabs.com' ) ) ?: 'fitness@technativelabs.com',
			)
		);
		wp_safe_redirect( admin_url( 'admin.php?page=uptown-booking-settings&saved=1' ) );
		exit;
	}

	public static function cancel_booking() {
		self::guard( 'uptown_booking_cancel' );
		global $wpdb;
		$id = (int) $_POST['id'];
		$booking = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Uptown_Booking_DB::bookings_table() . ' WHERE id = %d', $id ) );
		if ( $booking ) {
			$wpdb->update( Uptown_Booking_DB::bookings_table(), array( 'status' => 'cancelled' ), array( 'id' => $id ) );
			$slug = $wpdb->get_var( $wpdb->prepare( 'SELECT slug FROM ' . Uptown_Booking_DB::types_table() . ' WHERE id = %d', $booking->type_id ) );
			$type = $slug ? Uptown_Booking_DB::get_type_by_slug( $slug ) : null;
			if ( $type ) {
				Uptown_Booking_Mail::send_cancelled( $booking, $type );
			}
		}
		wp_safe_redirect( admin_url( 'admin.php?page=uptown-booking' ) );
		exit;
	}

	private static function as_time( $value ) {
		$value = sanitize_text_field( $value );
		if ( preg_match( '/^\d{2}:\d{2}$/', $value ) ) {
			return $value . ':00';
		}
		if ( preg_match( '/^\d{2}:\d{2}:\d{2}$/', $value ) ) {
			return $value;
		}
		return '09:00:00';
	}

	private static function field( $name, $label, $value, $type = 'text', $help = '' ) {
		echo '<p><label for="ubk-' . esc_attr( $name ) . '">' . esc_html( $label ) . '<br>';
		echo '<input id="ubk-' . esc_attr( $name ) . '" class="regular-text" type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
		echo '</label>';
		if ( $help ) {
			echo '<span class="description"> ' . esc_html( $help ) . '</span>';
		}
		echo '</p>';
	}

	private static function guard( $action ) {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( $action ) ) {
			wp_die( 'Not allowed' );
		}
	}
}
