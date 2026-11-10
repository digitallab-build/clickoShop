# แบบฟอร์มรับซื้อ / เทิร์น

## การทำงาน

1. ลูกค้าส่งรุ่นสินค้า ประกัน สภาพ และรูปได้สูงสุด 4 รูป (JPG/PNG/WEBP ไม่เกิน 5 MB ต่อรูป)
2. หน้าเว็บส่ง multipart request ไปที่ Supabase Edge Function `submit-trade-in`; Cloudflare Turnstile ช่วยกรองบอตก่อนบันทึก
3. Edge Function ตรวจ Origin, Turnstile, ความยาวข้อมูล, ชนิดไฟล์ และ signature ของรูป แล้วบันทึกข้อมูลใน `public.clicko_trade_inquiries` และรูปใน private bucket `clicko-trade-in-photos`
4. พนักงาน/ผู้ดูแลเข้าสู่ `/trade-in-admin/` ด้วย Supabase Auth เพื่อดูรายการ เปิดรูปด้วย signed URL อายุ 10 นาที และปรับสถานะได้

ฟอร์มไม่เก็บชื่อ เบอร์โทร เลขบัตร หรือ Facebook token และลูกค้าไม่ต้องสมัครสมาชิกก่อนส่ง คำขอเริ่มด้วยสถานะ `new`; รหัสอ้างอิงขึ้นต้นด้วย `CT-`.

## ตั้งค่าก่อนเปิดใช้

1. รัน migrations `202611090001`, `202611090002`, `202611090003` และ `202611090004` ตามลำดับใน SQL Editor ของ Supabase project เดิม
2. ติดตั้ง Supabase CLI และเชื่อม repo กับ project เดิม จากนั้น deploy function:

   ```powershell
   supabase link --project-ref mvpxjkpnlopgitvvzsyc
   supabase functions deploy submit-trade-in --no-verify-jwt
   ```

3. ใน Supabase Dashboard → Edge Function Secrets ตั้งค่า:
   - `CLICKO_SUPABASE_SERVICE_ROLE_KEY` — secret/service-role key ของ Supabase; ใช้ฝั่ง function เท่านั้น ห้ามใส่ใน WordPress setting, browser หรือ Git
   - `CLICKO_TURNSTILE_SECRET_KEY` — Turnstile secret จาก Cloudflare
   - `CLICKO_ALLOWED_ORIGINS` — origin ของเว็บจริง เช่น `https://tonzkittipon.github.io` (ไม่มี path `/clickoShop`); หลาย origin คั่นด้วย comma
4. สร้าง Cloudflare Turnstile widget ให้โดเมนเว็บจริง แล้วใส่ public Site Key ใน `site/assets/js/site-config.js` ที่ตัวแปร `turnstileSiteKey`; secret key อยู่ใน Supabase Function Secrets เท่านั้น
5. เพิ่มผู้ดูแล/พนักงานเข้า `clicko_admins` หรือ `clicko_staff_members` ตาม workflow เดิม และใช้หน้า `/trade-in-admin/` ทดสอบการเปิดรายการกับรูป
6. เติมนโยบายความเป็นส่วนตัวและกำหนดระยะเวลาเก็บ/ลบคำขอกับรูปก่อนเปิดรับข้อมูลจริง

ฟอร์มจะไม่รับคำขอถ้ายังไม่ได้ตั้ง Site Key, Function secrets, allowed origins และ migrations ครบ

## การส่ง Facebook

การส่งข้อมูลเข้า Inbox ส่วนตัวของ Kew Sippavit โดยอัตโนมัติทำผ่าน Messenger API ไม่ได้ API ของ Messenger ใช้ Page access token ส่งไปยังผู้ใช้ที่เคยเริ่มคุยกับเพจ โดยระบุ Page-scoped ID ของผู้ใช้; ไม่สามารถกำหนดบัญชีส่วนตัวของเจ้าของเป็นผู้รับข้อความจากเว็บได้ ([Meta Messenger Platform API](https://www.postman.com/meta/messenger-platform-api/documentation/iyp204x/messenger-platform-api?entity=request-22794852-87cd5a4f-57a9-4345-adfc-8794954779da)).

ดังนั้นคำขอและรูปจะอยู่ในแดชบอร์ดส่วนตัวของร้าน แต่ระบบนี้ยังไม่ส่ง Facebook notification ให้ Kew โดยอัตโนมัติ หากต้องการแจ้งเตือนทันที ต้องเลือกช่องทางที่รองรับ เช่น อีเมลร้าน หรือระบบแจ้งเตือนที่ร้านใช้อยู่

## การควบคุมไฟล์

Bucket เป็น private; ลูกค้าหรือผู้ใช้ทั่วไปไม่มีสิทธิ์อ่านรายการหรือรูป มีเพียง Edge Function ที่ใช้ secret ฝั่ง server สำหรับบันทึก และผู้ดูแล/พนักงานที่ Supabase อนุมัติจึงอ่านรายการและสร้าง signed URL ชั่วคราวเพื่อดูรูปได้ แนวทาง private bucket และ signed URL อธิบายใน [Supabase Storage docs](https://supabase.com/docs/guides/storage/buckets/fundamentals).
