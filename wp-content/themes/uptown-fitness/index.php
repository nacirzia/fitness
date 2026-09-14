<?php get_header(); ?>

<main id="main" class="site-main">
    <div class="container">
        <?php
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                ?>
                <article <?php post_class( 'post' ); ?> id="post-<?php the_ID(); ?>">
                    <header class="entry-header">
                        <h1 class="entry-title"><?php the_title(); ?></h1>
                    </header>
                    
                    <?php if ( has_post_thumbnail() ) { ?>
                        <div class="entry-image">
                            <?php the_post_thumbnail( 'large' ); ?>
                        </div>
                    <?php } ?>
                    
                    <div class="entry-content">
                        <?php
                        the_content( sprintf(
                            wp_kses_post( __( 'Continue reading<span class="meta-nav">&nbsp;&raquo;</span>', 'uptown-fitness' ) ),
                            get_the_title()
                        ) );
                        
                        wp_link_pages( array(
                            'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'uptown-fitness' ),
                            'after'  => '</div>',
                        ) );
                        ?>
                    </div>
                </article>
                <?php
            }
        } else {
            echo '<p>' . esc_html__( 'No posts found.', 'uptown-fitness' ) . '</p>';
        }
        ?>
    </div>
</main>

<?php get_footer(); ?>
