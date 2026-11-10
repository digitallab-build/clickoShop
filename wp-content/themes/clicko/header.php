<?php
/** Site header. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$contact = function_exists( 'clicko_get_contact_urls' ) ? clicko_get_contact_urls() : array();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="clicko-skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'clicko' ); ?></a>
<div class="clicko-topbar"><div class="clicko-shell"><span><?php echo esc_html( get_theme_mod( 'clicko_topbar_message', 'PC PARTS · GAMING GEAR · BUILD YOUR SETUP' ) ); ?></span><a href="<?php echo esc_url( ! empty( $contact['brand'] ) ? $contact['brand'] : 'https://www.facebook.com/Clicko.gg/' ); ?>" target="_blank" rel="noopener noreferrer">ติดตามข่าวสารที่เพจ Clicko <span aria-hidden="true">↗</span></a></div></div>
<header class="clicko-header">
	<div class="clicko-shell clicko-header__inner">
		<div class="clicko-brand">
			<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?><a class="clicko-brand__home" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Clicko หน้าแรก"><?php get_template_part( 'template-parts/brand-mark' ); ?><span class="screen-reader-text">Clicko Gaming Gear &amp; PC Parts</span></a><?php endif; ?>
			<span class="clicko-brand__tagline"><?php echo esc_html( get_theme_mod( 'clicko_brand_tagline', 'GAMING GEAR & PC PARTS' ) ); ?></span>
		</div>
		<button class="clicko-menu-toggle" type="button" aria-expanded="false" aria-controls="clicko-primary-nav"><span class="screen-reader-text">เปิดเมนู</span><span></span><span></span></button>
		<nav id="clicko-primary-nav" class="clicko-nav" aria-label="เมนูหลัก">
			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'fallback_cb' => 'clicko_default_menu', 'menu_class' => 'clicko-nav__list' ) ); ?>
		</nav>
		<div class="clicko-header__actions">
			<form class="clicko-header-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>"><label class="screen-reader-text" for="clicko-header-search">ค้นหาสินค้า</label><input id="clicko-header-search" type="search" name="clicko_q" placeholder="ค้นหาสินค้า..." value="<?php echo esc_attr( isset( $_GET['clicko_q'] ) ? sanitize_text_field( wp_unslash( $_GET['clicko_q'] ) ) : '' ); ?>"><button type="submit" aria-label="ค้นหา"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg></button></form>
			<a class="clicko-selection-link" href="<?php echo esc_url( home_url( '/selection/' ) ); ?>" aria-label="รายการที่สนใจ"><span aria-hidden="true">▢</span><span class="clicko-selection-count" data-clicko-count>0</span></a>
			<?php $account_url = home_url( '/account/' ); $dashboard_url = home_url( '/store-dashboard/' ); ?><a class="clicko-account-link" href="<?php echo esc_url( is_user_logged_in() && function_exists( 'clicko_is_staff_user' ) && clicko_is_staff_user() ? $dashboard_url : $account_url ); ?>" aria-label="<?php echo esc_attr( is_user_logged_in() ? 'บัญชีของฉัน' : 'เข้าสู่ระบบ / สมัครสมาชิก' ); ?>"><span aria-hidden="true">♙</span><span class="clicko-account-link__label"><?php echo is_user_logged_in() ? 'บัญชี' : 'เข้าสู่ระบบ'; ?></span></a>
			<a class="clicko-facebook-link" href="<?php echo esc_url( ! empty( $contact['brand'] ) ? $contact['brand'] : 'https://www.facebook.com/Clicko.gg/' ); ?>" target="_blank" rel="noopener noreferrer" aria-label="ติดตามเพจ Clicko"><span aria-hidden="true">f</span></a>
		</div>
	</div>
</header>
