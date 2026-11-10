(function () {
	'use strict';
	var root = document.querySelector('[data-storefront-settings-admin]');
	if (!root) { return; }
	var config = window.CLICKO_SITE || {};
	var client = window.supabase && config.supabaseUrl && config.publishableKey ? window.supabase.createClient(config.supabaseUrl, config.publishableKey) : null;
	var login = root.querySelector('[data-settings-login]');
	var workspace = root.querySelector('[data-settings-workspace]');
	var notice = root.querySelector('[data-settings-notice]');
	var form = root.querySelector('[data-settings-form]');
	function message(text, error) { notice.textContent = text || ''; notice.classList.toggle('is-error', Boolean(error)); }
	function setSignedIn(value) { login.hidden = value; workspace.hidden = !value; }
	function validUrl(value, optional) {
		if (!value && optional) { return true; }
		try { return new URL(value).protocol === 'https:'; } catch (error) { return false; }
	}
	async function loadSettings() {
		message('กำลังโหลดค่าหน้าร้าน…');
		var result = await client.from('clicko_storefront_settings').select('*').eq('id', 'storefront').single();
		if (result.error) { message('โหลดค่าร้านไม่สำเร็จ ตรวจว่ารัน migration 004 แล้ว: ' + result.error.message, true); return; }
		Object.keys(result.data).forEach(function (key) { if (form.elements[key]) { form.elements[key].value = key === 'contact_phone' ? (result.data[key] || config.contact_phone || '') : (result.data[key] == null ? '' : result.data[key]); } });
		message('แก้ไขข้อความและช่องทางติดต่อ จากนั้นกดบันทึก');
	}
	async function checkUser() {
		var session = await client.auth.getSession();
		var user = session.data.session && session.data.session.user;
		if (!user) { setSignedIn(false); return; }
		var result = await client.from('clicko_admins').select('user_id').eq('user_id', user.id).maybeSingle();
		if (result.error || !result.data) { setSignedIn(false); message('บัญชีนี้ไม่มีสิทธิ์ Admin สำหรับแก้ไขค่าหน้าร้าน', true); return; }
		setSignedIn(true);
		await loadSettings();
	}
	if (!client) { login.hidden = true; message('ยังไม่ได้ตั้งค่า Supabase', true); return; }
	login.addEventListener('submit', async function (event) {
		event.preventDefault();
		var result = await client.auth.signInWithPassword({ email: login.elements.email.value.trim(), password: login.elements.password.value });
		if (result.error) { message('เข้าสู่ระบบไม่สำเร็จ ตรวจอีเมลและรหัสผ่าน', true); return; }
		await checkUser();
	});
	form.addEventListener('submit', async function (event) {
		event.preventDefault();
		if (!validUrl(form.elements.seller_profile_url.value.trim(), false) || !validUrl(form.elements.seller_messenger_url.value.trim(), true) || !validUrl(form.elements.brand_page_url.value.trim(), false) || !validUrl(form.elements.sales_group_url.value.trim(), false)) {
			message('ลิงก์ติดต่อร้านต้องเป็น URL HTTPS ที่ถูกต้อง', true); return;
		}
		var keys = ['announcement','tagline','hero_eyebrow','hero_title','hero_highlight','hero_suffix','hero_description','pickup_location','contact_phone','seller_profile_url','seller_messenger_url','brand_page_url','sales_group_url','message_intro','message_footer'];
		var row = { id: 'storefront', updated_at: new Date().toISOString() };
		keys.forEach(function (key) { row[key] = form.elements[key].value.trim(); });
		var result = await client.from('clicko_storefront_settings').update(row).eq('id','storefront').select('updated_at').maybeSingle();
		if (result.error || !result.data) { message('บันทึกไม่สำเร็จ: ' + (result.error ? result.error.message : 'บัญชีนี้ไม่มีสิทธิ์'), true); return; }
		message('บันทึกค่าหน้าร้านแล้ว');
	});
	root.querySelector('[data-settings-logout]').addEventListener('click', async function () { await client.auth.signOut(); setSignedIn(false); message('ออกจากระบบแล้ว'); });
	checkUser().catch(function () { setSignedIn(false); message('เชื่อมต่อ Supabase ไม่สำเร็จ', true); });
}());
