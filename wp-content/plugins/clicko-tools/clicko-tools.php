<?php
/**
 * Plugin Name: Clicko Catalog Tools
 * Description: Clicko seller tools, Supabase customer accounts, and a shareable product selection.
 * Version: 1.7.0
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * Author: Clicko
 * Text Domain: clicko-tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CLICKO_TOOLS_VERSION', '1.7.0' );
define( 'CLICKO_TOOLS_OPTION', 'clicko_tools_settings' );

function clicko_default_settings() {
	return array(
		'seller_profile_url'   => 'https://www.facebook.com/sippavit.gomarn',
		'seller_messenger_url' => '',
		'brand_page_url'       => 'https://www.facebook.com/Clicko.gg/',
		'contact_phone'        => '',
		'supabase_url'         => 'https://mvpxjkpnlopgitvvzsyc.supabase.co',
		'supabase_publishable_key' => 'sb_publishable_LXq3zZSzdWbLWuSQwu74tQ_TV3V_uQs',
		'turnstile_site_key'   => '',
		'message_intro'        => 'สวัสดีครับ สนใจสินค้าจากเว็บไซต์ Clicko ครับ',
		'message_footer'       => 'รบกวนยืนยันสินค้าคงเหลือ ราคาปัจจุบัน และค่าจัดส่งด้วยครับ',
		'sales_button_label'   => 'ติดต่อซื้อกับผู้ขาย',
	);
}

function clicko_get_settings() {
	$saved = get_option( CLICKO_TOOLS_OPTION, array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), clicko_default_settings() );
}

function clicko_get_contact_urls() {
	$settings = clicko_get_settings();
	return array(
		'seller'   => $settings['seller_messenger_url'] ? $settings['seller_messenger_url'] : $settings['seller_profile_url'],
		'messenger' => $settings['seller_messenger_url'],
		'profile'  => $settings['seller_profile_url'],
		'brand'    => $settings['brand_page_url'],
	);
}

function clicko_sanitize_contact_url( $value, $kind ) {
	$value = esc_url_raw( trim( (string) $value ), array( 'https' ) );
	if ( '' === $value ) {
		return '';
	}
	$parts = wp_parse_url( $value );
	$host  = isset( $parts['host'] ) ? strtolower( $parts['host'] ) : '';
	$allowed = array(
		'profile'  => array( 'facebook.com', 'www.facebook.com' ),
		'messenger' => array( 'm.me', 'www.messenger.com', 'messenger.com', 'www.facebook.com', 'facebook.com' ),
		'brand'    => array( 'facebook.com', 'www.facebook.com' ),
	);
	return in_array( $host, $allowed[ $kind ], true ) ? $value : '';
}

function clicko_sanitize_settings( $input ) {
	$defaults = clicko_default_settings();
	$input = is_array( $input ) ? $input : array();
	$profile = clicko_sanitize_contact_url( $input['seller_profile_url'] ?? '', 'profile' );
	$brand = clicko_sanitize_contact_url( $input['brand_page_url'] ?? '', 'brand' );
	$phone = sanitize_text_field( $input['contact_phone'] ?? '' );
	return array(
		'seller_profile_url'   => $profile ? $profile : $defaults['seller_profile_url'],
		'seller_messenger_url' => clicko_sanitize_contact_url( $input['seller_messenger_url'] ?? '', 'messenger' ),
		'brand_page_url'       => $brand ? $brand : $defaults['brand_page_url'],
		'contact_phone'        => preg_replace( '/[^0-9+().\- ]/', '', $phone ),
		'supabase_url'         => esc_url_raw( trim( (string) ( $input['supabase_url'] ?? $defaults['supabase_url'] ) ), array( 'https' ) ),
		'supabase_publishable_key' => sanitize_text_field( $input['supabase_publishable_key'] ?? $defaults['supabase_publishable_key'] ),
		'turnstile_site_key'   => sanitize_text_field( $input['turnstile_site_key'] ?? '' ),
		'message_intro'        => sanitize_text_field( $input['message_intro'] ?? $defaults['message_intro'] ),
		'message_footer'       => sanitize_text_field( $input['message_footer'] ?? $defaults['message_footer'] ),
		'sales_button_label'   => sanitize_text_field( $input['sales_button_label'] ?? $defaults['sales_button_label'] ),
	);
}

function clicko_register_settings() {
	register_setting( 'clicko_tools_group', CLICKO_TOOLS_OPTION, array( 'type' => 'array', 'sanitize_callback' => 'clicko_sanitize_settings', 'default' => clicko_default_settings() ) );
}
add_action( 'admin_init', 'clicko_register_settings' );

function clicko_add_admin_menu() {
	add_menu_page( 'ตั้งค่า Clicko', 'Clicko', 'manage_options', 'clicko-settings', 'clicko_render_settings_page', 'dashicons-store', 58 );
}
add_action( 'admin_menu', 'clicko_add_admin_menu' );

function clicko_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$settings = clicko_get_settings();
	?>
	<div class="wrap">
		<h1>ตั้งค่า Clicko</h1>
		<p>แก้ช่องทางซื้อขายและข้อความที่ลูกค้าจะคัดลอกได้ที่นี่ การติดต่อซื้อขายใช้ Facebook ส่วนตัวของผู้ขาย ส่วนเพจ Clicko ใช้แสดงแบรนด์</p>
		<form action="options.php" method="post">
			<?php settings_fields( 'clicko_tools_group' ); ?>
			<table class="form-table" role="presentation">
				<tr><th scope="row"><label for="clicko-profile-url">Facebook ส่วนตัวของผู้ขาย</label></th><td><input class="regular-text" type="url" id="clicko-profile-url" name="<?php echo esc_attr( CLICKO_TOOLS_OPTION ); ?>[seller_profile_url]" value="<?php echo esc_attr( $settings['seller_profile_url'] ); ?>" required><p class="description">ใช้เป็นปลายทางสำรองเมื่อลิงก์ Messenger ยังไม่พร้อม</p></td></tr>
				<tr><th scope="row"><label for="clicko-messenger-url">ลิงก์ Messenger ส่วนตัว</label></th><td><input class="regular-text" type="url" id="clicko-messenger-url" name="<?php echo esc_attr( CLICKO_TOOLS_OPTION ); ?>[seller_messenger_url]" value="<?php echo esc_attr( $settings['seller_messenger_url'] ); ?>"><p class="description">เว้นว่างไว้เพื่อใช้โปรไฟล์สำรองก่อน กรอกเมื่อทดสอบแล้วว่าเปิดแชทผู้ขายได้จริง</p></td></tr>
				<tr><th scope="row"><label for="clicko-brand-url">เพจ Clicko สำหรับติดตามข่าวสาร</label></th><td><input class="regular-text" type="url" id="clicko-brand-url" name="<?php echo esc_attr( CLICKO_TOOLS_OPTION ); ?>[brand_page_url]" value="<?php echo esc_attr( $settings['brand_page_url'] ); ?>"></td></tr>
				<tr><th scope="row"><label for="clicko-contact-phone">เบอร์โทรติดต่อร้าน</label></th><td><input class="regular-text" type="tel" id="clicko-contact-phone" name="<?php echo esc_attr( CLICKO_TOOLS_OPTION ); ?>[contact_phone]" value="<?php echo esc_attr( $settings['contact_phone'] ); ?>" placeholder="เช่น 08x-xxx-xxxx"><p class="description">กรอกเบอร์ที่ต้องการให้ลูกค้าเห็นในหน้า ติดต่อเรา</p></td></tr>
				<tr><th scope="row"><label for="clicko-supabase-url">Supabase Project URL</label></th><td><input class="regular-text" type="url" id="clicko-supabase-url" name="<?php echo esc_attr( CLICKO_TOOLS_OPTION ); ?>[supabase_url]" value="<?php echo esc_attr( $settings['supabase_url'] ); ?>" required><p class="description">Project URL ของฐานข้อมูล Clicko ที่ใช้งานอยู่</p></td></tr>
				<tr><th scope="row"><label for="clicko-supabase-key">Supabase Publishable key</label></th><td><input class="large-text code" type="text" id="clicko-supabase-key" name="<?php echo esc_attr( CLICKO_TOOLS_OPTION ); ?>[supabase_publishable_key]" value="<?php echo esc_attr( $settings['supabase_publishable_key'] ); ?>" required><p class="description">ใช้ publishable/anon key เท่านั้น ห้ามใส่ service_role key</p></td></tr>
				<tr><th scope="row"><label for="clicko-turnstile-site-key">Cloudflare Turnstile Site Key</label></th><td><input class="regular-text code" type="text" id="clicko-turnstile-site-key" name="<?php echo esc_attr( CLICKO_TOOLS_OPTION ); ?>[turnstile_site_key]" value="<?php echo esc_attr( $settings['turnstile_site_key'] ); ?>"><p class="description">ใช้ Site Key สำหรับแบบฟอร์มรับซื้อ/เทิร์น; Secret Key ต้องตั้งเป็น Supabase Edge Function secret เท่านั้น</p></td></tr>
				<tr><th scope="row"><label for="clicko-message-intro">คำขึ้นต้นข้อความ</label></th><td><input class="large-text" type="text" id="clicko-message-intro" name="<?php echo esc_attr( CLICKO_TOOLS_OPTION ); ?>[message_intro]" value="<?php echo esc_attr( $settings['message_intro'] ); ?>"></td></tr>
				<tr><th scope="row"><label for="clicko-message-footer">คำลงท้ายข้อความ</label></th><td><input class="large-text" type="text" id="clicko-message-footer" name="<?php echo esc_attr( CLICKO_TOOLS_OPTION ); ?>[message_footer]" value="<?php echo esc_attr( $settings['message_footer'] ); ?>"></td></tr>
				<tr><th scope="row"><label for="clicko-button-label">ข้อความปุ่มติดต่อผู้ขาย</label></th><td><input class="regular-text" type="text" id="clicko-button-label" name="<?php echo esc_attr( CLICKO_TOOLS_OPTION ); ?>[sales_button_label]" value="<?php echo esc_attr( $settings['sales_button_label'] ); ?>"></td></tr>
			</table>
			<?php submit_button( 'บันทึกการตั้งค่า' ); ?>
		</form>
		<hr><h2>แก้ข้อมูลส่วนอื่น</h2><ul><li>สินค้า ราคา และ SKU: ไปที่ <strong>สินค้า → สินค้าทั้งหมด</strong></li><li>ภาพและเนื้อหาหน้าแรก: ไปที่ <strong>หน้า → หน้าแรก</strong></li><li>เมนูและโลโก้: ไปที่ <strong>รูปลักษณ์ → ปรับแต่ง</strong></li></ul>
	</div>
	<?php
}

function clicko_register_front_assets() {
	$base = plugin_dir_url( __FILE__ );
	wp_enqueue_style( 'clicko-tools', $base . 'assets/selection.css', array(), CLICKO_TOOLS_VERSION );
	$tool_dependencies = array();
	if ( is_page( 'trade-in' ) ) {
		wp_enqueue_script( 'clicko-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit', array(), null, true );
		$tool_dependencies[] = 'clicko-turnstile';
	}
	wp_enqueue_script( 'clicko-tools', $base . 'assets/selection.js', $tool_dependencies, CLICKO_TOOLS_VERSION, true );
	wp_localize_script( 'clicko-tools', 'ClickoCatalog', array(
		'detailsUrl' => esc_url_raw( rest_url( 'clicko/v1/selection-details' ) ),
		'contactUrl' => clicko_get_contact_urls()['seller'],
		'contactLabel' => clicko_get_contact_urls()['messenger'] ? 'เปิด Messenger ผู้ขาย' : 'เปิด Facebook ผู้ขาย',
		'intro'      => clicko_get_settings()['message_intro'],
		'footer'     => clicko_get_settings()['message_footer'],
		'currency'   => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'THB',
		'decimals'   => function_exists( 'wc_get_price_decimals' ) ? wc_get_price_decimals() : 2,
		'shopUrl'    => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/products/' ),
	) );
	if ( is_page( 'trade-in' ) ) {
		$settings = clicko_get_settings();
		$submit_url = trailingslashit( $settings['supabase_url'] ) . 'functions/v1/submit-trade-in';
		wp_localize_script( 'clicko-tools', 'ClickoTradeInConfig', array(
			'submitUrl' => esc_url_raw( $submit_url ),
			'publishableKey' => $settings['supabase_publishable_key'],
			'turnstileSiteKey' => $settings['turnstile_site_key'],
		) );
	}
	if ( is_page( 'account' ) || is_front_page() || is_page( 'products' ) || is_page( 'trade-in' ) || is_page( 'trade-in-admin' ) ) {
		wp_enqueue_script( 'clicko-supabase', 'https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2', array(), null, true );
		$settings = clicko_get_settings();
		$contact = clicko_get_contact_urls();
		if ( is_front_page() || is_page( 'products' ) ) {
			wp_enqueue_script( 'clicko-supabase-catalog', $base . 'assets/supabase-catalog.js', array( 'clicko-supabase' ), CLICKO_TOOLS_VERSION, true );
			wp_localize_script( 'clicko-supabase-catalog', 'ClickoSupabaseCatalogConfig', array(
				'url' => esc_url_raw( $settings['supabase_url'] ),
				'key' => $settings['supabase_publishable_key'],
				'contactUrl' => esc_url_raw( $contact['seller'] ),
				'intro' => $settings['message_intro'],
				'footer' => $settings['message_footer'],
			) );
		}
		if ( is_page( 'trade-in-admin' ) ) {
			wp_enqueue_script( 'clicko-trade-in-dashboard', $base . 'assets/trade-in-dashboard.js', array( 'clicko-supabase' ), CLICKO_TOOLS_VERSION, true );
			wp_localize_script( 'clicko-trade-in-dashboard', 'ClickoTradeInDashboardConfig', array(
				'url' => esc_url_raw( $settings['supabase_url'] ),
				'key' => $settings['supabase_publishable_key'],
			) );
		}
	}
	if ( is_page( 'account' ) ) {
		wp_enqueue_script( 'clicko-customer-account', $base . 'assets/customer-account.js', array( 'clicko-supabase' ), CLICKO_TOOLS_VERSION, true );
		wp_localize_script( 'clicko-customer-account', 'ClickoAccountConfig', array(
			'url' => esc_url_raw( $settings['supabase_url'] ),
			'key' => $settings['supabase_publishable_key'],
			'redirectTo' => esc_url_raw( home_url( '/account/' ) ),
		) );
	}
}
add_action( 'wp_enqueue_scripts', 'clicko_register_front_assets' );

function clicko_product_action_shortcode( $attributes ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return '';
	}
	$attributes = shortcode_atts( array( 'product_id' => get_the_ID() ), $attributes, 'clicko_product_action' );
	$product = wc_get_product( absint( $attributes['product_id'] ) );
	if ( ! $product || ! $product->is_visible() ) {
		return '';
	}
	ob_start();
	?>
	<div class="clicko-product-action" data-clicko-action data-product-id="<?php echo esc_attr( (string) $product->get_id() ); ?>">
		<?php if ( $product->is_type( 'variable' ) ) : ?>
			<label class="screen-reader-text" for="clicko-variation-<?php echo esc_attr( (string) $product->get_id() ); ?>">เลือกรุ่นสินค้า</label>
			<select id="clicko-variation-<?php echo esc_attr( (string) $product->get_id() ); ?>" class="clicko-variant-select" data-clicko-variation><option value="">เลือกรุ่น/ตัวเลือก</option>
				<?php foreach ( $product->get_available_variations() as $variation_data ) : $variation = wc_get_product( $variation_data['variation_id'] ); if ( ! $variation ) { continue; } $labels = array_filter( array_values( $variation->get_variation_attributes() ) ); ?>
					<option value="<?php echo esc_attr( (string) $variation->get_id() ); ?>"><?php echo esc_html( implode( ' / ', $labels ) . ( $variation->get_sku() ? ' · ' . $variation->get_sku() : '' ) ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php endif; ?>
		<button class="clicko-button" type="button" data-clicko-add>เพิ่มในรายการที่สนใจ <span aria-hidden="true">＋</span></button>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'clicko_product_action', 'clicko_product_action_shortcode' );

function clicko_selection_shortcode() {
	return '<section class="clicko-selection" data-clicko-selection aria-live="polite"><p class="clicko-selection__loading">กำลังโหลดรายการที่เลือก…</p></section>';
}
add_shortcode( 'clicko_selection', 'clicko_selection_shortcode' );

function clicko_contact_button_shortcode( $attributes ) {
	$attributes = shortcode_atts( array( 'label' => '', 'class' => '' ), $attributes, 'clicko_contact_button' );
	$urls = clicko_get_contact_urls();
	$label = $attributes['label'] ? $attributes['label'] : clicko_get_settings()['sales_button_label'];
	return '<a class="clicko-button ' . esc_attr( $attributes['class'] ) . '" href="' . esc_url( $urls['seller'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label ) . ' <span aria-hidden="true">↗</span></a>';
}
add_shortcode( 'clicko_contact_button', 'clicko_contact_button_shortcode' );

function clicko_brand_link_shortcode( $attributes ) {
	$attributes = shortcode_atts( array( 'label' => 'ติดตามเพจ Clicko', 'class' => 'clicko-text-link' ), $attributes, 'clicko_brand_link' );
	$urls = clicko_get_contact_urls();
	return '<a class="' . esc_attr( $attributes['class'] ) . '" href="' . esc_url( $urls['brand'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $attributes['label'] ) . ' <span aria-hidden="true">↗</span></a>';
}
add_shortcode( 'clicko_brand_link', 'clicko_brand_link_shortcode' );

function clicko_selection_details( WP_REST_Request $request ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return new WP_Error( 'clicko_store_unavailable', 'ระบบแค็ตตาล็อกยังไม่พร้อม', array( 'status' => 503 ) );
	}
	$items = $request->get_param( 'items' );
	if ( ! is_array( $items ) || count( $items ) > 40 ) {
		return new WP_Error( 'clicko_invalid_selection', 'รายการสินค้ามีรูปแบบไม่ถูกต้อง', array( 'status' => 400 ) );
	}
	$normalized = array();
	foreach ( $items as $row ) {
		if ( ! is_array( $row ) ) { continue; }
		$product_id = absint( $row['productId'] ?? $row['product_id'] ?? 0 );
		$product = wc_get_product( $product_id );
		if ( ! $product || 'publish' !== get_post_status( $product_id ) ) { continue; }
		$variation_id = absint( $row['variationId'] ?? $row['variation_id'] ?? 0 );
		$item_product = $product;
		$variation_label = '';
		if ( $product->is_type( 'variable' ) ) {
			$item_product = $variation_id ? wc_get_product( $variation_id ) : false;
			if ( ! $item_product || $item_product->get_parent_id() !== $product->get_id() ) { continue; }
			$variation_label = implode( ' / ', array_filter( array_values( $item_product->get_variation_attributes() ) ) );
		} elseif ( $variation_id ) {
			continue;
		}
		$quantity = min( 99, max( 1, absint( $row['quantity'] ?? 1 ) ) );
		$price = $item_product->get_price();
		$has_price = '' !== $price && is_numeric( $price );
		$display_price = $has_price ? (float) wc_get_price_to_display( $item_product ) : null;
		$normalized[] = array(
			'productId' => $product->get_id(),
			'variationId' => $variation_id,
			'name' => wp_strip_all_tags( $product->get_name() ),
			'variant' => sanitize_text_field( $variation_label ),
			'sku' => sanitize_text_field( $item_product->get_sku() ),
			'quantity' => $quantity,
			'url' => esc_url_raw( get_permalink( $product->get_id() ) ),
			'priceLabel' => $has_price ? wp_strip_all_tags( wc_price( $display_price ) ) : null,
			'lineMinor' => $has_price ? (int) round( $display_price * ( 10 ** wc_get_price_decimals() ) ) * $quantity : null,
			'inStock' => $item_product->is_in_stock(),
		);
	}
	return rest_ensure_response( array( 'items' => $normalized, 'currency' => get_woocommerce_currency(), 'decimals' => wc_get_price_decimals() ) );
}

function clicko_register_rest_routes() {
	register_rest_route( 'clicko/v1', '/selection-details', array(
		'methods' => WP_REST_Server::CREATABLE,
		'callback' => 'clicko_selection_details',
		'permission_callback' => '__return_true',
		'args' => array( 'items' => array( 'required' => true, 'type' => 'array' ) ),
	) );
}
add_action( 'rest_api_init', 'clicko_register_rest_routes' );

function clicko_create_page_once( $slug, $title, $content ) {
	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page instanceof WP_Post ) {
		return (int) $page->ID;
	}
	$page_id = wp_insert_post( array(
		'post_type' => 'page',
		'post_status' => 'publish',
		'post_title' => $title,
		'post_name' => $slug,
		'post_content' => $content,
	), true );
	return is_wp_error( $page_id ) ? 0 : (int) $page_id;
}

function clicko_activate_plugin() {
	$home_id = clicko_create_page_once( 'home', 'หน้าแรก', '' );
	clicko_create_page_once( 'selection', 'รายการที่สนใจ', '[clicko_selection]' );
	clicko_create_page_once( 'trade-in', 'รับซื้อ / เทิร์น / แลกเปลี่ยนอุปกรณ์', '[clicko_trade_in]' );
	clicko_create_page_once( 'trade-in-admin', 'รายการรับซื้อ / เทิร์น', '[clicko_trade_in_dashboard]' );
	clicko_create_page_once( 'account', 'เข้าสู่ระบบ / สมัครสมาชิก', '[clicko_account]' );
	clicko_create_page_once( 'products', 'สินค้าทั้งหมด', '[clicko_supabase_catalog layout="grid" title="สินค้าทั้งหมด"]' );
	clicko_create_page_once( 'store-dashboard', 'แดชบอร์ดร้านค้า', '[clicko_staff_dashboard]' );
	clicko_create_page_once( 'reviews', 'รีวิวร้าน', '[clicko_reviews]' );
	clicko_create_page_once( 'contact', 'ติดต่อร้าน', '[clicko_contact_details]' );
	clicko_create_page_once( 'warranty-returns', 'ประกันและการคืนสินค้า', '<h2>ข้อมูลประกัน</h2><p>ผู้ขายจะยืนยันเงื่อนไขประกันและการคืนสินค้าตามสินค้าแต่ละรายการผ่าน Facebook ก่อนตกลงซื้อขาย กรุณาเพิ่มเนื้อหาตามเงื่อนไขจริงของร้านก่อนเปิดเว็บไซต์</p><p>[clicko_contact_button]</p>' );
	clicko_create_page_once( 'privacy', 'นโยบายความเป็นส่วนตัว', clicko_default_privacy_content() );
	if ( $home_id && ! absint( get_option( 'page_on_front' ) ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
	}
	clicko_add_staff_role();
	if ( function_exists( 'wc_get_page_id' ) ) {
		update_option( 'woocommerce_enable_myaccount_registration', 'no' );
	}
	update_option( 'clicko_tools_version', CLICKO_TOOLS_VERSION );
}
register_activation_hook( __FILE__, 'clicko_activate_plugin' );

/** Add a restricted staff role for catalog work. */
function clicko_add_staff_role() {
	$capabilities = array( 'read' => true );
	$role = get_role( 'clicko_staff' );
	if ( ! $role ) {
		add_role( 'clicko_staff', 'พนักงาน Clicko', $capabilities );
		return;
	}
	foreach ( $capabilities as $capability => $grant ) {
		$role->add_cap( $capability, $grant );
	}
	foreach ( array( 'edit_products', 'edit_others_products', 'edit_published_products', 'publish_products', 'read_private_products', 'upload_files', 'edit_product', 'read_product', 'edit_published_product' ) as $capability ) {
		$role->remove_cap( $capability );
	}
}
add_action( 'init', 'clicko_add_staff_role' );

