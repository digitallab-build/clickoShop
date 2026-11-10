<?php
get_header();

$headline      = get_theme_mod( 'clicko_home_headline', "ซื้อขายคอมพิวเตอร์มือสอง\nและอุปกรณ์เกมมิง\nครบ จบ ในที่เดียว" );
$lines         = preg_split( '/\r\n|\r|\n/', $headline );
$shop_url      = home_url( '/products/' );
$trade_in_url  = home_url( '/trade-in/' );
$categories    = array(
	array( 'display', 'จอเกมมิง', 'MONITOR' ),
	array( 'gpu', 'การ์ดจอ', 'GPU' ),
	array( 'cpu', 'CPU', 'CPU' ),
	array( 'motherboard', 'เมนบอร์ด', 'MOTHERBOARD' ),
	array( 'keyboard', 'คีย์บอร์ด', 'KEYBOARD' ),
	array( 'mouse', 'เมาส์', 'MOUSE' ),
	array( 'audio', 'หูฟัง', 'HEADSET' ),
	array( 'other', 'อุปกรณ์อื่น ๆ', 'GAMING GEAR' ),
);
?>
<main id="main-content" class="clicko-home">
	<section class="clicko-hero">
		<div class="clicko-shell clicko-hero__grid">
			<div class="clicko-hero__copy">
				<p class="clicko-eyebrow"><?php echo esc_html( get_theme_mod( 'clicko_home_eyebrow', 'CLICKO / GAMING GEAR & PC PARTS' ) ); ?></p>
				<h1>
					<?php echo esc_html( $lines[0] ?? '' ); ?>
					<?php if ( isset( $lines[1] ) ) : ?><span><?php echo esc_html( $lines[1] ); ?></span><?php endif; ?>
					<?php if ( isset( $lines[2] ) ) : ?><small><?php echo esc_html( implode( ' ', array_slice( $lines, 2 ) ) ); ?></small><?php endif; ?>
				</h1>
				<p class="clicko-hero__description"><?php echo esc_html( get_theme_mod( 'clicko_home_description', 'เลือกชมคอมพิวเตอร์มือสองและอุปกรณ์เกมมิง พร้อมสอบถามรายละเอียดกับผู้ขายโดยตรง' ) ); ?></p>
				<div class="clicko-hero__actions">
					<a class="clicko-button" href="<?php echo esc_url( $shop_url ); ?>">เลือกดูหมวดสินค้า <span aria-hidden="true">→</span></a>
					<a class="clicko-text-link" href="<?php echo esc_url( $trade_in_url ); ?>">ขายหรือเทิร์นอุปกรณ์ <span aria-hidden="true">↗</span></a>
				</div>
			</div>
			<div class="clicko-hero__visual">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/clicko-gear-hero.svg' ); ?>" alt="ชุดคอมพิวเตอร์และอุปกรณ์เกมมิง Clicko" width="980" height="620" fetchpriority="high">
			</div>
		</div>
	</section>

	<section class="clicko-home-trust" aria-label="บริการของ Clicko">
		<div class="clicko-shell clicko-home-trust__grid">
			<div class="clicko-home-trust__item"><span class="clicko-home-trust__icon" aria-hidden="true">↗</span><div><strong>จัดส่งทั่วไทย</strong><p>นัดหมายและสอบถามวิธีจัดส่งกับผู้ขาย</p></div></div>
			<div class="clicko-home-trust__item"><span class="clicko-home-trust__icon" aria-hidden="true">✓</span><div><strong>เช็กสภาพก่อนซื้อ</strong><p>ขอรูปและรายละเอียดสินค้าเพิ่มเติมได้</p></div></div>
			<div class="clicko-home-trust__item"><span class="clicko-home-trust__icon" aria-hidden="true">⌁</span><div><strong>คุยกับผู้ขายโดยตรง</strong><p>สอบถามราคาและนัดซื้อขายผ่าน Messenger</p></div></div>
		</div>
	</section>

	<section class="clicko-home-categories clicko-shell" aria-labelledby="clicko-home-categories-title">
		<div class="clicko-section-heading clicko-home-section-heading">
			<div><p class="clicko-eyebrow">FIND YOUR GEAR</p><h2 id="clicko-home-categories-title">เลือกดูตามหมวดหมู่</h2><p class="clicko-section-subtitle">ไปยังหน้าสินค้าทั้งหมดเพื่อค้นหาและเลือกสินค้าที่สนใจ</p></div>
			<a class="clicko-text-link" href="<?php echo esc_url( $shop_url ); ?>">ดูสินค้าทั้งหมด <span aria-hidden="true">→</span></a>
		</div>
		<nav class="clicko-home-category-grid" aria-label="หมวดหมู่สินค้า">
			<?php foreach ( $categories as $category ) : ?>
				<a class="clicko-home-category" href="<?php echo esc_url( add_query_arg( 'clicko_q', $category[2], $shop_url ) ); ?>">
					<span class="clicko-home-category__icon"><?php echo clicko_category_icon( $category[0] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="clicko-home-category__label"><?php echo esc_html( $category[1] ); ?></span>
					<span class="clicko-home-category__arrow" aria-hidden="true">→</span>
				</a>
			<?php endforeach; ?>
		</nav>
	</section>

	<section class="clicko-home-trade">
		<div class="clicko-shell clicko-home-trade__inner">
			<div class="clicko-home-trade__copy">
				<p class="clicko-eyebrow">TRADE IN WITH CLICKO</p>
				<h2>มีอุปกรณ์ที่ไม่ได้ใช้?<br><span>ส่งข้อมูลให้เราประเมินได้</span></h2>
				<p>กรอกรุ่น สภาพ และแนบรูปสินค้าจริงผ่านเว็บไซต์ ทีมงานจะติดต่อกลับเพื่อคุยรายละเอียด</p>
				<a class="clicko-button clicko-button--white" href="<?php echo esc_url( $trade_in_url ); ?>">เริ่มส่งข้อมูลรับซื้อ / เทิร์น <span aria-hidden="true">→</span></a>
			</div>
			<div class="clicko-home-trade__badge" aria-hidden="true"><span>CLICKO</span><strong>BUY<br>SELL<br>TRADE</strong><small>GAMING GEAR & PC PARTS</small></div>
		</div>
	</section>

	<section class="clicko-home-links clicko-shell" aria-label="ข้อมูลร้าน">
		<a class="clicko-home-link-card" href="<?php echo esc_url( home_url( '/reviews/' ) ); ?>"><span class="clicko-home-link-card__icon" aria-hidden="true">★</span><span><strong>รีวิวจากลูกค้า</strong><small>ดูรีวิวและติดตามข่าวสารจากร้าน</small></span><b aria-hidden="true">→</b></a>
		<a class="clicko-home-link-card" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><span class="clicko-home-link-card__icon" aria-hidden="true">⌖</span><span><strong>ติดต่อ Clicko</strong><small>ช่องทางติดต่อและจุดนัดรับส่งสินค้า</small></span><b aria-hidden="true">→</b></a>
	</section>

	<?php while ( have_posts() ) : the_post(); if ( trim( wp_strip_all_tags( get_the_content() ) ) ) : ?><section class="clicko-shell clicko-home-content"><div class="entry-content"><?php the_content(); ?></div></section><?php endif; endwhile; ?>
</main>
<?php get_footer(); ?>
