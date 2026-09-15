<?php
/**
 * Uptown Fitness theme setup.
 *
 * @package Uptown_Fitness
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function uptown_fitness_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	register_nav_menus(
		array(
			'primary' => __( 'Hauptnavigation', 'uptown-fitness' ),
			'footer'  => __( 'Footernavigation', 'uptown-fitness' ),
		)
	);
}
add_action( 'after_setup_theme', 'uptown_fitness_setup' );

function uptown_fitness_assets() {
	wp_enqueue_style(
		'uptown-fonts',
		'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Outfit:wght@700;800;900&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'uptown-style', get_stylesheet_uri(), array( 'uptown-fonts' ), filemtime( get_stylesheet_directory() . '/style.css' ) );
	wp_enqueue_script(
		'uptown-script',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		filemtime( get_template_directory() . '/assets/js/main.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'uptown_fitness_assets' );

function uptown_fitness_register_post_types() {
	register_post_type(
		'service',
		array(
			'labels'       => array(
				'name'          => __( 'Training', 'uptown-fitness' ),
				'singular_name' => __( 'Trainingsangebot', 'uptown-fitness' ),
			),
			'public'       => true,
			'has_archive'  => 'training',
			'rewrite'      => array( 'slug' => 'training' ),
			'menu_icon'    => 'dashicons-universal-access-alt',
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		)
	);

	register_post_type(
		'trainer',
		array(
			'labels'       => array(
				'name'          => __( 'Coaches', 'uptown-fitness' ),
				'singular_name' => __( 'Coach', 'uptown-fitness' ),
			),
			'public'       => true,
			'has_archive'  => 'coaches',
			'rewrite'      => array( 'slug' => 'coaches' ),
			'menu_icon'    => 'dashicons-groups',
			'show_in_rest' => true,
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'uptown_fitness_register_post_types' );

/**
 * Return the bundled editorial image key assigned to a page or post.
 */
function uptown_image_key( $post_id = 0, $fallback = 'facility' ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$key     = $post_id ? get_post_meta( $post_id, '_uptown_image', true ) : '';
	return $key ? sanitize_file_name( $key ) : $fallback;
}

/**
 * Return the bundled editorial image URL assigned to a page or post.
 */
function uptown_image( $post_id = 0, $fallback = 'facility' ) {
	return get_template_directory_uri() . '/assets/images/' . uptown_image_key( $post_id, $fallback ) . '.jpg';
}

/**
 * Nearby editorial photos so inner pages get a small gallery, not just one hero.
 */
function uptown_gallery_keys( $post_id = 0 ) {
	$primary = uptown_image_key( $post_id );
	$map     = array(
		'hero'       => array( 'hero', 'studio', 'weights' ),
		'community'  => array( 'community', 'group', 'classes' ),
		'trainer'    => array( 'trainer', 'woman', 'weights' ),
		'woman'      => array( 'woman', 'stretch', 'trainer' ),
		'facility'   => array( 'facility', 'studio', 'weights' ),
		'membership' => array( 'membership', 'hero', 'group' ),
		'yoga'       => array( 'yoga', 'stretch', 'recovery' ),
		'cardio'     => array( 'cardio', 'studio', 'functional' ),
		'weights'    => array( 'weights', 'functional', 'trainer' ),
		'functional' => array( 'functional', 'classes', 'group' ),
		'nutrition'  => array( 'nutrition', 'community', 'stretch' ),
		'recovery'   => array( 'recovery', 'yoga', 'stretch' ),
	);
	$keys = $map[ $primary ] ?? array( $primary, 'studio', 'group' );
	if ( 'classes' === $primary ) {
		$keys = array( 'classes', 'group', 'yoga' );
	}
	return $keys;
}

