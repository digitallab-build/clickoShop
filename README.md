# Clicko — Gaming Gear & PC Parts

เว็บไซต์ production สำหรับ GitHub Pages อยู่ใน `site/` และเผยแพร่ด้วย GitHub Actions ส่วนโค้ด WordPress ใน `wp-content/` เก็บไว้เป็นระบบเดิม/อ้างอิง ไม่ได้ถูกรันบน GitHub Pages เพราะ Pages ไม่รองรับ PHP หรือ WordPress

## เผยแพร่บน GitHub Pages

1. ไปที่ repository **Settings → Pages** แล้วเลือก **GitHub Actions** เป็น Build and deployment source
2. รัน Supabase migrations `202611090001` ถึง `202611090004` ตามลำดับใน SQL Editor ของโปรเจกต์เดิม
3. ตรวจ `site/supabase-config.js` ว่ามี Project URL และ publishable/anon key ที่ถูกต้องเท่านั้น ห้ามใส่ `service_role` key ในไฟล์เว็บ
4. ตั้ง Supabase Auth Site URL และ redirect allow list ตามคู่มือด้านล่าง; ตั้ง Facebook provider/callback ใน Supabase หากเปิด Facebook Login
5. ก่อนเปิดฟอร์มรับซื้อ/เทิร์น ให้ตั้ง Cloudflare Turnstile และ Supabase Edge Function ตามคู่มือ production
6. push การเปลี่ยนแปลงใน `site/` หรือ workflow ไป branch `main`; GitHub Actions จะเผยแพร่เฉพาะ `site/`

URL เริ่มต้นที่คาดไว้คือ `https://tonzkittipon.github.io/clickoShop/` หาก repository ตั้ง custom domain ให้ใช้โดเมนจริงแทนใน Supabase Auth และ CORS

## ระบบและเอกสาร

- [คู่มือ deploy GitHub Pages และตั้งค่า production](docs/DEPLOY_GITHUB_PAGES_TH.md)
- [แผนเว็บไซต์และรายละเอียดระบบ](docs/CLICKO_WEBSITE_PLAN.md)
- [คู่มือผู้ขาย](docs/SELLER_GUIDE_TH.md)
- [แบบจำลองข้อมูลสมาชิก Supabase](docs/SUPABASE_CUSTOMER_DATA_MODEL.md)
- [การทำงานและตั้งค่าฟอร์มรับซื้อ/เทิร์น](docs/TRADE_IN_INTAKE.md)

โฟลเดอร์ `site/` มีหน้าแรก แค็ตตาล็อกสินค้า สมัคร/เข้าสู่ระบบลูกค้า ฟอร์มรับซื้อ/เทิร์น แดชบอร์ดพนักงาน รีวิว ติดต่อร้าน และหน้าตั้งค่าร้านสำหรับ Admin การจัดการสินค้าใช้หน้าจัดการ Supabase เดิม ส่วนข้อความหน้าร้านและช่องทางติดต่อแก้ได้ที่ `site-settings.html` หลัง Admin เข้าสู่ระบบ

ช่องทางซื้อขายคือ Facebook ส่วนตัวของผู้ขาย `https://www.facebook.com/sippavit.gomarn/`; เพจ `https://www.facebook.com/Clicko.gg/` ใช้แสดงแบรนด์และข่าวสาร
