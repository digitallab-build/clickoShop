-- The public Edge Function writes trade-in requests with the service_role.
-- Keep anonymous and authenticated table access revoked; grant only the
-- operations required for the server-side insert and returned reference.

grant insert on table public.clicko_trade_inquiries to service_role;
grant select (reference) on table public.clicko_trade_inquiries to service_role;
