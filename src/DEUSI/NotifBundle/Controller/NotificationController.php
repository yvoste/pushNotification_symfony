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
        $content = [
                'title'=>'Mon title',
                'message'=>'Simple piece of body text.\nSecond line of body text\nThird line of body text',
                'icon'=>'/pushNotification_symfony/web/icon/iconO.png',
                'image'=> 'http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg', // OPTIONAL displaying only with chrome
                'url'=> 'http://artepole.com', // OPTIONAL when the user clicks the notification this opens a new window to the URL
                'tag'=> 'group_1',  // OPTIONAL each message send with this tag replace the previous message sending  with the same tag
                'options'=>'{"action_0":{"action":"action1","title":"Coffee","icon":"/pushNotification_symfony/web/icon/icon-48.png"}, "action_1":{"action":"action2","title":"PLate","icon":"/pushNotification_symfony/web/icon/icon-48.png"}}'
                    
            ];
        // array of notifications
        $notifications = [
            [
                "endpoint" => "https://updates.push.services.mozilla.com/wpush/v2/gAAAAABajGIcy48LDL7EgA97MlQa8wxUdlNiakIzMURFGwUJSwbcs-jCziqW5B7khZC8BtQehpkpjja-BTeGlK8R_73zZ8rb1ysjKu6otkNgetk-45gxmDiTZXmxJ2BdZbVkKQ3q1BviIdIMR1_sX2mjPdEp-SlbzTxvSmIEXDcqfSAzGRVTjLQ", // Firefox 43+
                "payload" => json_encode($content),
                "userPublicKey" => "BGU9St01TwQYPo1Hv3Xx-5J8Cv4F2ctaXMPLdzabHGIoFoSuTzJD72_KfPIhY7yNE02jMhX3Mdkt7WZGqPT2LqU", // base 64 encoded, should be 88 chars
                "userAuthToken" => "RqhCqUCVba1s9dKCwjgU-A", // base 64 encoded, should be 24 chars
            ],
        ];
        $notifications1 = [
            [
                "endpoint" => "https://fcm.googleapis.com/fcm/send/dXLQTN1Cjq0:APA91bEktvZZHiLGfbhuBMZp-5GhQTGEmSbnWVklQSYmfvIlQx1n7YgCEfg_JocUaG4p35zQFmAUySm2-Cnxuc7M440bv2nrQ_ROuBOldJpwy5z9wz3DoPF_cL1ExN6wUEkhILOJ-FVJ", // Firefox 43+
                "payload" => json_encode($content),
                "userPublicKey" => "BAz3SGr5v-jATK6w52WxaPbIzopdi83ZtK1FkCbHS7ibjGv8fkQxDNiS1cfaO52Lg7YjV2kvvNvQEh4-Ax8Q6zA=", // base 64 encoded, should be 88 chars
                "userAuthToken" => "x95Qv-6WezZ4zS1AOTJ-qA==", // base 64 encoded, should be 24 chars
            ],
        ];
       
            
            
                
                
                
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

