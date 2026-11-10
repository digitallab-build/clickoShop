-- Customer contact details for buyback / trade-in inquiries.
-- Nullable columns keep existing inquiries readable; the public submission
-- function requires these values for all new requests.

alter table public.clicko_trade_inquiries
  add column if not exists customer_first_name text,
  add column if not exists customer_last_name text,
  add column if not exists customer_nickname text,
  add column if not exists customer_phone text;

do $$
begin
  if not exists (select 1 from pg_constraint where conname = 'clicko_trade_in_customer_first_name_length') then
    alter table public.clicko_trade_inquiries add constraint clicko_trade_in_customer_first_name_length
      check (customer_first_name is null or char_length(btrim(customer_first_name)) between 1 and 80) not valid;
  end if;
  if not exists (select 1 from pg_constraint where conname = 'clicko_trade_in_customer_last_name_length') then
    alter table public.clicko_trade_inquiries add constraint clicko_trade_in_customer_last_name_length
      check (customer_last_name is null or char_length(btrim(customer_last_name)) between 1 and 100) not valid;
  end if;
  if not exists (select 1 from pg_constraint where conname = 'clicko_trade_in_customer_nickname_length') then
    alter table public.clicko_trade_inquiries add constraint clicko_trade_in_customer_nickname_length
      check (customer_nickname is null or char_length(btrim(customer_nickname)) between 1 and 60) not valid;
  end if;
  if not exists (select 1 from pg_constraint where conname = 'clicko_trade_in_customer_phone_length') then
    alter table public.clicko_trade_inquiries add constraint clicko_trade_in_customer_phone_length
      check (customer_phone is null or char_length(btrim(customer_phone)) between 9 and 20) not valid;
  end if;
end;
$$;
