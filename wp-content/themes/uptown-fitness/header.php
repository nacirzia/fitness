<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main">Zum Inhalt springen</a>

<header class="site-header" data-header>
	<div class="nav-shell">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Uptown Fitness Startseite">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/uptownlogo.png' ); ?>" alt="Uptown Fitness">
		</a>

		<button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu" data-menu-toggle>
			<span></span><span></span><span></span>
			<span class="screen-reader-text">Menü öffnen</span>
		</button>

		<nav class="primary-nav" aria-label="Hauptnavigation" data-menu id="primary-menu">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'items_wrap'     => '<ul>%3$s</ul>',
						'depth'          => 2,
					)
				);
			} else {
				?>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/training/' ) ); ?>">Training</a></li>
					<li><a href="<?php echo esc_url( home_url( '/mitgliedschaft/' ) ); ?>">Mitgliedschaft</a></li>
					<li><a href="<?php echo esc_url( home_url( '/standorte/' ) ); ?>">Standorte</a></li>
					<li><a href="<?php echo esc_url( home_url( '/ueber-uns/' ) ); ?>">Über uns</a></li>
					<li><a href="<?php echo esc_url( home_url( '/magazin/' ) ); ?>">Magazin</a></li>
				</ul>
				<?php
			}
			?>
		</nav>

		<a class="nav-cta" href="<?php echo esc_url( home_url( '/probetraining/' ) ); ?>">
			<span>Kostenlos testen</span><i aria-hidden="true">↗</i>
		</a>
	</div>
</header>