function clicko_ensure_portal_pages() {
	clicko_create_page_once( 'account', 'เข้าสู่ระบบ / สมัครสมาชิก', '[clicko_account]' );
	$products_id = clicko_create_page_once( 'products', 'สินค้าทั้งหมด', '[clicko_supabase_catalog layout="grid" title="สินค้าทั้งหมด"]' );
	$products_page = $products_id ? get_post( $products_id ) : null;
	if ( $products_page instanceof WP_Post && false === strpos( $products_page->post_content, '[clicko_supabase_catalog' ) ) {
		$products_content = trim( $products_page->post_content );
		$products_content .= ( '' === $products_content ? '' : "\n\n" ) . '[clicko_supabase_catalog layout="grid" title="สินค้าทั้งหมด"]';
		wp_update_post( array( 'ID' => $products_page->ID, 'post_content' => $products_content ) );
	}
	$how_to_buy = get_page_by_path( 'how-to-buy', OBJECT, 'page' );
	if ( $how_to_buy instanceof WP_Post && 'publish' === $how_to_buy->post_status ) {
		wp_update_post( array( 'ID' => $how_to_buy->ID, 'post_status' => 'draft' ) );
	}
	clicko_create_page_once( 'trade-in', 'รับซื้อ / เทิร์น / แลกเปลี่ยนอุปกรณ์', '[clicko_trade_in]' );
	clicko_create_page_once( 'trade-in-admin', 'รายการรับซื้อ / เทิร์น', '[clicko_trade_in_dashboard]' );
	clicko_create_page_once( 'store-dashboard', 'แดชบอร์ดร้านค้า', '[clicko_staff_dashboard]' );
	$contact_id = clicko_create_page_once( 'contact', 'ติดต่อร้าน', '[clicko_contact_details]' );
	$contact_page = $contact_id ? get_post( $contact_id ) : null;
	if ( $contact_page instanceof WP_Post && false === strpos( $contact_page->post_content, '[clicko_contact_details]' ) ) {
		$contact_content = trim( $contact_page->post_content );
		$contact_content .= ( '' === $contact_content ? '' : "\n\n" ) . '[clicko_contact_details]';
		wp_update_post( array( 'ID' => $contact_page->ID, 'post_content' => $contact_content ) );
	}
	$reviews_id = clicko_create_page_once( 'reviews', 'รีวิวร้าน', '[clicko_reviews]' );
	$reviews_page = $reviews_id ? get_post( $reviews_id ) : null;
	if ( $reviews_page instanceof WP_Post && false === strpos( $reviews_page->post_content, '[clicko_reviews]' ) ) {
		$reviews_content = trim( $reviews_page->post_content );
		$reviews_content .= ( '' === $reviews_content ? '' : "\n\n" ) . '[clicko_reviews]';
		wp_update_post( array( 'ID' => $reviews_page->ID, 'post_content' => $reviews_content ) );
	}
	$privacy_id = clicko_create_page_once( 'privacy', 'นโยบายความเป็นส่วนตัว', clicko_default_privacy_content() );
	if ( $privacy_id && ! absint( get_option( 'wp_page_for_privacy_policy' ) ) ) {
		update_option( 'wp_page_for_privacy_policy', $privacy_id );
	}
	$privacy_page = get_page_by_path( 'privacy', OBJECT, 'page' );
	$legacy_privacy = '<h2>ข้อมูลที่เว็บไซต์เก็บ</h2><p>หากสมัครสมาชิก เว็บไซต์จะเก็บข้อมูลบัญชี เช่น ชื่อที่แสดง อีเมล และข้อมูลการเข้าสู่ระบบไว้ใน WordPress เพื่อให้ใช้บัญชีได้ ข้อมูลในแบบฟอร์มรับซื้อ/เทิร์นไม่ได้ส่งหรือบันทึกโดยเว็บไซต์</p><h2>การติดต่อและสิทธิ์ของสมาชิก</h2><p>การซื้อขายทำผ่าน Facebook ส่วนตัวของผู้ขาย เว็บไซต์นี้ไม่มีระบบชำระเงินหรือจัดส่ง ลูกค้าสามารถติดต่อผู้ดูแลเว็บไซต์เพื่อขอแก้ไขหรือลบบัญชีได้</p><p>ผู้ดูแลเว็บไซต์ควรตรวจและแก้ไขนโยบายนี้ให้ตรงกับบริการ ปลั๊กอิน และโฮสต์ที่ใช้งานจริงก่อนเปิดรับสมาชิก</p>';
	if ( $privacy_page instanceof WP_Post && $legacy_privacy === $privacy_page->post_content ) {
		wp_update_post( array( 'ID' => $privacy_page->ID, 'post_content' => clicko_default_privacy_content() ) );
	}
	$previous_trade_in_privacy = 'แบบฟอร์มรับซื้อ/เทิร์นใช้เตรียมข้อความในเบราว์เซอร์ ไม่ส่งข้อมูลที่กรอกไปยังฐานข้อมูลเว็บไซต์';
	if ( $privacy_page instanceof WP_Post && false !== strpos( $privacy_page->post_content, $previous_trade_in_privacy ) ) {
		$updated_privacy = str_replace( $previous_trade_in_privacy, 'แบบฟอร์มรับซื้อ/เทิร์นจะจัดเก็บรุ่นสินค้า ประกัน สภาพ และรูปใน Supabase แบบส่วนตัวเพื่อให้ร้านประเมิน โดยไม่เก็บชื่อ เบอร์โทร หรือเลขบัตรประชาชนในคำขอนี้', $privacy_page->post_content );
		wp_update_post( array( 'ID' => $privacy_page->ID, 'post_content' => $updated_privacy ) );
	}
}
add_action( 'init', 'clicko_ensure_portal_pages', 20 );
function clicko_default_privacy_content() {
	return '<h2>ข้อมูลสมาชิก</h2><p>ระบบบัญชีใช้ Supabase Auth เก็บอีเมลและข้อมูลสำหรับเข้าสู่ระบบ ส่วนโปรไฟล์สมาชิกใน Supabase อาจมีชื่อ นามสกุล วันเกิด ที่อยู่ปัจจุบัน เบอร์โทรศัพท์ และอีเมลติดต่อ (ถ้าจำเป็น) วันเกิด ที่อยู่ และเบอร์โทรเป็นข้อมูลไม่บังคับ เลขบัตรประชาชนไม่ถูกร้องขอ</p><h2>การใช้ข้อมูลและผู้ให้บริการ</h2><p>ใช้ข้อมูลเพื่อจัดการบัญชีสมาชิกและติดต่อเรื่องบริการของ Clicko โดยระบบบัญชีและฐานข้อมูลให้บริการผ่าน Supabase การเข้าสู่ระบบด้วย Facebook จะส่งต่อไปยัง Facebook ตามการตั้งค่าผู้ให้บริการ</p><h2>สิทธิ์และระยะเวลาเก็บ</h2><p>สมาชิกติดต่อร้านเพื่อขอแก้ไขหรือลบบัญชีได้ ข้อมูลจะเก็บเท่าที่จำเป็นต่อการให้บริการและภาระหน้าที่ของร้าน ผู้ดูแลเว็บไซต์ต้องระบุช่องทางติดต่อและระยะเวลาเก็บจริงของร้านก่อนเปิดรับสมาชิก</p><p>แบบฟอร์มรับซื้อ/เทิร์นจะจัดเก็บรุ่นสินค้า ประกัน สภาพ และรูปใน Supabase แบบส่วนตัวเพื่อให้ร้านประเมิน โดยไม่เก็บชื่อ เบอร์โทร หรือเลขบัตรประชาชนในคำขอนี้</p>';
}

