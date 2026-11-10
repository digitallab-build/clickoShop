/* Public browser configuration. Never put a Supabase service_role key here. */
(function () {
	'use strict';
	var project = window.CLICKO_SUPABASE || {};
	var root = document.querySelector('base') ? document.querySelector('base').href : window.location.origin + '/';
	var turnstileSiteKey = '';
	var sellerProfileUrl = 'https://www.facebook.com/sippavit.gomarn/';
	var messengerUrl = 'https://m.me/sippavit.gomarn';
	var brandPageUrl = 'https://www.facebook.com/Clicko.gg/';
	var salesGroupUrl = 'https://www.facebook.com/groups/3325289464276829';
	var sellerUrl = messengerUrl || sellerProfileUrl;
	window.CLICKO_SITE = {
		baseUrl: root,
		announcement: 'GAMING GEAR · PC PARTS · BUY / SELL / TRADE',
		tagline: 'GAMING GEAR & PC PARTS',
		hero_eyebrow: 'CLICKO / GAMING GEAR & PC PARTS',
		hero_title: 'ซื้อขายคอมพิวเตอร์',
		hero_highlight: 'และอุปกรณ์เกมมิง',
		hero_suffix: 'ครบ จบ ในที่เดียว',
		hero_description: 'เลือกชมสินค้า เช็กสภาพและรายละเอียด แล้วพูดคุยกับผู้ขายโดยตรง',
		pickup_location: 'ซอยรัชดา 36',
		contact_phone: '095-975-9965',
		seller_profile_url: sellerProfileUrl,
		seller_messenger_url: messengerUrl,
		brand_page_url: brandPageUrl,
		sales_group_url: salesGroupUrl,
		message_intro: 'สวัสดีครับ สนใจสินค้าจากเว็บไซต์ Clicko ครับ',
		message_footer: 'รบกวนยืนยันสินค้าคงเหลือ ราคาปัจจุบัน และค่าจัดส่งด้วยครับ',
		supabaseUrl: project.url || '',
		publishableKey: project.anonKey || '',
		sellerUrl: sellerUrl,
		sellerProfileUrl: sellerProfileUrl,
		messengerUrl: messengerUrl,
		brandPageUrl: brandPageUrl,
		contactPhone: '095-975-9965',
		turnstileSiteKey: turnstileSiteKey,
		tradeInUrl: (project.url || '').replace(/\/$/, '') + '/functions/v1/submit-trade-in',
		messageIntro: 'สวัสดีครับ สนใจสินค้าจากเว็บไซต์ Clicko ครับ',
		messageFooter: 'รบกวนยืนยันสินค้าคงเหลือ ราคาปัจจุบัน และค่าจัดส่งด้วยครับ'
	};
	window.ClickoSupabaseCatalogConfig = {
		url: window.CLICKO_SITE.supabaseUrl,
		key: window.CLICKO_SITE.publishableKey,
		contactUrl: sellerUrl,
		intro: window.CLICKO_SITE.messageIntro,
		footer: window.CLICKO_SITE.messageFooter
	};
	window.ClickoAccountConfig = {
		url: window.CLICKO_SITE.supabaseUrl,
		key: window.CLICKO_SITE.publishableKey,
		redirectTo: window.location.origin + window.location.pathname
	};
	window.ClickoTradeInDashboardConfig = {
		url: window.CLICKO_SITE.supabaseUrl,
		key: window.CLICKO_SITE.publishableKey
	};
	window.ClickoTradeInConfig = {
		submitUrl: window.CLICKO_SITE.tradeInUrl,
		publishableKey: window.CLICKO_SITE.publishableKey,
		turnstileSiteKey: turnstileSiteKey,
		messengerUrl: messengerUrl
	};
}());
