<?php
/** Site footer. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$contact = function_exists( 'clicko_get_contact_urls' ) ? clicko_get_contact_urls() : array();
?>

<section class="clicko-trustbar" aria-label="บริการและช่องทางของ Clicko"><div class="clicko-shell clicko-trustbar__inner">
	<div class="clicko-trust-item"><span class="clicko-trust-icon" aria-hidden="true">⌖</span><span><b>ดูสเปกได้ชัดเจน</b><small>ข้อมูลสินค้าอยู่ในหน้าเดียว</small></span></div>
	<div class="clicko-trust-item"><span class="clicko-trust-icon" aria-hidden="true">✓</span><span><b>ยืนยันก่อนซื้อ</b><small>สอบถามราคาและสินค้ากับผู้ขาย</small></span></div>
	<div class="clicko-trust-item"><span class="clicko-trust-icon" aria-hidden="true">☏</span><span><b>ติดต่อผู้ขายโดยตรง</b><small>พูดคุยผ่าน Facebook ส่วนตัว</small></span></div>
	<div class="clicko-trust-follow"><span>ติดตามข่าวสารได้ที่</span><a href="<?php echo esc_url( ! empty( $contact['brand'] ) ? $contact['brand'] : 'https://www.facebook.com/Clicko.gg/' ); ?>" target="_blank" rel="noopener noreferrer"><span aria-hidden="true">f</span> Facebook</a><div class="clicko-mascot" aria-hidden="true"><span>C</span></div></div>
</div></section>
<footer class="clicko-footer">
	<div class="clicko-shell">
		<div class="clicko-footer__main">
			<div><a class="clicko-brand__name" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span>c</span><span>l</span><span>i</span><span>c</span><span>k</span><span>o</span><i aria-hidden="true"></i></a><p>Gaming Gear &amp; PC Parts<br>เลือกของที่ใช่ แล้วคุยกับผู้ขายโดยตรง</p></div>
			<div><p class="clicko-eyebrow">ช่องทางของ Clicko</p><a href="<?php echo esc_url( ! empty( $contact['seller'] ) ? $contact['seller'] : 'https://www.facebook.com/sippavit.gomarn' ); ?>" target="_blank" rel="noopener noreferrer">ติดต่อซื้อกับผู้ขาย <span aria-hidden="true">↗</span></a><a href="<?php echo esc_url( ! empty( $contact['brand'] ) ? $contact['brand'] : 'https://www.facebook.com/Clicko.gg/' ); ?>" target="_blank" rel="noopener noreferrer">ติดตามเพจ Clicko <span aria-hidden="true">↗</span></a></div>
			<div><p class="clicko-eyebrow">ข้อมูลช่วยเหลือ</p><a href="<?php echo esc_url( home_url( '/how-to-buy/' ) ); ?>">วิธีซื้อสินค้า</a><a href="<?php echo esc_url( home_url( '/warranty-returns/' ) ); ?>">ประกันและการคืนสินค้า</a><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">ติดต่อร้าน</a></div>
		</div>
		<div class="clicko-footer__bottom"><span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Clicko — Gaming Gear &amp; PC Parts</span><span>ซื้อขายผ่าน Facebook ส่วนตัวของผู้ขาย</span></div>
	</div>
</footer>
<div class="clicko-toast" data-clicko-toast role="status" aria-live="polite"></div>
<?php wp_footer(); ?>
</body>
</html>
