<?php get_header(); ?>
<main id="main-content" class="clicko-shell clicko-page-content">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'clicko-content-card' ); ?>><h1><?php the_title(); ?></h1><div class="entry-content"><?php the_content(); ?></div></article>
	<?php endwhile; the_posts_pagination(); else : ?><p>ยังไม่มีเนื้อหา</p><?php endif; ?>
</main>
<?php get_footer(); ?>
