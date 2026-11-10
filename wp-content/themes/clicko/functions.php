<?php
/** Clicko theme setup and small presentation helpers. */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function clicko_theme_setup() {
	load_theme_textdomain( 'clicko', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 72, 'width' => 220, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'woocommerce' );
	register_nav_menus( array( 'primary' => __( 'Main menu', 'clicko' ) ) );
}
add_action( 'after_setup_theme', 'clicko_theme_setup' );

function clicko_default_menu() {
	echo '<ul class="clicko-nav__list">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">หน้าแรก</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/products/' ) ) . '">สินค้าทั้งหมด</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/trade-in/' ) ) . '">รับซื้อ / เทิร์น</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/reviews/' ) ) . '">รีวิวร้าน</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/contact/' ) ) . '">ติดต่อร้าน</a></li>';
	echo '</ul>';
}

function clicko_add_products_menu_item( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location || false !== strpos( $items, 'products' ) ) {
		return $items;
	}
	return $items . '<li class="menu-item menu-item-clicko-products"><a href="' . esc_url( home_url( '/products/' ) ) . '">สินค้าทั้งหมด</a></li>';
}
add_filter( 'wp_nav_menu_items', 'clicko_add_products_menu_item', 5, 2 );

function clicko_remove_how_to_buy_menu_item( $items, $args ) {
	foreach ( $items as $key => $item ) {
		$path = wp_parse_url( $item->url, PHP_URL_PATH );
		if ( is_string( $path ) && '/how-to-buy' === untrailingslashit( $path ) ) {
			unset( $items[ $key ] );
		}
	}
	return $items;
}
add_filter( 'wp_nav_menu_objects', 'clicko_remove_how_to_buy_menu_item', 20, 2 );

function clicko_add_trade_in_menu_item( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location || false !== strpos( $items, 'trade-in' ) ) {
		return $items;
	}
	$items .= '<li class="menu-item menu-item-clicko-trade-in"><a href="' . esc_url( home_url( '/trade-in/' ) ) . '">รับซื้อ / เทิร์น</a></li>';
	return $items;
}
add_filter( 'wp_nav_menu_items', 'clicko_add_trade_in_menu_item', 10, 2 );

function clicko_add_reviews_menu_item( $items, $args ) {
	if ( empty( $args->theme_location ) || 'primary' !== $args->theme_location || false !== strpos( $items, 'reviews' ) ) {
		return $items;
	}
	return $items . '<li class="menu-item menu-item-clicko-reviews"><a href="' . esc_url( home_url( '/reviews/' ) ) . '">รีวิวร้าน</a></li>';
}
add_filter( 'wp_nav_menu_items', 'clicko_add_reviews_menu_item', 20, 2 );

function clicko_theme_assets() {
	$theme = wp_get_theme();
	wp_enqueue_style( 'clicko-theme', get_stylesheet_uri(), array(), $theme->get( 'Version' ) );
	wp_enqueue_style( 'clicko-fonts', 'https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;800&display=swap', array(), null );
	wp_enqueue_style( 'clicko-storefront', get_template_directory_uri() . '/assets/css/storefront.css', array( 'clicko-theme', 'clicko-fonts' ), $theme->get( 'Version' ) );
	$accent_value = get_theme_mod( 'clicko_accent_color' );
	if ( ! $accent_value || '#1676df' === strtolower( $accent_value ) ) {
		$accent_value = '#D90429';
	}
	$accent = sanitize_hex_color( $accent_value );
	if ( $accent ) {
		wp_add_inline_style( 'clicko-storefront', ':root{--clicko-accent:' . $accent . ';}' );
	}
}
add_action( 'wp_enqueue_scripts', 'clicko_theme_assets' );