function clicko_upgrade_plugin() {
	if ( get_option( 'clicko_tools_version' ) === CLICKO_TOOLS_VERSION ) {
		return;
	}
	if ( function_exists( 'wc_get_page_id' ) ) {
		update_option( 'woocommerce_enable_myaccount_registration', 'no' );
	}
	update_option( 'clicko_tools_version', CLICKO_TOOLS_VERSION );
}
add_action( 'init', 'clicko_upgrade_plugin', 5 );

function clicko_is_staff_user( $user = null ) {
	$user = $user instanceof WP_User ? $user : wp_get_current_user();
	return $user && ( in_array( 'clicko_staff', (array) $user->roles, true ) || user_can( $user, 'manage_options' ) );
}

/** Route sign-ins to the appropriate dashboard and keep staff away from site settings. */
function clicko_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
	if ( ! ( $user instanceof WP_User ) ) {
		return $redirect_to;
	}
	if ( clicko_is_staff_user( $user ) ) {
		return home_url( '/store-dashboard/' );
	}
	if ( in_array( 'customer', (array) $user->roles, true ) || in_array( 'subscriber', (array) $user->roles, true ) ) {
		return home_url( '/account/' );
	}
	return $redirect_to;
}
add_filter( 'login_redirect', 'clicko_login_redirect', 10, 3 );

