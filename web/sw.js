

/* global self, caches, Promise, fetch, clients */
var log = console.log.bind(console);//bind our console to a variable
// Use a cacheName for cache versioning SERVICE WORKER
var cacheName = 'push_noti_v1';
var urlsToCache = [
    '/pushNotification_symfony/web/js/main.js',
    '/pushNotification_symfony/web/sw.js',
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
    '/pushNotification_symfony/web/platform/1'
];  
self.addEventListener('install', (event) => {
    console.info('Event: Install');
    event.waitUntil(precache());        
});
function precache(){
    caches.open(cacheName)
    .then((cache) => {
        //[] of files to cache & if any of the file not present `addAll` will fail
        return cache.addAll(urlsToCache)
        .then(() => {
            log('All files are cached');
            return self.skipWaiting(); //To forces the waiting service worker to become the active service worker
        })
        .catch((error) =>  {
            log('Failed to cache', error);
        });
    });
}
/*
self.addEventListener('fetch', function(event){
    event.respondWith(
        caches.open('push_noti_v1').then(function(cache){
            cache.match(event.request).then(function(response){
                if(response){
                    log("file is in cache__"+ event.request.url);
                    return response;
                } else {
                    log("file is not in cache "+ event.request.url);
                    var fetchPromise = fetch(event.request);
                    return fetchPromise;
                }
            });
        })
    );
});
*/

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
    log('Activate');
    event.waitUntil(
        // it will return all the OLD keys in the cache as an array
        caches.keys().then(function(keyList) {
            // run everything in parallel using Promise.all()(the new version of cacheName
          Promise.all(keyList.map(function(key) {
            log('Removing old cache__'+ key);
            if (key !== cacheName) {
                return caches.delete(key);
            }
        }));
      })
    );
    // la page client chargée dans la même portée n'a pas besoin d'être rechargée avant de pouvoir utiliser le service worker
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
var title = "";



self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }
    log('NOTIF');
    var sendNotification = function(message, title, tag) {
        // on actualise la page des notifications ou/et le compteur de notifications
        self.refreshNotifications();

        title = title || "DEUSI",
        icon = '/pushNotification_symfony/web/icon/icon-48.png';

        message = message || 'Il y a du neuf !';
        tag = tag || 'general';

        return self.registration.showNotification(title, {
            body: message,
            icon: icon,
            tag: tag
        });
    };
    log(event);
    log( event.data);
    if (event.data) {
        //var data = event.data.json();
        var data = event.data.text();
        event.waitUntil(
            //sendNotification(data.message, data.tag)
            sendNotification(data)
        );
    } else {
        event.waitUntil(
            self.registration.pushManager.getSubscription().then(function(subscription) {
                if (!subscription) {
                    return;
                }

                return fetch('api/notifications/last?endpoint=' + encodeURIComponent(subscription.endpoint)).then(function (response) {
                    if (response.status !== 200) {
                        throw new Error();
                    }

                    // Examine the text in the response
                    return response.json().then(function (data) {
                        if (data.error || !data.notification) {
                            throw new Error();
                        }

                        return sendNotification(data.notification.message);
                    });
                }).catch(function () {
                    return sendNotification();
                });
            })
        );
    }
});

self.refreshNotifications = function(clientList) {
    if (clientList === undefined) {
        clients.matchAll({ type: "window" }).then(function (clientList) {
            self.refreshNotifications(clientList);
        });
    } else {
        for (var i = 0; i < clientList.length; i++) {
            var client = clientList[i];
            if (client.url.search(/notifications/i) >= 0) {
                // si la page des notifications est ouverte on la recharge
                client.postMessage('reload');
            }

            // si on n'est pas sur la page des notifications on recharge le compteur
            client.postMessage('refreshNotifications');
        }
    }
};

self.addEventListener('notificationclick', function (event) {
    // fix http://crbug.com/463146
    event.notification.close();

    event.waitUntil(
        clients.matchAll({
            type: "window"
        })
            .then(function (clientList) {
                // si la page des notifications est ouverte on l'affiche en priorité
                for (var i = 0; i < clientList.length; i++) {
                    var client = clientList[i];
                    if (client.url.search(/notifications/i) >= 0 && 'focus' in client) {
                        return client.focus();
                    }
                }

                // sinon s'il y a quand même une page du site ouverte on l'affiche
                if (clientList.length && 'focus' in client) {
                    return client.focus();
                }

                // sinon on ouvre la page des notifications
                if (clients.openWindow) {
                    return clients.openWindow('notifications');
                }
            })
    );
});

self.addEventListener('message', function (event) {
    var message = event.data;

    switch (message) {
        case 'dispatchRemoveNotifications':
            clients.matchAll({ type: "window" }).then(function (clientList) {
                for (var i = 0; i < clientList.length; i++) {
                    clientList[i].postMessage('removeNotifications');
                }
            });
            break;
        default:
            console.warn("Message '" + message + "' not handled.");
            break;
    }
});


