<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uptown_App_Plans {

	public static function get_plan( $member_id, $month = '' ) {
		global $wpdb;
		$month = $month ? substr( sanitize_text_field( $month ), 0, 7 ) : gmdate( 'Y-m' );
		$plan  = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . Uptown_App_DB::plans_table() . ' WHERE member_id = %d AND month = %s LIMIT 1',
				$member_id,
				$month
			),
			ARRAY_A
		);
		if ( ! $plan ) {
			return null;
		}
		return self::hydrate( $plan );
	}

	public static function hydrate( $plan ) {
		global $wpdb;
		$plan_id = (int) $plan['id'];
		$workouts = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . Uptown_App_DB::workouts_table() . ' WHERE plan_id = %d ORDER BY day_number ASC', $plan_id ),
			ARRAY_A
		);
		$meals = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . Uptown_App_DB::meals_table() . ' WHERE plan_id = %d ORDER BY day_number ASC, id ASC', $plan_id ),
			ARRAY_A
		);
		$targets = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM ' . Uptown_App_DB::targets_table() . ' WHERE plan_id = %d ORDER BY id ASC', $plan_id ),
			ARRAY_A
		);

		foreach ( $workouts as &$w ) {
			$w['exercises'] = json_decode( $w['exercises'], true ) ?: array();
		}
		foreach ( $meals as &$m ) {
			$m['items'] = json_decode( $m['items'], true ) ?: array();
		}

		$plan['workouts'] = $workouts;
		$plan['meals']    = $meals;
		$plan['targets']  = $targets;
		$plan['days']     = self::build_days( $plan['month'], $workouts, $meals );
		return $plan;
	}

	public static function generate_for_member( $member_id, $month = '' ) {
		global $wpdb;
		$month   = $month ? substr( $month, 0, 7 ) : gmdate( 'Y-m' );
		$profile = Uptown_App_Auth::get_profile( $member_id );
		$goal    = $profile['goal'] ?: 'general';
		$tpl     = self::templates( $goal, $profile );

		$existing = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT id FROM ' . Uptown_App_DB::plans_table() . ' WHERE member_id = %d AND month = %s',
				$member_id,
				$month
			)
		);
		if ( $existing ) {
			$wpdb->delete( Uptown_App_DB::workouts_table(), array( 'plan_id' => $existing ) );
			$wpdb->delete( Uptown_App_DB::meals_table(), array( 'plan_id' => $existing ) );
			$wpdb->delete( Uptown_App_DB::targets_table(), array( 'plan_id' => $existing ) );
			$wpdb->update(
				Uptown_App_DB::plans_table(),
				array(
					'title'            => $tpl['title'],
					'summary'          => $tpl['summary'],
					'calories_target'  => $tpl['calories'],
					'status'           => 'published',
				),
				array( 'id' => $existing )
			);
			$plan_id = (int) $existing;
		} else {
			$wpdb->insert(
				Uptown_App_DB::plans_table(),
				array(
					'member_id'       => $member_id,
					'month'           => $month,
					'title'           => $tpl['title'],
					'summary'         => $tpl['summary'],
					'calories_target' => $tpl['calories'],
					'status'          => 'published',
					'created_at'      => current_time( 'mysql' ),
				)
			);
			$plan_id = (int) $wpdb->insert_id;
		}

		$days_in_month = (int) gmdate( 't', strtotime( $month . '-01' ) );
		for ( $day = 1; $day <= $days_in_month; $day++ ) {
			$weekday = (int) gmdate( 'N', strtotime( sprintf( '%s-%02d', $month, $day ) ) );
			$workout = $tpl['week'][ $weekday ];
			$wpdb->insert(
				Uptown_App_DB::workouts_table(),
				array(
					'plan_id'      => $plan_id,
					'day_number'   => $day,
					'title'        => $workout['title'],
					'focus'        => $workout['focus'],
					'duration_min' => $workout['duration'],
					'exercises'    => wp_json_encode( $workout['exercises'] ),
				)
			);
			foreach ( $tpl['meals'] as $meal ) {
				$wpdb->insert(
					Uptown_App_DB::meals_table(),
					array(
						'plan_id'    => $plan_id,
						'day_number' => $day,
						'meal_type'  => $meal['type'],
						'title'      => $meal['title'],
						'items'      => wp_json_encode( $meal['items'] ),
						'calories'   => $meal['calories'],
					)
				);
			}
		}

		foreach ( $tpl['targets'] as $target ) {
			$wpdb->insert(
				Uptown_App_DB::targets_table(),
				array(
					'plan_id'       => $plan_id,
					'label'         => $target['label'],
					'current_value' => $target['current'],
					'target_value'  => $target['target'],
					'unit'          => $target['unit'],
				)
			);
		}

		return self::get_plan( $member_id, $month );
	}

	private static function build_days( $month, $workouts, $meals ) {
		$by_day = array();
		foreach ( $workouts as $w ) {
			$d = (int) $w['day_number'];
			$by_day[ $d ]['workout'] = $w;
		}
		foreach ( $meals as $m ) {
			$d = (int) $m['day_number'];
			$by_day[ $d ]['meals'][] = $m;
		}
		ksort( $by_day );
		$out = array();
		foreach ( $by_day as $day => $data ) {
			$date = sprintf( '%s-%02d', $month, $day );
			$out[] = array(
				'day'     => $day,
				'date'    => $date,
				'weekday' => gmdate( 'D', strtotime( $date ) ),
				'workout' => $data['workout'] ?? null,
				'meals'   => $data['meals'] ?? array(),
			);
		}
		return $out;
	}

	private static function templates( $goal, $profile ) {
		$weight = (float) ( $profile['weight_kg'] ?: 70 );
		$target = (float) ( $profile['target_weight_kg'] ?: $weight );
		$calories = 'fat_loss' === $goal ? 1800 : ( 'muscle' === $goal ? 2400 : 2100 );

		$strength_upper = array(
			'title' => 'Upper Strength',
			'focus' => 'Push / Pull',
			'duration' => 50,
			'exercises' => array(
				array( 'name' => 'Warm-up row', 'sets' => '5 min', 'notes' => 'Easy pace' ),
				array( 'name' => 'Bench press or chest press', 'sets' => '4 x 8', 'notes' => 'Controlled tempo' ),
				array( 'name' => 'Lat pulldown', 'sets' => '4 x 10', 'notes' => '' ),
				array( 'name' => 'Seated row', 'sets' => '3 x 12', 'notes' => '' ),
				array( 'name' => 'Overhead press', 'sets' => '3 x 8', 'notes' => '' ),
				array( 'name' => 'Face pulls + plank', 'sets' => '3 rounds', 'notes' => '40s plank' ),
			),
		);
		$strength_lower = array(
			'title' => 'Lower Strength',
			'focus' => 'Legs / Glutes',
			'duration' => 50,
			'exercises' => array(
				array( 'name' => 'Bike warm-up', 'sets' => '5 min', 'notes' => '' ),
				array( 'name' => 'Goblet squat', 'sets' => '4 x 8', 'notes' => 'Full depth' ),
				array( 'name' => 'Romanian deadlift', 'sets' => '4 x 8', 'notes' => 'Soft knees' ),
				array( 'name' => 'Walking lunges', 'sets' => '3 x 10/leg', 'notes' => '' ),
				array( 'name' => 'Hip thrust', 'sets' => '3 x 12', 'notes' => '' ),
				array( 'name' => 'Calf raises + dead bug', 'sets' => '3 rounds', 'notes' => '' ),
			),
		);
		$hiit = array(
			'title' => 'HIIT Conditioning',
			'focus' => 'Intervals',
			'duration' => 35,
			'exercises' => array(
				array( 'name' => 'Warm-up', 'sets' => '4 min', 'notes' => 'Easy cardio' ),
				array( 'name' => 'Bike or rower intervals', 'sets' => '10 x 40s on / 20s off', 'notes' => 'Hard but clean' ),
				array( 'name' => 'Kettlebell swings', 'sets' => '4 x 12', 'notes' => '' ),
				array( 'name' => 'Mountain climbers', 'sets' => '3 x 30s', 'notes' => '' ),
				array( 'name' => 'Walk-down', 'sets' => '4 min', 'notes' => '' ),
			),
		);
		$cardio = array(
			'title' => 'Cardio & Core',
			'focus' => 'Zone 2',
			'duration' => 40,
			'exercises' => array(
				array( 'name' => 'Treadmill or bike', 'sets' => '25 min', 'notes' => 'Conversational pace' ),
				array( 'name' => 'Side plank', 'sets' => '3 x 25s/side', 'notes' => '' ),
				array( 'name' => 'Bird dog', 'sets' => '3 x 8/side', 'notes' => '' ),
				array( 'name' => 'Dead bug', 'sets' => '3 x 8', 'notes' => '' ),
			),
		);
		$mobility = array(
			'title' => 'Mobility & Recovery',
			'focus' => 'Reset',
			'duration' => 30,
			'exercises' => array(
				array( 'name' => 'World’s greatest stretch', 'sets' => '2 x 6/side', 'notes' => '' ),
				array( 'name' => '90/90 hips', 'sets' => '2 min', 'notes' => '' ),
				array( 'name' => 'Thoracic openers', 'sets' => '2 x 8', 'notes' => '' ),
				array( 'name' => 'Easy walk', 'sets' => '15 min', 'notes' => 'Optional sunshine' ),
			),
		);
		$full = array(
			'title' => 'Full Body Engine',
			'focus' => 'Mixed',
			'duration' => 45,
			'exercises' => array(
				array( 'name' => 'Kettlebell goblet squat', 'sets' => '3 x 10', 'notes' => '' ),
				array( 'name' => 'Push-ups or incline push-ups', 'sets' => '3 x 8–12', 'notes' => '' ),
				array( 'name' => 'Dumbbell row', 'sets' => '3 x 10/side', 'notes' => '' ),
				array( 'name' => 'Farmer carry', 'sets' => '4 x 30m', 'notes' => '' ),
				array( 'name' => 'Bike finish', 'sets' => '8 min', 'notes' => 'Steady' ),
			),
		);

		$titles = array(
			'fat_loss'   => 'Fat-loss month: leaner, stronger, consistent',
			'muscle'     => 'Muscle month: progressive strength',
			'endurance'  => 'Endurance month: engine and recovery',
			'mobility'   => 'Mobility month: move better, hurt less',
			'general'    => 'Balanced Uptown month',
		);

		$meals = array(
			array( 'type' => 'breakfast', 'title' => 'Protein breakfast', 'calories' => 420, 'items' => array( 'Skyr or eggs', 'Oats or rye bread', 'Berries', 'Black coffee or tea' ) ),
			array( 'type' => 'lunch', 'title' => 'Club-ready lunch', 'calories' => 550, 'items' => array( 'Chicken, tofu or fish', 'Rice or potatoes', 'Large salad', 'Olive oil' ) ),
			array( 'type' => 'snack', 'title' => 'Smart snack', 'calories' => 220, 'items' => array( 'Greek yogurt or protein shake', 'Apple or banana', 'Handful of nuts' ) ),
			array( 'type' => 'dinner', 'title' => 'Simple dinner', 'calories' => 610, 'items' => array( 'Salmon, lentils or lean beef', 'Vegetables', 'Quinoa or bread', 'Optional dark chocolate' ) ),
		);

		return array(
			'title'    => $titles[ $goal ] ?? $titles['general'],
			'summary'  => 'Built from your profile: ' . $goal . '. Train 5–6 days, keep 1–2 easy days, and hit protein at every meal. Clubs are 24/7 — use Berlin Mitte, Hamburg or Köln as your home base.',
			'calories' => $calories,
			'week'     => array(
				1 => $strength_upper,
				2 => $cardio,
				3 => $strength_lower,
				4 => $hiit,
				5 => $full,
				6 => $mobility,
				7 => $mobility,
			),
			'meals'    => $meals,
			'targets'  => array(
				array( 'label' => 'Body weight', 'current' => (string) $weight, 'target' => (string) $target, 'unit' => 'kg' ),
				array( 'label' => 'Workouts this month', 'current' => '0', 'target' => '20', 'unit' => 'sessions' ),
				array( 'label' => 'Daily protein', 'current' => '0', 'target' => (string) max( 90, (int) round( $weight * 1.6 ) ), 'unit' => 'g' ),
				array( 'label' => 'Sleep', 'current' => '6.5', 'target' => '7.5', 'unit' => 'hours' ),
			),
		);
	}
}
