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
  console.log('askPermission' + id);
  return new Promise(function(resolve, reject) {
    const permissionResult = Notification.requestPermission(function(result) {
      console.log(result);
      resolve(result);
    });
    if (permissionResult) {
      permissionResult.then(resolve, reject);
      console.log('asking permission');
    }
  })
  .then(function(permissionResult) {
    if (permissionResult !== 'granted') {
      //throw new Error('We weren\'t granted permission.');
      console.log('permission denied');
    } else {
      console.log('according permission');
      subscribeUserToPush();
    }
  });
}

function subscribeUserToPush() {
    console.log('subscribeUserToPush');
    return navigator.serviceWorker.register('sw.js')
    .then(function(registration) {
        const subscribeOptions = {
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(applicationServerPublicKey)
        };
        return registration.pushManager.subscribe(subscribeOptions);

    })
    .then(function(pushSubscription) {
        console.log('Received PushSubscription: ', JSON.stringify(pushSubscription));
    /*
    Received PushSubscription:  {"endpoint":"https://fcm.googleapis.com/fcm/send/eppIRWUq3Z4:APA91bEsNfVEX01dNJDgOXu-j9SMBnOI6A1zPRghsc7dXZKzuhEZVCISUWxRqLxmoD-xNiSc1WScmyGABps0APFeviCl3dqdIivzd-wk6-t4kfwC5gbYds_61PITp6uDb2eewX_VcsV5",
    "expirationTime":null,
    "keys":{
      "p256dh":"BOrGsOShQVSMX6PzkeGj6Q6PNhESDbLXPgXLrbzfuCzVpY1ok9xwXFrxMFxZ9XZ_JBAU0ortAgopb7qKCXDKJuc=",
      "auth":"gCwPb8v0_M5kC51G0HnDjw=="
    }}
    */
    //sauvegarde de l'inscription dans le serveur applicatif (2)
        $.ajax({
            type: "POST",
            url : 'pushNotification_symfony/web/NotifBundle/subscription',            
            data: 'sub='+pushSubscription,
            contentType: "application/json; charset=utf-8",
            dataType: "json",
            success: function(data){
                alert(data);
            },
            failure: function(errMsg) {
                alert(errMsg);
            } 
        });      
    }).catch(function (subscriptionErr) {
        // Check for a permission prompt issue
        console.log('Subscription failed ' + subscriptionErr);
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
    console.log('User is subscribed');

    updateSubscriptionOnServer(subscription);

    isSubscribed = true;

    updateBtn();
  })
  .catch(function(err) {
    console.log('Failed to subscribe the user: ', err);
    updateBtn();
  });
}

// Called if the subscribing user change his mind and he unsubscribe to the service
function unsubscribeUser() {
  console.log('Unsubscribed');
  swRegistration.pushManager.getSubscription()
  .then(function(subscription) {
    if (subscription) {
      console.log(subscription);
      return subscription.unsubscribe();
    }
  })
  .catch(function(error) {
    console.log('Error unsubscribing', error);
  })
  .then(function() {
    updateSubscriptionOnServer(null);

    console.log('User is unsubscribed.');
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

function initializeUI() {
  pushButton.addEventListener('click', function() {
    pushButton.disabled = true;
    if (isSubscribed) {
      // TODO: Unsubscribe user
    } else {
      subscribeUser();
    }
  });

  // Set the initial subscription value
  swRegistration.pushManager.getSubscription()
  .then(function(subscription) {
    isSubscribed = !(subscription === null);

    updateSubscriptionOnServer(subscription);

    if (isSubscribed) {
      console.log('User IS subscribed.');
    } else {
      console.log('User is NOT subscribed.');
    }

    updateBtn();
  });
}
console.log('watch when the script is loading by index');
if ('serviceWorker' in navigator && 'PushManager' in window) {
  console.log('Service Worker and Push is supported');
  navigator.serviceWorker.register('http://localhost/pushNotification_symfony/web/sw/sw.js')
  .then(function(swReg) {
    console.log('Service Worker is registered', swReg);
    swRegistration = swReg;
  })
  .catch(function(error) {
    console.error('Service Worker Error', error);
  });
} else {
  console.warn('Push messaging is not supported');
  pushButton.textContent = 'Push Not Supported';
}

/* onclick button page2 */
function page2(){
  window.location="templates/lose.html";
}
/* onclick button page 1 */
function page1(){
  window.location="../index.html";
}
