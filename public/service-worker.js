// Service Worker for Tappo Market IMS PWA
const CACHE_NAME = 'tappo-ims-v1.0.0';
const API_CACHE_NAME = 'tappo-ims-api-v1.0.0';

// Assets to cache immediately on install
const STATIC_ASSETS = [
  '/ims-dashboard/templates/dashboard.php',
  '/ims-dashboard/pos/pos.php',
  '/ims-dashboard/templates/products.php',
  '/ims-dashboard/templates/orders.php',
  '/ims-dashboard/templates/customers.php',
  '/ims-dashboard/css/style.css',
  '/ims-dashboard/css/enhancements.css',
  '/ims-dashboard/pos/css/pos-style.css',
  '/ims-dashboard/pos/css/mobile-responsive.css',
  '/ims-dashboard/js/dashboard.js',
  '/ims-dashboard/js/dashboard-charts.js',
  '/ims-dashboard/js/notification-manager.js',
  '/ims-dashboard/js/theme-toggle.js',
  '/ims-dashboard/js/keyboard-shortcuts.js',
  '/ims-dashboard/js/autocomplete.js',
  '/ims-dashboard/js/products.js',
  '/ims-dashboard/images/icon-192x192.png',
  '/ims-dashboard/images/icon-512x512.png',
  '/favicon.ico'
];

// API endpoints to cache
const API_ENDPOINTS = [
  '/api/products',
  '/api/orders',
  '/api/customers',
  '/api/analytics/sales-trends',
  '/api/analytics/top-products',
  '/api/reports/sales'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[Service Worker] Caching static assets');
        return cache.addAll(STATIC_ASSETS.map(url => new Request(url, { cache: 'no-cache' })));
      })
      .catch((error) => {
        console.error('[Service Worker] Cache failed:', error);
      })
  );
  
  // Force the waiting service worker to become the active service worker
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...');
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME && cacheName !== API_CACHE_NAME) {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  
  // Take control of all pages immediately
  return self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip chrome-extension and other non-http(s) requests
  if (!request.url.startsWith('http')) {
    return;
  }
  
  // Handle API requests separately
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(apiCacheStrategy(request));
    return;
  }
  
  // Handle static assets
  event.respondWith(staticCacheStrategy(request));
});

// Static assets: Cache-first strategy
async function staticCacheStrategy(request) {
  try {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      console.log('[Service Worker] Serving from cache:', request.url);
      return cachedResponse;
    }
    
    console.log('[Service Worker] Fetching from network:', request.url);
    const networkResponse = await fetch(request);
    
    // Cache successful responses
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.error('[Service Worker] Fetch failed:', error);
    
    // Return offline page if available
    const offlinePage = await caches.match('/ims-dashboard/offline.html');
    if (offlinePage) {
      return offlinePage;
    }
    
    // Return generic offline response
    return new Response('Offline - Please check your internet connection', {
      status: 503,
      statusText: 'Service Unavailable',
      headers: new Headers({
        'Content-Type': 'text/plain'
      })
    });
  }
}

// API requests: Network-first, fallback to cache
async function apiCacheStrategy(request) {
  const cache = await caches.open(API_CACHE_NAME);
  
  try {
    // Try network first
    const networkResponse = await fetch(request);
    
    // Cache successful API responses (with short TTL)
    if (networkResponse && networkResponse.status === 200) {
      const responseClone = networkResponse.clone();
      cache.put(request, responseClone);
    }
    
    return networkResponse;
  } catch (error) {
    console.warn('[Service Worker] Network failed, trying cache:', request.url);
    
    // Fallback to cache
    const cachedResponse = await cache.match(request);
    if (cachedResponse) {
      console.log('[Service Worker] Serving API from cache:', request.url);
      return cachedResponse;
    }
    
    // Return error response
    return new Response(JSON.stringify({ 
      error: 'Offline', 
      message: 'No internet connection and no cached data available' 
    }), {
      status: 503,
      statusText: 'Service Unavailable',
      headers: new Headers({
        'Content-Type': 'application/json'
      })
    });
  }
}

// Background sync for offline orders
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-orders') {
    event.waitUntil(syncOrders());
  }
});

async function syncOrders() {
  console.log('[Service Worker] Syncing offline orders...');
  
  try {
    // Get pending orders from IndexedDB
    const db = await openDB();
    const tx = db.transaction('offline-orders', 'readonly');
    const store = tx.objectStore('offline-orders');
    const orders = await store.getAll();
    
    // Send each order to server
    for (const order of orders) {
      try {
        const response = await fetch('/api/orders', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${order.token}`
          },
          body: JSON.stringify(order.data)
        });
        
        if (response.ok) {
          // Remove from offline queue
          const deleteTx = db.transaction('offline-orders', 'readwrite');
          const deleteStore = deleteTx.objectStore('offline-orders');
          await deleteStore.delete(order.id);
          
          console.log('[Service Worker] Order synced:', order.id);
        }
      } catch (error) {
        console.error('[Service Worker] Failed to sync order:', order.id, error);
      }
    }
  } catch (error) {
    console.error('[Service Worker] Sync failed:', error);
  }
}

// Push notifications
self.addEventListener('push', (event) => {
  console.log('[Service Worker] Push notification received');
  
  const data = event.data ? event.data.json() : {};
  const title = data.title || 'Tappo Market';
  const options = {
    body: data.body || 'You have a new notification',
    icon: '/ims-dashboard/images/icon-192x192.png',
    badge: '/ims-dashboard/images/badge-72x72.png',
    vibrate: [200, 100, 200],
    data: data,
    actions: [
      { action: 'view', title: 'View' },
      { action: 'dismiss', title: 'Dismiss' }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
  console.log('[Service Worker] Notification clicked:', event.action);
  
  event.notification.close();
  
  if (event.action === 'view') {
    const url = event.notification.data.url || '/ims-dashboard/templates/dashboard.php';
    event.waitUntil(
      clients.openWindow(url)
    );
  }
});

// Helper: Open IndexedDB
function openDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('tappo-ims-db', 1);
    
    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);
    
    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains('offline-orders')) {
        db.createObjectStore('offline-orders', { keyPath: 'id', autoIncrement: true });
      }
    };
  });
}

// Message handler for manual cache updates
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CACHE_URLS') {
    event.waitUntil(
      caches.open(CACHE_NAME).then((cache) => {
        return cache.addAll(event.data.urls);
      })
    );
  }
});
