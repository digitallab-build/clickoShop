<?php
/**
 * Plugin Name: Clicko Catalog Tools
 * Description: Seller contact settings and a shareable product selection for the Clicko catalog.
 * Version: 1.1.0
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * Author: Clicko
 * Text Domain: clicko-tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CLICKO_TOOLS_VERSION', '1.1.0' );
define( 'CLICKO_TOOLS_OPTION', 'clicko_tools_settings' );

function clicko_default_settings() {
	return array(
		'seller_profile_url'   => 'https://www.facebook.com/sippavit.gomarn',
		'seller_messenger_url' => '',
		'brand_page_url'       => 'https://www.facebook.com/Clicko.gg/',
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
	return array(
		'seller_profile_url'   => $profile ? $profile : $defaults['seller_profile_url'],
		'seller_messenger_url' => clicko_sanitize_contact_url( $input['seller_messenger_url'] ?? '', 'messenger' ),
		'brand_page_url'       => $brand ? $brand : $defaults['brand_page_url'],
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
	wp_enqueue_script( 'clicko-tools', $base . 'assets/selection.js', array(), CLICKO_TOOLS_VERSION, true );
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
	clicko_create_page_once( 'how-to-buy', 'วิธีซื้อสินค้า', '<h2>เลือกสินค้าแล้วติดต่อผู้ขาย</h2><ol><li>เลือกสินค้าและรุ่นที่สนใจ แล้วเพิ่มลงในรายการ</li><li>ตรวจจำนวนและคัดลอกรายการสินค้า</li><li>เปิด Messenger ของผู้ขาย วางรายการ แล้วส่งข้อความ</li><li>รอผู้ขายยืนยันราคา สินค้าคงเหลือ และค่าจัดส่ง</li></ol><p>รายการที่สนใจยังไม่ใช่คำสั่งซื้อและยังไม่มีการจองสินค้า</p><p>[clicko_contact_button] &nbsp; [clicko_brand_link]</p>' );
	clicko_create_page_once( 'contact', 'ติดต่อร้าน', '<h2>ติดต่อซื้อขาย</h2><p>การซื้อขายทำผ่าน Facebook ส่วนตัวของผู้ขาย กรุณาส่งรายการสินค้าที่สนใจเพื่อยืนยันราคาและสต็อก</p><p>[clicko_contact_button]</p><h2>ติดตามข่าวสาร</h2><p>[clicko_brand_link]</p>' );
	clicko_create_page_once( 'warranty-returns', 'ประกันและการคืนสินค้า', '<h2>ข้อมูลประกัน</h2><p>ผู้ขายจะยืนยันเงื่อนไขประกันและการคืนสินค้าตามสินค้าแต่ละรายการผ่าน Facebook ก่อนตกลงซื้อขาย กรุณาเพิ่มเนื้อหาตามเงื่อนไขจริงของร้านก่อนเปิดเว็บไซต์</p><p>[clicko_contact_button]</p>' );
	if ( $home_id && ! absint( get_option( 'page_on_front' ) ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
	}
}
register_activation_hook( __FILE__, 'clicko_activate_plugin' );

function clicko_keep_catalog_only() {
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		wp_safe_redirect( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/products/' ), 302 );
		exit;
	}
}
add_action( 'template_redirect', 'clicko_keep_catalog_only', 1 );
