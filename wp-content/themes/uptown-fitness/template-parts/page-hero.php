<?php
$lead    = get_post_meta( get_the_ID(), '_uptown_lead', true );
$eyebrow = get_post_meta( get_the_ID(), '_uptown_eyebrow', true ) ?: 'UPTOWN FITNESS';
$image   = uptown_image();
$crumb   = isset( $args['crumb'] ) ? $args['crumb'] : '';
?>
<section class="page-hero">
	<img class="page-hero__photo" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
	<div class="page-hero__shade"></div>
	<div class="page-hero__content">
		<div class="breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Start</a>
			<span> / </span>
			<?php if ( $crumb ) : ?>
				<?php echo wp_kses_post( $crumb ); ?>
			<?php else : ?>
				<span><?php the_title(); ?></span>
			<?php endif; ?>
		</div>
		<p class="eyebrow reveal"><?php echo esc_html( $eyebrow ); ?></p>
		<h1 class="reveal"><?php the_title(); ?></h1>
		<?php if ( $lead ) : ?>
			<p class="page-hero__lead reveal"><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>
	</div>
</section>
