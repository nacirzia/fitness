<?php
require __DIR__ . '/bootstrap.php';
global $wpdb;

$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
$plan = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . Uptown_App_DB::plans_table() . ' WHERE id = %d', $id ), ARRAY_A );
if ( ! $plan ) {
	wp_die( 'Plan not found.' );
}

if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
	check_admin_referer( 'uf_edit_plan' );
	$wpdb->update(
		Uptown_App_DB::plans_table(),
		array(
			'title'           => sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ),
			'summary'         => sanitize_textarea_field( wp_unslash( $_POST['summary'] ?? '' ) ),
			'calories_target' => (int) $_POST['calories_target'],
			'status'          => sanitize_key( $_POST['status'] ?? 'published' ),
		),
		array( 'id' => $id )
	);
	wp_safe_redirect( 'plan-edit.php?id=' . $id . '&saved=1' );
	exit;
}

$full = Uptown_App_Plans::hydrate( $plan );
uf_admin_header( $plan['month'] . ' plan', 'plans' );
?>
<?php if ( ! empty( $_GET['saved'] ) ) : ?><div class="mb-4 rounded-lg bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">Saved.</div><?php endif; ?>
<form method="post" class="bg-white rounded-2xl p-6 shadow-sm border border-zinc-200 space-y-3 mb-6">
	<?php wp_nonce_field( 'uf_edit_plan' ); ?>
	<label class="block text-sm font-semibold">Title<input class="mt-1 w-full rounded-lg border px-3 py-2" name="title" value="<?php echo uf_admin_h( $plan['title'] ); ?>"></label>
	<label class="block text-sm font-semibold">Summary<textarea class="mt-1 w-full rounded-lg border px-3 py-2" name="summary" rows="3"><?php echo uf_admin_h( $plan['summary'] ); ?></textarea></label>
	<div class="grid grid-cols-2 gap-3">
		<label class="text-sm font-semibold">Calories/day<input class="mt-1 w-full rounded-lg border px-3 py-2" name="calories_target" value="<?php echo (int) $plan['calories_target']; ?>"></label>
		<label class="text-sm font-semibold">Status
			<select class="mt-1 w-full rounded-lg border px-3 py-2" name="status">
				<option value="published" <?php selected( $plan['status'], 'published' ); ?>>Published</option>
				<option value="draft" <?php selected( $plan['status'], 'draft' ); ?>>Draft</option>
			</select>
		</label>
	</div>
	<button class="bg-ink text-white rounded-lg px-4 py-2 font-bold">Save plan</button>
</form>

<div class="grid md:grid-cols-3 gap-4 mb-6">
	<?php foreach ( $full['targets'] as $t ) : ?>
		<div class="bg-white rounded-2xl p-4 border border-zinc-200">
			<div class="text-xs uppercase text-zinc-500"><?php echo uf_admin_h( $t['label'] ); ?></div>
			<div class="text-2xl font-extrabold"><?php echo uf_admin_h( $t['current_value'] ); ?> → <?php echo uf_admin_h( $t['target_value'] ); ?> <span class="text-sm text-zinc-500"><?php echo uf_admin_h( $t['unit'] ); ?></span></div>
		</div>
	<?php endforeach; ?>
</div>

<div class="bg-white rounded-2xl p-6 border border-zinc-200">
	<h2 class="text-xl font-bold mb-4">Month calendar</h2>
	<div class="space-y-3 max-h-[640px] overflow-auto">
		<?php foreach ( $full['days'] as $day ) : ?>
			<div class="border border-zinc-100 rounded-xl p-4">
				<div class="font-bold"><?php echo uf_admin_h( $day['date'] . ' · ' . $day['weekday'] ); ?></div>
				<div class="text-sm text-uptown mt-1"><?php echo uf_admin_h( $day['workout']['title'] ?? 'Rest' ); ?> · <?php echo (int) ( $day['workout']['duration_min'] ?? 0 ); ?> min</div>
				<ul class="text-xs text-zinc-600 mt-2 list-disc ml-4">
					<?php foreach ( ( $day['workout']['exercises'] ?? array() ) as $ex ) : ?>
						<li><?php echo uf_admin_h( ( $ex['name'] ?? '' ) . ' — ' . ( $ex['sets'] ?? '' ) ); ?></li>
					<?php endforeach; ?>
				</ul>
				<div class="mt-2 text-xs text-zinc-500">
					<?php foreach ( $day['meals'] as $meal ) : ?>
						<span class="inline-block mr-3"><?php echo uf_admin_h( ucfirst( $meal['meal_type'] ) . ' ' . $meal['calories'] . ' kcal' ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
<?php uf_admin_footer(); ?>
