
//bind our console to a variable
var log = console.log.bind(console);
var urlsToCache = [
    '/pushNotification_symfony/web/js/keys.js',
    '/pushNotification_symfony/web/js/main.js',
    '/pushNotification_symfony/web/sw.js',
    '/pushNotification_symfony/web/bootstrap/css/bootstrap.css',
    '/pushNotification_symfony/web/js/jquery/jQuery3.3.1.js',
    '/pushNotification_symfony/web/bootstrap/js/bootstrap.js',   
    '/pushNotification_symfony/web/favicon.png',
    '/pushNotification_symfony/web/manifest.json',
    '/pushNotification_symfony/web/icon/iconO.png',
    '/pushNotification_symfony/web/icon/icon-48.png',
    '/pushNotification_symfony/web/icon/icon-72.png',
    '/pushNotification_symfony/web/icon/icon-96.png',
    '/pushNotification_symfony/web/icon/icon-144.png',
    '/pushNotification_symfony/web/icon/icon-192.png',
    '/pushNotification_symfony/web/platform/1'
];  

/* global self, caches, Promise, fetch, clients */

/*__________________________________________________________*/
//
//         USED CODE
/*__________________________________________________________*/

// Use a cacheName for cache versioning SERVICE WORKER
var cacheName = 'push_noti_v8';

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

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.open(cacheName)
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
            if (key !== cacheName) {
                log('Removing old cache__'+ key);
                return caches.delete(key);
            }
        }));
      })
    );
    // la page client chargée dans la même portée n'a pas besoin d'être rechargée avant de pouvoir utiliser le service worker
    return self.clients.claim();
});

/*__________________________________________________________________________*/
//                                                         
//         END OF USED CODE                            
/*__________________________________________________________________________*/


/*___________________________________________________________________________*/
//
//         CACHE STRATEGY n°1 NETWORK or CACHE 
//         one or the other depending on the response time
//         this strategy display the latest ressources if the network works
//         but it's not change the ressouces in services workers
/*____________________________________________________________________________*/
/*
// Cache strategy n°1
var CACHE = 'network-or-cache';
//On install, cache some resource.
self.addEventListener('install', function(evt) {
    console.log('The service worker is being installed.');
    //Ask the service worker to keep installing until the returning promise resolves.

    evt.waitUntil(precache());
 });

//On fetch, use cache but update the entry with the latest contents from the server.
self.addEventListener('fetch', function(evt) {
    console.log('The service worker is serving the asset.');
    // Try network and if it fails, go for the cached copy.

    evt.respondWith(fromNetwork(evt.request, 400).catch(function () {
        return fromCache(evt.request);
    }));
});

//Open a cache and use addAll() with an array of assets to add all of them to the cache. 
//Return a promise resolving when all the assets are added.
function precache() {
  return caches.open(CACHE).then(function (cache) {
    return cache.addAll(urlsToCache);
  });
}

//Time limited network request. 
//If the network fails or the response is not served before timeout, the promise is rejected.
function fromNetwork(request, timeout) {
    return new Promise(function (fulfill, reject) {

        //Reject in case of timeout.
        var timeoutId = setTimeout(reject, timeout);
        //Fulfill in case of success.
        fetch(request).then(function (response) {
            clearTimeout(timeoutId);
            fulfill(response);

            //Reject also if network fetch rejects.
        }, reject);
    });
}

//Open the cache where the assets were stored and search for the requested resource. 
//Notice that in case of no matching, the promise still resolves but it does with undefined as value.
function fromCache(request) {
  return caches.open(CACHE).then(function (cache) {
    return cache.match(request).then(function (matching) {
      return matching || Promise.reject('no-match');
    });
  });
}
*/
/*_________________________________________________________________*/
//                                                                  //
//         END OF CACHE STRATEGY n°1 NETWORK or CACHE              //
/*_________________________________________________________________*/
/*_________________________________________________________________*/
//
//         CACHE STRATEGY n°2 CACHE ONLY
//         this strategy serve only files who are in the cache
/*__________________________________________________________________*/
/*
    var CACHE = 'cache-only';
   // On install, cache some resources.
    self.addEventListener('install', function(evt) {
        console.log('The service worker is being installed.');
      
        //Ask the service worker to keep installing until the returning promise resolves.

        evt.waitUntil(precache());
    });
    
    //On fetch, use cache only strategy.
    self.addEventListener('fetch', function(evt) {
        console.log('The service worker is serving the asset.');
        evt.respondWith(fromCache(evt.request));
    });
    
    //Open a cache and use addAll() with an array of assets to add all of them to the cache. 
    //Return a promise resolving when all the assets are added.
    function precache() {
      return caches.open(CACHE).then(function (cache) {
        return cache.addAll(urlsToCache);
      });
    }

    //Open the cache where the assets were stored and search for the requested resource.
    // Notice that in case of no matching, the promise still resolves but it does with undefined as value.
    function fromCache(request) {
        return caches.open(CACHE).then(function (cache) {
            return cache.match(request).then(function (matching) {
              return matching || Promise.reject('no-match');
            });
        });
    }
*/
/*______________________________________________________________________*/
//                                                                      //
//         END OF CACHE STRATEGY n°2 CACHE ONLY                         //
/*_______________________________________________________________________*/
/*_______________________________________________________________________*/
//
//         CACHE STRATEGY n°3 CACHE and UPDATE ASYNCHRONOUSLY
//         this strategy serve only files who are in the cache
//         and update the content in the service workers asynchronously
//         the new content are available when the user reload the page
/*_______________________________________________________________________*/
 /*   var CACHE = 'cache-and-update';
   // On install, cache some resources.
    self.addEventListener('install', function(evt) {
        console.log('The service worker is being installed.');
      
        //Ask the service worker to keep installing until the returning promise resolves.

        evt.waitUntil(precache());
    });
    
    //On fetch, use cache but update the entry with the latest contents from the server.
    //You can use respondWith() to answer immediately, without waiting for the network response to reach the service worker…
    //…and waitUntil() to prevent the worker from being killed until the cache is updated.
    self.addEventListener('fetch', function(evt) {
        console.log('The service worker is serving the asset.');
        evt.respondWith(fromCache(evt.request));
        evt.waitUntil(update(evt.request));
    });
    
    //Open a cache and use addAll() with an array of assets to add all of them to the cache. 
    //Return a promise resolving when all the assets are added.
    function precache() {
      return caches.open(CACHE).then(function (cache) {
        return cache.addAll(urlsToCache);
      });
    }

    //Open the cache where the assets were stored and search for the requested resource.
    // Notice that in case of no matching, the promise still resolves but it does with undefined as value.
    function fromCache(request) {
        return caches.open(CACHE).then(function (cache) {
            return cache.match(request).then(function (matching) {
              return matching || Promise.reject('no-match');
            });
        });
    }
    
    //Update consists in opening the cache, performing a network request and storing the new response data.
    function update(request) {
        return caches.open(CACHE).then(function (cache) {
            return fetch(request).then(function (response) {
                return cache.put(request, response);
            });
        });
    }
*/
/*___________________________________________________________________________*/
//                                                                            //
//         END OF CACHE STRATEGY n°3 CACHE and UPDATE ASYNCHRONOUSLY          //
/*_________________________________________________________________________*/

