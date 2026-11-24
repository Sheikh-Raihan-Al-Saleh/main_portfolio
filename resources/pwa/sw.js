/*
| The admin panel's service worker.
|
| Served by PwaController at /sw.js with VERSION set to a hash of the current
| Vite build (PwaService::version), so every deploy changes this file and the
| browser installs the new worker on its own. The new worker activates at
| once and clears the old asset cache; the page then reloads on its next
| navigation (resources/js/composables/usePwa.ts).
|
| What it caches: only the hashed, immutable files under /build/assets. Admin
| pages are always fetched from the network (they hold private, changing
| data); when the network is down a small offline page is shown instead.
*/

const VERSION = '__VERSION__';
const ASSET_CACHE = `admin-assets-${VERSION}`;

const OFFLINE_HTML = `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Offline · Admin</title>
<style>
  :root { color-scheme: light dark; --bg:#f6f7f9; --card:#fff; --line:#e3e6ea; --text:#1b1f24; --muted:#59626e; --accent:#15803d; --on:#fff; }
  @media (prefers-color-scheme: dark) { :root { --bg:#0b0d10; --card:#12151a; --line:#1f242c; --text:#e6e8eb; --muted:#8b949e; --accent:#4ade80; --on:#0b0d10; } }
  * { box-sizing: border-box; }
  body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: var(--bg); color: var(--text); font: 14px/1.5 ui-monospace, 'JetBrains Mono', Consolas, monospace; }
  main { width: 100%; max-width: 460px; border: 1px solid var(--line); border-radius: 12px; background: var(--card); padding: 24px; }
  h1 { margin: 0 0 12px; font-size: 18px; }
  p { margin: 0 0 6px; color: var(--muted); }
  .a { color: var(--accent); }
  button { margin-top: 18px; min-height: 44px; padding: 0 18px; border: 0; border-radius: 8px; background: var(--accent); color: var(--on); font: inherit; font-weight: 700; cursor: pointer; }
</style>
</head>
<body>
<main>
  <h1><span class="a">$</span> ping admin</h1>
  <p>// no connection — you're offline.</p>
  <p>// your changes weren't sent. Reconnect, then try again.</p>
  <button type="button" onclick="location.reload()">↻ retry</button>
</main>
</body>
</html>`;

/** Admin pages: the panel, account settings and the login screens. */
function isAppPage(pathname) {
    return ['/admin', '/settings', '/login', '/two-factor'].some(
        (prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`),
    );
}

self.addEventListener('install', () => {
    // Take over straight away: cached assets are content-hashed, so the old
    // and new versions never clash.
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        (async () => {
            const keys = await caches.keys();

            await Promise.all(
                keys
                    .filter(
                        (key) =>
                            key.startsWith('admin-assets-') &&
                            key !== ASSET_CACHE,
                    )
                    .map((key) => caches.delete(key)),
            );

            await self.clients.claim();
        })(),
    );
});

self.addEventListener('message', (event) => {
    if (event.data === 'version') {
        event.source?.postMessage({ type: 'version', version: VERSION });
    }
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== self.location.origin) {
        return;
    }

    if (url.pathname.startsWith('/build/assets/')) {
        event.respondWith(cacheFirst(request));

        return;
    }

    if (request.mode === 'navigate' && isAppPage(url.pathname)) {
        event.respondWith(fetch(request).catch(offlinePage));
    }
});

/** Hashed build files never change, so a cached copy is always right. */
async function cacheFirst(request) {
    const cache = await caches.open(ASSET_CACHE);
    const cached = await cache.match(request);

    if (cached) {
        return cached;
    }

    const response = await fetch(request);

    if (response.ok) {
        cache.put(request, response.clone());
    }

    return response;
}

function offlinePage() {
    return new Response(OFFLINE_HTML, {
        status: 503,
        headers: { 'Content-Type': 'text/html; charset=utf-8' },
    });
}
