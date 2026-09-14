<?php
require __DIR__ . '/bootstrap.php';
global $wpdb;

$members = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Uptown_App_DB::members_table() );
$plans   = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . Uptown_App_DB::plans_table() );
$latest  = $wpdb->get_results( 'SELECT id, display_name, email, created_at FROM ' . Uptown_App_DB::members_table() . ' ORDER BY id DESC LIMIT 8', ARRAY_A );

uf_admin_header( 'Dashboard', 'dashboard' );
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
	<div class="bg-white rounded-2xl p-6 shadow-sm border border-zinc-200">
		<div class="text-xs uppercase tracking-widest text-zinc-500">Members</div>
		<div class="text-4xl font-extrabold mt-2"><?php echo (int) $members; ?></div>
	</div>
	<div class="bg-white rounded-2xl p-6 shadow-sm border border-zinc-200">
		<div class="text-xs uppercase tracking-widest text-zinc-500">Monthly plans</div>
		<div class="text-4xl font-extrabold mt-2"><?php echo (int) $plans; ?></div>
	</div>
	<div class="bg-white rounded-2xl p-6 shadow-sm border border-zinc-200">
		<div class="text-xs uppercase tracking-widest text-zinc-500">Mobile demo</div>
		<p class="mt-2 text-sm text-zinc-600">demo@uptown-fitness.de<br><span class="font-mono">Uptown2026!</span></p>
	</div>
</div>
<div class="bg-white rounded-2xl p-6 shadow-sm border border-zinc-200">
	<div class="flex items-center justify-between mb-4">
		<h2 class="text-xl font-bold">Latest members</h2>
		<a class="text-sm font-semibold text-uptown" href="members.php">View all</a>
	</div>
	<table class="w-full text-sm">
		<thead class="text-left text-zinc-500 uppercase text-xs tracking-wider">
			<tr><th class="py-2">Name</th><th>Email</th><th>Created</th></tr>
		</thead>
		<tbody>
		<?php foreach ( $latest as $row ) : ?>
			<tr class="border-t border-zinc-100">
				<td class="py-3 font-semibold"><?php echo uf_admin_h( $row['display_name'] ); ?></td>
				<td><?php echo uf_admin_h( $row['email'] ); ?></td>
				<td><?php echo uf_admin_h( $row['created_at'] ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php uf_admin_footer(); ?>
