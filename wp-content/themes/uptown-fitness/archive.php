<?php
get_header();
$title = is_post_type_archive( 'service' ) ? 'DEIN TRAINING' : ( is_post_type_archive( 'trainer' ) ? 'UNSERE COACHES' : get_the_archive_title() );
$lead  = is_post_type_archive( 'service' )
	? 'Kraft, Ausdauer, Mobility oder persönliche Begleitung: Finde das Training, das zu deinem Ziel und deinem Alltag passt.'
	: ( is_post_type_archive( 'trainer' ) ? 'Menschen mit Erfahrung, Energie und einem klaren Blick für deinen nächsten Schritt.' : 'Ideen, Wissen und Impulse für dein Training.' );
?>
<main id="main" class="archive-main">
	<header class="archive-head">
		<p class="eyebrow reveal">UPTOWN FITNESS</p>
		<h1 class="reveal"><?php echo wp_kses_post( $title ); ?></h1>
		<p class="reveal"><?php echo esc_html( $lead ); ?></p>
	</header>

	<div class="archive-grid">
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			<a class="archive-card reveal" href="<?php the_permalink(); ?>">
				<figure class="archive-card__image">
					<img src="<?php echo esc_url( uptown_image() ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
				</figure>
				<div class="archive-card__body">
					<span><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></span>
					<h2><?php the_title(); ?></h2>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					<i>Mehr erfahren ↗</i>
				</div>
			</a>
		<?php endwhile; else : ?>
			<p>Aktuell sind noch keine Beiträge vorhanden.</p>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
