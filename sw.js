// Service Worker - Pass-through for max compatibility
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    // Return early if it's not a GET request or not same-origin
    if (event.request.method !== 'GET') return;
    
    // Explicitly do nothing so it falls back to network
    return;
});
