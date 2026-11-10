(function () {
	'use strict';

	var config = window.ClickoAccountConfig || {};
	var root = document.querySelector('[data-clicko-customer-account]');
	if (!root) { return; }

	var notice = root.querySelector('[data-account-notice]');
	var authPanel = root.querySelector('[data-account-auth]');
	var profileForm = root.querySelector('[data-account-profile]');
	var resetForm = root.querySelector('[data-account-new-password]');
	var loginForm = root.querySelector('[data-account-login]');
	var registerForm = root.querySelector('[data-account-register]');
	var emailLabel = root.querySelector('[data-account-email]');
	var contactEmailWrap = root.querySelector('[data-account-contact-email-wrap]');
	var supabaseApi = window.supabase;
	var client;
	var currentUser = null;

	function message(text, isError) {
		notice.textContent = text || '';
		notice.classList.toggle('is-error', Boolean(isError));
	}

	function setBusy(form, busy) {
		form.querySelectorAll('button').forEach(function (button) { button.disabled = busy; });
	}

	function showSignedOut() {
		currentUser = null;
		authPanel.hidden = false;
		profileForm.hidden = true;
		resetForm.hidden = true;
	}

	function showProfile(user, profile) {
		currentUser = user;
		authPanel.hidden = true;
		resetForm.hidden = true;
		profileForm.hidden = false;
		emailLabel.textContent = user.email || 'บัญชี Facebook (ไม่มีอีเมลจากผู้ให้บริการ)';
		contactEmailWrap.hidden = Boolean(user.email);
		profileForm.elements.contact_email.required = !user.email;
		var metadata = user.user_metadata || {};
		profileForm.elements.first_name.value = profile.first_name || metadata.first_name || '';
		profileForm.elements.last_name.value = profile.last_name || metadata.last_name || '';
		profileForm.elements.birth_date.value = profile.birth_date || '';
		profileForm.elements.phone.value = profile.phone || '';
		profileForm.elements.current_address.value = profile.current_address || '';
		profileForm.elements.contact_email.value = profile.contact_email || '';
	}

	async function loadProfile(user) {
		message('กำลังโหลดข้อมูลบัญชี…');
		var result = await client.from('clicko_customer_profiles')
			.select('first_name,last_name,birth_date,current_address,phone,contact_email')
			.eq('user_id', user.id)
			.maybeSingle();
		if (result.error) { throw result.error; }
		showProfile(user, result.data || {});
		message(result.data ? 'เข้าสู่ระบบแล้ว แก้ไขข้อมูลสมาชิกได้ที่นี่' : 'เข้าสู่ระบบแล้ว กรุณากรอกข้อมูลสมาชิกให้ครบ');
	}

	function handleAuthState(event, session) {
		if (event === 'PASSWORD_RECOVERY') {
			authPanel.hidden = true;
			profileForm.hidden = true;
			resetForm.hidden = false;
			message('ตั้งรหัสผ่านใหม่สำหรับบัญชีของคุณ');
			return;
		}
		if (!session || !session.user) {
			showSignedOut();
			return;
		}
		loadProfile(session.user).catch(function () {
			showSignedOut();
			message('โหลดโปรไฟล์ไม่สำเร็จ ตรวจว่าได้รันไฟล์ migration ของ Supabase แล้วลองเข้าสู่ระบบอีกครั้ง', true);
		});
	}

	if (!config.url || !config.key || !supabaseApi || !supabaseApi.createClient) {
		root.querySelector('[data-account-config-missing]').hidden = false;
		root.querySelector('[data-account-auth]').hidden = true;
		return;
	}
	client = supabaseApi.createClient(config.url, config.key);
	client.auth.onAuthStateChange(function (event, session) {
		window.setTimeout(function () { handleAuthState(event, session); }, 0);
	});
	client.auth.getSession().then(function (result) {
		if (result.error) { throw result.error; }
		handleAuthState('INITIAL_SESSION', result.data.session);
	}).catch(function () { message('เชื่อมต่อระบบบัญชีไม่สำเร็จ กรุณาลองใหม่ภายหลัง', true); });

	loginForm.addEventListener('submit', async function (event) {
		event.preventDefault();
		setBusy(loginForm, true);
		message('กำลังเข้าสู่ระบบ…');
		try {
			var result = await client.auth.signInWithPassword({ email: loginForm.elements.email.value.trim(), password: loginForm.elements.password.value });
			if (result.error) { message('เข้าสู่ระบบไม่สำเร็จ ตรวจอีเมลและรหัสผ่านแล้วลองอีกครั้ง', true); return; }
			loginForm.reset();
		} catch (error) { message('เชื่อมต่อระบบบัญชีไม่สำเร็จ กรุณาลองใหม่', true); }
		finally { setBusy(loginForm, false); }
	});

	registerForm.addEventListener('submit', async function (event) {
		event.preventDefault();
		setBusy(registerForm, true);
		message('กำลังสร้างบัญชี…');
		try {
			var result = await client.auth.signUp({
				email: registerForm.elements.email.value.trim(),
				password: registerForm.elements.password.value,
				options: { emailRedirectTo: config.redirectTo }
			});
			if (result.error) { message('สมัครสมาชิกไม่สำเร็จ: ' + result.error.message, true); return; }
			registerForm.reset();
			message(result.data.session ? 'สร้างบัญชีแล้ว กรุณากรอกข้อมูลสมาชิกด้านล่าง' : 'สร้างบัญชีแล้ว กรุณายืนยันอีเมลจากกล่องจดหมาย แล้วกลับมาเข้าสู่ระบบเพื่อกรอกข้อมูลสมาชิก');
			if (result.data.session) { handleAuthState('SIGNED_IN', result.data.session); }
		} catch (error) { message('เชื่อมต่อระบบสมัครสมาชิกไม่สำเร็จ กรุณาลองใหม่', true); }
		finally { setBusy(registerForm, false); }
	});

	root.querySelector('[data-account-facebook]').addEventListener('click', async function (event) {
		var button = event.currentTarget;
		button.disabled = true;
		message('กำลังเปิด Facebook…');
		try {
			var result = await client.auth.signInWithOAuth({ provider: 'facebook', options: { redirectTo: config.redirectTo, scopes: 'email' } });
			if (result.error) { button.disabled = false; message('เข้าสู่ระบบด้วย Facebook ไม่สำเร็จ: ' + result.error.message, true); }
		} catch (error) { button.disabled = false; message('เชื่อมต่อ Facebook ไม่สำเร็จ กรุณาลองใหม่', true); }
	});

	root.querySelector('[data-account-reset]').addEventListener('click', async function () {
		var email = loginForm.elements.email.value.trim();
		if (!email) { message('กรอกอีเมลในช่องเข้าสู่ระบบก่อนขอลิงก์ตั้งรหัสผ่านใหม่', true); loginForm.elements.email.focus(); return; }
		try {
			var result = await client.auth.resetPasswordForEmail(email, { redirectTo: config.redirectTo });
			message(result.error ? 'ส่งลิงก์ตั้งรหัสผ่านไม่สำเร็จ: ' + result.error.message : 'หากอีเมลนี้มีบัญชีอยู่ ระบบจะส่งลิงก์ตั้งรหัสผ่านไปให้');
		} catch (error) { message('ส่งลิงก์ตั้งรหัสผ่านไม่สำเร็จ กรุณาลองใหม่', true); }
	});

	profileForm.addEventListener('submit', async function (event) {
		event.preventDefault();
		if (!currentUser) { return; }
		setBusy(profileForm, true);
		message('กำลังบันทึกโปรไฟล์…');
		var values = profileForm.elements;
		var row = {
			user_id: currentUser.id,
			first_name: values.first_name.value.trim(),
			last_name: values.last_name.value.trim(),
			birth_date: values.birth_date.value || null,
			current_address: values.current_address.value.trim() || null,
			phone: values.phone.value.trim() || null,
			contact_email: values.contact_email.value.trim() || null
		};
		try {
			var result = await client.from('clicko_customer_profiles').upsert(row, { onConflict: 'user_id' });
			message(result.error ? 'บันทึกไม่สำเร็จ: ' + result.error.message : 'บันทึกข้อมูลสมาชิกแล้ว');
		} catch (error) { message('บันทึกไม่สำเร็จ กรุณาตรวจการเชื่อมต่อแล้วลองใหม่', true); }
		finally { setBusy(profileForm, false); }
	});

	resetForm.addEventListener('submit', async function (event) {
		event.preventDefault();
		setBusy(resetForm, true);
		try {
			var result = await client.auth.updateUser({ password: resetForm.elements.password.value });
			if (result.error) { message('บันทึกรหัสผ่านใหม่ไม่สำเร็จ: ' + result.error.message, true); return; }
			resetForm.reset();
			message('เปลี่ยนรหัสผ่านแล้ว');
			handleAuthState('SIGNED_IN', { user: result.data.user });
		} catch (error) { message('เปลี่ยนรหัสผ่านไม่สำเร็จ กรุณาลองใหม่', true); }
		finally { setBusy(resetForm, false); }
	});

	root.querySelector('[data-account-logout]').addEventListener('click', async function () {
		try {
			var result = await client.auth.signOut();
			message(result.error ? 'ออกจากระบบไม่สำเร็จ: ' + result.error.message : 'ออกจากระบบแล้ว', Boolean(result.error));
		} catch (error) { message('ออกจากระบบไม่สำเร็จ กรุณาลองใหม่', true); }
	});
}());
