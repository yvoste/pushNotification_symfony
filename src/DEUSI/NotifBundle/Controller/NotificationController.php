<?php
// src/DEUSI/NotifBundle/Controller/NotificationController.php

namespace DEUSI\NotifBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Minishlink\WebPush\WebPush;

class NotificationController extends Controller
{
    public function subscriptionAction(Request $request)
    {
        // On crée un objet Subscription
        $subscription = new \DEUSI\NotifBundle\Entity\Subscription();
        
        $sub = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($sub['endpoint'])) {
            return  new Response(json_encode(array('error' => true)));            
        }            
            
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($method) {
            case 'POST':
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
                break;
            case 'PUT':
                // update the key and token of subscription corresponding to the endpoint
                break;
            case 'DELETE':
                // delete the subscription corresponding to the endpoint
                break;
            default:
                return  new Response(json_encode(['error' => true, 'metohd' => 'No method']));  
        }
    }
    public function sendNotificationAction()
    {
        // array of notifications
        $notifications = array(
            array(
                'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/gAAAAABahvCNv0Z863cc1SUSQgvYKBYeBUJT77WOkwP4Yl0vHq1ceOon_lLcPJhwZwcfWpwaFI0NBH-c2PrGGh0Pi5KEaxe3gbmgrGxp9xjVES5duGPsYmyVLTbQA5H95UtpTQeTIaYKjARri__uCY03lVywWWO0ATAmBumBCH1vsV3lyb6aJLY', // Firefox 43+
                'payload' => 'MON DEUSI',
                'userPublicKey' => 'BCxky8eXvIpt3H74NLRpb7_wMmkEl8IO2O4a3YWYBZ9VfwFGcryCoCGXfl-l5beYskPhMTu-6Bl-2o5SJeUoLc0', // base 64 encoded, should be 88 chars
                'userAuthToken' => 'ajJl7eKX_lSNX7JeVKo37w', // base 64 encoded, should be 24 chars
            ),
        );
        
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
        $logger->info($fff[0]['message']);
        $logger->info($fff[0]['statusCode']);
        $logger->info($fff[0]['headers']);
        $logger->info($fff[0]['content']);
        $logger->info($fff[0]['expired']);
        // On redirige vers la page home
        return $this->redirectToRoute('deusi_platform_home', 
                [
                    'page' => 1
                ]
             );
    }
    
}

