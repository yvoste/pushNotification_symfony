

/* global self, caches, Promise, fetch */

// Use a cacheName for cache versioning SERVICE WORKER
var cacheName = 'push_noti_v1';
var urlsToCache = [
    '/pushNotification_symfony/web/js/main.js',
    '/pushNotification_symfony/web/sw/sw.js',
    '/pushNotification_symfony/web/bootstrap/css/bootstrap.css',
    '/pushNotification_symfony/web/js/jquery/jQuery3.3.1.js',
    '/pushNotification_symfony/web/bootstrap/js/bootstrap.js',   
    '/pushNotification_symfony/web/favicon.png',
    '/pushNotification_symfony/web/manifest.json',
    '/pushNotification_symfony/web/icon/icon-48.png',
    '/pushNotification_symfony/web/icon/icon-72.png',
    '/pushNotification_symfony/web/icon/icon-96.png',
    '/pushNotification_symfony/web/icon/icon-144.png',
    '/pushNotification_symfony/web/icon/icon-192.png',
    '/pushNotification_symfony/web/platform/1',
];  
self.addEventListener('install', (event) => {
    console.info('Event: Install');
    event.waitUntil(
        caches.open(cacheName)
        .then((cache) => {
            //[] of files to cache & if any of the file not present `addAll` will fail
            return cache.addAll(urlsToCache)
            .then(() => {
                console.info('All files are cached');
                return self.skipWaiting(); //To forces the waiting service worker to become the active service worker
            })
            .catch((error) =>  {
                console.error('Failed to cache', error);
            });
        })
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.open('push_noti_v1')
        .then((cache) => {
            return cache.match(event.request)
            .then((response) => {
                var fetchPromise = fetch(event.request)
                .then((networkResponse) => {
                    cache.put(event.request, networkResponse.clone());
                    return networkResponse;
                });
                return response || fetchPromise;
            });
        })
    );
});
self.addEventListener('activate', function(event) {
    console.log('Activate');
    event.waitUntil(
        caches.keys().then(function(keyList) {
          return Promise.all(keyList.map(function(key) {
            console.log('Removing old cache__'+ key);
            if (key !== cacheName) {
                return caches.delete(key);
            }
        }));
      })
    );
    return self.clients.claim();
});
/*/
//Adding `activate` event listener
self.addEventListener('activate', (event) => {
  console.info('Event: Activate');
  //Remove old and unwanted caches
    event.waitUntil(
        caches.keys().then((cacheName) => {
            return Promise.all(
                cacheName.map((cache) => {
                  if (cache !== cacheName) {     //cacheName = 'push_noti_v1'
                    return caches.delete(cache); //Deleting the cache
                  }
                })
            );
        })
    );
});*/
/*
// During the installation phase, you'll usually want to cache static assets.
self.addEventListener('install', function(event) {
    console.log('it was used only one time')
    // Once the service worker is installed, go ahead and fetch the resources to make this work offline.
    event.waitUntil(
        caches.open(cacheName)
        .then(function(cache) {
            return cache.addAll(urlsToCache)
            .then(function() {
              console.log('install');
                return self.skipWaiting();
            });
        })
    );
});

self.addEventListener('activate', function(event) {
    console.log('Activate');
    event.waitUntil(
        caches.keys().then(function(keyList) {
          return Promise.all(keyList.map(function(key) {
            console.log('Removing old cache__'+ key);
            if (key !== cacheName) {
                return caches.delete(key);
            }
        }));
      })
    );
    return self.clients.claim();
});


// Use ServiceWorker (or not) to fetch data
self.addEventListener('fetch', function(event) {
   console.log('Fetch___'+ event.request.url);
  event.respondWith(
    caches.match(event.request).then(function(response) {
      console.log(response)
      return response || fetch(event.request);
    })
  );
});
*/