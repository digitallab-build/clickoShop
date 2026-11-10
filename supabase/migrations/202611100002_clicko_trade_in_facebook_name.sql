-- Store the customer's Facebook display name explicitly on trade-in requests.
-- Keep existing values when upgrading databases that already have nicknames.

do $$
begin
  if exists (
    select 1 from information_schema.columns
    where table_schema = 'public'
      and table_name = 'clicko_trade_inquiries'
      and column_name = 'customer_nickname'
  ) and not exists (
    select 1 from information_schema.columns
    where table_schema = 'public'
      and table_name = 'clicko_trade_inquiries'
      and column_name = 'customer_facebook_name'
  ) then
    alter table public.clicko_trade_inquiries
      rename column customer_nickname to customer_facebook_name;
  end if;

  if exists (
    select 1 from pg_constraint
    where conrelid = 'public.clicko_trade_inquiries'::regclass
      and conname = 'clicko_trade_in_customer_nickname_length'
  ) and not exists (
    select 1 from pg_constraint
    where conrelid = 'public.clicko_trade_inquiries'::regclass
      and conname = 'clicko_trade_in_customer_facebook_name_length'
  ) then
    alter table public.clicko_trade_inquiries
      rename constraint clicko_trade_in_customer_nickname_length
      to clicko_trade_in_customer_facebook_name_length;
  end if;
end;
$$;
