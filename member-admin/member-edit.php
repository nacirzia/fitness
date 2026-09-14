<?php
require __DIR__ . '/bootstrap.php';
global $wpdb;

$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
$member = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Uptown_App_DB::members_table() . ' WHERE id = %d', $id ), ARRAY_A );
if ( ! $member ) {
	wp_die( 'Member not found.' );
}
$profile = Uptown_App_Auth::get_profile( $id );

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	check_admin_referer( 'uf_edit_member' );
	if ( ! empty( $_POST['save_member'] ) ) {
		$update = array(
			'display_name' => sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) ),
			'phone'        => sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) ),
			'status'       => sanitize_key( $_POST['status'] ?? 'active' ),
		);
		if ( ! empty( $_POST['password'] ) ) {
			$update['password'] = wp_hash_password( (string) wp_unslash( $_POST['password'] ) );
		}
		$wpdb->update( Uptown_App_DB::members_table(), $update, array( 'id' => $id ) );
		Uptown_App_Auth::save_profile( $id, wp_unslash( $_POST ) );
		wp_safe_redirect( 'member-edit.php?id=' . $id . '&saved=1' );
		exit;
	}
	if ( ! empty( $_POST['generate_plan'] ) ) {
		$month = sanitize_text_field( wp_unslash( $_POST['month'] ?? gmdate( 'Y-m' ) ) );
		Uptown_App_Plans::generate_for_member( $id, $month );
		wp_safe_redirect( 'plans.php?member=' . $id . '&month=' . rawurlencode( $month ) );
		exit;
	}
}

$plans = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Uptown_App_DB::plans_table() . ' WHERE member_id = %d ORDER BY month DESC', $id ), ARRAY_A );
uf_admin_header( $member['display_name'], 'members' );
?>
<?php if ( ! empty( $_GET['saved'] ) ) : ?><div class="mb-4 rounded-lg bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">Saved.</div><?php endif; ?>
<div class="grid lg:grid-cols-2 gap-6">
	<form method="post" class="bg-white rounded-2xl p-6 shadow-sm border border-zinc-200 space-y-3">
		<?php wp_nonce_field( 'uf_edit_member' ); ?>
		<input type="hidden" name="save_member" value="1">
		<label class="block text-sm font-semibold">Name<input class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-2" name="display_name" value="<?php echo uf_admin_h( $member['display_name'] ); ?>"></label>
		<label class="block text-sm font-semibold">Email<input class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-2 bg-zinc-50" value="<?php echo uf_admin_h( $member['email'] ); ?>" disabled></label>
		<label class="block text-sm font-semibold">New password<input class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-2" name="password" placeholder="Leave blank to keep"></label>
		<label class="block text-sm font-semibold">Phone<input class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-2" name="phone" value="<?php echo uf_admin_h( $member['phone'] ); ?>"></label>
		<label class="block text-sm font-semibold">Status
			<select class="mt-1 w-full rounded-lg border border-zinc-200 px-3 py-2" name="status">
				<option value="active" <?php selected( $member['status'], 'active' ); ?>>Active</option>
				<option value="paused" <?php selected( $member['status'], 'paused' ); ?>>Paused</option>
			</select>
		</label>
		<div class="grid grid-cols-2 gap-3">
			<label class="text-sm font-semibold">Height (cm)<input class="mt-1 w-full rounded-lg border px-3 py-2" name="height_cm" value="<?php echo uf_admin_h( $profile['height_cm'] ); ?>"></label>
			<label class="text-sm font-semibold">Weight (kg)<input class="mt-1 w-full rounded-lg border px-3 py-2" name="weight_kg" value="<?php echo uf_admin_h( $profile['weight_kg'] ); ?>"></label>
			<label class="text-sm font-semibold">Target (kg)<input class="mt-1 w-full rounded-lg border px-3 py-2" name="target_weight_kg" value="<?php echo uf_admin_h( $profile['target_weight_kg'] ); ?>"></label>
			<label class="text-sm font-semibold">Age<input class="mt-1 w-full rounded-lg border px-3 py-2" name="age" value="<?php echo uf_admin_h( $profile['age'] ); ?>"></label>
		</div>
		<label class="block text-sm font-semibold">Goal
			<select class="mt-1 w-full rounded-lg border px-3 py-2" name="goal">
				<?php foreach ( array( 'fat_loss' => 'Fat loss', 'muscle' => 'Muscle', 'endurance' => 'Endurance', 'mobility' => 'Mobility', 'general' => 'General' ) as $k => $lab ) : ?>
					<option value="<?php echo uf_admin_h( $k ); ?>" <?php selected( $profile['goal'], $k ); ?>><?php echo uf_admin_h( $lab ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label class="block text-sm font-semibold">Activity
			<select class="mt-1 w-full rounded-lg border px-3 py-2" name="activity_level">
				<?php foreach ( array( 'low', 'moderate', 'high' ) as $lvl ) : ?>
					<option value="<?php echo $lvl; ?>" <?php selected( $profile['activity_level'], $lvl ); ?>><?php echo ucfirst( $lvl ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label class="block text-sm font-semibold">Experience
			<select class="mt-1 w-full rounded-lg border px-3 py-2" name="experience">
				<?php foreach ( array( 'beginner', 'intermediate', 'advanced' ) as $lvl ) : ?>
					<option value="<?php echo $lvl; ?>" <?php selected( $profile['experience'], $lvl ); ?>><?php echo ucfirst( $lvl ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label class="block text-sm font-semibold">Injuries / notes
			<textarea class="mt-1 w-full rounded-lg border px-3 py-2" name="injuries" rows="3"><?php echo uf_admin_h( $profile['injuries'] ); ?></textarea>
		</label>
		<button class="bg-ink text-white rounded-lg px-4 py-2 font-bold">Save profile</button>
	</form>
	<div class="space-y-6">
		<form method="post" class="bg-white rounded-2xl p-6 shadow-sm border border-zinc-200">
			<?php wp_nonce_field( 'uf_edit_member' ); ?>
			<h2 class="text-xl font-bold mb-3">Generate monthly plan</h2>
			<p class="text-sm text-zinc-600 mb-3">Creates workouts, diet and targets from this member’s goal and body data.</p>
			<input type="month" name="month" value="<?php echo uf_admin_h( gmdate( 'Y-m' ) ); ?>" class="rounded-lg border px-3 py-2 mb-3 w-full">
			<button class="bg-uptown text-white rounded-lg px-4 py-2 font-bold" name="generate_plan" value="1">Generate plan</button>
		</form>
		<div class="bg-white rounded-2xl p-6 shadow-sm border border-zinc-200">
			<h2 class="text-xl font-bold mb-3">Plans</h2>
			<ul class="text-sm space-y-2">
				<?php foreach ( $plans as $plan ) : ?>
					<li class="flex justify-between border-b border-zinc-100 py-2">
						<span><?php echo uf_admin_h( $plan['month'] . ' · ' . $plan['title'] ); ?></span>
						<a class="text-uptown font-semibold" href="plan-edit.php?id=<?php echo (int) $plan['id']; ?>">Open</a>
					</li>
				<?php endforeach; ?>
				<?php if ( ! $plans ) : ?><li class="text-zinc-500">No plans yet.</li><?php endif; ?>
			</ul>
		</div>
	</div>
</div>
<?php uf_admin_footer(); ?>
