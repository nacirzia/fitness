<?php
require __DIR__ . '/bootstrap.php';
global $wpdb;

if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['create_member'] ) ) {
	check_admin_referer( 'uf_create_member' );
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$name  = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
	$pass  = (string) wp_unslash( $_POST['password'] ?? '' );
	$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	if ( $email && $name && strlen( $pass ) >= 6 ) {
		$wpdb->insert(
			Uptown_App_DB::members_table(),
			array(
				'email'        => $email,
				'password'     => wp_hash_password( $pass ),
				'display_name' => $name,
				'phone'        => $phone,
				'status'       => 'active',
				'created_at'   => current_time( 'mysql' ),
			)
		);
		$mid = (int) $wpdb->insert_id;
		Uptown_App_Auth::save_profile(
			$mid,
			array(
				'height_cm'        => $_POST['height_cm'] ?? '',
				'weight_kg'        => $_POST['weight_kg'] ?? '',
				'target_weight_kg' => $_POST['target_weight_kg'] ?? '',
				'age'              => $_POST['age'] ?? '',
				'gender'           => $_POST['gender'] ?? '',
				'goal'             => $_POST['goal'] ?? 'general',
				'activity_level'   => $_POST['activity_level'] ?? 'moderate',
				'experience'       => $_POST['experience'] ?? 'beginner',
			)
		);
		wp_safe_redirect( 'member-edit.php?id=' . $mid . '&created=1' );
		exit;
	}
}

$rows = $wpdb->get_results( 'SELECT * FROM ' . Uptown_App_DB::members_table() . ' ORDER BY id DESC', ARRAY_A );
uf_admin_header( 'Members', 'members' );
?>
<div class="grid lg:grid-cols-5 gap-6">
	<section class="lg:col-span-3 bg-white rounded-2xl p-6 shadow-sm border border-zinc-200">
		<table class="w-full text-sm">
			<thead class="text-left text-xs uppercase tracking-wider text-zinc-500">
				<tr><th class="py-2">Member</th><th>Email</th><th>Status</th><th></th></tr>
			</thead>
			<tbody>
			<?php foreach ( $rows as $row ) : ?>
				<tr class="border-t border-zinc-100">
					<td class="py-3 font-semibold"><?php echo uf_admin_h( $row['display_name'] ); ?></td>
					<td><?php echo uf_admin_h( $row['email'] ); ?></td>
					<td><span class="text-xs uppercase"><?php echo uf_admin_h( $row['status'] ); ?></span></td>
					<td class="text-right">
						<a class="text-uptown font-semibold" href="member-edit.php?id=<?php echo (int) $row['id']; ?>">Edit</a>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</section>
	<section class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-zinc-200">
		<h2 class="text-xl font-bold mb-4">New member</h2>
		<form method="post" class="space-y-3">
			<?php wp_nonce_field( 'uf_create_member' ); ?>
			<input type="hidden" name="create_member" value="1">
			<input class="w-full rounded-lg border border-zinc-200 px-3 py-2" name="display_name" placeholder="Full name" required>
			<input class="w-full rounded-lg border border-zinc-200 px-3 py-2" type="email" name="email" placeholder="Email" required>
			<input class="w-full rounded-lg border border-zinc-200 px-3 py-2" name="password" placeholder="Password (min 6)" required>
			<input class="w-full rounded-lg border border-zinc-200 px-3 py-2" name="phone" placeholder="Phone">
			<div class="grid grid-cols-2 gap-2">
				<input class="rounded-lg border border-zinc-200 px-3 py-2" name="height_cm" placeholder="Height cm">
				<input class="rounded-lg border border-zinc-200 px-3 py-2" name="weight_kg" placeholder="Weight kg">
				<input class="rounded-lg border border-zinc-200 px-3 py-2" name="target_weight_kg" placeholder="Target kg">
				<input class="rounded-lg border border-zinc-200 px-3 py-2" name="age" placeholder="Age">
			</div>
			<select class="w-full rounded-lg border border-zinc-200 px-3 py-2" name="goal">
				<option value="fat_loss">Fat loss</option>
				<option value="muscle">Build muscle</option>
				<option value="endurance">Endurance</option>
				<option value="mobility">Mobility</option>
				<option value="general">General fitness</option>
			</select>
			<select class="w-full rounded-lg border border-zinc-200 px-3 py-2" name="gender">
				<option value="female">Female</option>
				<option value="male">Male</option>
				<option value="other">Other</option>
			</select>
			<button class="w-full bg-uptown text-white rounded-lg py-2.5 font-bold">Create member</button>
		</form>
	</section>
</div>
<?php uf_admin_footer(); ?>
