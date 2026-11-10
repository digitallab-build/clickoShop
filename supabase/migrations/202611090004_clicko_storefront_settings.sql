-- Public storefront content with Admin-only editing for the GitHub Pages build.
-- Depends on clicko_current_user_is_admin() from migration 002.

create table if not exists public.clicko_storefront_settings (
  id text primary key default 'storefront' check (id = 'storefront'),
  announcement text not null default 'GAMING GEAR · PC PARTS · BUY / SELL / TRADE' check (char_length(announcement) <= 180),
  tagline text not null default 'GAMING GEAR & PC PARTS' check (char_length(tagline) <= 100),
  hero_eyebrow text not null default 'CLICKO / GAMING GEAR & PC PARTS' check (char_length(hero_eyebrow) <= 120),
  hero_title text not null default 'ซื้อขายคอมพิวเตอร์' check (char_length(hero_title) <= 100),
  hero_highlight text not null default 'และอุปกรณ์เกมมิง' check (char_length(hero_highlight) <= 100),
  hero_suffix text not null default 'ครบ จบ ในที่เดียว' check (char_length(hero_suffix) <= 100),
  hero_description text not null default 'เลือกชมสินค้า เช็กสภาพและรายละเอียด แล้วพูดคุยกับผู้ขายโดยตรง' check (char_length(hero_description) <= 500),
  pickup_location text not null default 'ซอยรัชดา 36' check (char_length(pickup_location) <= 180),
  contact_phone text not null default '095-975-9965' check (char_length(contact_phone) <= 40),
  seller_profile_url text not null default 'https://www.facebook.com/sippavit.gomarn/' check (char_length(seller_profile_url) <= 500),
  seller_messenger_url text not null default '' check (char_length(seller_messenger_url) <= 500),
  brand_page_url text not null default 'https://www.facebook.com/Clicko.gg/' check (char_length(brand_page_url) <= 500),
  message_intro text not null default 'สวัสดีครับ สนใจสินค้าจากเว็บไซต์ Clicko ครับ' check (char_length(message_intro) <= 500),
  message_footer text not null default 'รบกวนยืนยันสินค้าคงเหลือ ราคาปัจจุบัน และค่าจัดส่งด้วยครับ' check (char_length(message_footer) <= 500),
  updated_at timestamptz not null default now()
);

insert into public.clicko_storefront_settings (id)
values ('storefront')
on conflict (id) do nothing;

alter table public.clicko_storefront_settings enable row level security;
revoke all on table public.clicko_storefront_settings from anon, authenticated;
grant select on table public.clicko_storefront_settings to anon, authenticated;
grant update on table public.clicko_storefront_settings to authenticated;

drop policy if exists "public read storefront settings" on public.clicko_storefront_settings;
create policy "public read storefront settings"
on public.clicko_storefront_settings
for select to anon, authenticated
using (id = 'storefront');

drop policy if exists "admins update storefront settings" on public.clicko_storefront_settings;
create policy "admins update storefront settings"
on public.clicko_storefront_settings
for update to authenticated
using (id = 'storefront' and (select public.clicko_current_user_is_admin()))
with check (id = 'storefront' and (select public.clicko_current_user_is_admin()));
