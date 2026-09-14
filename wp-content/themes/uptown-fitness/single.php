<?php
get_header();
while ( have_posts() ) :
	the_post();
	$type  = get_post_type();
	$label = 'post' === $type ? 'Magazin' : ( get_post_type_object( $type )->labels->name ?? 'Uptown' );
	$link  = get_post_type_archive_link( $type ) ?: home_url( '/magazin/' );
	get_template_part(
		'template-parts/page-hero',
		null,
		array(
			'crumb' => '<a href="' . esc_url( $link ) . '">' . esc_html( $label ) . '</a><span> / </span><span>' . esc_html( get_the_title() ) . '</span>',
		)
	);
	?>
	<main id="main">
		<article <?php post_class( 'page-content' ); ?>>
			<div class="prose">
				<?php the_content(); ?>
				<div class="callout">
					<h2>DEIN NÄCHSTER SCHRITT</h2>
					<p>Erlebe Uptown Fitness live und finde heraus, welches Training zu dir passt.</p>
					<a class="button button--light" href="<?php echo esc_url( home_url( '/probetraining/' ) ); ?>">Kostenlos testen <span>↗</span></a>
				</div>
			</div>
		</article>
	</main>
	<?php
endwhile;
get_footer();
