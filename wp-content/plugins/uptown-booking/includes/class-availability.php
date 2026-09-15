<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_Booking_Availability {

	public static function settings() {
		$defaults = array(
			'min_notice_hours' => 2,
			'max_days_ahead'   => 28,
			'from_name'        => 'Uptown Fitness',
			'from_email'       => 'fitness@technativelabs.com',
		);
		$saved = get_option( 'uptown_booking_settings', array() );
		return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
	}

	public static function hours_for_host( $host_id ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . Uptown_Booking_DB::hours_table() . ' WHERE host_id = %d ORDER BY weekday ASC, start_time ASC',
				$host_id
			)
		);
	}

	public static function replace_hours( $host_id, $rows ) {
		global $wpdb;
		$wpdb->delete( Uptown_Booking_DB::hours_table(), array( 'host_id' => (int) $host_id ) );
		foreach ( $rows as $row ) {
			if ( empty( $row['start_time'] ) || empty( $row['end_time'] ) ) {
				continue;
			}
			$wpdb->insert(
				Uptown_Booking_DB::hours_table(),
				array(
					'host_id'    => (int) $host_id,
					'weekday'    => (int) $row['weekday'],
					'start_time' => $row['start_time'],
					'end_time'   => $row['end_time'],
				)
			);
		}
	}

	public static function month_slots( $type, $from, $to ) {
		$settings = self::settings();
		$tz       = wp_timezone();
		$now      = new DateTimeImmutable( 'now', $tz );
		$min      = $now->modify( '+' . (int) $settings['min_notice_hours'] . ' hours' );
		$max      = $now->modify( '+' . (int) $settings['max_days_ahead'] . ' days' )->setTime( 23, 59, 59 );

		$from_dt = DateTimeImmutable::createFromFormat( 'Y-m-d', $from, $tz );
		$to_dt   = DateTimeImmutable::createFromFormat( 'Y-m-d', $to, $tz );
		if ( ! $from_dt || ! $to_dt ) {
			return array();
		}

		if ( $from_dt < $now->setTime( 0, 0, 0 ) ) {
			$from_dt = $now->setTime( 0, 0, 0 );
		}
		if ( $to_dt > $max ) {
			$to_dt = $max;
		}

		$hours = self::hours_for_host( $type->host_id );
		$by_day = array();
		foreach ( $hours as $row ) {
			$by_day[ (int) $row->weekday ][] = $row;
		}

		$booked = self::booked_range( $type->host_id, $from_dt->format( 'Y-m-d 00:00:00' ), $to_dt->format( 'Y-m-d 23:59:59' ) );
		$duration = (int) $type->duration_min;
		$buffer   = (int) $type->buffer_min;
		$step     = max( 5, $duration + $buffer );

		$out  = array();
		$day  = $from_dt;
		while ( $day <= $to_dt ) {
			$iso = (int) $day->format( 'N' );
			$key = $day->format( 'Y-m-d' );
			$out[ $key ] = array();
			if ( ! empty( $by_day[ $iso ] ) ) {
				foreach ( $by_day[ $iso ] as $window ) {
					$cursor = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $key . ' ' . $window->start_time, $tz );
					$end    = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $key . ' ' . $window->end_time, $tz );
					if ( ! $cursor || ! $end ) {
						continue;
					}
					while ( $cursor->modify( '+' . $duration . ' minutes' ) <= $end ) {
						$slot_end = $cursor->modify( '+' . $duration . ' minutes' );
						if ( $cursor >= $min && ! self::overlaps( $cursor, $slot_end, $booked ) ) {
							$out[ $key ][] = $cursor->format( 'H:i' );
						}
						$cursor = $cursor->modify( '+' . $step . ' minutes' );
					}
				}
			}
			$day = $day->modify( '+1 day' );
		}

		return $out;
	}

	public static function slot_datetime( $date, $time ) {
		$tz = wp_timezone();
		$dt = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $date . ' ' . $time, $tz );
		return $dt ?: null;
	}

	public static function is_free( $host_id, DateTimeImmutable $start, DateTimeImmutable $end, $ignore_id = 0 ) {
		global $wpdb;
		$sql = 'SELECT id FROM ' . Uptown_Booking_DB::bookings_table() . '
			WHERE host_id = %d AND status = %s AND starts_at < %s AND ends_at > %s';
		$args = array( $host_id, 'confirmed', $end->format( 'Y-m-d H:i:s' ), $start->format( 'Y-m-d H:i:s' ) );
		if ( $ignore_id ) {
			$sql   .= ' AND id != %d';
			$args[] = $ignore_id;
		}
		$sql .= ' LIMIT 1';
		return ! $wpdb->get_var( $wpdb->prepare( $sql, $args ) );
	}

	private static function booked_range( $host_id, $from, $to ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT starts_at, ends_at FROM ' . Uptown_Booking_DB::bookings_table() . '
				WHERE host_id = %d AND status = %s AND starts_at < %s AND ends_at > %s',
				$host_id,
				'confirmed',
				$to,
				$from
			)
		);
	}

	private static function overlaps( DateTimeImmutable $start, DateTimeImmutable $end, $booked ) {
		$s = $start->format( 'Y-m-d H:i:s' );
		$e = $end->format( 'Y-m-d H:i:s' );
		foreach ( $booked as $row ) {
			if ( $s < $row->ends_at && $e > $row->starts_at ) {
				return true;
			}
		}
		return false;
	}
}
