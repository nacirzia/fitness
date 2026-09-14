<?php

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
}

if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	auth_redirect();
	exit;
}

if ( ! class_exists( 'Uptown_App_DB' ) ) {
	wp_die( 'Activate the Uptown Member App plugin first.' );
}

function uf_admin_h( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' );
}

function uf_admin_header( $title, $active = 'dashboard' ) {
	$base = home_url( '/member-admin/' );
	$nav  = array(
		'dashboard' => array( 'Dashboard', $base ),
		'members'   => array( 'Members', $base . 'members.php' ),
		'plans'     => array( 'Plans', $base . 'plans.php' ),
	);
	echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
	echo '<title>' . uf_admin_h( $title ) . ' · Uptown Member Admin</title>';
	echo '<script src="https://cdn.tailwindcss.com"></script>';
	echo '<script>tailwind.config={theme:{extend:{colors:{uptown:"#f53c0f",ink:"#09090b"}}}}</script>';
	echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Outfit:wght@700;800&display=swap" rel="stylesheet">';
	echo '<style>body{font-family:Manrope,system-ui,sans-serif}h1,h2,h3{font-family:Outfit,sans-serif}</style>';
	echo '</head><body class="bg-zinc-100 text-zinc-900 min-h-screen">';
	echo '<div class="flex min-h-screen">';
	echo '<aside class="w-64 bg-ink text-white p-6 flex flex-col">';
	echo '<div class="text-2xl font-extrabold tracking-tight">UPTOWN<span class="text-uptown">.</span></div>';
	echo '<div class="text-xs uppercase tracking-widest text-zinc-400 mt-1">Member Admin</div>';
	echo '<nav class="mt-8 space-y-1 flex-1">';
	foreach ( $nav as $key => $item ) {
		$cls = $active === $key ? 'bg-uptown text-white' : 'text-zinc-300 hover:bg-white/10';
		echo '<a class="block rounded-lg px-3 py-2 text-sm font-semibold ' . $cls . '" href="' . uf_admin_h( $item[1] ) . '">' . uf_admin_h( $item[0] ) . '</a>';
	}
	echo '</nav>';
	echo '<a class="text-xs text-zinc-400 hover:text-white" href="' . uf_admin_h( wp_logout_url( home_url( '/member-admin/' ) ) ) . '">Log out</a>';
	echo '</aside><main class="flex-1 p-8">';
	echo '<div class="max-w-6xl mx-auto">';
	echo '<h1 class="text-4xl font-extrabold uppercase tracking-tight mb-6">' . uf_admin_h( $title ) . '</h1>';
}

function uf_admin_footer() {
	echo '</div></main></div></body></html>';
}