function clicko_woocommerce_login_redirect( $redirect_to, $user ) {
	if ( ! ( $user instanceof WP_User ) ) {
		return $redirect_to;
	}
	return clicko_is_staff_user( $user ) ? home_url( '/store-dashboard/' ) : home_url( '/account/' );
}
add_filter( 'woocommerce_login_redirect', 'clicko_woocommerce_login_redirect', 10, 2 );

function clicko_restrict_staff_admin() {
	if ( ! is_admin() || ! is_user_logged_in() || wp_doing_ajax() || wp_doing_cron() || clicko_is_staff_user() && current_user_can( 'manage_options' ) ) {
		return;
	}
	$user = wp_get_current_user();
	if ( in_array( 'clicko_staff', (array) $user->roles, true ) ) {
		wp_safe_redirect( home_url( '/store-dashboard/' ) );
		exit;
	}
	if ( array_intersect( array( 'customer', 'subscriber' ), (array) $user->roles ) ) {
		wp_safe_redirect( home_url( '/account/' ) );
		exit;
	}
	if ( ! in_array( 'clicko_staff', (array) $user->roles, true ) ) {
		return;
	}
	$allowed = array( 'index.php', 'edit.php', 'post-new.php', 'post.php', 'upload.php', 'async-upload.php', 'profile.php' );
	$screen = isset( $GLOBALS['pagenow'] ) ? $GLOBALS['pagenow'] : '';
	$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
	$editing_product = in_array( $screen, array( 'post-new.php', 'post.php' ), true ) && ( 'product' === $post_type || ( isset( $_GET['post'] ) && 'product' === get_post_type( absint( $_GET['post'] ) ) ) );
	if ( ! in_array( $screen, $allowed, true ) || ( 'edit.php' === $screen && 'product' !== $post_type ) || ( in_array( $screen, array( 'post-new.php', 'post.php' ), true ) && ! $editing_product ) ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=product' ) );
		exit;
	}
}
add_action( 'admin_init', 'clicko_restrict_staff_admin' );

