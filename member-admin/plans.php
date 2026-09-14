<?php
require __DIR__ . '/bootstrap.php';
global $wpdb;

$plans = $wpdb->get_results(
	'SELECT p.*, m.display_name, m.email FROM ' . Uptown_App_DB::plans_table() . ' p
	 LEFT JOIN ' . Uptown_App_DB::members_table() . ' m ON m.id = p.member_id
	 ORDER BY p.month DESC, p.id DESC',
	ARRAY_A
);
uf_admin_header( 'Plans', 'plans' );
?>
<div class="bg-white rounded-2xl p-6 shadow-sm border border-zinc-200">
	<table class="w-full text-sm">
		<thead class="text-left text-xs uppercase tracking-wider text-zinc-500">
			<tr><th class="py-2">Month</th><th>Member</th><th>Title</th><th>Calories</th><th></th></tr>
		</thead>
		<tbody>
		<?php foreach ( $plans as $plan ) : ?>
			<tr class="border-t border-zinc-100">
				<td class="py-3 font-mono"><?php echo uf_admin_h( $plan['month'] ); ?></td>
				<td><?php echo uf_admin_h( $plan['display_name'] ); ?></td>
				<td><?php echo uf_admin_h( $plan['title'] ); ?></td>
				<td><?php echo (int) $plan['calories_target']; ?></td>
				<td class="text-right"><a class="text-uptown font-semibold" href="plan-edit.php?id=<?php echo (int) $plan['id']; ?>">Edit</a></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php uf_admin_footer(); ?>
