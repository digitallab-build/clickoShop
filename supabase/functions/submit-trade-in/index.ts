import { createClient } from 'npm:@supabase/supabase-js@2';

const bucket = 'clicko-trade-in-photos';
const maxFileBytes = 5 * 1024 * 1024;
const maxFiles = 4;
const allowedTypes = new Map([
  ['image/jpeg', 'jpg'],
  ['image/png', 'png'],
  ['image/webp', 'webp'],
]);

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

function validImageSignature(type: string, bytes: Uint8Array): boolean {
  if (type === 'image/jpeg') return bytes[0] === 0xff && bytes[1] === 0xd8 && bytes[2] === 0xff;
  if (type === 'image/png') return bytes.slice(0, 8).join(',') === '137,80,78,71,13,10,26,10';
  if (type === 'image/webp') {
    return String.fromCharCode(...bytes.slice(0, 4)) === 'RIFF'
      && String.fromCharCode(...bytes.slice(8, 12)) === 'WEBP';
  }
  return false;
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
  if (contentLength > 22 * 1024 * 1024) return response({ error: 'Request is too large' }, 413, origin);
  if (!(request.headers.get('content-type') || '').toLowerCase().startsWith('multipart/form-data;')) {
    return response({ error: 'Expected a multipart form' }, 415, origin);
  }

  const secretKey = Deno.env.get('CLICKO_SUPABASE_SERVICE_ROLE_KEY');
  const turnstileSecret = Deno.env.get('CLICKO_TURNSTILE_SECRET_KEY');
  const supabaseUrl = Deno.env.get('SUPABASE_URL');
  if (!secretKey || !turnstileSecret || !supabaseUrl) {
    return response({ error: 'Trade-in form is not configured yet' }, 503, origin);
  }

  let form: FormData;
  try {
    form = await request.formData();
  } catch {
    return response({ error: 'Could not read the submitted form' }, 400, origin);
  }

  // Silently accept bot honeypot submissions without storing them.
  if (String(form.get('company_website') || '').trim()) return response({ ok: true }, 202, origin);

  const model = String(form.get('model') || '').trim();
  const warranty = String(form.get('warranty') || '').trim();
  const condition = String(form.get('condition') || '').trim();
  const captchaToken = String(form.get('cf-turnstile-response') || '').trim();
  if (model.length < 2 || model.length > 160 || condition.length < 2 || condition.length > 1000 || warranty.length > 100) {
    return response({ error: 'Please check the product details and try again' }, 400, origin);
  }
  if (!captchaToken || !(await verifyTurnstile(captchaToken, turnstileSecret, origin))) {
    return response({ error: 'Security check expired. Please verify again and resubmit' }, 400, origin);
  }

  const files = form.getAll('photos').filter((value): value is File => value instanceof File && value.size > 0);
  if (files.length < 1 || files.length > maxFiles) return response({ error: `Choose between 1 and ${maxFiles} photos` }, 400, origin);

  const uploads: { path: string; bytes: Uint8Array; mime: string }[] = [];
  let totalBytes = 0;
  for (const file of files) {
    const extension = allowedTypes.get(file.type);
    if (!extension || file.size > maxFileBytes) {
      return response({ error: 'Photos must be JPG, PNG, or WEBP, up to 5 MB each' }, 400, origin);
    }
    totalBytes += file.size;
    if (totalBytes > 20 * 1024 * 1024) return response({ error: 'Total photo size must be 20 MB or less' }, 413, origin);
    const bytes = new Uint8Array(await file.arrayBuffer());
    if (!validImageSignature(file.type, bytes)) return response({ error: 'One of the selected files is not a valid image' }, 400, origin);
    uploads.push({ path: `${crypto.randomUUID()}.${extension}`, bytes, mime: file.type });
  }

  const db = createClient(supabaseUrl, secretKey, { auth: { persistSession: false, autoRefreshToken: false } });
  const id = crypto.randomUUID();
  const storedPaths: string[] = [];
  for (const upload of uploads) {
    const path = `${id}/${upload.path}`;
    const result = await db.storage.from(bucket).upload(path, upload.bytes, {
      contentType: upload.mime,
      upsert: false,
    });
    if (result.error) {
      if (storedPaths.length) await db.storage.from(bucket).remove(storedPaths);
      return response({ error: 'Could not store the selected photos. Please try again' }, 500, origin);
    }
    storedPaths.push(path);
  }

  const inserted = await db.from('clicko_trade_inquiries').insert({
    id,
    model,
    warranty: warranty || null,
    condition_notes: condition,
    photo_paths: storedPaths,
  }).select('reference').single();

  if (inserted.error) {
    if (storedPaths.length) await db.storage.from(bucket).remove(storedPaths);
    return response({ error: 'Could not save the request. Please try again' }, 500, origin);
  }

  return response({ ok: true, reference: inserted.data.reference }, 201, origin);
});
