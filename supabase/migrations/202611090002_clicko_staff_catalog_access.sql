-- Grant catalog-only access to explicitly allow-listed Supabase Auth staff.

-- Staff accounts are Supabase Auth users explicitly allow-listed by an Admin.
-- They can maintain the catalog, while finance/order data remains Admin-only.
create table if not exists public.clicko_staff_members (
  user_id uuid primary key references auth.users (id) on delete cascade,
  created_at timestamptz not null default now()
);

alter table public.clicko_staff_members enable row level security;
revoke all on table public.clicko_staff_members from anon, authenticated;
grant select on table public.clicko_staff_members to authenticated;

create or replace function public.clicko_current_user_is_admin()
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
  select exists (
    select 1 from public.clicko_admins
    where user_id = (select auth.uid())
  );
$$;

create or replace function public.clicko_current_user_is_staff()
returns boolean
language sql
stable
security definer
set search_path = ''
as $$
  select exists (
    select 1 from public.clicko_staff_members
    where user_id = (select auth.uid())
  );
$$;

revoke all on function public.clicko_current_user_is_admin() from public, anon;
revoke all on function public.clicko_current_user_is_staff() from public, anon;
grant execute on function public.clicko_current_user_is_admin() to authenticated;
grant execute on function public.clicko_current_user_is_staff() to authenticated;

drop policy if exists "staff read own membership" on public.clicko_staff_members;
create policy "staff read own membership"
on public.clicko_staff_members
for select to authenticated
using (user_id = (select auth.uid()) or (select public.clicko_current_user_is_admin()));

-- Add staff access without widening access to orders or cost tables.
grant select, insert, update on table public.clicko_products to authenticated;
drop policy if exists "staff read clicko products" on public.clicko_products;
create policy "staff read clicko products"
on public.clicko_products
for select to authenticated
using ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin()));

drop policy if exists "staff insert clicko products" on public.clicko_products;
create policy "staff insert clicko products"
on public.clicko_products
for insert to authenticated
with check ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin()));

drop policy if exists "staff update clicko products" on public.clicko_products;
create policy "staff update clicko products"
on public.clicko_products
for update to authenticated
using ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin()))
with check ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin()));

grant select, insert, update, delete on table public.clicko_product_photos to authenticated;
drop policy if exists "staff manage product photos" on public.clicko_product_photos;
create policy "staff manage product photos"
on public.clicko_product_photos
for all to authenticated
using ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin()))
with check ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin()));

drop policy if exists "staff upload product images" on storage.objects;
create policy "staff upload product images"
on storage.objects
for insert to authenticated
with check (bucket_id = 'product-images' and ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin())));

drop policy if exists "staff update product images" on storage.objects;
create policy "staff update product images"
on storage.objects
for update to authenticated
using (bucket_id = 'product-images' and ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin())))
with check (bucket_id = 'product-images' and ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin())));

drop policy if exists "staff delete product images" on storage.objects;
create policy "staff delete product images"
on storage.objects
for delete to authenticated
using (bucket_id = 'product-images' and ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin())));

-- After creating the employee in Authentication > Users, allow-list their UUID:
-- insert into public.clicko_staff_members (user_id) values ('EMPLOYEE-AUTH-UUID');
