# แบบฐานข้อมูลสมาชิก Clicko บน Supabase

เอกสารนี้กำหนดให้ Supabase เป็นแหล่งข้อมูลหลักของสินค้าและสมาชิก โดยบัญชีลูกค้าใช้ Supabase Auth และเก็บโปรไฟล์ใน Postgres แยกต่างหาก เชื่อมด้วย UUID จาก `auth.users` ไม่เก็บรหัสผ่านหรือ Facebook access token ในตารางโปรไฟล์

## สิ่งที่พบใน repo ปัจจุบัน

- `clicko-store.github.io` มี Supabase project และหน้าเว็บเรียกใช้ `clicko_products`, `clicko_orders`, `clicko_product_costs`, `clicko_product_photos` และ `clicko_admins`
- `supabase-setup.sql` เป็นไฟล์ bootstrap รุ่นเก่าและไม่ครบกับ schema ที่หน้าเว็บปัจจุบันใช้ จึงไม่ควรรันซ้ำทับฐานข้อมูลปัจจุบัน
- หน้าแรก Clicko ใน WordPress อ่านสินค้าใน Supabase; เครื่องมือจัดการสินค้าปัจจุบันอยู่ที่หน้า Admin ของ repo ล่าสุด และใช้ `clicko_products` / `clicko_product_photos` / Storage bucket `product-images`

## โครงสร้างที่แนะนำ

| ข้อมูล | ที่เก็บ | ระดับการบังคับกรอก |
| --- | --- | --- |
| อีเมลและวิธียืนยันตัวตน | Supabase Auth (`auth.users`) | จำเป็นสำหรับ email login; Facebook อาจไม่ส่งอีเมลถ้าบัญชีหรือสิทธิ์แอปไม่พร้อม |
| ชื่อ / นามสกุล | `public.clicko_customer_profiles` | จำเป็น |
| วันเกิด | `public.clicko_customer_profiles.birth_date` | ไม่บังคับ; เก็บเมื่อมีเหตุผลใช้งานชัดเจน |
| เบอร์โทรศัพท์ | `public.clicko_customer_profiles.phone` | ไม่บังคับ; เก็บเมื่อจำเป็นต่อการติดต่อ |
| ที่อยู่ปัจจุบัน | `public.clicko_customer_profiles.current_address` | ไม่บังคับ; สำหรับร้านที่ตกลงซื้อขายทาง Facebook ควรเก็บเมื่อจำเป็นต่อการจัดส่งเท่านั้น |
| เลขบัตรประชาชน | ไม่ใส่ในแบบเริ่มต้น | แนะนำไม่เก็บสำหรับการสมัครสมาชิกหน้าร้าน |

ตารางโปรไฟล์ใช้ `user_id uuid primary key references auth.users(id) on delete cascade` และ `created_at`, `updated_at` โดยไม่ทำสำเนาอีเมลจาก Auth ลงใน public table เว้นแต่จะมีเหตุผลสำหรับการรายงานและออกแบบ trigger sync เพิ่มเติม

## สิทธิ์และการเข้าถึง

- เปิด Row Level Security (RLS) และ grant เฉพาะ `authenticated`
- สมาชิก select/insert/update ได้เฉพาะแถวที่ `user_id = auth.uid()`
- `anon` ไม่มีสิทธิ์อ่านหรือเขียนข้อมูลโปรไฟล์
- Admin ที่ต้องดูแลบัญชีใช้เครื่องมือ server-side ที่ตรวจสิทธิ์แอดมิน ไม่ใส่ service-role key ใน JavaScript หน้าบ้านหรือ Git
- พนักงานเข้าสู่ระบบด้วย Supabase Auth และต้องถูกเพิ่มใน `clicko_staff_members`; สิทธิ์พนักงานจำกัดอยู่ที่สินค้า รูปสินค้า และคำขอรับซื้อ/เทิร์น ไม่ให้เห็นข้อมูลสมาชิก ต้นทุน หรือคำสั่งซื้อ

## ไฟล์สำหรับตั้งฐานข้อมูลและนำเข้าข้อมูล