/*___________________________________________________________________________*/
//
//         CACHE STRATEGY n°4 CACHE and UPDATE AND REFRESH
//         this strategy serve files who are in the cache update the content 
//         and told to the user that a new content is available
/*______________________________________________________________________________*/
 /*   var CACHE = 'cache-and-update';

    //On install, cache some resource.

    self.addEventListener('install', function(evt) {
      console.log('The service worker is being installed.');

    //Open a cache and use addAll() with an array of assets to add all of them to the cache. 
    //Ask the service worker to keep installing until the returning promise resolves.

        evt.waitUntil(caches.open(CACHE).then(function (cache) {
            cache.addAll(urlsToCache);
        }));
    });

    //On fetch, use cache but update the entry with the latest contents from the server.

    
    self.addEventListener('fetch', (event) => {
        event.respondWith(
            caches.open(CACHE)
            .then(function(cache){
                return cache.match(event.request)
                .then(function(response){
                    var fetchPromise = fetch(event.request)
                    .then(function(networkResponse){
                        cache.put(event.request, networkResponse.clone());
                        return networkResponse;
                    });
                    return response || fetchPromise;
                });
            })
        );
    });
    //Open the cache where the assets were stored and search for the requested resource. 
    //Notice that in case of no matching, the promise still resolves but it does with undefined as value.

    

    //Update consists in opening the cache, performing a network request and storing the new response data.

    function update(request) {
        return caches.open(CACHE).then(function (cache) {
            return fetch(request).then(function (response) {
                return cache.put(request, response.clone()).then(function () {
                    return response;
                });
            });
        });
    }

    //Sends a message to the clients.

    function refresh(response) {
        return self.clients.matchAll().then(function (clients) {
            clients.forEach(function (client) {

                //Encode which resource has been updated. By including the ETag the client can check if the content has changed.
                //Notice not all servers return the ETag header. 
                //If this is not provided you should use other cache headers or rely on your own means to check if the content has changed.

                    var message = {
                        type: 'refresh',
                        url: response.url,
                        eTag: response.headers.get('ETag')
                    };
                //Tell the client about the update.

                client.postMessage(JSON.stringify(message));
            });
        });
    }
*/
/*_____________________________________________________________________*/
//                                                                      //
//         END OF CACHE STRATEGY n°4 CACHE and UPDATE AND REFRESH      //
/*_____________________________________________________________________*/
/**********************************************************/
//  This part concerns only the push notifications        //
/***********************************************************/

var data = {};
self.addEventListener('notificationclick', function(event) {
    
    if (!event.action) {
        // Was a normal notification click
        log('Notification Click.');
        log(data.url);
        event.notification.close();
        event.waitUntil(
            clients.openWindow(data.url)
        );
        return;
    }

    switch (event.action) {
        case 'action1': 
            log('action1');
            break;
        case 'action2': 
            log('action2');
            break;
        default:
            log('unknow');
            break;
    }
    
});

self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }
    log('NOTIF');    
    var sendNotification = function(data) {
        log(data.title);
        log(data.image);
        log(data.url);
        log(data.icon);
        // on actualise la page des notifications ou/et le compteur de notifications
        self.refreshNotifications();

        title = data.title || "DEUSI",
        icon = data.icon || '/pushNotification_symfony/web/icon/icon-36.png';

        message = data.message || 'Il y a du neuf !';
        tag = data.tag || 'general';
        
        image = data.image || '';
        
        if ('actions' in Notification.prototype) {
            log('Action buttons are supported');
            var opt = JSON.parse(data.options);
            var objLength = Object.keys(opt).length;
            var actions = [];
            for(var i = 0 ; i < objLength; i++){
                actions.push({'action':opt['action_'+i].action, 'title':opt['action_'+i].title, 'icon':opt['action_'+i].icon});           
            };
        } else {
            log('Action buttons are NOT supported');
            var actions = [];
        }                
        
        let options = {
            body: message,
            icon: icon,
            image: image,
            tag: tag,
            actions: actions
        }

        return self.registration.showNotification(title, options);
    };
    //log(event);
    log( event.data);
    if (event.data) {
        data = event.data.json();
        //var data = event.data.text();
        event.waitUntil(                
            sendNotification(data)
            //sendNotification(data)
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