function clicko_theme_customize( $wp_customize ) {
	$wp_customize->add_section( 'clicko_home', array(
		'title'       => __( 'Clicko homepage', 'clicko' ),
		'description' => __( 'Edit the home page introduction without changing code.', 'clicko' ),
		'priority'    => 35,
	) );
	$fields = array(
		'clicko_home_eyebrow' => array( 'label' => __( 'Hero label', 'clicko' ), 'default' => 'CLICKO / GAMING GEAR & PC PARTS', 'type' => 'text' ),
		'clicko_home_headline' => array( 'label' => __( 'Home page headline', 'clicko' ), 'default' => "รับซื้อและขาย\nคอมพิวเตอร์มือสอง\nและอุปกรณ์เกมมิง ครบ จบ ในที่เดียว", 'type' => 'textarea' ),
		'clicko_home_description' => array( 'label' => __( 'Home page introduction', 'clicko' ), 'default' => 'รวมคอมพิวเตอร์มือสองและอุปกรณ์เกมมิง เลือกสินค้าที่สนใจแล้วสอบถามผู้ขายได้โดยตรง', 'type' => 'textarea' ),
		'clicko_brand_tagline' => array( 'label' => __( 'Brand tagline', 'clicko' ), 'default' => 'GAMING GEAR & PC PARTS', 'type' => 'text' ),
		'clicko_topbar_message' => array( 'label' => __( 'Top bar message', 'clicko' ), 'default' => 'PC PARTS · GAMING GEAR · BUILD YOUR SETUP', 'type' => 'text' ),
		'clicko_accent_color' => array( 'label' => __( 'Accent color', 'clicko' ), 'default' => '#D90429', 'type' => 'color' ),
	);
	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $field['default'],
			'sanitize_callback' => 'color' === $field['type'] ? 'sanitize_hex_color' : ( 'textarea' === $field['type'] ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $field['label'],
			'section' => 'clicko_home',
			'type'    => $field['type'],
		) );
	}
}
add_action( 'customize_register', 'clicko_theme_customize' );

function clicko_register_query_vars( $vars ) {
	$vars[] = 'clicko_availability';
	return $vars;
}
add_filter( 'query_vars', 'clicko_register_query_vars' );

function clicko_filter_catalog_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ( ! $query->is_post_type_archive( 'product' ) && ! $query->is_tax( 'product_cat' ) && ! $query->is_search() ) ) {
		return;
	}
	$availability = sanitize_key( $query->get( 'clicko_availability' ) );
	if ( '' === $availability ) { $availability = 'available'; }
	if ( 'all' === $availability ) { return; }
	if ( ! in_array( $availability, array( 'available', 'reserved', 'sold' ), true ) ) {
		return;
	}
	$meta_query = (array) $query->get( 'meta_query' );
	if ( 'available' === $availability || 'sold' === $availability ) {
		$legacy_stock = 'available' === $availability ? 'instock' : 'outofstock';
		$meta_query[] = array( 'relation' => 'OR', array( 'key' => '_clicko_stock_state', 'value' => $availability, 'compare' => '=' ), array( 'relation' => 'AND', array( 'key' => '_clicko_stock_state', 'compare' => 'NOT EXISTS' ), array( 'key' => '_stock_status', 'value' => $legacy_stock, 'compare' => '=' ) ) );
	} else {
		$meta_query[] = array( 'key' => '_clicko_stock_state', 'value' => $availability, 'compare' => '=' );
	}
	$query->set( 'meta_query', $meta_query );
}
add_action( 'pre_get_posts', 'clicko_filter_catalog_query' );

