(function () {
	'use strict';

	var config = window.ClickoSupabaseCatalogConfig || {};
	var root = document.querySelector('[data-clicko-supabase-catalog]');
	if (!root || !window.supabase || !window.supabase.createClient || !config.url || !config.key) { return; }

	var client = window.supabase.createClient(config.url, config.key);
	var grid = root.querySelector('[data-catalog-grid]');
	var notice = root.querySelector('[data-catalog-notice]');
	var count = root.querySelector('[data-catalog-count]');
	var search = root.querySelector('[data-catalog-search]');
	var category = root.querySelector('[data-catalog-category]');
	var condition = root.querySelector('[data-catalog-condition]');
	var status = root.querySelector('[data-catalog-status]');
	var sort = root.querySelector('[data-catalog-sort]');
	var dialog = root.querySelector('[data-catalog-dialog]');
	var detail = root.querySelector('[data-catalog-detail]');
	var selectionBar = root.querySelector('[data-catalog-selection]');
	var selectionCount = root.querySelector('[data-selection-count]');
	var categoryChips = root.querySelector('[data-catalog-category-chips]');
	var products = [];
	var storageKey = 'clicko-supabase-selection-v1';
	var selectedSkus = readSelection();
	var stateLabels = { available: 'พร้อมขาย', reserved: 'จองแล้ว', sold: 'ขายแล้ว' };

	function announce(text, error) {
		notice.textContent = text || '';
		notice.classList.toggle('is-error', Boolean(error));
	}

	function node(tag, className, text) {
		var item = document.createElement(tag);
		if (className) { item.className = className; }
		if (text !== undefined && text !== null) { item.textContent = String(text); }
		return item;
	}

	function safeImageUrl(value) {
		if (!value) { return ''; }
		try { var parsed = new URL(value); return parsed.protocol === 'https:' ? parsed.href : ''; }
		catch (error) { return ''; }
	}

	function productState(product) {
		return ['available', 'reserved', 'sold'].indexOf(product.stock_status) !== -1 ? product.stock_status : (product.sold ? 'sold' : 'available');
	}

	function money(value) {
		if (value === null || value === undefined || value === '') { return 'สอบถามราคา'; }
		var amount = Number(value);
		return Number.isFinite(amount) ? new Intl.NumberFormat('th-TH', { maximumFractionDigits: 2 }).format(amount) + ' บาท' : 'สอบถามราคา';
	}

	function readSelection() {
		try {
			var saved = JSON.parse(window.localStorage.getItem(storageKey) || '[]');
			return Array.isArray(saved) ? saved.filter(function (sku) { return typeof sku === 'string' && sku.length <= 80; }) : [];
		} catch (error) { return []; }
	}

	function saveSelection() {
		try { window.localStorage.setItem(storageKey, JSON.stringify(selectedSkus)); }
		catch (error) { announce('เบราว์เซอร์ไม่สามารถเก็บรายการที่สนใจได้', true); }
		selectionBar.hidden = selectedSkus.length === 0;
		selectionCount.textContent = 'รายการที่สนใจ ' + selectedSkus.length + ' ชิ้น';
		document.querySelectorAll('[data-clicko-count]').forEach(function (item) { item.textContent = String(selectedSkus.length); });
		root.querySelectorAll('[data-catalog-interest]').forEach(function (button) {
			var chosen = selectedSkus.indexOf(button.dataset.catalogInterest) !== -1;
			button.textContent = chosen ? 'เพิ่มแล้ว ✓' : 'เพิ่มรายการ';
			button.setAttribute('aria-pressed', String(chosen));
		});
	}

	function makeContactMessage(rows) {
		var lines = [config.intro || 'สวัสดีครับ สนใจสินค้าจากเว็บไซต์ Clicko ครับ'];
		rows.forEach(function (product) {
			lines.push('- ' + product.name + (product.sku ? ' (SKU ' + product.sku + ')' : '') + ' · ' + money(product.price));
		});
		lines.push(config.footer || 'รบกวนยืนยันสินค้าคงเหลือ ราคาปัจจุบัน และค่าจัดส่งด้วยครับ');
		return lines.join('\n');
	}

	function openSeller(messageText) {
		var sellerTab = window.open(config.contactUrl || 'https://www.facebook.com/sippavit.gomarn', '_blank');
		if (sellerTab) { sellerTab.opener = null; }
		function copied() { announce('คัดลอกรายการแล้ว วางข้อความในแชท Facebook เพื่อส่งให้ผู้ขาย'); }
		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(messageText).then(copied).catch(function () { window.prompt('คัดลอกรายการนี้แล้วส่งให้ผู้ขายทาง Facebook', messageText); });
		} else { window.prompt('คัดลอกรายการนี้แล้วส่งให้ผู้ขายทาง Facebook', messageText); }
		if (!sellerTab) { announce('เบราว์เซอร์บล็อกหน้าต่างใหม่ กรุณาอนุญาตป๊อปอัป'); }
	}

	function productMatches(product) {
		var term = search.value.trim().toLocaleLowerCase();
		var text = [product.name, product.sku, product.category, product.sub, product.specs].join(' ').toLocaleLowerCase();
		if (term && text.indexOf(term) === -1) { return false; }
		if (category.value && product.category !== category.value) { return false; }
		if (condition.value && product.condition !== condition.value) { return false; }
		if (status.value !== 'all' && productState(product) !== status.value) { return false; }
		return true;
	}

	function renderCard(product) {
		var article = node('article', 'clicko-product-card clicko-supabase-product-card');
		var imageButton = node('button', 'clicko-product-card__image clicko-supabase-product-card__image');
		imageButton.type = 'button';
		imageButton.dataset.catalogDetail = product.sku;
		var imageUrl = safeImageUrl(product.image);
		if (imageUrl) {
			var image = node('img'); image.src = imageUrl; image.alt = product.name || 'สินค้า Clicko'; image.loading = 'lazy'; imageButton.appendChild(image);
		} else {
			imageButton.appendChild(node('span', 'clicko-product-card__placeholder', 'CLICKO'));
		}
		article.appendChild(imageButton);

		var body = node('div', 'clicko-product-card__body');
		var titleRow = node('div', 'clicko-product-card__title-row');
		var titleGroup = node('div');
		if (product.category) { titleGroup.appendChild(node('p', 'clicko-eyebrow', product.category)); }
		titleGroup.appendChild(node('h3', '', product.name || 'สินค้า Clicko'));
		if (product.sku) { titleGroup.appendChild(node('p', 'clicko-product-card__sku', 'SKU: ' + product.sku)); }
		titleRow.appendChild(titleGroup);
		if (product.condition) { titleRow.appendChild(node('span', 'clicko-product-condition', product.condition)); }
		body.appendChild(titleRow);

		var state = productState(product);
		var specs = String(product.specs || '').split(/[\n,;|]+/).map(function (part) { return part.trim(); }).filter(Boolean).slice(0, 3);
		if (specs.length) {
			var specsRow = node('div', 'clicko-product-specs-row');
			specs.forEach(function (spec) { var span = node('span'); span.appendChild(node('b', '', spec)); specsRow.appendChild(span); });
			body.appendChild(specsRow);
		}
		if (product.warranty) { body.appendChild(node('p', 'clicko-product-warranty', '✓ ประกัน ' + product.warranty)); }
		article.appendChild(body);

		var purchase = node('div', 'clicko-product-card__purchase');
		purchase.appendChild(node('span', 'clicko-stock-status is-' + state, '● ' + (stateLabels[state] || 'สอบถามสถานะ')));
		purchase.appendChild(node('div', 'clicko-product-card__price', money(product.price)));
		var contactButton = node('button', 'clicko-button', 'สอบถามผู้ขาย');
		contactButton.type = 'button'; contactButton.dataset.catalogContact = product.sku;
		purchase.appendChild(contactButton);
		var controls = node('div', 'clicko-supabase-product-card__controls');
		var detailButton = node('button', 'clicko-detail-button', 'ดูรายละเอียด →'); detailButton.type = 'button'; detailButton.dataset.catalogDetail = product.sku;
		var interestButton = node('button', 'clicko-detail-button clicko-interest-button', 'เพิ่มรายการ'); interestButton.type = 'button'; interestButton.dataset.catalogInterest = product.sku;
		controls.appendChild(detailButton); controls.appendChild(interestButton); purchase.appendChild(controls);
		article.appendChild(purchase);
		return article;
	}

	function render() {
		var visible = products.filter(productMatches);
		if (sort.value === 'price-low' || sort.value === 'price-high') {
			var direction = sort.value === 'price-low' ? 1 : -1;
			visible.sort(function (a, b) { return direction * ((Number(a.price) || 0) - (Number(b.price) || 0)); });
		} else {
			visible.sort(function (a, b) { return String(b.created_at || '').localeCompare(String(a.created_at || '')); });
		}
		grid.replaceChildren();
		visible.forEach(function (product) { grid.appendChild(renderCard(product)); });
		count.textContent = visible.length + ' รายการ';
		if (!products.length) { announce('ยังไม่มีสินค้าใน Supabase'); }
		else if (!visible.length) { announce('ไม่พบสินค้าที่ตรงกับตัวกรอง'); }
		else { announce(''); }
		saveSelection();
	}

	function renderDetail(product) {
		detail.replaceChildren();
		var wrap = node('div', 'clicko-supabase-product-detail');
		var imageUrl = safeImageUrl(product.image);
		if (imageUrl) { var image = node('img'); image.src = imageUrl; image.alt = product.name || ''; wrap.appendChild(image); }
		var content = node('div');
		content.appendChild(node('p', 'clicko-eyebrow', (product.category || 'CLICKO') + (product.sku ? ' / SKU ' + product.sku : '')));
		content.appendChild(node('h2', '', product.name || 'สินค้า Clicko'));
		content.appendChild(node('span', 'clicko-stock-status is-' + productState(product), stateLabels[productState(product)]));
		content.appendChild(node('p', 'clicko-supabase-product-detail__price', money(product.price)));
		if (product.condition) { content.appendChild(node('p', '', 'สภาพ: ' + product.condition)); }
		if (product.warranty) { content.appendChild(node('p', '', 'ประกัน: ' + product.warranty)); }
		if (product.sub) { content.appendChild(node('p', '', 'ประเภท: ' + product.sub)); }
		if (product.specs) { content.appendChild(node('h3', '', 'สเปกสินค้า')); content.appendChild(node('p', 'clicko-supabase-product-detail__text', product.specs)); }
		if (product.note) { content.appendChild(node('h3', '', 'หมายเหตุ')); content.appendChild(node('p', 'clicko-supabase-product-detail__text', product.note)); }
		var contact = node('button', 'clicko-button', 'สอบถามผู้ขาย'); contact.type = 'button'; contact.dataset.catalogContact = product.sku;
		var add = node('button', 'clicko-detail-button clicko-interest-button', 'เพิ่มรายการ'); add.type = 'button'; add.dataset.catalogInterest = product.sku;
		content.appendChild(contact); content.appendChild(add); wrap.appendChild(content); detail.appendChild(wrap);
		if (typeof dialog.showModal === 'function') { dialog.showModal(); } else { dialog.setAttribute('open', ''); }
		saveSelection();
	}

	function fillCategories() {
		var categories = Array.from(new Set(products.map(function (product) { return product.category; }).filter(Boolean))).sort(function (a, b) { return a.localeCompare(b, 'th'); });
		categories.forEach(function (name) { var option = node('option', '', name); option.value = name; category.appendChild(option); });
		if (categoryChips) {
			categoryChips.replaceChildren();
			[{ label: 'ทั้งหมด', value: '', icon: 'grid' }].concat(categories.map(function (name) {
				var value = name.toLocaleLowerCase();
				var icon = /monitor|display|จอ/.test(value) ? 'monitor' : /gpu|graphic|vga|การ์ดจอ/.test(value) ? 'gpu' : /cpu|processor|ซีพียู/.test(value) ? 'cpu' : /board|เมนบอร์ด/.test(value) ? 'board' : /keyboard|คีย์บอร์ด/.test(value) ? 'keyboard' : /mouse|เมาส์/.test(value) ? 'mouse' : /audio|head|หูฟัง/.test(value) ? 'headset' : /gamepad|controller|จอย/.test(value) ? 'gamepad' : /pad|แผ่นรอง/.test(value) ? 'package' : 'package';
				return { label: name, value: name, icon: icon };
			})).forEach(function (item) {
				var button = node('button', 'clicko-category-chip' + (item.value === category.value ? ' is-active' : ''));
				button.type = 'button';
				button.dataset.catalogCategoryChip = item.value;
				button.setAttribute('aria-pressed', String(item.value === category.value));
				var categoryIcon = node('span', 'clicko-category-chip__icon');
				categoryIcon.innerHTML = window.ClickoIcon ? window.ClickoIcon(item.icon) : item.label.slice(0, 1);
				button.appendChild(categoryIcon);
				button.appendChild(node('span', '', item.label));
				categoryChips.appendChild(button);
			});
		}
	}

	root.addEventListener('input', function (event) { if (event.target === search) { render(); } });
	root.addEventListener('change', function (event) { if ([category, condition, status, sort].indexOf(event.target) !== -1) { render(); } });
	root.addEventListener('click', function (event) {
		var categoryChip = event.target.closest('[data-catalog-category-chip]');
		if (categoryChip) {
			category.value = categoryChip.dataset.catalogCategoryChip;
			categoryChips.querySelectorAll('[data-catalog-category-chip]').forEach(function (button) {
				var active = button === categoryChip;
				button.classList.toggle('is-active', active);
				button.setAttribute('aria-pressed', String(active));
			});
			render();
			return;
		}
		var detailButton = event.target.closest('[data-catalog-detail]');
		if (detailButton) { var product = products.find(function (item) { return item.sku === detailButton.dataset.catalogDetail; }); if (product) { renderDetail(product); } return; }
		var contactButton = event.target.closest('[data-catalog-contact]');
		if (contactButton) { var contactProduct = products.find(function (item) { return item.sku === contactButton.dataset.catalogContact; }); if (contactProduct) { openSeller(makeContactMessage([contactProduct])); } return; }
		var interestButton = event.target.closest('[data-catalog-interest]');
		if (interestButton) { var sku = interestButton.dataset.catalogInterest; selectedSkus = selectedSkus.indexOf(sku) === -1 ? selectedSkus.concat([sku]).slice(0, 40) : selectedSkus.filter(function (saved) { return saved !== sku; }); saveSelection(); return; }
		if (event.target.closest('[data-catalog-close]')) { if (typeof dialog.close === 'function') { dialog.close(); } else { dialog.removeAttribute('open'); } return; }
		if (event.target.closest('[data-selection-clear]')) { selectedSkus = []; saveSelection(); return; }
		if (event.target.closest('[data-selection-send]')) { var chosen = products.filter(function (item) { return selectedSkus.indexOf(item.sku) !== -1; }); if (chosen.length) { openSeller(makeContactMessage(chosen)); } }
	});

	var searchParams = new URLSearchParams(window.location.search);
	if (searchParams.has('clicko_q')) { search.value = searchParams.get('clicko_q') || ''; }
	client.from('clicko_products')
		.select('sku,name,category,sub,condition,price,warranty,sold,specs,note,image,stock_status,is_hidden,created_at')
		.eq('is_hidden', false)
		.order('created_at', { ascending: false })
		.limit(1000)
		.then(function (result) {
			if (result.error) { throw result.error; }
			products = Array.isArray(result.data) ? result.data : [];
			fillCategories(); render();
		}).catch(function () { grid.replaceChildren(); count.textContent = ''; announce('เชื่อมต่อแค็ตตาล็อก Supabase ไม่สำเร็จ ตรวจค่า project และ RLS แล้วลองใหม่', true); });
}());
