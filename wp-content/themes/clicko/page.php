<?php get_header(); ?>
<main id="main-content" class="clicko-shell clicko-page-content">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'clicko-content-card' . ( is_page( 'trade-in' ) ? ' clicko-content-card--trade-in' : '' ) ); ?>><p class="clicko-eyebrow">CLICKO / INFORMATION</p><h1><?php the_title(); ?></h1><div class="entry-content"><?php the_content(); ?></div></article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
