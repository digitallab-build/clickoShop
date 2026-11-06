<?php
get_header();
$headline = get_theme_mod( 'clicko_home_headline', "รับซื้อและขาย\nคอมพิวเตอร์มือสอง\nและอุปกรณ์เกมมิง ครบ จบ ในที่เดียว" );
$lines = preg_split( '/\r\n|\r|\n/', $headline );
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/products/' );
?>
<main id="main-content">
	<section class="clicko-hero">
		<div class="clicko-shell clicko-hero__grid">
			<div class="clicko-hero__copy">
				<p class="clicko-eyebrow"><?php echo esc_html( get_theme_mod( 'clicko_home_eyebrow', 'CLICKO / GAMING GEAR & PC PARTS' ) ); ?></p>
				<h1>
					<?php echo esc_html( $lines[0] ?? '' ); ?>
					<?php if ( isset( $lines[1] ) ) : ?><span><?php echo esc_html( $lines[1] ); ?></span><?php endif; ?>
					<?php if ( isset( $lines[2] ) ) : ?><small><?php echo esc_html( implode( ' ', array_slice( $lines, 2 ) ) ); ?></small><?php endif; ?>
				</h1>
				<p class="clicko-hero__description"><?php echo esc_html( get_theme_mod( 'clicko_home_description', 'รวมคอมพิวเตอร์มือสองและอุปกรณ์เกมมิง เลือกสินค้าที่สนใจแล้วสอบถามผู้ขายได้โดยตรง' ) ); ?></p>
				<div class="clicko-hero__actions">
					<a class="clicko-button" href="<?php echo esc_url( $shop_url ); ?>">ดูสินค้าทั้งหมด <span aria-hidden="true">→</span></a>
					<a class="clicko-text-link" href="<?php echo esc_url( home_url( '/how-to-buy/' ) ); ?>">วิธีซื้อสินค้า <span aria-hidden="true">↗</span></a>
				</div>
			</div>
			<div class="clicko-hero__visual">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-setup.svg' ); ?>" alt="" width="1000" height="620" fetchpriority="high">
			</div>
		</div>
	</section>

	<section class="clicko-category-strip" aria-label="หมวดหมู่สินค้า">
		<div class="clicko-shell clicko-category-strip__items"><?php clicko_render_categories(); ?></div>
	</section>

	<section class="clicko-section clicko-section--products">
		<div class="clicko-shell">
			<div class="clicko-section-heading">
				<div><p class="clicko-eyebrow">CLICKO PICKS</p><h2>สินค้าแนะนำ</h2><p class="clicko-section-subtitle">สินค้าและอุปกรณ์ที่น่าสนใจจาก Clicko</p></div>
				<a class="clicko-text-link" href="<?php echo esc_url( $shop_url ); ?>">สินค้าทั้งหมด <span aria-hidden="true">→</span></a>
			</div>
			<?php clicko_render_catalog_toolbar(); ?>
			<div class="clicko-product-grid is-list"><?php clicko_render_featured_products(); ?></div>
			<p class="clicko-price-note">ราคาและสินค้าคงเหลือยืนยันกับผู้ขายอีกครั้งก่อนซื้อ</p>
		</div>
	</section>

	<section class="clicko-home-contact">
		<div class="clicko-shell clicko-home-contact__inner">
			<div><p class="clicko-eyebrow">NEED HELP?</p><h2>สนใจชิ้นไหน<br><span>สอบถามผู้ขายได้เลย</span></h2><p>เลือกสินค้าที่สนใจ คัดลอกรายการ แล้วส่งให้ผู้ขายยืนยันรายละเอียดใน Messenger</p></div>
			<a class="clicko-button clicko-button--white" href="<?php echo esc_url( home_url( '/selection/' ) ); ?>">ดูรายการที่สนใจ <span aria-hidden="true">→</span></a>
		</div>
	</section>
	<?php while ( have_posts() ) : the_post(); if ( trim( wp_strip_all_tags( get_the_content() ) ) ) : ?><section class="clicko-shell clicko-home-content"><div class="entry-content"><?php the_content(); ?></div></section><?php endif; endwhile; ?>
</main>
<?php get_footer(); ?>

\n