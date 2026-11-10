-- Add the Facebook trading group URL to Admin-editable public storefront settings.
alter table public.clicko_storefront_settings
  add column if not exists sales_group_url text not null
  default 'https://www.facebook.com/groups/3325289464276829'
  check (char_length(sales_group_url) <= 500);

update public.clicko_storefront_settings
set sales_group_url = 'https://www.facebook.com/groups/3325289464276829'
where id = 'storefront' and (sales_group_url is null or sales_group_url = '');
