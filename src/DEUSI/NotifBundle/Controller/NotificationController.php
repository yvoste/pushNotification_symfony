<?php
// src/DEUSI/NotifBundle/Controller/NotificationController.php

namespace DEUSI\NotifBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DEUSI\NotifBundle\Entity\Subscription;

class NotificationController extends Controller
{
    public function subscriptionAction(Request $request)
    {
        $sub = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($sub['endpoint'])) {
            return  new Response(json_encode(array('error' => true)));            
        }            
            
        if($request->isMethod('POST')) {            
            // On crée un objet Subscription
            $subscription = new Subscription();
            // create a new subscription entry in your database (endpoint is unique)
            $subscription->setEndpoint($sub['endpoint']);
            $subscription->setExpirationTime(0);
            $subscription->setKeyP256dh($sub['keys']['p256dh']);
            $subscription->setKeyAuth($sub['keys']['auth']);


            $em = $this->getDoctrine()->getManager();
            $em->persist($subscription);
            $em->flush();
            if ($subscription->getId()) {
                $response = new Response(\json_encode(['error' => false, 'method' => 'POST']));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            return  new Response(json_encode(['error' => true, 'method' => 'No flush'])); 
        } elseif ($request->isMethod('PUT')){
            // update the key and token of subscription corresponding to the endpoint
        } elseif ($request->isMethod('DELETE')){            
            // find objet
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository(Subscription::class);
            $subscription = $repo->findOneBy(['keyAuth' => $sub['keys']['auth']]);
            // delete the subscription corresponding to the endpoint
            if(null !== $subscription){
               $em->remove($subscription);
               $em->flush();
               return  new Response(json_encode(['error' => false]));
            }
            return  new Response(json_encode(['error' => true, 'method' => 'No user']));         
        } else {
            return  new Response(json_encode(['error' => true, 'method' => 'No method']));  
        }
    }
    public function sendNotificationAction()
    {
        $content = ['title'=>'Mon title',
                    'message'=>'Simple piece of body text.\nSecond line of body text\nThird line of body text', 
                    'image'=> 'http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg',
                    'url'=> 'http://artepole.com'
            ];
        // array of notifications
        $notifications = [
            [
                "endpoint" => "https://updates.push.services.mozilla.com/wpush/v2/gAAAAABaixJdV_5GA02oJ6ElL-CiJQH7JVvFSqY2r8EVcO7Yer0LT6cWS9OItUNFyNB-HUbg4CjZ-RqS4hYyDFxkNnhWf8CGX_WJKihARFtkvmYWAOyTYqxd-T_lRuo06UepPX8uk6oIOshw6_MfcblS0m5kIhZBkOzMAu9X2OsPmxY7kDxr8O8", // Firefox 43+
                "payload" => json_encode($content),
                "userPublicKey" => "BC8UD2l0pBKZzAz-4f8RmtV0uSTaKMEnaZnPFzjNGhyQTQ_mJBB_wjLObNmFr9sBs6nQiTDvj6wI89_uNPe8MiA", // base 64 encoded, should be 88 chars
                "userAuthToken" => "SqG-iE1va_Pb6qODqP6CiQ", // base 64 encoded, should be 24 chars
            ],
        ];
        /*$notifications = [
            [
                "endpoint" => "https://fcm.googleapis.com/fcm/send/cVzysw4Es0o:APA91bH9NF5qkJFXWLZ0nx5ESY6GbCulSwLfdTfYcsI_6T7fJ2WoLNOSf6kPqqaE6Gb4Vdsp3QzD6rHTupNMNio3ZrTP0bqjqyyfist-i1r9PF1tRErqM0RINd9o0UOVWQtZBb9cOcb8", // Firefox 43+
                "payload" => json_encode($content),
                "userPublicKey" => "BMS3MunXzuAZAMm8SNzNiQeQ0ptNO105022uiRHyoqaI15Iv1QXzkB5biUalKddyD529aJ2Cenv7L3P6nlzAxZA=", // base 64 encoded, should be 88 chars
                "userAuthToken" => "9Dp_bRXQv2RcyIOBS1X26Q==", // base 64 encoded, should be 24 chars
            ],
        ];
        */
            
            
                
                
                
        /** @var \Minishlink\WebPush\WebPush */
        $webPush = $this->container->get('minishlink_web_push');
        // send multiple notifications with payload
        foreach ($notifications as $notification) {
            $webPush->sendNotification(
                $notification['endpoint'],
                $notification['payload'], // optional (defaults null)
                $notification['userPublicKey'], // optional (defaults null)
                $notification['userAuthToken'] // optional (defaults null)
            );
        }
        $fff = $webPush->flush();
        $logger = $this->get('logger');
        $logger->info($fff[0]['success']);
        // On redirige vers la page home
        return $this->redirectToRoute('deusi_platform_home', 
                [
                    'page' => 1
                ]
             );
    }
    
}

