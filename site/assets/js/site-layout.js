(function () {
	'use strict';
	var config = window.CLICKO_SITE || {};
	var base = config.baseUrl || new URL('../', window.location.href).href;
	var seller = config.sellerUrl || 'https://www.facebook.com/sippavit.gomarn/';
	var brand = config.brandPageUrl || 'https://www.facebook.com/Clicko.gg/';
	var routes = [
		['หน้าแรก', ''], ['สินค้าทั้งหมด', 'products/'], ['รับซื้อ / เทิร์น', 'trade-in/'],
		['รีวิวร้าน', 'reviews/'], ['ติดต่อเรา', 'contact/']
	];
	function route(path) { return new URL(path, base).href; }
	function escape(value) { return String(value).replace(/[&<>"']/g, function (character) { return ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' })[character]; }); }
	function logoMark() {
		return '<span class="clicko-logo-mark" aria-hidden="true"><span class="clicko-logo-key">C</span><span class="clicko-logo-key">L</span><span class="clicko-logo-key">I</span><span class="clicko-logo-key">C</span><span class="clicko-logo-key">K</span><span class="clicko-logo-key clicko-logo-key--mouse"><i></i></span></span><span class="screen-reader-text">Clicko Gaming Gear &amp; PC Parts</span>';
	}
	function buildHeader() {
		var nav = routes.map(function (item) { return '<li><a href="' + route(item[1]) + '">' + escape(item[0]) + '</a></li>'; }).join('');
		return '<div class="clicko-topbar"><div class="clicko-shell"><span>' + escape(config.announcement || 'GAMING GEAR · PC PARTS · BUY / SELL / TRADE') + '</span><a href="' + escape(brand) + '" target="_blank" rel="noopener noreferrer">ติดตามข่าวสารที่เพจ Clicko ↗</a></div></div>' +
		'<header class="clicko-header"><div class="clicko-shell clicko-header__inner"><div class="clicko-brand"><a class="clicko-brand__home" href="' + route('') + '" aria-label="Clicko หน้าแรก">' + logoMark() + '</a><span class="clicko-brand__tagline">' + escape(config.tagline || 'GAMING GEAR & PC PARTS') + '</span></div>' +
		'<button class="clicko-menu-toggle" type="button" aria-expanded="false" aria-controls="clicko-primary-nav"><span class="screen-reader-text">เปิดเมนู</span><span></span><span></span></button>' +
		'<nav id="clicko-primary-nav" class="clicko-nav" aria-label="เมนูหลัก"><ul class="clicko-nav__list">' + nav + '</ul></nav>' +
		'<div class="clicko-header__actions"><form class="clicko-header-search" role="search" method="get" action="' + route('products/') + '"><label class="screen-reader-text" for="clicko-site-search">ค้นหาสินค้า</label><input id="clicko-site-search" type="search" name="clicko_q" placeholder="ค้นหาสินค้า..."><button type="submit" aria-label="ค้นหา"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg></button></form>' +
		'<a class="clicko-selection-link" data-clicko-interest-link href="' + route('products/') + '" aria-label="รายการสินค้าที่สนใจ"><span aria-hidden="true">♡</span><span class="clicko-selection-count" data-clicko-count>0</span></a>' +
		'<a class="clicko-account-link" href="' + route('account/') + '" aria-label="เข้าสู่ระบบ / สมัครสมาชิก"><span aria-hidden="true">♙</span><span class="clicko-account-link__label">เข้าสู่ระบบ</span></a>' +
		'<a class="clicko-facebook-link" href="' + escape(brand) + '" target="_blank" rel="noopener noreferrer" aria-label="ติดตามเพจ Clicko"><span aria-hidden="true">f</span></a></div></div></header>';
	}
	function buildFooter() {
		return '<section class="clicko-trustbar" aria-label="บริการและช่องทางของ Clicko"><div class="clicko-shell clicko-trustbar__inner">' +
		'<div class="clicko-trust-item"><span class="clicko-trust-icon" aria-hidden="true">↗</span><span><b>จัดส่งทั่วไทย</b><small>สอบถามวิธีจัดส่งกับผู้ขาย</small></span></div>' +
		'<div class="clicko-trust-item"><span class="clicko-trust-icon" aria-hidden="true">✓</span><span><b>ตรวจสอบก่อนซื้อ</b><small>ขอรายละเอียดสินค้าก่อนตกลง</small></span></div>' +
		'<div class="clicko-trust-item"><span class="clicko-trust-icon" aria-hidden="true">⌁</span><span><b>คุยกับผู้ขายโดยตรง</b><small>ซื้อขายผ่าน Facebook ส่วนตัว</small></span></div>' +
		'<div class="clicko-trust-follow"><span>ติดตามข่าวสารได้ที่</span><a href="' + escape(brand) + '" target="_blank" rel="noopener noreferrer"><span aria-hidden="true">f</span> Facebook</a></div></div></section>' +
		'<footer class="clicko-footer"><div class="clicko-shell"><div class="clicko-footer__main"><div><a class="clicko-brand__home clicko-brand__home--footer" href="' + route('') + '" aria-label="Clicko หน้าแรก">' + logoMark() + '</a><p>เลือกของที่ใช่ แล้วคุยกับผู้ขายโดยตรง</p></div>' +
		'<div><p class="clicko-eyebrow">ช่องทางของ Clicko</p><a href="' + escape(seller) + '" target="_blank" rel="noopener noreferrer">ติดต่อซื้อกับผู้ขาย ↗</a><a href="' + escape(brand) + '" target="_blank" rel="noopener noreferrer">ติดตามเพจ Clicko ↗</a><a href="' + route('contact/') + '">ติดต่อร้าน</a></div>' +
		'<div><p class="clicko-eyebrow">ข้อมูลช่วยเหลือ</p><a href="' + route('trade-in/') + '">รับซื้อ / เทิร์นอุปกรณ์</a><a href="' + route('warranty-returns/') + '">ประกันและการคืนสินค้า</a><a href="' + route('privacy/') + '">นโยบายความเป็นส่วนตัว</a><a href="' + route('store-dashboard/') + '">เข้าสู่ระบบพนักงาน</a></div></div>' +
		'<div class="clicko-footer__bottom"><span>© ' + new Date().getFullYear() + ' Clicko — Gaming Gear &amp; PC Parts</span><span>ติดต่อซื้อขายผ่าน Facebook ส่วนตัวของผู้ขาย</span></div></div></footer>';
	}
	document.querySelectorAll('[data-clicko-header]').forEach(function (container) { container.innerHTML = buildHeader(); });
	document.querySelectorAll('[data-clicko-footer]').forEach(function (container) { container.innerHTML = buildFooter(); });
	function applyStoreSettings(row) {
		Object.keys(row || {}).forEach(function (key) {
			if (Object.prototype.hasOwnProperty.call(config, key) && !(key === 'contact_phone' && !row[key])) { config[key] = row[key] == null ? '' : row[key]; }
		});
		config.sellerUrl = config.seller_messenger_url || config.seller_profile_url || seller;
		config.brandPageUrl = config.brand_page_url || brand;
		seller = config.sellerUrl;
		brand = config.brandPageUrl;
		window.ClickoSupabaseCatalogConfig.contactUrl = config.sellerUrl;
		window.ClickoSupabaseCatalogConfig.intro = config.message_intro;
		window.ClickoSupabaseCatalogConfig.footer = config.message_footer;
		document.querySelectorAll('[data-site-setting]').forEach(function (element) {
			var value = config[element.dataset.siteSetting];
			if (value !== undefined) { element.textContent = value; }
		});
		document.querySelectorAll('[data-site-href]').forEach(function (element) {
			var value = config[element.dataset.siteHref];
			if (value && validHttps(value)) { element.href = value; }
		});
		document.querySelectorAll('[data-site-map-location]').forEach(function (element) {
			var location = config.pickup_location || 'ซอยรัชดา 36';
			element.href = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(location);
		});
		document.querySelectorAll('[data-site-phone]').forEach(function (element) {
			if (config.contact_phone) { element.hidden = false; element.href = 'tel:' + config.contact_phone.replace(/[^0-9+]/g, ''); element.querySelector('[data-site-phone-text]').textContent = config.contact_phone; }
		});
		document.querySelectorAll('[data-clicko-header]').forEach(function (container) { container.innerHTML = buildHeader(); });
		document.querySelectorAll('[data-clicko-footer]').forEach(function (container) { container.innerHTML = buildFooter(); });
	}
	function validHttps(value) { try { return new URL(value).protocol === 'https:'; } catch (error) { return false; } }
	applyStoreSettings({});
	if (window.supabase && config.supabaseUrl && config.publishableKey) {
		var settingsClient = window.supabase.createClient(config.supabaseUrl, config.publishableKey);
		settingsClient.from('clicko_storefront_settings').select('*').eq('id','storefront').maybeSingle().then(function (result) {
			if (!result.error && result.data) { applyStoreSettings(result.data); }
		}).catch(function () {});
	}
	function updateCount() {
		var count = 0;
		try { var selected = JSON.parse(localStorage.getItem('clicko-supabase-selection-v1') || '[]'); count = Array.isArray(selected) ? selected.length : 0; } catch (error) {}
		document.querySelectorAll('[data-clicko-count]').forEach(function (node) { node.textContent = String(count); });
	}
	updateCount();
	window.addEventListener('storage', updateCount);
	document.addEventListener('click', function (event) {
		var toggle = event.target.closest('.clicko-menu-toggle');
		if (toggle) {
			var open = document.body.classList.toggle('clicko-menu-open');
			toggle.setAttribute('aria-expanded', String(open));
		}
	});
}());