function clicko_product_card( $product ) {
	if ( ! ( $product instanceof WC_Product ) ) {
		return;
	}
	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	$category = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
	$condition = $product->get_attribute( 'condition' ) ?: $product->get_attribute( 'pa_condition' );
	$warranty = $product->get_attribute( 'warranty' ) ?: $product->get_attribute( 'pa_warranty' );
	$stock_state = get_post_meta( $product->get_id(), '_clicko_stock_state', true );
	if ( ! in_array( $stock_state, array( 'available', 'reserved', 'sold' ), true ) ) {
		$stock_state = $product->is_in_stock() ? 'available' : 'sold';
	}
	$stock_labels = array( 'available' => 'พร้อมขาย', 'reserved' => 'จองแล้ว', 'sold' => 'ขายแล้ว' );
	$specs = array();
	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute->get_visible() ) { continue; }
		$value = $product->get_attribute( $attribute->get_name() );
		if ( '' === $value ) { continue; }
		$specs[] = array( 'label' => wc_attribute_label( $attribute->get_name() ), 'value' => $value );
		if ( count( $specs ) >= 3 ) { break; }
	}
	?>
	<article class="clicko-product-card">
		<a class="clicko-product-card__image" href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="ดู <?php echo esc_attr( $product->get_name() ); ?>">
			<?php if ( $product->get_image_id() ) : ?>
				<?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ) ); ?>
			<?php else : ?>
				<span class="clicko-product-card__placeholder" aria-hidden="true"><span class="clicko-product-card__placeholder-icon">▣</span>CLICKO</span>
			<?php endif; ?>
		</a>
		<div class="clicko-product-card__body">
			<div class="clicko-product-card__title-row">
				<div><?php if ( $category ) : ?><p class="clicko-eyebrow"><?php echo esc_html( $category ); ?></p><?php endif; ?><h3><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3><?php if ( $product->get_sku() ) : ?><p class="clicko-product-card__sku">SKU: <?php echo esc_html( $product->get_sku() ); ?></p><?php endif; ?></div>
				<?php if ( $condition ) : ?><span class="clicko-product-condition"><?php echo esc_html( $condition ); ?></span><?php endif; ?>
			</div>
			<?php if ( $specs ) : ?><div class="clicko-product-specs-row"><?php foreach ( $specs as $spec ) : ?><span><b><?php echo esc_html( $spec['value'] ); ?></b><small><?php echo esc_html( $spec['label'] ); ?></small></span><?php endforeach; ?></div><?php endif; ?>
			<?php if ( $warranty ) : ?><p class="clicko-product-warranty"><span aria-hidden="true">✓</span> ประกัน <?php echo esc_html( $warranty ); ?></p><?php endif; ?>
		</div>
		<div class="clicko-product-card__purchase">
			<span class="clicko-stock-status is-<?php echo esc_attr( $stock_state ); ?>"><i></i><?php echo esc_html( $stock_labels[ $stock_state ] ); ?></span>
			<div class="clicko-product-card__price"><?php echo $product->get_price_html() ? wp_kses_post( $product->get_price_html() ) : '<span>สอบถามราคา</span>'; ?></div>
			<?php echo do_shortcode( '[clicko_product_action product_id="' . absint( $product->get_id() ) . '"]' ); ?>
			<a class="clicko-detail-button" href="<?php echo esc_url( $product->get_permalink() ); ?>">ดูรายละเอียด <span aria-hidden="true">→</span></a>
		</div>
	</article>
	<?php
}

function clicko_category_icon( $slug, $name = '' ) {
	$key = strtolower( $slug . ' ' . $name );
	$icons = array(
		'display' => '<rect x="3" y="4" width="18" height="13" rx="1.5"/><path d="M8 21h8M12 17v4"/>',
		'gpu' => '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="12" r="3"/><circle cx="17" cy="12" r="1.5"/><path d="M5 3v2m4-2v2m4-2v2m4-2v2m4-2v2"/>',
		'cpu' => '<rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 9h6v6H9zM9 2v4m6-4v4M9 18v4m6-4v4M2 9h4m-4 6h4m12-6h4m-4 6h4"/>',
		'motherboard' => '<rect x="5" y="3" width="14" height="18" rx="1.5"/><rect x="8" y="6" width="8" height="6" rx="1"/><path d="M8 15h2m2 0h2m2 0h.01M8 18h8"/>',
		'keyboard' => '<rect x="2.5" y="6" width="19" height="12" rx="2"/><path d="M6 10h.01M9 10h.01M12 10h.01M15 10h.01M18 10h.01M6 13h.01M9 13h.01M12 13h.01M15 13h.01M18 13h.01M8 16h8"/>',
		'mouse' => '<path d="M12 22a7 7 0 0 0 7-7V9a7 7 0 0 0-14 0v6a7 7 0 0 0 7 7Z"/><path d="M12 2v7m-7 0h14"/>',
		'audio' => '<path d="M3 14v-3a9 9 0 0 1 18 0v3"/><rect x="2" y="13" width="5" height="8" rx="2"/><rect x="17" y="13" width="5" height="8" rx="2"/><path d="M19 21a7 7 0 0 1-7 2"/>',
		'other' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
	);
	$match = 'other';
	$terms = array(
		'display' => array( 'monitor', 'display', 'จอ' ),
		'gpu' => array( 'gpu', 'graphic', 'vga', 'การ์ดจอ' ),
		'cpu' => array( 'cpu', 'processor', 'ซีพียู' ),
		'motherboard' => array( 'motherboard', 'mainboard', 'เมนบอร์ด' ),
		'keyboard' => array( 'keyboard', 'คีย์บอร์ด' ),
		'mouse' => array( 'mouse', 'เมาส์' ),
		'audio' => array( 'headphone', 'headset', 'audio', 'หูฟัง' ),
	);
	foreach ( $terms as $icon => $needles ) {
		foreach ( $needles as $needle ) {
			if ( false !== strpos( $key, strtolower( $needle ) ) ) { $match = $icon; break 2; }
		}
	}
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[ $match ] . '</svg>';
}

