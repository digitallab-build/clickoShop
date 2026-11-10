import { createClient } from 'npm:@supabase/supabase-js@2';

function allowedOrigins(): string[] {
  return (Deno.env.get('CLICKO_ALLOWED_ORIGINS') || '')
    .split(',')
    .map((value) => value.trim().replace(/\/$/, ''))
    .filter(Boolean);
}

function corsHeaders(origin: string): HeadersInit {
  return {
    'Access-Control-Allow-Origin': origin,
    'Access-Control-Allow-Headers': 'apikey, content-type, x-client-info',
    'Access-Control-Allow-Methods': 'POST, OPTIONS',
    'Vary': 'Origin',
  };
}

function response(body: Record<string, unknown>, status: number, origin: string): Response {
  return Response.json(body, { status, headers: corsHeaders(origin) });
}

function supabaseServiceKey(): string {
  const configuredKey = Deno.env.get('CLICKO_SUPABASE_SERVICE_ROLE_KEY');
  if (configuredKey) return configuredKey;

  try {
    const secretKeys = JSON.parse(Deno.env.get('SUPABASE_SECRET_KEYS') || '{}');
    if (typeof secretKeys.default === 'string') return secretKeys.default;
  } catch {
    // Fall through to the legacy injected key if the new key map is unavailable.
  }

  return Deno.env.get('SUPABASE_SERVICE_ROLE_KEY') || '';
}

async function verifyTurnstile(token: string, secret: string, origin: string): Promise<boolean> {
  const body = new URLSearchParams({ secret, response: token });
  const verify = await fetch('https://challenges.cloudflare.com/turnstile/v0/siteverify', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body,
  });
  if (!verify.ok) return false;
  const result = await verify.json();
  const expectedHost = new URL(origin).hostname;
  return result.success === true && result.hostname === expectedHost;
}

Deno.serve(async (request) => {
  const origin = (request.headers.get('origin') || '').replace(/\/$/, '');
  const origins = allowedOrigins();
  if (!origin || !origins.includes(origin)) return new Response('Origin not allowed', { status: 403 });
  if (request.method === 'OPTIONS') return new Response('ok', { headers: corsHeaders(origin) });
  if (request.method !== 'POST') return response({ error: 'Method not allowed' }, 405, origin);

  const contentLength = Number(request.headers.get('content-length') || 0);
  if (contentLength > 16 * 1024) return response({ error: 'Request is too large' }, 413, origin);
  if (!(request.headers.get('content-type') || '').toLowerCase().includes('application/json')) {
    return response({ error: 'Expected a JSON request' }, 415, origin);
  }

  const secretKey = supabaseServiceKey();
  const turnstileSecret = Deno.env.get('CLICKO_TURNSTILE_SECRET_KEY');
  const supabaseUrl = Deno.env.get('SUPABASE_URL');
  if (!secretKey || !turnstileSecret || !supabaseUrl) {
    return response({ error: 'Trade-in form is not configured yet' }, 503, origin);
  }

  let form: Record<string, unknown>;
  try {
    form = await request.json();
  } catch {
    return response({ error: 'Could not read the submitted form' }, 400, origin);
  }

  // Silently accept bot honeypot submissions without storing them.
  if (String(form.company_website || '').trim()) return response({ ok: true }, 202, origin);

  const customerFirstName = String(form.customer_first_name || '').trim();
  const customerLastName = String(form.customer_last_name || '').trim();
  const customerFacebookName = String(form.customer_facebook_name || '').trim();
  const customerPhone = String(form.customer_phone || '').trim();
  const model = String(form.model || '').trim();
  const warranty = String(form.warranty || '').trim();
  const condition = String(form.condition || '').trim();
  const captchaToken = String(form.captcha_token || '').trim();
  const phoneDigits = customerPhone.replace(/\D/g, '');
  if (
    customerFirstName.length < 1 || customerFirstName.length > 80
    || customerLastName.length < 1 || customerLastName.length > 100
    || customerFacebookName.length < 1 || customerFacebookName.length > 60
    || phoneDigits.length < 9 || phoneDigits.length > 15
    || model.length < 2 || model.length > 160
    || condition.length < 2 || condition.length > 1000
    || warranty.length > 100
  ) {
    return response({ error: 'Please check the customer contact and product details' }, 400, origin);
  }
  if (!captchaToken || !(await verifyTurnstile(captchaToken, turnstileSecret, origin))) {
    return response({ error: 'Security check expired. Please verify again and resubmit' }, 400, origin);
  }

  const db = createClient(supabaseUrl, secretKey, { auth: { persistSession: false, autoRefreshToken: false } });
  const id = crypto.randomUUID();

  const inserted = await db.from('clicko_trade_inquiries').insert({
    id,
    customer_first_name: customerFirstName,
    customer_last_name: customerLastName,
    customer_facebook_name: customerFacebookName,
    customer_phone: customerPhone,
    model,
    warranty: warranty || null,
    condition_notes: condition,
    photo_paths: [],
  }).select('reference').single();

  if (inserted.error) {
    return response({ error: 'Could not save the request. Please try again' }, 500, origin);
  }

  return response({ ok: true, reference: inserted.data.reference }, 201, origin);
});
