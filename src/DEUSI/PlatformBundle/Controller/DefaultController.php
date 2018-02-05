<?php

namespace DEUSI\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('@DEUSIPlatform/Default/index.html.twig');
    }
}
