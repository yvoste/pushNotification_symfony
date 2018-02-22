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
        $date = date("Y-m-d H:i:s");;
        $content = [
                'title'=>'Mon title',
                'message'=>'Simple piece of body text.'."\n".'Second line of body text'."\n".'Third line of body text',
                'icon'=>'/pushNotification_symfony/web/icon/iconO.png',
                'image'=> 'http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg', // OPTIONAL displaying only with CHROME
                'url'=> 'http://artepole.com', // OPTIONAL when the user clicks the notification this opens a new window to the URL
                'tag'=> 'group_1',  // OPTIONAL each message send with this tag replace the previous message sending  with the same tag
                'options'=>'{"action_0":{'
                                . '"action":"action1",'
                                . '"title":"Add an advert",'
                                . '"icon":"/pushNotification_symfony/web/icon/icon-48.png"'
                            . '}, '
                            . '"action_1":{'
                                . '"action":"action2",'
                                . '"title":"PLate",'
                                 . '"icon":"/pushNotification_symfony/web/icon/icon-48.png"'
                            . '}'
                        . '}',  // ONLY CHROME
                'requiereInteraction' => 1,   // ONLY CHROME this imply the user must be interact for closing the notification (by default is false)
                'datas' => '{"dot":{'
                                . '"time": "'.$date.'",'
                                . '"message": "My message in data!"'
                                . '}'
                            . '}'
   
        ];
        // find objet
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Subscription::class);
        $subscriptions = $repo->findAll();
        
        $notifications = [];
        foreach($subscriptions as $subscription){
            $notifications[] = [
                "endpoint" => $subscription->getEndpoint(), // Firefox 43+
                "payload" => json_encode($content),
                "userPublicKey" => $subscription->getKeyP256dh(), // base 64 encoded, should be 88 chars
                "userAuthToken" => $subscription->getKeyAuth(), // base 64 encoded, should be 24 chars
            ];         
        }          
                
        /** @var \Minishlink\WebPush\WebPush */
        $webPush = $this->container->get("minishlink_web_push");
        //$webPush = new Webpush();
        // send multiple notifications with payload
        foreach ($notifications as $notification) {
            $webPush->sendNotification(
                $notification['endpoint'],
                $notification['payload'], // optional (defaults null)
                $notification['userPublicKey'], // optional (defaults null)
                $notification['userAuthToken'] // optional (defaults null)
            );
        }
        $webPush->flush();
        //$logger = $this->get('logger');
        //$logger->info($fff[0]['success']);
        // On redirige vers la page home
        return $this->redirectToRoute('deusi_platform_home', 
                [
                    'page' => 1
                ]
             );
    }
    
}

