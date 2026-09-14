<?php get_header(); ?>
<main id="main" class="archive-main">
	<header class="archive-head">
		<p class="eyebrow reveal">UPTOWN MAGAZIN</p>
		<h1 class="reveal">WISSEN.<br>IMPULSE. FORTSCHRITT.</h1>
		<p class="reveal">Fundierte Trainingstipps, alltagstaugliche Ernährung und ehrliche Antworten auf die Fragen, die dich wirklich weiterbringen.</p>
	</header>
	<div class="archive-grid">
		<?php while ( have_posts() ) : the_post(); ?>
			<a class="archive-card reveal" href="<?php the_permalink(); ?>">
				<figure class="archive-card__image">
					<img src="<?php echo esc_url( uptown_image() ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
				</figure>
				<div class="archive-card__body">
					<span><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?> · MAGAZIN</span>
					<h2><?php the_title(); ?></h2>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					<i>Artikel lesen ↗</i>
				</div>
			</a>
		<?php endwhile; ?>
	</div>
</main>
<?php get_footer(); ?>
