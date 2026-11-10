(function () {
	'use strict';

	var STORAGE_KEY = 'clicko-selection-v1';
	var config = window.ClickoCatalog || {};

	function readItems() {
		try {
			var value = JSON.parse(window.localStorage.getItem(STORAGE_KEY) || '[]');
			return Array.isArray(value) ? value.filter(function (item) {
				return item && Number.isInteger(Number(item.productId)) && Number(item.productId) > 0;
			}) : [];
		} catch (error) {
			return [];
		}
	}

	function writeItems(items) {
		try {
			window.localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
			return true;
		} catch (error) {
			announce('เบราว์เซอร์ไม่สามารถบันทึกรายการไว้ได้ รายการนี้อาจหายเมื่อปิดหน้าเว็บ');
			return false;
		}
	}

	function itemKey(item) {
		return String(item.productId) + ':' + String(item.variationId || 0);
	}

	function announce(message) {
		var toast = document.querySelector('[data-clicko-toast]');
		if (!toast) { return; }
		toast.textContent = message;
		toast.classList.add('is-visible');
		window.clearTimeout(announce.timer);
		announce.timer = window.setTimeout(function () { toast.classList.remove('is-visible'); }, 3200);
	}

	function updateCounts() {
		var count = readItems().reduce(function (sum, item) { return sum + Math.max(1, Number(item.quantity) || 1); }, 0);
		document.querySelectorAll('[data-clicko-count]').forEach(function (node) { node.textContent = String(count); });
	}

	function element(tag, className, text) {
		var node = document.createElement(tag);
		if (className) { node.className = className; }
		if (text !== undefined && text !== null) { node.textContent = String(text); }
		return node;
	}

	function refreshSelection() {
		var container = document.querySelector('[data-clicko-selection]');
		if (!container) { return; }
		var stored = readItems();
		if (!stored.length) {
			container.innerHTML = '<div class="clicko-empty-state"><p class="clicko-eyebrow">YOUR SELECTION</p><h1>ยังไม่มีสินค้าในรายการ</h1><p>เลือกสินค้าที่สนใจ แล้วกลับมาคัดลอกรายการเพื่อส่งให้ผู้ขาย</p><a class="clicko-button" data-clicko-shop-link>เลือกดูสินค้า <span aria-hidden="true">→</span></a></div>';
			var emptyShopLink = container.querySelector('[data-clicko-shop-link]');
			if (emptyShopLink) { emptyShopLink.href = config.shopUrl || '/'; }
			updateCounts();
			return;
		}
		container.innerHTML = '<p class="clicko-selection__loading">กำลังตรวจสอบข้อมูลสินค้าล่าสุด…</p>';
		fetch(config.detailsUrl, {
			method: 'POST',
			cache: 'no-store',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ items: stored })
		}).then(function (response) {
			if (!response.ok) { throw new Error('catalog unavailable'); }
			return response.json();
		}).then(function (data) {
			drawSelection(container, data.items || [], data.currency || config.currency || 'THB', Number(data.decimals || config.decimals || 2));
		}).catch(function () {
			container.innerHTML = '<div class="clicko-empty-state"><p class="clicko-eyebrow">CATALOG UNAVAILABLE</p><h1>โหลดรายการสินค้าไม่สำเร็จ</h1><p>ลองโหลดหน้านี้อีกครั้ง หากยังไม่สำเร็จให้คัดลอกรายการจากหน้าสินค้า</p><button class="clicko-button" type="button" data-clicko-refresh>ลองอีกครั้ง</button></div>';
		});
	}

	function drawSelection(container, products, currency, decimals) {
		var stored = readItems();
		var storedByKey = {};
		stored.forEach(function (item) { storedByKey[itemKey(item)] = item; });
		var available = products.map(function (product) {
			var saved = storedByKey[itemKey(product)];
			product.quantity = saved ? Math.max(1, Math.min(99, Number(saved.quantity) || 1)) : 1;
			return product;
		});
		var isComplete = available.length === stored.length;
		var wrapper = element('div', 'clicko-selection__layout');
		var list = element('div', 'clicko-selection__items');
		available.forEach(function (product) {
			var row = element('article', 'clicko-selection-item');
			var detail = element('div', 'clicko-selection-item__detail');
			var title = element('h2', '', product.name);
			var variantText = [product.variant, product.sku ? 'SKU ' + product.sku : ''].filter(Boolean).join(' · ');
			var meta = element('p', 'clicko-selection-item__meta', variantText || 'สินค้า Clicko');
			var stockNote = element('p', product.inStock ? 'clicko-selection-item__stock' : 'clicko-selection-item__stock is-unavailable', product.inStock ? 'สถานะรอผู้ขายยืนยัน' : 'เว็บระบุว่าสินค้าหมด · สอบถามผู้ขาย');
			var link = element('a', 'clicko-selection-item__link', 'ดูสินค้า ↗');
			link.href = product.url;
			detail.appendChild(title);
			detail.appendChild(meta);
			detail.appendChild(stockNote);
			detail.appendChild(link);
			var controls = element('div', 'clicko-selection-item__controls');
			var price = element('strong', 'clicko-selection-item__price', product.priceLabel || 'สอบถามราคา');
			var quantity = element('div', 'clicko-quantity');
			var decrease = element('button', '', '−');
			decrease.type = 'button'; decrease.dataset.clickoQuantity = '-1'; decrease.dataset.clickoKey = itemKey(product); decrease.setAttribute('aria-label', 'ลดจำนวน ' + product.name);
			var quantityValue = element('span', '', product.quantity);
			var increase = element('button', '', '+');
			increase.type = 'button'; increase.dataset.clickoQuantity = '1'; increase.dataset.clickoKey = itemKey(product); increase.setAttribute('aria-label', 'เพิ่มจำนวน ' + product.name);
			quantity.append(decrease, quantityValue, increase);
			var remove = element('button', 'clicko-remove', 'ลบ');
			remove.type = 'button'; remove.dataset.clickoRemove = itemKey(product);
			controls.append(price, quantity, remove);
			row.append(detail, controls);
			list.appendChild(row);
		});
		var summary = element('aside', 'clicko-selection-summary');
		summary.appendChild(element('p', 'clicko-eyebrow', 'READY TO ASK?'));
		summary.appendChild(element('h2', '', 'ส่งรายการให้ผู้ขาย'));
		summary.appendChild(element('p', 'clicko-selection-summary__note', 'ผู้ขายจะยืนยันราคาและสินค้าคงเหลืออีกครั้ง รายการนี้ยังไม่ใช่คำสั่งซื้อ'));
		var total = available.reduce(function (sum, product) { return product.lineMinor === null ? null : sum + Number(product.lineMinor || 0); }, 0);
		var totalRow = element('div', 'clicko-selection-summary__total');
		totalRow.append(element('span', '', 'รวมราคาที่แสดง'), element('strong', '', total === null ? 'บางรายการสอบถามราคา' : formatMoney(total, currency, decimals)));
		summary.appendChild(totalRow);
		if (!isComplete) {
			summary.appendChild(element('p', 'clicko-inline-notice', 'มีสินค้าบางรายการเปลี่ยนแปลงหรือไม่พร้อมแล้ว กรุณาตรวจสอบรายการอีกครั้ง'));
		}
		var copy = element('button', 'clicko-button clicko-button--full', 'คัดลอกรายการสินค้า');
		copy.type = 'button'; copy.dataset.clickoCopy = 'true';
		var contact = element('a', 'clicko-button clicko-button--outline clicko-button--full', config.contactLabel || 'เปิด Facebook ผู้ขาย');
		contact.href = config.contactUrl || 'https://www.facebook.com/sippavit.gomarn';
		contact.target = '_blank'; contact.rel = 'noopener noreferrer';
		var textarea = element('textarea', 'clicko-selection-summary__fallback');
		textarea.readOnly = true; textarea.setAttribute('aria-label', 'ข้อความรายการสินค้าสำหรับคัดลอก');
		var summaryText = createMessage(available, total, currency, decimals);
		textarea.value = summaryText;
		summary.append(copy, contact, textarea);
		var continueLink = element('a', 'clicko-text-link', 'เลือกสินค้าเพิ่ม →'); continueLink.href = config.shopUrl || '/';
		list.appendChild(continueLink);
		wrapper.append(list, summary);
		container.replaceChildren(wrapper);
		container.dataset.summaryText = summaryText;
		container.dataset.selectionCurrency = currency;
		updateCounts();
	}

	function formatMoney(minorUnits, currency, decimals) {
		var value = Number(minorUnits) / Math.pow(10, decimals);
		try { return new Intl.NumberFormat('th-TH', { style: 'currency', currency: currency, minimumFractionDigits: decimals, maximumFractionDigits: decimals }).format(value); }
		catch (error) { return value.toFixed(decimals) + ' ' + currency; }
	}

	function createMessage(items, total, currency, decimals) {
		var lines = [config.intro || 'สวัสดีครับ สนใจสินค้าจากเว็บไซต์ Clicko ครับ', ''];
		items.forEach(function (item, index) {
			lines.push((index + 1) + '. ' + item.name + (item.variant ? ' — ' + item.variant : ''));
			if (item.sku) { lines.push('   SKU: ' + item.sku); }
			lines.push('   จำนวน: ' + item.quantity);
			lines.push('   ราคาที่แสดง: ' + (item.priceLabel || 'สอบถามราคา'));
			lines.push('   ลิงก์สินค้า: ' + item.url);
			lines.push('');
		});
		lines.push('รวมราคาที่แสดง: ' + (total === null ? 'มีสินค้าที่ต้องสอบถามราคา' : formatMoney(total, currency, decimals)));
		lines.push('ค่าจัดส่งและสินค้าคงเหลือรอยืนยันจากผู้ขาย');
		lines.push('');
		lines.push(config.footer || 'รบกวนยืนยันสินค้าคงเหลือ ราคาปัจจุบัน และค่าจัดส่งด้วยครับ');
		return lines.join('\n');
	}

	document.addEventListener('click', function (event) {
		var addButton = event.target.closest('[data-clicko-add]');
		if (addButton) {
			var action = addButton.closest('[data-clicko-action]');
			var variantSelect = action && action.querySelector('[data-clicko-variation]');
			var variationId = variantSelect ? Number(variantSelect.value) : 0;
			if (variantSelect && !variationId) { announce('กรุณาเลือกรุ่นสินค้าก่อนเพิ่มในรายการ'); variantSelect.focus(); return; }
			var item = { productId: Number(action.dataset.productId), variationId: variationId, quantity: 1 };
			var items = readItems();
			var existing = items.find(function (saved) { return itemKey(saved) === itemKey(item); });
			if (existing) { existing.quantity = Math.min(99, Number(existing.quantity || 1) + 1); }
			else { items.push(item); }
			writeItems(items); updateCounts(); announce('เพิ่มสินค้าในรายการที่สนใจแล้ว');
			addButton.classList.add('is-added');
			window.setTimeout(function () { addButton.classList.remove('is-added'); }, 900);
			return;
		}
		var quantityButton = event.target.closest('[data-clicko-quantity]');
		if (quantityButton) {
			var key = quantityButton.dataset.clickoKey;
			var delta = Number(quantityButton.dataset.clickoQuantity);
			var updated = readItems().map(function (item) { if (itemKey(item) === key) { item.quantity = Math.max(1, Math.min(99, Number(item.quantity || 1) + delta)); } return item; });
			writeItems(updated); refreshSelection(); return;
		}
		var removeButton = event.target.closest('[data-clicko-remove]');
		if (removeButton) { writeItems(readItems().filter(function (item) { return itemKey(item) !== removeButton.dataset.clickoRemove; })); refreshSelection(); return; }
		if (event.target.closest('[data-clicko-refresh]')) { refreshSelection(); return; }
		var copyButton = event.target.closest('[data-clicko-copy]');
		if (copyButton) {
			var selection = copyButton.closest('[data-clicko-selection]');
			var text = selection ? selection.dataset.summaryText : '';
			var fallback = selection && selection.querySelector('.clicko-selection-summary__fallback');
			if (!text) { return; }
			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(text).then(function () { announce('คัดลอกรายการแล้ว เปิดแชทผู้ขายและวางข้อความเพื่อส่งได้เลย'); }).catch(function () { if (fallback) { fallback.classList.add('is-visible'); fallback.focus(); fallback.select(); } announce('เลือกข้อความในช่องแล้ว กดคัดลอกด้วยตนเองได้เลย'); });
			} else if (fallback) { fallback.classList.add('is-visible'); fallback.focus(); fallback.select(); announce('เลือกข้อความในช่องแล้ว กดคัดลอกด้วยตนเองได้เลย'); }
		}
	});

	var menuToggle = document.querySelector('.clicko-menu-toggle');
	if (menuToggle) { menuToggle.addEventListener('click', function () { var expanded = menuToggle.getAttribute('aria-expanded') === 'true'; menuToggle.setAttribute('aria-expanded', String(!expanded)); document.body.classList.toggle('clicko-menu-open', !expanded); }); }
	var viewKey = 'clicko-catalog-view-v1';
	function setCatalogView(view) {
		var normalized = view === 'grid' ? 'grid' : 'list';
		document.querySelectorAll('.clicko-product-grid').forEach(function (grid) {
			grid.classList.toggle('is-list', normalized === 'list');
			grid.classList.toggle('is-grid', normalized === 'grid');
		});
		document.querySelectorAll('[data-clicko-view]').forEach(function (button) {
			var active = button.dataset.clickoView === normalized;
			button.classList.toggle('is-active', active);
			button.setAttribute('aria-pressed', String(active));
		});
		try { window.localStorage.setItem(viewKey, normalized); } catch (error) { /* View preference is optional. */ }
	}
	var initialView = 'list';
	try { initialView = window.localStorage.getItem(viewKey) || initialView; } catch (error) { /* Use the default view. */ }
	setCatalogView(initialView);
	document.querySelectorAll('[data-clicko-view]').forEach(function (button) {
		button.addEventListener('click', function () { setCatalogView(button.dataset.clickoView); });
	});
	document.querySelectorAll('.clicko-product-toolbar select').forEach(function (select) {
		select.addEventListener('change', function () {
			if (select.form) { select.form.requestSubmit ? select.form.requestSubmit() : select.form.submit(); }
		});
	});
	document.querySelectorAll('[data-clicko-trade-form]').forEach(function (form) {
	var config = window.ClickoTradeInConfig || {};
	var status = form.querySelector('[data-clicko-trade-status]');
	var submitButton = form.querySelector('[type="submit"]');
	var widgetId = null;
	if (config.turnstileSiteKey && window.turnstile) {
		widgetId = window.turnstile.render(form.querySelector('[data-clicko-turnstile]'), { sitekey: config.turnstileSiteKey, theme: 'light' });
	} else {
		submitButton.disabled = true;
		status.textContent = 'ระบบรับข้อมูลยังตั้งค่าไม่ครบ กรุณาติดต่อร้าน';
		status.classList.add('is-error');
	}
	form.addEventListener('submit', async function (event) {
		event.preventDefault();
		if (!config.submitUrl || !config.publishableKey || !config.messengerUrl || !config.turnstileSiteKey || !window.turnstile) {
			status.textContent = 'ระบบรับข้อมูลยังตั้งค่าไม่ครบ กรุณาติดต่อร้าน';
			status.classList.add('is-error');
			return;
		}
		var fields = new FormData(form);
		submitButton.disabled = true;
		status.classList.remove('is-error');
		status.textContent = 'กำลังบันทึกข้อมูลในระบบ...';
		try {
			var result = await fetch(config.submitUrl, {
				method: 'POST',
				headers: { apikey: config.publishableKey, 'Content-Type': 'application/json' },
				body: JSON.stringify({
					company_website: fields.get('company_website'),
					customer_first_name: fields.get('customer_first_name'),
					customer_last_name: fields.get('customer_last_name'),
					customer_facebook_name: fields.get('customer_facebook_name'),
					customer_phone: fields.get('customer_phone'),
					model: fields.get('model'),
					warranty: fields.get('warranty'),
					condition: fields.get('condition'),
					captcha_token: window.turnstile.getResponse(widgetId)
				})
			});
			var payload = await result.json();
			if (!result.ok || !payload.ok) { throw new Error(payload.error || 'ส่งข้อมูลไม่สำเร็จ'); }
			form.reset();
			if (widgetId !== null) { window.turnstile.reset(widgetId); }
			status.textContent = 'บันทึกข้อมูลแล้ว หมายเลขอ้างอิง ' + payload.reference + ' กำลังเปิด Messenger ของ Kew...';
			window.location.assign(config.messengerUrl);
		} catch (error) {
			status.textContent = error.message || 'ส่งข้อมูลไม่สำเร็จ กรุณาลองใหม่';
			status.classList.add('is-error');
			if (widgetId !== null) { window.turnstile.reset(widgetId); }
		} finally {
			submitButton.disabled = false;
		}
	});
});updateCounts();
	refreshSelection();
}());
