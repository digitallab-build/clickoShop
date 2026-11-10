-- Add private customer profile data to the existing Clicko Supabase project.
-- Run this additive migration in the Supabase SQL Editor; do not rerun the
-- legacy supabase-setup.sql bootstrap against the existing production schema.

create table if not exists public.clicko_customer_profiles (
  user_id uuid primary key references auth.users (id) on delete cascade,
  first_name text not null check (char_length(btrim(first_name)) between 1 and 100),
  last_name text not null check (char_length(btrim(last_name)) between 1 and 100),
  birth_date date,
  current_address text check (current_address is null or char_length(current_address) <= 1200),
  phone text check (phone is null or char_length(phone) <= 40),
  contact_email text check (
    contact_email is null or (
      char_length(contact_email) <= 254
      and contact_email ~* '^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$'
    )
  ),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create or replace function public.clicko_touch_customer_profile_updated_at()
returns trigger
language plpgsql
set search_path = ''
as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

drop trigger if exists clicko_customer_profiles_touch_updated_at
  on public.clicko_customer_profiles;
create trigger clicko_customer_profiles_touch_updated_at
before update on public.clicko_customer_profiles
for each row execute function public.clicko_touch_customer_profile_updated_at();

alter table public.clicko_customer_profiles enable row level security;
revoke all on table public.clicko_customer_profiles from anon, authenticated;
grant select, insert, update on table public.clicko_customer_profiles to authenticated;

drop policy if exists "customers read own profile" on public.clicko_customer_profiles;
create policy "customers read own profile"
on public.clicko_customer_profiles
for select to authenticated
using (user_id = (select auth.uid()));

drop policy if exists "customers create own profile" on public.clicko_customer_profiles;
create policy "customers create own profile"
on public.clicko_customer_profiles
for insert to authenticated
with check (user_id = (select auth.uid()));

drop policy if exists "customers update own profile" on public.clicko_customer_profiles;
create policy "customers update own profile"
on public.clicko_customer_profiles
for update to authenticated
using (user_id = (select auth.uid()))
with check (user_id = (select auth.uid()));
