
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
var cacheName = 'push_noti_v3';

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
    // `claim()` sets this worker as the active worker for all clients that
    // match the workers scope and triggers an `oncontrollerchange` event for
    // the clients.
    return self.clients.claim();
});

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
        if(!event.notification.data){
             log('Notifiaction data');
            const notificationData = event.notification.data; 
            const intermediateData = [];
            Object.keys(notificationData).forEach(function(key){
                intermediateData.push(` ${key}: ${notificationData[key]}`);
            });
            log(intermediateData);  
        }
        return;
     } else {       
        switch (event.action) {            
            case 'action1':
                log('action1')
                event.waitUntil(
                    clients.openWindow('http://localhost/pushNotification_symfony/web/app_dev.php/platform/1')
                );

                break;
            case 'action2': 
                log('action2');
                break;
            default:
                log('unknow');
                break;
        }    
    }
});

self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }
    log(self);
    log('NOTIF');    
    var sendNotification = function(data) {
        log(data.title);
        log(data.message);
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
        requiereInteraction = false;
        if(data.requiereInteraction === 1){
            requiereInteraction = true;
        }
        mydata = [];
        if(data.datas !== ''){
            let dot = JSON.parse(data.datas);
            for(let name in dot){
                if(dot.hasOwnProperty(name)){
                    mydata = dot[name];
                }
            };
        }
        log(mydata);
        log(requiereInteraction);
        if ('actions' in Notification.prototype) {
            log('Action buttons are supported');
            let opt = JSON.parse(data.options);
            let objLength = Object.keys(opt).length;
            actions = [];
            for(let i = 0 ; i < objLength; i++){
                actions.push({'action':opt['action_'+i].action, 'title':opt['action_'+i].title, 'icon':opt['action_'+i].icon});           
            };
        } else {
            log('Action buttons are NOT supported');
            actions = [];
        }                
        // 'Simple piece of body text.\n\rSecond line of body text\n\rThird line of body text'
        let options = {
            body: message,
            icon: icon,
            image: image,
            tag: tag,
            actions: actions,
            requireInteraction: requiereInteraction,
            data: mydata
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