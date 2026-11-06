<?php
get_header();
while ( have_posts() ) : the_post();
	$product = wc_get_product( get_the_ID() );
	if ( ! $product ) { continue; }
?>
<main id="main-content" class="clicko-shell clicko-product-page">
	<nav class="clicko-breadcrumb" aria-label="ตำแหน่งหน้าเว็บ"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">หน้าแรก</a><span aria-hidden="true">/</span><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">สินค้าทั้งหมด</a><span aria-hidden="true">/</span><span><?php the_title(); ?></span></nav>
	<div class="clicko-product-detail">
		<div class="clicko-product-detail__media"><?php if ( $product->get_image_id() ) : ?><?php echo wp_kses_post( $product->get_image( 'woocommerce_single', array( 'class' => 'clicko-product-detail__image' ) ) ); ?><?php else : ?><div class="clicko-product-detail__placeholder">CLICKO<span>PRODUCT IMAGE</span></div><?php endif; ?></div>
		<div class="clicko-product-detail__info"><p class="clicko-eyebrow">CLICKO / <?php echo esc_html( $product->get_sku() ? 'SKU ' . $product->get_sku() : 'GAMING GEAR & PC PARTS' ); ?></p><h1><?php the_title(); ?></h1><div class="clicko-product-detail__price"><?php echo $product->get_price_html() ? wp_kses_post( $product->get_price_html() ) : '<span>สอบถามราคา</span>'; ?></div><div class="clicko-product-detail__description"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div><?php echo do_shortcode( '[clicko_product_action product_id="' . absint( $product->get_id() ) . '"]' ); ?><p class="clicko-product-detail__notice">ราคาและสินค้าคงเหลืออาจเปลี่ยนแปลง ผู้ขายจะยืนยันอีกครั้งทาง Messenger</p></div>
	</div>
	<?php if ( $product->get_description() ) : ?><section class="clicko-product-specs"><p class="clicko-eyebrow">PRODUCT DETAILS</p><h2>รายละเอียดสินค้า</h2><div class="entry-content"><?php echo wp_kses_post( apply_filters( 'the_content', $product->get_description() ) ); ?></div></section><?php endif; ?>
	<?php $related = wc_get_related_products( $product->get_id(), 4 ); if ( $related ) : ?><section class="clicko-related"><div class="clicko-section-heading"><div><p class="clicko-eyebrow">YOU MAY ALSO LIKE</p><h2>สินค้าใกล้เคียง</h2></div></div><div class="clicko-product-grid"><?php foreach ( $related as $related_id ) { clicko_product_card( wc_get_product( $related_id ) ); } ?></div></section><?php endif; ?>
</main>
<?php endwhile; get_footer(); ?>