function clicko_staff_admin_menu() {
	if ( ! is_user_logged_in() || ! in_array( 'clicko_staff', (array) wp_get_current_user()->roles, true ) ) {
		return;
	}
	global $menu;
	$allowed = array( 'index.php', 'edit.php?post_type=product', 'upload.php', 'profile.php' );
	foreach ( (array) $menu as $item ) {
		if ( isset( $item[2] ) && ! in_array( $item[2], $allowed, true ) ) {
			remove_menu_page( $item[2] );
		}
	}
}
add_action( 'admin_menu', 'clicko_staff_admin_menu', 999 );

function clicko_account_shortcode() {
	ob_start();
	?>
	<section class="clicko-customer-account clicko-shell" data-clicko-customer-account>
		<p class="clicko-eyebrow">CLICKO ACCOUNT</p><h1>บัญชีลูกค้า</h1>
		<p class="clicko-customer-account__notice" data-account-notice role="status" aria-live="polite"></p>
		<div class="clicko-customer-account__missing" data-account-config-missing hidden>ระบบบัญชียังไม่ได้ตั้งค่า Supabase กรุณาติดต่อร้าน</div>
		<div class="clicko-customer-account__auth" data-account-auth>
			<form data-account-login><h2>เข้าสู่ระบบ</h2><label>อีเมล<input name="email" type="email" autocomplete="email" required></label><label>รหัสผ่าน<input name="password" type="password" autocomplete="current-password" required></label><button class="clicko-button" type="submit">เข้าสู่ระบบ</button><button class="clicko-text-button" data-account-reset type="button">ลืมรหัสผ่าน?</button></form>
			<form data-account-register><h2>สมัครสมาชิก</h2><label>อีเมล<input name="email" type="email" autocomplete="email" required></label><label>รหัสผ่าน (อย่างน้อย 8 ตัว)<input name="password" type="password" autocomplete="new-password" minlength="8" required></label><button class="clicko-detail-button" type="submit">สมัครด้วยอีเมล</button></form>
			<div class="clicko-customer-account__facebook"><span>หรือ</span><button class="clicko-detail-button" data-account-facebook type="button">เข้าสู่ระบบด้วย Facebook</button></div>
		</div>
		<form class="clicko-customer-account__profile" data-account-profile hidden>
			<h2>ข้อมูลสมาชิก</h2><p>อีเมลบัญชี: <strong data-account-email></strong></p>
			<div class="clicko-customer-account__fields"><label>ชื่อ<input name="first_name" maxlength="100" autocomplete="given-name" required></label><label>นามสกุล<input name="last_name" maxlength="100" autocomplete="family-name" required></label><label>วันเดือนปีเกิด (ไม่บังคับ)<input name="birth_date" type="date" autocomplete="bday"></label><label>เบอร์โทรศัพท์ (ไม่บังคับ)<input name="phone" type="tel" maxlength="40" autocomplete="tel"></label><label class="clicko-customer-account__wide">ที่อยู่ปัจจุบัน (ไม่บังคับ)<textarea name="current_address" maxlength="1200" autocomplete="street-address"></textarea></label><label class="clicko-customer-account__wide" data-account-contact-email-wrap>อีเมลสำหรับติดต่อ (กรณีบัญชี Facebook ไม่มีอีเมล)<input name="contact_email" type="email" maxlength="254" autocomplete="email"></label></div>
			<button class="clicko-button" type="submit">บันทึกข้อมูลสมาชิก</button>
			<button class="clicko-text-button" data-account-logout type="button">ออกจากระบบ</button>
		</form>
		<form class="clicko-customer-account__reset" data-account-new-password hidden><h2>ตั้งรหัสผ่านใหม่</h2><label>รหัสผ่านใหม่<input name="password" type="password" minlength="8" autocomplete="new-password" required></label><button class="clicko-button" type="submit">บันทึกรหัสผ่าน</button></form>
		<p class="clicko-customer-account__privacy">สมัครสมาชิกเพื่อจัดเก็บข้อมูลตามที่กรอกเท่านั้น ไม่มีการขอเลขบัตรประชาชน · <a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>">นโยบายความเป็นส่วนตัว</a></p>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'clicko_account', 'clicko_account_shortcode' );

function clicko_supabase_catalog_shortcode( $attributes ) {
	$attributes = shortcode_atts( array( 'layout' => 'list', 'title' => 'เลือกซื้อสินค้า' ), $attributes, 'clicko_supabase_catalog' );
	$layout = in_array( $attributes['layout'], array( 'grid', 'list' ), true ) ? $attributes['layout'] : 'list';
	$catalog_class = 'clicko-supabase-catalog clicko-supabase-catalog--' . $layout;
	$category_chips = 'grid' === $layout ? '<div class="clicko-supabase-catalog__categories" data-catalog-category-chips role="group" aria-label="เลือกหมวดหมู่สินค้า"></div>' : '';
	return '<section class="' . esc_attr( $catalog_class ) . '" id="' . ( 'list' === $layout ? 'catalog' : 'all-products' ) . '" data-clicko-supabase-catalog><div class="clicko-supabase-catalog__heading"><div><p class="clicko-eyebrow">FIND YOUR GEAR</p><h2>' . esc_html( $attributes['title'] ) . '</h2><p>เลือกดูอุปกรณ์ตามหมวดหมู่ ค้นหา และกรองสินค้าได้</p></div><div class="clicko-supabase-catalog__count" data-catalog-count></div></div>' . $category_chips . '<div class="clicko-supabase-catalog__filters"><label>ค้นหา<input type="search" data-catalog-search placeholder="ชื่อสินค้า รุ่น หรือ SKU"></label><label class="clicko-supabase-catalog__category-select">หมวดหมู่<select data-catalog-category><option value="">ทุกหมวด</option></select></label><label>สภาพ<select data-catalog-condition><option value="">มือ 1 และมือ 2</option><option value="มือ1">มือ 1</option><option value="มือ2">มือ 2</option></select></label><label>สถานะ<select data-catalog-status><option value="available">พร้อมขาย</option><option value="reserved">จองแล้ว</option><option value="sold">ขายแล้ว</option><option value="all">ทุกสถานะ</option></select></label><label>เรียงตาม<select data-catalog-sort><option value="newest">มาใหม่</option><option value="price-low">ราคาต่ำไปสูง</option><option value="price-high">ราคาสูงไปต่ำ</option></select></label></div><p class="clicko-supabase-catalog__notice" data-catalog-notice role="status" aria-live="polite">กำลังโหลดสินค้า…</p><div class="clicko-product-grid' . ( 'grid' === $layout ? '' : ' is-list' ) . '" data-catalog-grid></div><aside class="clicko-supabase-selection" data-catalog-selection hidden><span data-selection-count></span><button type="button" class="clicko-button" data-selection-send>คัดลอกรายการและเปิด Facebook</button><button type="button" class="clicko-text-button" data-selection-clear>ล้างรายการ</button></aside><dialog class="clicko-supabase-product-dialog" data-catalog-dialog><button type="button" class="clicko-supabase-product-dialog__close" data-catalog-close aria-label="ปิดรายละเอียด">×</button><div data-catalog-detail></div></dialog></section>';
}
add_shortcode( 'clicko_supabase_catalog', 'clicko_supabase_catalog_shortcode' );

function clicko_staff_dashboard_shortcode() {
	$catalog_admin_url = 'https://clicko-store.github.io/admin.html';
	return '<section class="clicko-staff-dashboard clicko-shell"><p class="clicko-eyebrow">CLICKO STORE WORKSPACE</p><h1>แดชบอร์ดร้านค้า</h1><p>พนักงานและผู้ดูแลเข้าสู่ระบบด้วยบัญชี Supabase ที่ร้านอนุมัติไว้</p><div class="clicko-staff-dashboard__actions"><a class="clicko-button" href="' . esc_url( $catalog_admin_url ) . '" target="_blank" rel="noopener noreferrer">เข้าสู่ระบบจัดการสินค้า</a><a class="clicko-detail-button" href="' . esc_url( home_url( '/trade-in-admin/' ) ) . '">ดูรายการรับซื้อ / เทิร์น</a><a class="clicko-detail-button" href="' . esc_url( home_url( '/#catalog' ) ) . '">ดูหน้าร้าน</a></div><p class="clicko-staff-dashboard__note">พนักงานเห็นเฉพาะข้อมูลสินค้า รูปภาพ และคำขอรับซื้อ/เทิร์นที่จำเป็นต่อการทำงาน</p></section>';
}
add_shortcode( 'clicko_staff_dashboard', 'clicko_staff_dashboard_shortcode' );

function clicko_trade_in_shortcode() {
	ob_start();
	?>
	<section class="clicko-trade-in" aria-labelledby="clicko-trade-title">
		<div><p class="clicko-eyebrow">BUYBACK / TRADE-IN</p><h2 id="clicko-trade-title">รับซื้อ / เทิร์นอุปกรณ์</h2><p>กรอกรุ่น สภาพ และแนบรูปสินค้า ระบบจะบันทึกคำขอไว้ในระบบส่วนตัวของร้านเพื่อให้ Kew ตรวจสอบ</p></div>
		<form data-clicko-trade-form enctype="multipart/form-data">
			<label class="clicko-trade-in__honeypot" aria-hidden="true">เว็บไซต์<input name="company_website" type="text" tabindex="-1" autocomplete="off"></label>
			<label>รุ่น / ชื่อสินค้า<input name="model" maxlength="160" required placeholder="เช่น การ์ดจอ RTX 4070"></label>
			<label>ประกันสินค้า<input name="warranty" maxlength="100" placeholder="เช่น เหลือประกันถึง 12/2027"></label>
			<label class="clicko-trade-in__wide">สภาพ / ตำหนิ<textarea name="condition" maxlength="500" required placeholder="บอกสภาพการใช้งาน ตำหนิ และอุปกรณ์ที่มี"></textarea></label>
			<label class="clicko-trade-in__wide">รูปสินค้าจริง<input name="photos" type="file" accept="image/jpeg,image/png,image/webp" multiple required><small>แนบอย่างน้อย 1 รูป สูงสุด 4 รูป JPG, PNG หรือ WEBP ไม่เกิน 5 MB ต่อรูป</small></label>
			<div class="clicko-trade-in__captcha clicko-trade-in__wide" data-clicko-turnstile></div>
			<button class="clicko-button" type="submit">ส่งข้อมูลและรูปให้ร้าน <span aria-hidden="true">↗</span></button>
			<p class="clicko-trade-in__status" data-clicko-trade-status role="status" aria-live="polite">ข้อมูลและรูปจะถูกจัดเก็บแบบส่วนตัวเพื่อให้ร้านตรวจสอบ</p>
		</form>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'clicko_trade_in', 'clicko_trade_in_shortcode' );

function clicko_trade_in_dashboard_shortcode() {
	ob_start();
	?>
	<section class="clicko-trade-in-dashboard clicko-shell" data-clicko-trade-dashboard>
		<p class="clicko-eyebrow">CLICKO / PRIVATE REQUESTS</p>
		<h1>รายการรับซื้อ / เทิร์น</h1>
		<p class="clicko-trade-in-dashboard__notice" data-trade-dashboard-notice role="status" aria-live="polite"></p>
		<div class="clicko-trade-in-dashboard__missing" data-trade-dashboard-config-missing hidden>ระบบยังไม่ได้ตั้งค่า Supabase</div>
		<div class="clicko-trade-in-dashboard__login" data-trade-dashboard-login>
			<form data-trade-dashboard-login-form><label>อีเมล<input name="email" type="email" autocomplete="username" required></label><label>รหัสผ่าน<input name="password" type="password" autocomplete="current-password" required></label><button class="clicko-button" type="submit">เข้าสู่ระบบเพื่อดูรายการ</button></form>
		</div>
		<div class="clicko-trade-in-dashboard__content" data-trade-dashboard-content hidden>
			<div class="clicko-trade-in-dashboard__toolbar"><label>สถานะ<select data-trade-dashboard-filter><option value="all">ทุกสถานะ</option><option value="new">ใหม่</option><option value="contacted">ติดต่อแล้ว</option><option value="evaluated">ประเมินแล้ว</option><option value="closed">ปิดรายการ</option><option value="spam">สแปม</option></select></label><button class="clicko-detail-button" data-trade-dashboard-refresh type="button">โหลดใหม่</button><button class="clicko-text-button" data-trade-dashboard-logout type="button">ออกจากระบบ</button></div>
			<div class="clicko-trade-in-dashboard__list" data-trade-dashboard-list></div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'clicko_trade_in_dashboard', 'clicko_trade_in_dashboard_shortcode' );

function clicko_reviews_shortcode() {
	$post_url = 'https://www.facebook.com/Clicko.gg/posts/pfbid022wNTWG9VhE5CiV22LM4LL9VyVEf1hp1GRgt2SsEcqrabxyvo3mHS373nYR2514sjl';
	$embed_url = 'https://www.facebook.com/plugins/post.php?href=' . rawurlencode( $post_url ) . '&show_text=true&width=500';
	return '<section class="clicko-reviews"><p class="clicko-eyebrow">CLICKO / CUSTOMER REVIEWS</p><h2>รีวิวจากลูกค้า</h2><p>ขอบคุณลูกค้าทุกท่านที่ไว้วางใจ Clicko</p><div class="clicko-reviews__embed"><iframe src="' . esc_url( $embed_url ) . '" title="โพสต์รีวิวร้าน Clicko บน Facebook" width="500" height="649" loading="lazy" scrolling="no" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowfullscreen="true"></iframe></div></section>';
}
add_shortcode( 'clicko_reviews', 'clicko_reviews_shortcode' );

function clicko_contact_details_shortcode() {
	$settings = clicko_get_settings();
	$contact_urls = clicko_get_contact_urls();
	$map_url = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( 'ซอยรัชดา 36' );
	ob_start();
	?>
	<section class="clicko-contact-details">
		<p class="clicko-eyebrow">CLICKO / CONTACT</p>
		<h2>ติดต่อเรา</h2>
		<p class="clicko-contact-details__intro">สอบถามสินค้าและนัดหมายกับร้าน Clicko ได้ผ่านช่องทางต่อไปนี้</p>
		<div class="clicko-contact-details__grid">
			<a class="clicko-contact-details__card" href="<?php echo esc_url( $contact_urls['profile'] ); ?>" target="_blank" rel="noopener noreferrer"><span class="clicko-contact-details__icon" aria-hidden="true">f</span><span><strong>Facebook เจ้าของร้าน</strong><small>Kew Sippavit · ติดต่อซื้อขายโดยตรง</small></span><span aria-hidden="true">↗</span></a>
			<a class="clicko-contact-details__card" href="<?php echo esc_url( $contact_urls['brand'] ); ?>" target="_blank" rel="noopener noreferrer"><span class="clicko-contact-details__icon" aria-hidden="true">f</span><span><strong>Facebook Page ร้าน</strong><small>Clicko · Gaming Gear &amp; PC Parts</small></span><span aria-hidden="true">↗</span></a>
			<?php if ( ! empty( $settings['contact_phone'] ) ) : ?>
				<a class="clicko-contact-details__card" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $settings['contact_phone'] ) ); ?>"><span class="clicko-contact-details__icon" aria-hidden="true">☎</span><span><strong>โทรศัพท์</strong><small><?php echo esc_html( $settings['contact_phone'] ); ?></small></span><span aria-hidden="true">↗</span></a>
			<?php endif; ?>
			<a class="clicko-contact-details__card" href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer"><span class="clicko-contact-details__icon" aria-hidden="true">⌖</span><span><strong>จุดนัดรับส่งพัสดุ</strong><small>ซอยรัชดา 36</small></span><span aria-hidden="true">↗</span></a>
		</div>
		<p class="clicko-contact-details__note">กรุณานัดหมายกับร้านล่วงหน้าก่อนเดินทางหรือส่งพัสดุ</p>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode( 'clicko_contact_details', 'clicko_contact_details_shortcode' );

