/*
    the VAPID keys are generated with the plugin web-push
    for install it npm install -g web-push (it's done)
    to generate keys type the following code **  web-push generate-vapid-keys **

    Public Key:
    BBVLiXsgeG4pvKLxAZvGBwfov31kGDD3OJpWt_WIdbdWdST7VO6T36eywK0TFeBwLqMZrZMRl45aaxFgfV03dzw

    Private Key:
    81gu7NF1WkL28Y1wpVMeSA60qSZE7Mous-gkLAW2I54

    Received PushSubscription:  {"endpoint":"https://fcm.googleapis.com/fcm/send/eppIRWUq3Z4:APA91bEsNfVEX01dNJDgOXu-j9SMBnOI6A1zPRghsc7dXZKzuhEZVCISUWxRqLxmoD-xNiSc1WScmyGABps0APFeviCl3dqdIivzd-wk6-t4kfwC5gbYds_61PITp6uDb2eewX_VcsV5",
    "expirationTime":null,
    "keys":{
      "p256dh":"BOrGsOShQVSMX6PzkeGj6Q6PNhESDbLXPgXLrbzfuCzVpY1ok9xwXFrxMFxZ9XZ_JBAU0ortAgopb7qKCXDKJuc=",
      "auth":"gCwPb8v0_M5kC51G0HnDjw=="
    }}

*/


/* global Notification, subscriptionDetails, pushButton */

'use strict';
//let log = console.log.bind(console);//bind our console to a variable
// VAPID key public
const applicationServerPublicKey = 'BBVLiXsgeG4pvKLxAZvGBwfov31kGDD3OJpWt_WIdbdWdST7VO6T36eywK0TFeBwLqMZrZMRl45aaxFgfV03dzw';

let isSubscribed = false;
let swRegistration = null;

// Called to convert the public key, which is base 64 URL safe encoded, to a UInt8Array.
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

// Called when the user publish a new advert (after the advert has been validated and record in database)
//  for asking permission to send notifications (open prompt)
function askPermission(id) {
    log(getNotificationPermissionState());
    log('askPermission' + id);
    return new Promise(function(resolve, reject) {
      const permissionResult = Notification.requestPermission(function(result) {
        log(result);
        resolve(result);
      });
      if (permissionResult) {
        permissionResult.then(resolve, reject);
        log('asking permission');
      }
    })
    .then(function(permissionResult) {
      if (permissionResult !== 'granted') {
        //throw new Error('We weren\'t granted permission.');
        log('permission denied');
      } else {
        log('according permission');
        subscribeUserToPush();
      }
    });
}
// return the state of permission
function getNotificationPermissionState() {
    if (navigator.permissions) {
      return navigator.permissions.query({name: 'notifications'})
      .then((result) => {
        return result.state;
      });
    }

    return new Promise((resolve) => {
      resolve(Notification.permission);
    });
}
function subscribeUserToPush() {
    log('subscribeUserToPush');
    return navigator.serviceWorker.register('http://localhost/pushNotification_symfony/web/sw.js')
    .then(function(registration) {
        const subscribeOptions = {
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(applicationServerPublicKey)
        };
        //log(registration.pushManager.subscribe(subscribeOptions));
        return registration.pushManager.subscribe(subscribeOptions);

    })
    .then(function(pushSubscription) {
        log('Received PushSubscription ok ');
        //sendSubscriptionToBackEnd(pushSubscription);
        //return pushSubscription;
       //log(JSON.stringify(pushSubscription));
       /* var endpoint = pushSubscription.endpoint;
        var expirationTime = 0;
        var key_p256dh = pushSubscription.getKey('p256dh');
        var key_auth = pushSubscription.getKey('auth');
        log(endpoint+'______'+expirationTime+'____'+key_p256dh+'_____'+key_auth);*/
        /*
        const Subscription = {
            endpoint: pushSubscription.endpoint,
            keys: {
              p256dh: pushSubscription.getKeys('p256dh'),
              auth: pushSubscription.getKeys('auth')
            }
         };
         log(Subscription);*/
        //sauvegarde de l'inscription dans le serveur applicatif (2)
       /* $.ajax({
            type: "POST",
            headers: {'cache-control': 'no-cache'},
            async : true,
            dataType: "json",
            url : 'http://localhost/pushNotification_symfony/web/notif/subscribe',            
            data: 'sub='+JSON.stringify(pushSubscription),            
            success: function(data){
                alert(data.error);
            },
            failure: function(errMsg) {
                alert(errMsg);
            } 
        });*/
        sendSubscriptionToBackEnd(pushSubscription)
    }).catch(function (subscriptionErr) {
        // Check for a permission prompt issue
        log('Subscription failed ' + subscriptionErr);
    });
}
function sendSubscriptionToBackEnd(subscription) {
    fetch('http://localhost/pushNotification_symfony/web/notif/subscribe', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(subscription)
    })
    .then(function(response) {
        log(response);
        if (response.error === true) {
          throw new Error('Bad status code from server.');
        }
        return response.json();
    })
   .catch(function (err) {
        log('Could not register subscription into app server', err);
    });
}

function updateSubscriptionOnServer(subscription) {
    // TODO: Send subscription to application server

    const subscriptionJson = document.getElementById('enablePush');
    //const subscriptionDetails =
      //document.querySelector('.js-subscription-details');

    if (subscription) {
        subscriptionJson.textContent = JSON.stringify(subscription);
        subscriptionDetails.classList.remove('is-invisible');
    } else {
        subscriptionDetails.classList.add('is-invisible');
    }
}


function subscribeUser() {
  const applicationServerKey = urlB64ToUint8Array(applicationServerPublicKey);
  swRegistration.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: applicationServerKey
  })
  .then(function(subscription) {
    log('User is subscribed');

    updateSubscriptionOnServer(subscription);

    isSubscribed = true;

    updateBtn();
  })
  .catch(function(err) {
    log('Failed to subscribe the user: ', err);
    updateBtn();
  });
}

// Called if the subscribing user change his mind and he unsubscribe to the service
function unsubscribeUser() {
  log('Unsubscribed');
  swRegistration.pushManager.getSubscription()
  .then(function(subscription) {
    if (subscription) {
     log(subscription);
      return subscription.unsubscribe();
    }
  })
  .catch(function(error) {
    log('Error unsubscribing', error);
  })
  .then(function() {
    updateSubscriptionOnServer(null);

    log('User is unsubscribed.');
    isSubscribed = false;

    updateBtn();
  });
}


function updateBtn() {
  if (Notification.permission === 'denied') {
    pushButton.textContent = 'Push Messaging Blocked.';
    pushButton.disabled = true;
    updateSubscriptionOnServer(null);
    return;
  }

  if (isSubscribed) {
    pushButton.textContent = 'Disable Push Messaging';
  } else {
    pushButton.textContent = 'Enable Push Messaging';
  }

  pushButton.disabled = false;
}
log('watch when the script is loading by index');
if ('serviceWorker' in navigator && 'PushManager' in window) {
  log('Service Worker and Push is supported');
  navigator.serviceWorker.register('http://localhost/pushNotification_symfony/web/sw.js')
  .then(function(swReg) {
    log('Service Worker is registered', swReg);
    swRegistration = swReg;
  })
  .catch(function(error) {
    log('Service Worker Error', error);
  });
} else {
  warn('Push messaging is not supported');
}


/* onclick button page2 */
function page2(){
  window.location="templates/lose.html";
}
/* onclick button page 1 */
function page1(){
  window.location="../index.html";
}



