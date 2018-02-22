'use strict';

let isSubscribed = false;
let registration = '';
let url_FileSW = 'http://localhost/pushNotification_symfony/web/sw.js';
let url_Server = 'http://localhost/pushNotification_symfony/web/notif/subscribe';
const pushButton = document.querySelector('.push-btn');
//  for asking permission to send notifications (open prompt)
function subscribeUser(iduser) {
    log('askPermission' + iduser);
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
            isSubscribed = true;
            updateBtn();            
            subscribeUserToPush();
        }
    })
    .catch(function(err) {
        log('Failed to subscribe the user: ', err);
    });
}
// Call service to get the JSON subscription to the service of web push
function subscribeUserToPush() {
    log('subscribeUserToPush');
    return navigator.serviceWorker.register(url_FileSW)
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
        sendSubscriptionToBackEnd(pushSubscription, 1)
    }).catch(function (subscriptionErr) {
        // Check for a permission prompt issue
        log('Subscription failed ' + subscriptionErr);
    });
}
// Send the JSON to the backend server to record it
function sendSubscriptionToBackEnd(subscription, up) {
    if(up === 1){
        fetch(url_Server, {
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
    } else {
        fetch(url_Server, {
            method: 'DELETE',
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
            log('Could not unregister subscription into app server', err);
        });
    }
}
// Called if the subscribing user change his mind and he unsubscribe to the service
function unsubscribeUser(iduser) {
    log('Unsubscribed' + iduser);
    //var unEndpoint = '';
    registration.pushManager.getSubscription()
    .then(function(subscription) {
        if (subscription) {
            log(subscription);
            sendSubscriptionToBackEnd(subscription);

            log('User is unsubscribed.');
            isSubscribed = false;

            updateBtn();
            return subscription.unsubscribe();
        }
    })
    .catch(function(error) {
        log('Error unsubscribing', error);
    });    
}
// return the state of permission
function getNotificationPermissionState() {
    if (navigator.permissions) {
        return navigator.permissions.query({name: 'notifications'})
        .then(function(result){
            log(result.state);
            return result.state;
        });
    }

    return new Promise(function(resolve){
        resolve(Notification.permission);
    });
}
// tooggle the button 
function updateBtn() {
    if (Notification.permission === 'denied') {
      pushButton.textContent = 'Push Messaging Blocked.';
      pushButton.disabled = true;
      sendSubscriptionToBackEnd(null);
      return;
    }
    if (isSubscribed) {
      pushButton.textContent = 'Disable Push Messaging';
    } else {
      pushButton.textContent = 'Enable Push Messaging';
    }
    log(isSubscribed);
    pushButton.disabled = false;
}
function initializeUI() {
    pushButton.addEventListener('click', function() {
        pushButton.disabled = true;
        // this part is hard code it will be replace by the real ID of user if we want send specific notifications for a unique user
        var iduser = 2;
        if (isSubscribed) {
            unsubscribeUser(iduser);
        } else {
            subscribeUser(iduser);
        }
    });
    // Set the initial subscription value
    registration.pushManager.getSubscription()
    .then(function(subscription) {
        isSubscribed = !(subscription === null);

        if (isSubscribed) {
          console.log('User IS subscribed.');
        } else {
          console.log('User is NOT subscribed.');
        }

        updateBtn();
    });
}
log('watch when the script is loading by index');
if ('serviceWorker' in navigator && 'PushManager' in window) {
    log('Service Worker and Push is supported');
    navigator.serviceWorker.register(url_FileSW)
    .then(function(swReg) {
        log('Service Worker is registered', swReg);
        log('the scope is ', swReg.scope);
        getNotificationPermissionState();
        registration = swReg;
        initializeUI();
    })
    .catch(function(error) {
        log('Service Worker Error', error);
    });
} else {
    warn('Push messaging is not supported');
    pushButton.textContent = 'Push Not Supported';
}








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



