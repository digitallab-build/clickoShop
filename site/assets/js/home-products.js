(function () {
	'use strict';

	var section = document.querySelector('[data-clicko-home-arrivals]');
	var config = window.ClickoSupabaseCatalogConfig || {};
	if (!section) { return; }

	var grid = section.querySelector('[data-home-arrivals-grid]');
	var group = section.querySelector('[data-home-arrivals-group]');
	var status = section.querySelector('[data-home-arrivals-status]');
	var baseUrl = window.CLICKO_SITE && window.CLICKO_SITE.baseUrl ? window.CLICKO_SITE.baseUrl : '/';
	if (!config.url || !config.key) {
		status.textContent = 'ยังไม่ได้ตั้งค่าการเชื่อมต่อรายการสินค้า';
		return;
	}
	var categoryRules = {
		monitor: /monitor|display|จอมอนิเตอร์|จอเกม|จอคอม|^จอ$/,
		gpu: /gpu|graphics|graphic card|vga|การ์ดจอ/,
		cpu: /\bcpu\b|processor|ซีพียู/,
		motherboard: /motherboard|mainboard|เมนบอร์ด/,
		keyboard: /keyboard|คีย์บอร์ด/,
		mouse: /mouse|เมาส์/,
		headset: /headset|headphone|audio|หูฟัง/,
		other: /gamepad|controller|จอย|อุปกรณ์อื่น|gaming gear/
	};

	function imageUrl(value) {
		if (!value) { return ''; }
		try { var url = new URL(value); return url.protocol === 'https:' ? url.href : ''; }
		catch (error) { return ''; }
	}

	function matchesCategory(product, key) {
		var rule = categoryRules[key];
		var main = [product.category, product.sub].filter(Boolean).join(' ').toLocaleLowerCase();
		var details = [product.name, product.specs].filter(Boolean).join(' ').toLocaleLowerCase();
		if (key === 'monitor' && /การ์ดจอ/.test(main)) { return false; }
		return rule.test(main) || rule.test(details);
	}

	function setCategoryImage(key, products, usedImages) {
		var rule = categoryRules[key];
		if (!rule) { return; }
		var candidates = products.filter(function (product) { return matchesCategory(product, key) && imageUrl(product.image); });
		var chosen = candidates.find(function (product) { return !usedImages.has(product.image); }) || candidates[0];
		if (!chosen) { return; }
		usedImages.add(chosen.image);
		var tile = document.querySelector('[data-home-category="' + key + '"]');
		var image = tile && tile.querySelector('[data-home-category-image]');
		if (!image) { return; }
		image.src = imageUrl(chosen.image);
		image.alt = chosen.name || '';
		image.hidden = false;
		image.addEventListener('error', function () { image.hidden = true; image.parentElement.classList.remove('has-product-image'); }, { once: true });
		image.parentElement.classList.add('has-product-image');
	}

	function money(value) {
		if (value === null || value === undefined || value === '') { return 'สอบถามราคา'; }
		var amount = Number(value);
		return Number.isFinite(amount) ? new Intl.NumberFormat('th-TH', { maximumFractionDigits: 2 }).format(amount) + ' บาท' : 'สอบถามราคา';
	}

	function productLink(product) {
		var url = new URL('products/', baseUrl);
		url.searchParams.set('clicko_q', product.sku || product.name || '');
		return url.href;
	}

	function renderCard(product) {
		var article = document.createElement('article');
		article.className = 'clicko-home-arrival-card';
		var link = document.createElement('a');
		link.className = 'clicko-home-arrival-card__image';
		link.href = productLink(product);
		link.setAttribute('aria-label', 'ดูสินค้า ' + (product.name || 'Clicko'));
	var photo = imageUrl(product.image);
	if (photo) {
		var image = document.createElement('img');
		image.src = photo;
		image.alt = product.name || 'สินค้า Clicko';
		image.loading = 'lazy';
		image.addEventListener('error', function () { image.replaceWith(document.createTextNode('ไม่มีรูปสินค้า')); }, { once: true });
		link.appendChild(image);
	} else {
		var placeholder = document.createElement('span');
		placeholder.className = 'clicko-home-arrival-card__placeholder';
		placeholder.textContent = 'ยังไม่มีรูปสินค้า';
		link.appendChild(placeholder);
	}
		article.appendChild(link);

		var details = document.createElement('div');
		details.className = 'clicko-home-arrival-card__details';
		if (product.category) {
			var category = document.createElement('p');
			category.className = 'clicko-eyebrow';
			category.textContent = product.category;
			details.appendChild(category);
		}
		var title = document.createElement('h3');
		title.textContent = product.name || 'สินค้า Clicko';
		details.appendChild(title);
		if (product.condition || product.sku) {
			var meta = document.createElement('p');
			meta.className = 'clicko-home-arrival-card__meta';
			meta.textContent = [product.condition, product.sku ? 'SKU: ' + product.sku : ''].filter(Boolean).join(' · ');
			details.appendChild(meta);
		}
		var footer = document.createElement('div');
		footer.className = 'clicko-home-arrival-card__footer';
		var price = document.createElement('strong');
		price.textContent = money(product.price);
		var view = document.createElement('a');
		view.href = productLink(product);
		view.textContent = 'ดูสินค้า →';
		footer.append(price, view);
		details.appendChild(footer);
		article.appendChild(details);
		return article;
	}

	var query = new URL(config.url.replace(/\/$/, '') + '/rest/v1/clicko_products');
	var recentCutoff = new Date(Date.now() - 48 * 60 * 60 * 1000).toISOString();
	query.search = new URLSearchParams({
		select: 'sku,name,category,sub,condition,price,warranty,specs,image,stock_status,sold,is_hidden,created_at',
		is_hidden: 'eq.false',
		order: 'created_at.desc',
		limit: '100'
	}).toString();
	var controller = new AbortController();
	var timeout = window.setTimeout(function () { controller.abort(); }, 8000);
	window.fetch(query.href, {
		headers: { apikey: config.key, Authorization: 'Bearer ' + config.key, Accept: 'application/json' },
		signal: controller.signal
	}).then(function (response) {
		if (!response.ok) { throw new Error('Catalog request failed: ' + response.status); }
		return response.json();
	}).then(function (data) {
			var products = (Array.isArray(data) ? data : []).filter(function (product) {
				return product.stock_status === 'available' || (!product.stock_status && !product.sold);
			});
			var usedImages = new Set();
			Object.keys(categoryRules).forEach(function (key) { setCategoryImage(key, products, usedImages); });
			var recent = products.filter(function (product) {
				return product.created_at && new Date(product.created_at).getTime() >= new Date(recentCutoff).getTime();
			}).slice(0, 12);
			group.replaceChildren();
			grid.replaceChildren(group);
			grid.classList.toggle('is-static', recent.length < 2);
			recent.forEach(function (product) { group.appendChild(renderCard(product)); });
			if (recent.length > 1) {
				var duplicateGroup = group.cloneNode(true);
				duplicateGroup.removeAttribute('data-home-arrivals-group');
				duplicateGroup.setAttribute('aria-hidden', 'true');
				duplicateGroup.querySelectorAll('a').forEach(function (link) { link.setAttribute('tabindex', '-1'); });
				grid.appendChild(duplicateGroup);
				grid.style.setProperty('--clicko-marquee-duration', Math.max(24, recent.length * 5) + 's');
			}
			status.hidden = recent.length > 0;
			if (!recent.length) { status.textContent = 'ยังไม่มีสินค้าใหม่ใน 48 ชั่วโมงที่ผ่านมา'; }
	}).catch(function (error) {
		status.textContent = error && error.name === 'AbortError'
			? 'เชื่อมต่อรายการสินค้านานเกินไป กรุณาลองดูสินค้าทั้งหมด'
			: 'โหลดสินค้าเข้าใหม่ไม่สำเร็จ กรุณาลองดูสินค้าทั้งหมด';
	}).finally(function () { window.clearTimeout(timeout); });
}());