function clicko_add_product_stock_state_box() {
	add_meta_box( 'clicko-product-stock-state', 'สถานะสินค้า Clicko', 'clicko_render_product_stock_state_box', 'product', 'side', 'high' );
}
add_action( 'add_meta_boxes_product', 'clicko_add_product_stock_state_box' );

function clicko_render_product_stock_state_box( $post ) {
	$state = get_post_meta( $post->ID, '_clicko_stock_state', true );
	$cost = get_post_meta( $post->ID, '_clicko_unit_cost', true );
	if ( ! in_array( $state, array( 'available', 'reserved', 'sold' ), true ) ) {
		$state = 'instock' === get_post_meta( $post->ID, '_stock_status', true ) ? 'available' : 'sold';
	}
	wp_nonce_field( 'clicko_save_stock_state', 'clicko_stock_state_nonce' );
	?>
	<label for="clicko-stock-state">หน้าร้านแสดง</label>
	<select id="clicko-stock-state" name="clicko_stock_state" style="width:100%">
		<option value="available" <?php selected( $state, 'available' ); ?>>พร้อมขาย</option>
		<option value="reserved" <?php selected( $state, 'reserved' ); ?>>จองแล้ว</option>
		<option value="sold" <?php selected( $state, 'sold' ); ?>>ขายแล้ว</option>
	</select>
	<p class="description">สถานะนี้ใช้แสดงในแค็ตตาล็อก Clicko โดยไม่เปลี่ยนยอดสต๊อก WooCommerce</p>
	<p><label for="clicko-unit-cost">ต้นทุนต่อชิ้น (บาท)</label><input id="clicko-unit-cost" name="clicko_unit_cost" type="number" min="0" step="0.01" value="<?php echo esc_attr( $cost ); ?>" style="width:100%"></p>
	<p class="description">ข้อมูลต้นทุนนี้แสดงเฉพาะผู้มีสิทธิ์แก้ไขสินค้า</p>
	<?php
}

