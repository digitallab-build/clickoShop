(function () {
	'use strict';

	var storageKey = 'clicko-language';
	var dictionary = {
		'หน้าแรก': 'Home', 'สินค้าทั้งหมด': 'All Products', 'รับซื้อ / เทิร์น': 'Buyback / Trade-in', 'รับซื้อ / เทิร์นอุปกรณ์': 'Buyback / Trade-in',
		'รีวิวร้าน': 'Reviews', 'รีวิวจากลูกค้า': 'Customer Reviews', 'กลุ่มซื้อขาย': 'Sales Group', 'กลุ่มซื้อขาย ↗': 'Sales Group ↗', 'ติดต่อเรา': 'Contact', 'ติดต่อ Clicko': 'Contact Clicko',
		'ค้นหาสินค้า': 'Search products', 'ค้นหาสินค้า...': 'Search products...', 'ค้นหา': 'Search', 'เลือกภาษา': 'Select language',
		'เข้าสู่ระบบ': 'Log in', 'เลือกประเภทการเข้าสู่ระบบ': 'Choose account type', 'สำหรับลูกค้า': 'Customer', 'สำหรับพนักงาน': 'Staff',
		'สมัครหรือเข้าสู่ระบบสมาชิก': 'Register or log in', 'เข้าสู่ระบบจัดการร้าน': 'Staff dashboard', 'ติดตามข่าวสารที่เพจ Clicko ↗': 'Follow Clicko on Facebook ↗',
		'สินค้าทั้งหมด — Clicko': 'All Products — Clicko', 'รับซื้อ / เทิร์นอุปกรณ์ — Clicko': 'Buyback / Trade-in — Clicko',
		'ติดต่อเรา — Clicko': 'Contact — Clicko', 'รีวิวร้าน — Clicko': 'Reviews — Clicko', 'นโยบายความเป็นส่วนตัว — Clicko': 'Privacy Policy — Clicko',
		'ไม่พบหน้า — Clicko': 'Page Not Found — Clicko', 'คำขอรับซื้อ / เทิร์น — Clicko': 'Buyback / Trade-in Requests — Clicko', 'บัญชีลูกค้า — Clicko': 'Customer Account — Clicko',
		'จัดส่งทั่วไทย': 'Nationwide shipping', 'สอบถามวิธีจัดส่งกับผู้ขาย': 'Ask the seller about shipping', 'ตรวจสอบก่อนซื้อ': 'Check before purchase',
		'ขอรายละเอียดสินค้าก่อนตกลง': 'Ask for more product details', 'คุยกับผู้ขายโดยตรง': 'Talk directly with the seller', 'ซื้อขายผ่าน Facebook ส่วนตัว': 'Deal directly through Facebook',
		'ติดตามข่าวสารได้ที่': 'Follow us', 'เลือกของที่ใช่ แล้วคุยกับผู้ขายโดยตรง': 'Find your gear and talk directly with the seller',
		'ช่องทางของ Clicko': 'Clicko Channels', 'ติดต่อซื้อกับผู้ขาย ↗': 'Contact the seller ↗', 'ติดตามเพจ Clicko ↗': 'Follow Clicko ↗',
		'เข้ากลุ่มซื้อขาย ↗': 'Join the sales group ↗', 'ติดต่อร้าน': 'Contact the store', 'ข้อมูลช่วยเหลือ': 'Help & Information',
		'รับซื้อ / เทิร์นอุปกรณ์': 'Buyback / Trade-in', 'ประกันและการคืนสินค้า': 'Warranty & Returns', 'นโยบายความเป็นส่วนตัว': 'Privacy Policy',
		'เข้าสู่ระบบพนักงาน': 'Staff Login', 'ติดต่อซื้อขายผ่าน Facebook ส่วนตัวของผู้ขาย': 'Contact the seller directly on Facebook',
		'ซื้อขายคอมพิวเตอร์': 'Buy and sell computers', 'และอุปกรณ์เกมมิง': 'and gaming gear', 'ครบ จบ ในที่เดียว': 'Everything in one place',
		'เลือกชมสินค้า เช็กสภาพและรายละเอียด แล้วพูดคุยกับผู้ขายโดยตรง': 'Browse products, check their condition and details, then talk directly with the seller.',
		'เลือกดูหมวดสินค้า': 'Browse Categories', 'ขายหรือเทิร์นอุปกรณ์': 'Sell or trade your gear', 'เลือกดูตามหมวดหมู่': 'Shop by Category',
		'เลือกหมวดเพื่อค้นหาสินค้าในหน้าสินค้าทั้งหมด': 'Choose a category to find products.', 'ดูสินค้าทั้งหมด': 'View All Products',
		'จอเกมมิง': 'Gaming Monitors', 'การ์ดจอ': 'Graphics Cards', 'เมนบอร์ด': 'Motherboards', 'คีย์บอร์ด': 'Keyboards', 'เมาส์': 'Mice', 'หูฟัง': 'Headsets', 'อุปกรณ์อื่น ๆ': 'Other Gear',
		'มีอุปกรณ์ที่ไม่ได้ใช้?': 'Have gear you no longer use?', 'ส่งข้อมูลให้เราประเมินได้': 'Send us the details for an evaluation',
		'กรอกรุ่นและสภาพอุปกรณ์ผ่านเว็บไซต์ ทีมงานจะติดต่อกลับเพื่อคุยรายละเอียด': 'Enter the model and condition through the website. Our team will contact you to discuss the next steps.',
		'เริ่มส่งข้อมูลรับซื้อ / เทิร์น': 'Start a Buyback / Trade-in Request', 'ดูรีวิวและติดตามข่าวสารจากร้าน': 'Read reviews and follow store updates',
		'ช่องทางติดต่อและจุดนัดรับส่งสินค้า': 'Contact details and meetup location',
		'FIND YOUR GEAR': 'FIND YOUR GEAR', 'สินค้าทั้งหมด': 'All Products', 'เลือกดูอุปกรณ์ตามหมวดหมู่ ค้นหา และกรองสินค้าได้': 'Browse by category, search, and filter products.',
		'เลือกหมวดหมู่สินค้า': 'Choose product category', 'ทุกหมวด': 'All Categories', 'สภาพ': 'Condition', 'มือ 1 และมือ 2': 'New and Pre-owned',
		'มือ 1': 'New', 'มือ 2': 'Pre-owned', 'สถานะ': 'Availability', 'พร้อมขาย': 'Available', 'จองแล้ว': 'Reserved', 'ขายแล้ว': 'Sold', 'ทุกสถานะ': 'All Statuses',
		'เรียงตาม': 'Sort by', 'มาใหม่': 'Newest', 'ราคาต่ำไปสูง': 'Price: Low to High', 'ราคาสูงไปต่ำ': 'Price: High to Low',
		'ชื่อสินค้า รุ่น หรือ SKU': 'Product name, model, or SKU', 'กำลังโหลดสินค้า…': 'Loading products…', 'เชื่อมต่อแค็ตตาล็อก Supabase ไม่สำเร็จ ตรวจค่า project และ RLS แล้วลองใหม่': 'Could not load the product catalog. Check the project settings and access policies, then try again.',
		'ราคาและสินค้าคงเหลือควรยืนยันกับผู้ขายอีกครั้งก่อนซื้อ': 'Please confirm the price and availability with the seller before purchase.',
		'คัดลอกรายการและเปิด Facebook': 'Copy List & Contact Seller', 'ล้างรายการ': 'Clear List', 'ปิดรายละเอียด': 'Close details', 'ถามราคา': 'Ask for price', 'สอบถามราคา': 'Ask for price',
		'สอบถามผู้ขาย': 'Contact Seller', 'ดูรายละเอียด →': 'View Details →', 'เพิ่มรายการ': 'Add to List', 'เพิ่มแล้ว ✓': 'Added ✓', 'สเปกสินค้า': 'Specifications', 'หมายเหตุ': 'Notes',
		'รหัสสินค้า': 'SKU', 'ประกัน': 'Warranty', 'ประเภท': 'Category', 'ไม่พบสินค้าที่ตรงกับตัวกรอง': 'No products match these filters.', 'ยังไม่มีสินค้าใน Supabase': 'No products are listed yet.',
		'BUYBACK / TRADE-IN': 'BUYBACK / TRADE-IN', 'กรอกข้อมูลติดต่อและรายละเอียดอุปกรณ์ ระบบจะบันทึกคำขอไว้ให้ทางร้านตรวจสอบ ก่อนพาไปพูดคุยกับผู้ขายผ่าน Messenger': 'Enter your contact details and item information. The store will review your request before you continue the conversation with the seller on Messenger.',
		'การส่งข้อมูลยังไม่ถือเป็นการเสนอราคา ร้านจะติดต่อกลับเพื่อสอบถามรายละเอียดและประเมินราคา': 'Submitting this form is not an offer. The store will contact you to discuss the item and provide an estimate.',
		'ชื่อ': 'First Name', 'นามสกุล': 'Last Name', 'ชื่อเล่น': 'Nickname', 'เบอร์โทรศัพท์': 'Phone Number', 'รุ่น / ชื่อสินค้า': 'Model / Product Name',
		'ประกันสินค้า': 'Warranty', 'สภาพ / ตำหนิ': 'Condition / Defects', 'ชื่อจริง': 'Your first name', 'ชื่อเล่นที่ให้ร้านเรียก': 'What should we call you?',
		'เช่น 081-234-5678': 'e.g. 081-234-5678', 'เช่น การ์ดจอ RTX 4070': 'e.g. RTX 4070 graphics card', 'เช่น เหลือประกันถึง 12/2027': 'e.g. Warranty until 12/2027',
		'บอกสภาพการใช้งาน ตำหนิ และอุปกรณ์ที่มี': 'Describe the condition, any defects, and included accessories.',
		'บันทึกข้อมูลและไป Messenger': 'Save Details & Open Messenger', 'ข้อมูลติดต่อและรายละเอียดสินค้าจะถูกบันทึกในระบบของร้านเพื่อใช้ติดต่อนัดหมายและประเมินราคา': 'Your contact and item details will be saved so the store can follow up and evaluate your item.',
		'กำลังบันทึกข้อมูลในระบบ...': 'Saving your request…', 'กำลังเปิด Messenger ของ Kew...': 'Opening the seller’s Messenger…',
		'กรุณากรอกข้อมูลให้ครบถ้วน': 'Please complete all required fields.', 'รับซื้อ / เทิร์นอุปกรณ์': 'Buyback / Trade-in', 'การซื้อขายและการชำระเงินตกลงกันโดยตรงระหว่างลูกค้ากับผู้ขายผ่าน Facebook': 'The customer and seller arrange payment and the transaction directly through Facebook.',
		'CLICKO / CONTACT': 'CLICKO / CONTACT', 'สอบถามสินค้าและนัดหมายกับร้าน Clicko ได้ผ่านช่องทางต่อไปนี้': 'Contact Clicko about products or meetups through these channels.',
		'Facebook เจ้าของร้าน': 'Seller’s Facebook', 'Facebook Page ร้าน': 'Clicko Facebook Page', 'จุดนัดรับส่งพัสดุ': 'Meetup / Parcel Drop-off',
		'โทรศัพท์': 'Phone', 'ซอยรัชดา 36': 'Ratchadaphisek Soi 36', 'โทร': 'Call', 'ส่งข้อความ': 'Message',
		'Kew Sippavit · ติดต่อซื้อขายโดยตรง': 'Kew Sippavit · Contact the seller directly', 'Clicko · Gaming Gear & PC Parts': 'Clicko · Gaming Gear & PC Parts',
		'กรุณานัดหมายกับร้านล่วงหน้าก่อนเดินทางหรือส่งพัสดุ': 'Please contact the store before visiting or sending a parcel.',
		'CLICKO / REVIEWS': 'CLICKO / REVIEWS', 'ขอบคุณลูกค้าทุกท่านที่ไว้วางใจ Clicko': 'Thank you to all our customers for trusting Clicko.',
		'CLICKO / PRIVACY': 'CLICKO / PRIVACY', 'นโยบายความเป็นส่วนตัว': 'Privacy Policy',
		'Clicko จัดเก็บข้อมูลบัญชีสมาชิกที่ผู้ใช้กรอก เช่น ชื่อ นามสกุล วันเกิด ที่อยู่ เบอร์โทรศัพท์ และอีเมล เพื่อจัดการบัญชีและติดต่อเกี่ยวกับการใช้บริการ ข้อมูลบัญชีจัดเก็บผ่าน Supabase และจำกัดการเข้าถึงตามสิทธิ์ของบัญชี': 'Clicko stores member details such as name, date of birth, address, phone number, and email to manage accounts and provide service. Account data is stored in Supabase with access limited by account permissions.',
		'เมื่อส่งคำขอรับซื้อหรือเทิร์น เว็บไซต์จะจัดเก็บชื่อ นามสกุล ชื่อเล่น เบอร์โทร รายละเอียดสินค้า และสภาพสินค้าใน Supabase เพื่อระบุคำขอ ติดต่อกลับ และประเมินราคา พนักงานที่ได้รับอนุญาตใช้หน้า dashboard ของร้านเพื่อตรวจสอบข้อมูล รูปที่แนบไว้กับคำขอเก่าจะอยู่ในพื้นที่ส่วนตัวของร้าน ส่วนฟอร์มปัจจุบันไม่รับอัปโหลดรูป': 'When you submit a buyback or trade-in request, Clicko stores your name, nickname, phone number, item details, and condition in Supabase so authorized staff can identify and follow up on the request. Photos from older requests remain private; the current form does not accept uploads.',
		'เว็บไซต์ไม่ขอและไม่จัดเก็บเลขบัตรประชาชนหรือ Facebook token การส่งลูกค้าไป Messenger เป็นเพียงการเปิดช่องทางให้ลูกค้าติดต่อผู้ขาย โดยเว็บไซต์ไม่ส่งข้อความในนามลูกค้า': 'The website does not collect national ID numbers or Facebook tokens. Messenger links let customers contact the seller; the website does not send messages on their behalf.',
		'หากต้องการแก้ไขหรือลบข้อมูล หรือสอบถามเรื่องความเป็นส่วนตัว กรุณา': 'To correct or delete your data, or ask about privacy, please',
		'ประกันและการคืนสินค้า': 'Warranty & Returns', 'เงื่อนไขประกันและการคืนสินค้าขึ้นอยู่กับสินค้าแต่ละรายการ กรุณาขอให้ผู้ขายยืนยันสภาพ ประกัน อุปกรณ์ที่ได้รับ และเงื่อนไขก่อนตกลงซื้อขาย': 'Warranty and return terms vary by item. Please confirm the condition, warranty, included accessories, and terms with the seller before purchasing.',
		'การชำระเงิน การจัดส่ง และการรับประกันดำเนินการโดยผู้ซื้อกับผู้ขายโดยตรงผ่านช่องทางที่ตกลงกัน': 'The buyer and seller arrange payment, delivery, and warranty directly through their agreed channel.',
		'ติดต่อร้านเพื่อสอบถาม': 'Contact the Store',
		'ไม่พบหน้าที่ต้องการ': 'Page Not Found', 'ลิงก์อาจไม่ถูกต้องหรือหน้าถูกย้ายแล้ว': 'This link may be incorrect or the page may have moved.', 'กลับหน้าแรก': 'Back to Home',
		'บัญชีลูกค้า': 'Customer Account', 'สมัครสมาชิก': 'Create Account', 'เข้าสู่ระบบ': 'Log In', 'อีเมล': 'Email', 'รหัสผ่าน': 'Password',
		'ลืมรหัสผ่าน?': 'Forgot password?', 'สมัครด้วยอีเมล': 'Register with Email', 'หรือ': 'or', 'เข้าสู่ระบบด้วย Facebook': 'Continue with Facebook',
		'ข้อมูลสมาชิก': 'Member Profile', 'อีเมลบัญชี:': 'Account email:', 'วันเดือนปีเกิด (ไม่บังคับ)': 'Date of Birth (optional)',
		'เบอร์โทรศัพท์ (ไม่บังคับ)': 'Phone Number (optional)', 'ที่อยู่ปัจจุบัน (ไม่บังคับ)': 'Current Address (optional)',
		'อีเมลสำหรับติดต่อ (กรณีบัญชี Facebook ไม่มีอีเมล)': 'Contact Email (if your Facebook account has no email)',
		'บันทึกข้อมูลสมาชิก': 'Save Profile', 'ตั้งรหัสผ่านใหม่': 'Set a New Password', 'รหัสผ่านใหม่': 'New Password', 'บันทึกรหัสผ่าน': 'Save Password',
		'สมัครสมาชิกเพื่อจัดเก็บข้อมูลที่กรอกเท่านั้น ไม่มีการขอเลขบัตรประชาชน ·': 'Registration stores only the details you provide. We do not ask for a national ID ·',
		'รหัสผ่าน (อย่างน้อย 8 ตัว)': 'Password (at least 8 characters)',
		'ข้อมูลบัญชีจัดเก็บผ่าน Supabase และจำกัดการเข้าถึงตามสิทธิ์ของบัญชี': 'Account data is stored in Supabase with access restricted by account permissions.',
		'สวัสดีครับ สนใจสินค้าจากเว็บไซต์ Clicko ครับ': 'Hello, I’m interested in this item from the Clicko website.',
		'รบกวนยืนยันสินค้าคงเหลือ ราคาปัจจุบัน และค่าจัดส่งด้วยครับ': 'Please confirm availability, the current price, and shipping cost.',
		'Log in': 'Log in'
	};
	var originalText = new WeakMap();
	var originalAttributes = new WeakMap();
	var current = 'th';
	var previewTimer;

	function readLanguage() {
		try { return localStorage.getItem(storageKey) === 'en' ? 'en' : 'th'; }
		catch (error) { return 'th'; }
	}

	function translate(value) {
		var text = String(value == null ? '' : value);
		if (current !== 'en') { return text; }
		if (Object.prototype.hasOwnProperty.call(dictionary, text)) { return dictionary[text]; }
		var trimmed = text.trim();
		if (Object.prototype.hasOwnProperty.call(dictionary, trimmed)) {
			return text.slice(0, text.indexOf(trimmed)) + dictionary[trimmed] + text.slice(text.indexOf(trimmed) + trimmed.length);
		}
		var match = trimmed.match(/^✓?\s*ประกัน\s*(.*)$/);
		if (match) { return text.replace(trimmed, '✓ Warranty ' + match[1]); }
		match = trimmed.match(/^สภาพ:\s*(.*)$/);
		if (match) { return text.replace(trimmed, 'Condition: ' + translate(match[1])); }
		match = trimmed.match(/^แสดง\s*(\d+)\s*รายการ$/);
		if (match) { return text.replace(trimmed, 'Showing ' + match[1] + ' items'); }
		match = trimmed.match(/^รายการที่สนใจ\s*(\d+)\s*ชิ้น$/);
		if (match) { return text.replace(trimmed, 'Your List · ' + match[1] + ' items'); }
		match = trimmed.match(/^(.+)\s*บาท$/);
		if (match && /^[\d,.]+$/.test(match[1])) { return 'THB ' + match[1]; }
		return text;
	}

	function applyTextNode(node) {
		var parent = node.parentElement;
		if (!parent || /^(SCRIPT|STYLE|NOSCRIPT|TEXTAREA|INPUT|OPTION)$/.test(parent.tagName)) { return; }
		if (!originalText.has(node)) { originalText.set(node, node.nodeValue); }
		var source = originalText.get(node);
		node.nodeValue = current === 'en' ? translate(source) : source;
	}

	function applyElement(element) {
		if (element.nodeType === Node.TEXT_NODE) { applyTextNode(element); return; }
		if (element.nodeType !== Node.ELEMENT_NODE) { return; }
		if (/^(SCRIPT|STYLE|NOSCRIPT)$/.test(element.tagName)) { return; }
		var attributes = ['placeholder', 'aria-label', 'title', 'alt'];
		var saved = originalAttributes.get(element) || {};
		attributes.forEach(function (name) {
			if (!element.hasAttribute(name)) { return; }
			if (!Object.prototype.hasOwnProperty.call(saved, name)) { saved[name] = element.getAttribute(name); }
			element.setAttribute(name, current === 'en' ? translate(saved[name]) : saved[name]);
		});
		originalAttributes.set(element, saved);
		Array.prototype.forEach.call(element.childNodes, applyElement);
	}

	function updateSwitches() {
		document.querySelectorAll('[data-clicko-language]').forEach(function (button) {
			button.setAttribute('aria-pressed', String(button.dataset.clickoLanguage === current));
		});
	}

	function showPreviewNotice() {
		var existing = document.querySelector('.clicko-language-preview-note');
		if (existing) { existing.remove(); }
		var note = document.createElement('div');
		note.className = 'clicko-language-preview-note';
		note.setAttribute('role', 'status');
		note.textContent = 'English preview: interface text is translated. Product names and specifications stay as entered until an automatic translation service is connected.';
		document.body.appendChild(note);
		window.clearTimeout(previewTimer);
		previewTimer = window.setTimeout(function () { note.remove(); }, 6500);
	}

	function setLanguage(language, showNotice) {
		current = language === 'en' ? 'en' : 'th';
		try { localStorage.setItem(storageKey, current); } catch (error) { /* Language selection remains active for this page. */ }
		document.documentElement.lang = current;
		applyElement(document.body);
		document.title = current === 'en' ? translate(originalTitle) : originalTitle;
		updateSwitches();
		if (showNotice && current === 'en') { showPreviewNotice(); }
	}

	var originalTitle = document.title;
	current = readLanguage();
	window.ClickoLanguage = {
		get: function () { return current; },
		translate: translate,
		set: setLanguage
	};

	document.addEventListener('click', function (event) {
		var button = event.target.closest('[data-clicko-language]');
		if (button) { setLanguage(button.dataset.clickoLanguage, true); }
	});

	var observer = new MutationObserver(function (records) {
		records.forEach(function (record) {
			if (record.type === 'childList') { record.addedNodes.forEach(applyElement); }
			else if (record.type === 'characterData') { applyTextNode(record.target); }
		});
	});
	observer.observe(document.body, { childList: true, subtree: true, characterData: true });
	setLanguage(current, false);
}());
