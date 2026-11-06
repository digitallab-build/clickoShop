<?php
get_header();
$page_title = is_product_category() ? single_term_title( '', false ) : 'อุปกรณ์คอมพิวเตอร์และเกมมิงเกียร์';
?>
<main id="main-content" class="clicko-shell clicko-page-content">
	<header class="clicko-archive-heading"><p class="clicko-eyebrow">SHOP / CLICKO</p><h1><?php echo esc_html( $page_title ); ?></h1><p>เลือกดูสินค้าและติดต่อผู้ขายเพื่อยืนยันรายละเอียด</p></header>
	<form class="clicko-search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"><label class="screen-reader-text" for="clicko-product-search">ค้นหาสินค้า</label><input id="clicko-product-search" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="ค้นหาชื่อ รุ่น หรือ SKU"><input type="hidden" name="post_type" value="product"><button class="clicko-button" type="submit">ค้นหา <span aria-hidden="true">→</span></button></form>
	<?php clicko_render_catalog_toolbar(); ?>
	<?php if ( function_exists( 'woocommerce_product_loop' ) && woocommerce_product_loop() ) : ?>
		<p class="clicko-muted clicko-result-count"><?php woocommerce_result_count(); ?></p>
		<div class="clicko-product-grid is-list"><?php while ( have_posts() ) : the_post(); clicko_product_card( wc_get_product( get_the_ID() ) ); endwhile; ?></div>
		<nav class="clicko-pagination" aria-label="แบ่งหน้าสินค้า"><?php echo wp_kses_post( paginate_links( array( 'prev_text' => '← ก่อนหน้า', 'next_text' => 'ถัดไป →' ) ) ); ?></nav>
	<?php else : ?><div class="clicko-empty-state"><p class="clicko-eyebrow">NO PRODUCTS FOUND</p><h2>ยังไม่พบสินค้าที่ค้นหา</h2><p>ลองเปลี่ยนคำค้นหา หรือกลับมาดูสินค้าใหม่ภายหลัง</p></div><?php endif; ?>
</main>
<?php get_footer(); ?>
