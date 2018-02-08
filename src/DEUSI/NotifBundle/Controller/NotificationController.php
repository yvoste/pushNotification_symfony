<?php
// src/DEUSI/NotifBundle/Controller/NotificationController.php

namespace DEUSI\NotifBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function indexAction()
    {
        return $this->render('@DEUSINotif/Notification/index.html.twig');
    }
}

