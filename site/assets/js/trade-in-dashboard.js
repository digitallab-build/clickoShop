(function () {
	'use strict';

	var root = document.querySelector('[data-clicko-trade-dashboard]');
	if (!root) { return; }

	var config = window.ClickoTradeInDashboardConfig || {};
	var api = window.supabase;
	var notice = root.querySelector('[data-trade-dashboard-notice]');
	var loginPanel = root.querySelector('[data-trade-dashboard-login]');
	var loginForm = root.querySelector('[data-trade-dashboard-login-form]');
	var content = root.querySelector('[data-trade-dashboard-content]');
	var list = root.querySelector('[data-trade-dashboard-list]');
	var filter = root.querySelector('[data-trade-dashboard-filter]');
	var client;
	var busy = false;
	var statusLabels = { new: 'ใหม่', contacted: 'ติดต่อแล้ว', evaluated: 'ประเมินแล้ว', closed: 'ปิดรายการ', spam: 'สแปม' };

	function announce(text, error) {
		notice.textContent = text || '';
		notice.classList.toggle('is-error', Boolean(error));
	}

	function element(tag, className, text) {
		var node = document.createElement(tag);
		if (className) { node.className = className; }
		if (text !== undefined) { node.textContent = String(text); }
		return node;
	}

	function setSignedIn(signedIn) {
		loginPanel.hidden = signedIn;
		content.hidden = !signedIn;
		if (!signedIn) { list.replaceChildren(); }
	}

	function buildCard(record) {
		var card = element('article', 'clicko-trade-request-card');
		var heading = element('div', 'clicko-trade-request-card__heading');
		heading.append(element('h2', '', record.reference));
		heading.append(element('time', '', new Date(record.created_at).toLocaleString('th-TH')));
		card.append(heading);
		card.append(element('p', 'clicko-trade-request-card__model', record.model));
		if (record.warranty) { card.append(element('p', '', 'ประกัน: ' + record.warranty)); }
		card.append(element('p', 'clicko-trade-request-card__condition', record.condition_notes));

		var photos = element('div', 'clicko-trade-request-card__photos');
		card.append(photos);
		(record.photo_paths || []).forEach(function (path) {
			client.storage.from('clicko-trade-in-photos').createSignedUrl(path, 600).then(function (result) {
				if (result.error || !result.data || !result.data.signedUrl) { return; }
				var link = element('a', 'clicko-trade-request-card__photo');
				link.href = result.data.signedUrl;
				link.target = '_blank';
				link.rel = 'noopener noreferrer';
				link.setAttribute('aria-label', 'เปิดรูปสินค้าจริง');
				var image = element('img');
				image.src = result.data.signedUrl;
				image.alt = 'รูปสินค้าจริงของ ' + record.reference;
				image.loading = 'lazy';
				link.append(image);
				photos.append(link);
			}).catch(function () {});
		});
		if (!(record.photo_paths || []).length) { photos.append(element('small', '', 'ไม่มีรูปแนบ')); }

		var statusRow = element('div', 'clicko-trade-request-card__status');
		statusRow.append(element('label', '', 'สถานะรายการ'));
		var select = document.createElement('select');
		select.setAttribute('aria-label', 'เปลี่ยนสถานะ ' + record.reference);
		Object.keys(statusLabels).forEach(function (value) {
			var option = document.createElement('option');
			option.value = value;
			option.textContent = statusLabels[value];
			option.selected = record.status === value;
			select.append(option);
		});
		select.addEventListener('change', async function () {
			select.disabled = true;
			var result = await client.from('clicko_trade_inquiries').update({ status: select.value }).eq('id', record.id).select('id').maybeSingle();
			if (result.error || !result.data) {
				select.value = record.status;
				announce('เปลี่ยนสถานะไม่สำเร็จ: ' + (result.error ? result.error.message : 'ไม่มีสิทธิ์'), true);
			} else {
				record.status = select.value;
				announce('อัปเดตสถานะ ' + record.reference + ' แล้ว');
				if (filter.value !== 'all' && filter.value !== record.status) { card.remove(); }
			}
			select.disabled = false;
		});
		statusRow.append(select);
		card.append(statusRow);
		return card;
	}

	async function loadRequests() {
		if (busy) { return; }
		busy = true;
		list.replaceChildren();
		announce('กำลังโหลดรายการ...');
		var query = client.from('clicko_trade_inquiries')
			.select('id,reference,model,warranty,condition_notes,photo_paths,status,created_at')
			.order('created_at', { ascending: false })
			.limit(200);
		if (filter.value !== 'all') { query = query.eq('status', filter.value); }
		var result = await query;
		busy = false;
		if (result.error) {
			announce('โหลดรายการไม่ได้ ตรวจว่าบัญชีนี้เป็นผู้ดูแลหรือพนักงานที่ได้รับสิทธิ์แล้ว: ' + result.error.message, true);
			return;
		}
		if (!result.data.length) {
			announce('ยังไม่มีรายการในสถานะนี้');
			return;
		}
		result.data.forEach(function (record) { list.append(buildCard(record)); });
		announce('แสดง ' + result.data.length + ' รายการล่าสุด');
	}

	if (!config.url || !config.key || !api || !api.createClient) {
		root.querySelector('[data-trade-dashboard-config-missing]').hidden = false;
		loginPanel.hidden = true;
		return;
	}
	client = api.createClient(config.url, config.key);

	loginForm.addEventListener('submit', async function (event) {
		event.preventDefault();
		var button = loginForm.querySelector('[type="submit"]');
		button.disabled = true;
		announce('กำลังเข้าสู่ระบบ...');
		var result = await client.auth.signInWithPassword({
			email: loginForm.elements.email.value.trim(),
			password: loginForm.elements.password.value,
		});
		button.disabled = false;
		if (result.error) { announce('เข้าสู่ระบบไม่สำเร็จ ตรวจอีเมลและรหัสผ่านอีกครั้ง', true); return; }
		loginForm.reset();
		setSignedIn(true);
		await loadRequests();
	});

	root.querySelector('[data-trade-dashboard-refresh]').addEventListener('click', loadRequests);
	filter.addEventListener('change', loadRequests);
	root.querySelector('[data-trade-dashboard-logout]').addEventListener('click', async function () {
		await client.auth.signOut();
		setSignedIn(false);
		announce('ออกจากระบบแล้ว');
	});

	client.auth.getSession().then(function (result) {
		if (result.data.session) { setSignedIn(true); return loadRequests(); }
		setSignedIn(false);
	}).catch(function () { announce('เชื่อมต่อ Supabase ไม่สำเร็จ', true); });
}());
