<?php
get_header();
while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-hero' );
	?>
	<main id="main">
		<article <?php post_class( 'page-content' ); ?>>
			<div class="prose">
				<?php the_content(); ?>
			</div>
		</article>
	</main>
	<?php
endwhile;
get_footer();
