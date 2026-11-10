-- Private buyback / trade-in leads and photos.
-- Requires migrations 001 and 002, which define the profile and staff helpers.

create table if not exists public.clicko_trade_inquiries (
  id uuid primary key default gen_random_uuid(),
  reference text not null unique default (
    'CT-' || upper(substr(replace(gen_random_uuid()::text, '-', ''), 1, 10))
  ),
  model text not null check (char_length(btrim(model)) between 2 and 160),
  warranty text check (warranty is null or char_length(warranty) <= 100),
  condition_notes text not null check (char_length(btrim(condition_notes)) between 2 and 1000),
  photo_paths text[] not null default array[]::text[],
  status text not null default 'new'
    check (status in ('new', 'contacted', 'evaluated', 'closed', 'spam')),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index if not exists clicko_trade_inquiries_status_created_idx
  on public.clicko_trade_inquiries (status, created_at desc);

create or replace function public.clicko_touch_trade_inquiry_updated_at()
returns trigger
language plpgsql
set search_path = ''
as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

drop trigger if exists clicko_trade_inquiries_touch_updated_at
  on public.clicko_trade_inquiries;
create trigger clicko_trade_inquiries_touch_updated_at
before update on public.clicko_trade_inquiries
for each row execute function public.clicko_touch_trade_inquiry_updated_at();

alter table public.clicko_trade_inquiries enable row level security;
revoke all on table public.clicko_trade_inquiries from anon, authenticated;
grant select on table public.clicko_trade_inquiries to authenticated;
grant update (status) on table public.clicko_trade_inquiries to authenticated;

drop policy if exists "staff read trade in inquiries" on public.clicko_trade_inquiries;
create policy "staff read trade in inquiries"
on public.clicko_trade_inquiries
for select to authenticated
using ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin()));

drop policy if exists "staff update trade in inquiries" on public.clicko_trade_inquiries;
create policy "staff update trade in inquiries"
on public.clicko_trade_inquiries
for update to authenticated
using ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin()))
with check ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin()));

insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values ('clicko-trade-in-photos', 'clicko-trade-in-photos', false, 5242880,
  array['image/jpeg', 'image/png', 'image/webp'])
on conflict (id) do update
set public = false,
    file_size_limit = 5242880,
    allowed_mime_types = array['image/jpeg', 'image/png', 'image/webp'];

drop policy if exists "staff read trade in photos" on storage.objects;
create policy "staff read trade in photos"
on storage.objects
for select to authenticated
using (
  bucket_id = 'clicko-trade-in-photos'
  and ((select public.clicko_current_user_is_staff()) or (select public.clicko_current_user_is_admin()))
);

-- Public uploads are deliberately disallowed. Only the submit-trade-in Edge Function
-- may write to this private bucket with its server-side Supabase secret.
