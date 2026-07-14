<?php
/**
 * Fallback - FSE theme uses HTML templates.
 * @package Lahr_Editorial
 */
get_header();
?>
<main class="le-section">
    <div class="le-container">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <article><h1><?php the_title(); ?></h1><?php the_content(); ?></article>
        <?php endwhile; endif; ?>
    </div>
</main>
<?php get_footer(); ?>
