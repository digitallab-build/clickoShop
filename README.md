# Clicko — Gaming Gear & PC Parts

แค็ตตาล็อกสินค้า Clicko สำหรับให้ลูกค้าค้นหาและเลือกอุปกรณ์คอมพิวเตอร์ ก่อนติดต่อซื้อกับผู้ขายผ่าน Facebook ส่วนตัว

## ขอบเขตช่องทาง

- **ติดต่อซื้อ:** Facebook ส่วนตัวของผู้ขาย — https://www.facebook.com/sippavit.gomarn
- **ติดตามแบรนด์:** เพจ Clicko.gg — https://www.facebook.com/Clicko.gg/
- ลิงก์เพจใช้แสดงแบรนด์และข่าวสาร ไม่ใช่ช่องทางซื้อขาย

## เทคโนโลยี

- WordPress 6.6 ขึ้นไป
- PHP 8.0 ขึ้นไป
- WooCommerce สำหรับสินค้า SKU ตัวเลือกสินค้า หมวด และคุณลักษณะ
- แนะนำ Managed WordPress Hosting ที่มี HTTPS, staging และ backup

หน้าร้านอยู่ที่ `wp-content/themes/clicko` ส่วนตั้งค่าช่องทางผู้ขายและรายการสินค้าที่สนใจอยู่ที่ `wp-content/plugins/clicko-tools` ผู้ขายเพิ่มสินค้า แก้รูปและราคา ปรับข้อความหน้าแรก เปลี่ยนสี และแก้ URL Facebook ได้ผ่าน WordPress Dashboard โดยไม่ต้องแก้ source code

## เอกสาร

- [แผนเว็บไซต์และรายละเอียดระบบ](docs/CLICKO_WEBSITE_PLAN.md)
- [คู่มือผู้ขายภาษาไทย](docs/SELLER_GUIDE_TH.md)

## ติดตั้งใน WordPress

1. ติดตั้ง WordPress และ WooCommerce ให้เสร็จ
2. คัดลอกโฟลเดอร์ `wp-content/themes/clicko` ไปที่ `wp-content/themes/` และ `wp-content/plugins/clicko-tools` ไปที่ `wp-content/plugins/` บนโฮสต์
3. เปิดใช้ธีม **Clicko** และปลั๊กอิน **Clicko Catalog Tools** จาก Dashboard
4. ใช้ WooCommerce ตั้งหน้าร้านเป็นหน้า Shop และตั้งค่าเงินเป็น THB
5. เปิด **การตั้งค่า → Clicko** เพื่อตรวจ URL ผู้ขายและเพจร้าน รวมทั้งใส่ Messenger URL หลังทดสอบแล้วเท่านั้น
6. เพิ่มสินค้าและรูปจากเมนู **สินค้า → เพิ่มสินค้า**
7. ไปที่ **หน้า → หน้าแรก** แล้วแก้เนื้อหาและรูปที่ต้องการแสดง
8. ตรวจหน้าร้านบนมือถือและ desktop ก่อนส่ง URL ให้ลูกค้า

ปลั๊กอินจะสร้างหน้าแรก รายการที่สนใจ วิธีซื้อ ติดต่อร้าน และประกันเมื่อเปิดใช้งานครั้งแรก หน้าแรกที่มีอยู่ก่อนแล้วจะไม่ถูกแทนที่

## การตรวจโค้ด

เครื่องมือพัฒนาใน workspace นี้ไม่มี PHP และไม่มี WordPress runtime จึงยังรัน PHP lint หรือ preview เว็บจริงจากเครื่องนี้ไม่ได้
