<?php

namespace DEUSI\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class newController extends Controller
{
    public function indexAction()
    {
        return $this->render('@DEUSIPlatform/Default/indexa.html.twig');
    }

    public function index2Action()
    {
        return $this->render('@DEUSIPlatform/Default/indexa.html.twig');
    }
}
