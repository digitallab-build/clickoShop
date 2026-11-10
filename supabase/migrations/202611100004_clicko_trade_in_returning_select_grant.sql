-- PostgREST requires table-level SELECT for INSERT ... RETURNING, even when
-- the application requests only the reference column.
grant select on table public.clicko_trade_inquiries to service_role;