function clicko_render_categories() {
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/products/' );
	$terms = taxonomy_exists( 'product_cat' ) ? get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'number' => 8, 'parent' => 0 ) ) : array();
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		$defaults = array(
			array( 'display', 'จอเกมมิ่ง' ), array( 'gpu', 'การ์ดจอ' ), array( 'cpu', 'CPU' ),
			array( 'motherboard', 'เมนบอร์ด' ), array( 'keyboard', 'คีย์บอร์ด' ), array( 'mouse', 'เมาส์' ),
			array( 'audio', 'หูฟัง' ), array( 'other', 'อื่น ๆ' ),
		);
		foreach ( $defaults as $item ) {
			echo '<a class="clicko-category-link" href="' . esc_url( $shop_url ) . '">' . clicko_category_icon( $item[0] ) . '<span>' . esc_html( $item[1] ) . '</span></a>';
		}
		return;
	}
	foreach ( $terms as $term ) {
		echo '<a class="clicko-category-link" href="' . esc_url( get_term_link( $term ) ) . '">' . clicko_category_icon( $term->slug, $term->name ) . '<span>' . esc_html( $term->name ) . '</span></a>';
	}
}

function clicko_render_catalog_toolbar() {
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/products/' );
	$terms = taxonomy_exists( 'product_cat' ) ? get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'parent' => 0 ) ) : array();
	$selected_category = sanitize_title( get_query_var( 'product_cat' ) );
	$selected_stock = sanitize_key( get_query_var( 'clicko_availability' ) );
	if ( '' === $selected_stock ) { $selected_stock = 'available'; }
	$selected_order = sanitize_key( get_query_var( 'orderby' ) );
	?>
	<form class="clicko-product-toolbar" method="get" action="<?php echo esc_url( $shop_url ); ?>">
		<label><span>หมวดหมู่</span><select name="product_cat"><option value="">ทั้งหมด</option><?php if ( ! is_wp_error( $terms ) ) : foreach ( $terms as $term ) : ?><option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $selected_category, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option><?php endforeach; endif; ?></select></label>
		<label><span>สถานะ</span><select name="clicko_availability"><option value="all" <?php selected( $selected_stock, 'all' ); ?>>ทั้งหมด</option><option value="available" <?php selected( $selected_stock, 'available' ); ?>>พร้อมขาย</option><option value="reserved" <?php selected( $selected_stock, 'reserved' ); ?>>จองแล้ว</option><option value="sold" <?php selected( $selected_stock, 'sold' ); ?>>ขายแล้ว</option></select></label>
		<label><span>เรียงตาม</span><select name="orderby"><option value="menu_order" <?php selected( $selected_order, 'menu_order' ); ?>>แนะนำ</option><option value="date" <?php selected( $selected_order, 'date' ); ?>>มาใหม่</option><option value="price" <?php selected( $selected_order, 'price' ); ?>>ราคาต่ำไปสูง</option><option value="price-desc" <?php selected( $selected_order, 'price-desc' ); ?>>ราคาสูงไปต่ำ</option></select></label>
		<input type="hidden" name="post_type" value="product">
		<button class="clicko-button clicko-button--filter" type="submit">กรองสินค้า</button>
		<div class="clicko-view-toggle" role="group" aria-label="รูปแบบแสดงสินค้า"><button type="button" data-clicko-view="grid" aria-label="แสดงแบบตาราง">▦</button><button type="button" class="is-active" data-clicko-view="list" aria-label="แสดงแบบรายการ">☷</button></div>
	</form>
	<?php
}

function clicko_render_featured_products() {
	if ( ! function_exists( 'wc_get_products' ) ) {
		echo '<p class="clicko-empty-note">ติดตั้ง WooCommerce แล้วเพิ่มสินค้าในหลังบ้าน เพื่อแสดงรายการบนหน้านี้</p>';
		return;
	}
	$products = wc_get_products( array( 'status' => 'publish', 'limit' => 4, 'featured' => true, 'orderby' => 'date', 'order' => 'DESC' ) );
	if ( empty( $products ) ) {
		$products = wc_get_products( array( 'status' => 'publish', 'limit' => 4, 'orderby' => 'date', 'order' => 'DESC' ) );
	}
	if ( empty( $products ) ) {
		echo '<p class="clicko-empty-note">สินค้าจะแสดงที่นี่เมื่อผู้ขายเพิ่มสินค้าใน Dashboard</p>';
		return;
	}
	foreach ( $products as $product ) {
		clicko_product_card( $product );
	}
}
