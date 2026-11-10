# Deploy Clicko บน GitHub Pages

## สิ่งที่ workflow ทำ

ไฟล์ `.github/workflows/deploy-pages.yml` เผยแพร่เฉพาะโฟลเดอร์ `site/` ไป GitHub Pages เมื่อมีการ push การเปลี่ยนแปลงใน `site/` หรือ workflow ไปยัง branch `main` หรือเมื่อสั่งรัน workflow เองจาก GitHub Actions โฟลเดอร์ WordPress, migration และเอกสารจะไม่ถูกส่งเป็นหน้าเว็บ

ค่า URL สำหรับ project repository นี้คาดว่าเป็น `https://digitallab-build.github.io/clickoShop/` ตรวจ URL จริงได้จาก **Repository → Settings → Pages** หลังเปิดใช้งาน

## ตั้งค่า GitHub Pages

1. เปิด **Repository → Settings → Pages**
2. ใน **Build and deployment** เลือก **GitHub Actions**
3. Push ไฟล์เข้า branch `main` หรือเปิด **Actions → Deploy Clicko static site to GitHub Pages → Run workflow**
4. ตรวจ run ล่าสุดในแท็บ **Actions** ให้จบสถานะสำเร็จ แล้วเปิด URL ที่ GitHub Pages แสดง

## ตั้งค่า Supabase

1. ใช้ Supabase project เดิมซึ่งมีแค็ตตาล็อกและ Auth อยู่แล้ว
2. รัน `supabase/migrations/202611090001_clicko_customer_profiles.sql`, `202611090002_clicko_staff_catalog_access.sql`, `202611090003_clicko_trade_inquiries.sql` และ `202611090004_clicko_storefront_settings.sql` ตามลำดับ
3. ตรวจ `site/supabase-config.js` ให้มี project URL และ publishable/anon key เท่านั้น ห้ามใช้ `service_role` key ใน browser หรือ commit ลง Git
4. ใน **Authentication → URL Configuration** ตั้ง Site URL เป็น URL หน้าเว็บจริง เช่น `https://digitallab-build.github.io/clickoShop/` และเพิ่ม redirect URL `https://digitallab-build.github.io/clickoShop/**` หรืออย่างน้อย URL `/account/`
5. หากเปิด Facebook Login ให้ตั้ง Facebook provider ใน Supabase และตั้ง OAuth redirect/callback ตาม URL ที่ Supabase แสดง
6. เพิ่มบัญชีพนักงานใน `clicko_staff_members` และบัญชีผู้ดูแลใน `clicko_admins` ตามสิทธิ์ที่ต้องใช้; หน้าตั้งค่าร้านสงวนให้ Admin

## เปิดใช้ฟอร์มรับซื้อ/เทิร์น

หน้าฟอร์มจะยังส่งไม่ได้จนกว่าจะกำหนด Turnstile Site Key ใน `site/assets/js/site-config.js` (ตัวแปร `turnstileSiteKey`) และตั้ง function secrets `CLICKO_SUPABASE_SERVICE_ROLE_KEY`, `CLICKO_TURNSTILE_SECRET_KEY`, `CLICKO_ALLOWED_ORIGINS` ใน Supabase ให้ตรงกับ origin เว็บ เช่น `https://digitallab-build.github.io` จากนั้น deploy Edge Function `submit-trade-in` ตาม `docs/TRADE_IN_INTAKE.md` ห้ามใส่ secret key ในไฟล์ `site/`

## สิ่งที่ผู้ดูแลแก้ได้จากหน้าเว็บ

- สินค้า ราคา รูป หมวดหมู่ และสถานะ: `admin.html` (ใช้ Supabase)
- เนื้อหาหัวเว็บ/ข้อความหน้าแรก/ช่องทาง Facebook/โทรศัพท์/จุดนัดรับและข้อความ Messenger: `site-settings.html` (Admin เท่านั้น)
- คำขอรับซื้อ/เทิร์น: `trade-in-admin/` (พนักงานหรือ Admin ตาม policy)

รูป banner ปัจจุบันอยู่ใน `site/assets/images/clicko-red-gaming-hero.png`; การเปลี่ยนภาพต้องเปลี่ยนไฟล์ใน repo แล้ว deploy ใหม่

## ข้อจำกัดที่ควรรู้

- GitHub Pages เป็น static hosting; ไม่มี PHP runtime, WordPress dashboard หรือ server-side secret storage
- การควบคุมสิทธิ์และข้อมูลจริงต้องบังคับด้วย Supabase Auth และ Row Level Security
- ฟอร์มรับซื้อ/เทิร์นพึ่ง Edge Function และ Turnstile; ถ้ายังไม่ตั้งค่าจะไม่รับข้อมูล
- Messenger ของผู้ขายยังเปิดเป็นลิงก์ให้ลูกค้าติดต่อเอง เว็บไซต์ไม่สามารถส่งข้อความอัตโนมัติเข้า Facebook ส่วนตัวได้