function uptown_inject_content_images( $content ) {
	if ( is_admin() || ! is_singular() || is_front_page() ) {
		return $content;
	}
	if ( has_shortcode( $content, 'uptown_booking' ) || false !== stripos( $content, 'class="ubk"' ) ) {
		return $content;
	}
	if ( false !== stripos( $content, '<img' ) ) {
		return $content;
	}

	$keys  = uptown_gallery_keys();
	$title = get_the_title();
	$main  = get_template_directory_uri() . '/assets/images/' . $keys[0] . '.jpg';
	$figure = '<figure class="content-figure image-reveal"><img src="' . esc_url( $main ) . '" alt="' . esc_attr( $title ) . '"><figcaption>' . esc_html( $title ) . ' bei Uptown Fitness</figcaption></figure>';

	$pos = strpos( $content, '</p>' );
	if ( false !== $pos ) {
		$content = substr( $content, 0, $pos + 4 ) . $figure . substr( $content, $pos + 4 );
	} else {
		$content = $figure . $content;
	}

	$gallery = '<div class="content-gallery">';
	foreach ( array_slice( $keys, 1 ) as $key ) {
		$gallery .= '<figure class="image-reveal"><img src="' . esc_url( get_template_directory_uri() . '/assets/images/' . $key . '.jpg' ) . '" alt="' . esc_attr( $title ) . '"></figure>';
	}
	$gallery .= '</div>';

	return $content . $gallery;
}
add_filter( 'the_content', 'uptown_inject_content_images', 12 );

/**
 * Short, SEO-friendly description for pages without a dedicated SEO plugin.
 */
function uptown_meta_description() {
	if ( is_singular() ) {
		$description = get_post_meta( get_the_ID(), '_uptown_meta_description', true );
		if ( ! $description ) {
			$description = wp_strip_all_tags( get_the_excerpt() );
		}
		if ( $description ) {
			echo '<meta name="description" content="' . esc_attr( wp_trim_words( $description, 28, '' ) ) . '">' . "\n";
		}
	} elseif ( is_front_page() ) {
		echo '<meta name="description" content="Uptown Fitness: Dein smartes 24/7 Gym mit modernem Equipment, starken Kursen und persönlichem Coaching. Jetzt kostenlos testen.">' . "\n";
	}
}
add_action( 'wp_head', 'uptown_meta_description', 2 );

function uptown_body_classes( $classes ) {
	$classes[] = 'uptown-ready';
	return $classes;
}
add_filter( 'body_class', 'uptown_body_classes' );

function uptown_excerpt_length() {
	return 26;
}
add_filter( 'excerpt_length', 'uptown_excerpt_length', 999 );

function uptown_excerpt_more() {
	return ' …';
}
add_filter( 'excerpt_more', 'uptown_excerpt_more' );

function uptown_handle_contact() {
	if ( empty( $_POST['uptown_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['uptown_nonce'] ) ), 'uptown_contact' ) ) {
		wp_safe_redirect( home_url( '/kontakt/' ) );
		exit;
	}
	if ( ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'sent', '1', home_url( '/kontakt/' ) ) );
		exit;
	}

	$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$topic   = sanitize_text_field( wp_unslash( $_POST['topic'] ?? 'Kontakt' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

	if ( $name && is_email( $email ) && $message ) {
		wp_mail(
			get_option( 'admin_email' ),
			'Uptown Fitness: ' . $topic,
			"Name: {$name}\nE-Mail: {$email}\nTelefon: {$phone}\n\n{$message}"
		);
	}

	wp_safe_redirect( add_query_arg( 'sent', '1', home_url( '/kontakt/' ) ) );
	exit;
}
add_action( 'admin_post_nopriv_uptown_contact', 'uptown_handle_contact' );
add_action( 'admin_post_uptown_contact', 'uptown_handle_contact' );

function uptown_mail_from() {
	return 'fitness@technativelabs.com';
}

function uptown_mail_from_name() {
	return 'Uptown Fitness';
}

add_filter( 'wp_mail_from', 'uptown_mail_from' );
add_filter( 'wp_mail_from_name', 'uptown_mail_from_name' );