function clicko_save_product_stock_state( $post_id ) {
	if ( ! isset( $_POST['clicko_stock_state_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['clicko_stock_state_nonce'] ) ), 'clicko_save_stock_state' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$state = isset( $_POST['clicko_stock_state'] ) ? sanitize_key( wp_unslash( $_POST['clicko_stock_state'] ) ) : '';
	if ( in_array( $state, array( 'available', 'reserved', 'sold' ), true ) ) {
		update_post_meta( $post_id, '_clicko_stock_state', $state );
	}
	if ( isset( $_POST['clicko_unit_cost'] ) ) {
		$cost = trim( (string) wp_unslash( $_POST['clicko_unit_cost'] ) );
		if ( '' === $cost ) {
			delete_post_meta( $post_id, '_clicko_unit_cost' );
		} elseif ( is_numeric( $cost ) && (float) $cost >= 0 ) {
			update_post_meta( $post_id, '_clicko_unit_cost', function_exists( 'wc_format_decimal' ) ? wc_format_decimal( $cost ) : (string) (float) $cost );
		}
	}
}
add_action( 'save_post_product', 'clicko_save_product_stock_state' );

function clicko_keep_catalog_only() {
	$legacy_account = function_exists( 'is_account_page' ) && is_account_page() && ! is_page( 'account' );
	if ( ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) || $legacy_account ) {
		wp_safe_redirect( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/products/' ), 302 );
		exit;
	}
}
add_action( 'template_redirect', 'clicko_keep_catalog_only', 1 );