แยกไฟล์เป็น 2 ประเภทเพื่อให้ย้อนตรวจได้:

1. [Migration 001](../supabase/migrations/202611090001_clicko_customer_profiles.sql) สร้างตารางโปรไฟล์และ RLS; [Migration 002](../supabase/migrations/202611090002_clicko_staff_catalog_access.sql) เพิ่มสิทธิ์พนักงานกับแค็ตตาล็อก; [Migration 003](../supabase/migrations/202611090003_clicko_trade_inquiries.sql) สร้างคำขอรับซื้อ/เทิร์นและ private photo bucket รันตามลำดับใน SQL Editor หลังตรวจ project และ schema เป้าหมาย
2. ไม่ต้อง import สินค้าเดิมซ้ำ เพราะข้อมูลอยู่ใน `clicko_products` แล้ว; เพิ่ม/แก้สินค้าใหม่ทีละรายการในหน้าจัดการ `clicko-store.github.io/admin.html` ปัจจุบันยังไม่มีตัวนำเข้า CSV/Excel สำหรับสินค้า ส่วนไฟล์ SQL ใน repo ใช้สร้างตาราง/นโยบาย ไม่ใช่ไฟล์นำเข้าสินค้า การ import ตรงใน Table Editor เป็นงานทางเทคนิคและไม่อัปโหลดรูปหรือทำขั้นตอนตรวจสอบของ Admin
3. โปรไฟล์สมาชิกสร้างจากหน้า `/account/` หลังยืนยันตัวตน ไม่ควร bulk import CSV เพราะต้องผูกแต่ละแถวกับ UUID จริงจาก Supabase Auth และข้อมูลเป็นข้อมูลส่วนบุคคล อย่า commit CSV สมาชิกลง Git

ไฟล์ migrations สร้างแล้วแต่ยังไม่ได้รันกับฐานข้อมูลจริง; Edge Function ต้อง deploy และตั้ง secrets ตาม [คู่มือแบบฟอร์มรับซื้อ/เทิร์น](TRADE_IN_INTAKE.md)

### เพิ่มพนักงานเข้าระบบ

1. ผู้ดูแลเชิญพนักงานจาก Supabase **Authentication → Users** โดยใช้อีเมลของพนักงาน
2. คัดลอก UUID ของผู้ใช้ แล้วเพิ่มสิทธิ์ผ่าน SQL Editor:

```sql
insert into public.clicko_staff_members (user_id)
values ('ใส่-auth-user-uuid-ตรงนี้');
```

3. พนักงานเข้าสู่ระบบที่หน้า Admin ของ repo ล่าสุดด้วยอีเมล/รหัสผ่านเดียวกัน สิทธิ์นี้ให้แก้สินค้าและรูปเท่านั้น; การถอดสิทธิ์ทำได้ด้วย `delete from public.clicko_staff_members where user_id = 'AUTH-USER-UUID';`

## ข้อมูลเลขบัตรประชาชน

เลขบัตรไม่จำเป็นต่อการเปิดบัญชีหรือสอบถามซื้อสินค้าผ่าน Facebook จึงไม่ควรรวมในฟอร์มสมัครปกติ พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล มาตรา 22 กำหนดให้เก็บข้อมูลเท่าที่จำเป็นต่อวัตถุประสงค์ที่ชอบด้วยกฎหมาย หากธุรกิจมีเหตุจำเป็นเฉพาะ ควรกำหนดวัตถุประสงค์และระยะเวลาเก็บก่อน แล้วแยกเก็บแบบเข้ารหัสฝั่ง server พร้อมจำกัดผู้มีสิทธิ์และไม่ส่งค่าจริงกลับไปยัง browser หลังบันทึก

## Login ด้วย Facebook

ใช้ Supabase Auth OAuth provider `facebook`; ต้องตั้ง Facebook App ID/Secret ใน Supabase Dashboard และตั้ง callback URL/redirect allow list ให้ตรงกับโดเมนจริง หาก Facebook ไม่คืนอีเมล ลูกค้าต้องกรอกและยืนยันอีเมลแยก
