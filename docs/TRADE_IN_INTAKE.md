# แบบฟอร์มรับซื้อ / เทิร์น

## การทำงาน

1. ลูกค้ากรอกชื่อ นามสกุล ชื่อเล่น เบอร์โทร รุ่นสินค้า ประกัน และสภาพ/ตำหนิ
2. หน้าเว็บส่ง JSON ไปที่ Supabase Edge Function `submit-trade-in`; Cloudflare Turnstile ช่วยกรองบอตก่อนบันทึก
3. Edge Function ตรวจ Origin, Turnstile, ความยาวข้อมูล และเบอร์โทร แล้วบันทึกรายละเอียดใน `public.clicko_trade_inquiries`
4. เมื่อบันทึกสำเร็จ หน้าเว็บพาลูกค้าไป Messenger ของ Kew เพื่อคุยซื้อขาย/เทิร์นต่อ โดยข้อความและรูปไม่ได้ถูกส่งเข้าแชตอัตโนมัติ
5. พนักงาน/ผู้ดูแลเข้าสู่ `/trade-in-admin/` ด้วย Supabase Auth เพื่อดูข้อมูลลูกค้าและรายการสินค้า แล้วปรับสถานะได้

ฟอร์มเก็บชื่อ นามสกุล ชื่อเล่น และเบอร์โทรไว้เพื่อระบุและติดต่อลูกค้า ไม่เก็บเลขบัตรหรือ Facebook token และลูกค้าไม่ต้องสมัครสมาชิกก่อนส่ง คำขอเริ่มด้วยสถานะ `new`; รหัสอ้างอิงขึ้นต้นด้วย `CT-`. รูปเก่าที่เคยแนบไว้ยังคงแสดงในรายการเดิมได้ แต่ฟอร์มใหม่ไม่มีการอัปโหลดรูป

## ตั้งค่าก่อนเปิดใช้

1. รัน migrations `202611090001` ถึง `202611090005` และ `202611100001` ตามลำดับใน SQL Editor ของ Supabase project เดิม
2. ติดตั้ง Supabase CLI และเชื่อม repo กับ project เดิม จากนั้น deploy function:

   ```powershell
   supabase link --project-ref mvpxjkpnlopgitvvzsyc
   supabase functions deploy submit-trade-in --no-verify-jwt
   ```

3. ใน Supabase Dashboard → Edge Function Secrets ตั้งค่า:
   - `CLICKO_SUPABASE_SERVICE_ROLE_KEY` — secret/service-role key ของ Supabase; ใช้ฝั่ง function เท่านั้น ห้ามใส่ใน WordPress setting, browser หรือ Git
   - `CLICKO_TURNSTILE_SECRET_KEY` — Turnstile secret จาก Cloudflare
   - `CLICKO_ALLOWED_ORIGINS` — origin ของเว็บจริง เช่น `https://digitallab-build.github.io` (ไม่มี path `/clickoShop`); หลาย origin คั่นด้วย comma
4. สร้าง Cloudflare Turnstile widget ให้โดเมนเว็บจริง แล้วใส่ public Site Key ใน `site/assets/js/site-config.js` ที่ตัวแปร `turnstileSiteKey`; secret key อยู่ใน Supabase Function Secrets เท่านั้น
5. เพิ่มผู้ดูแล/พนักงานเข้า `clicko_admins` หรือ `clicko_staff_members` ตาม workflow เดิม และใช้หน้า `/trade-in-admin/` ทดสอบการดูข้อมูลลูกค้าและคำขอ
6. เติมนโยบายความเป็นส่วนตัวและกำหนดระยะเวลาเก็บ/ลบข้อมูลลูกค้าก่อนเปิดรับข้อมูลจริง

ฟอร์มจะไม่รับคำขอถ้ายังไม่ได้ตั้ง Site Key, Function secrets, allowed origins และ migrations ครบ

## การส่ง Facebook

ลิงก์ Messenger ที่ตั้งไว้คือ `https://m.me/sippavit.gomarn`; หลังบันทึกคำขอสำเร็จ หน้าเว็บจะเปิดลิงก์นี้ให้ลูกค้าไปเริ่มคุยกับ Kew เอง ระบบไม่ส่งข้อความหรือข้อมูลจาก Supabase เข้า Messenger อัตโนมัติ ลูกค้าคุยรายละเอียดต่อใน Messenger ได้ ขณะที่ร้านดูข้อมูลที่บันทึกไว้ใน `/trade-in-admin/`.
