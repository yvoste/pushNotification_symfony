<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace DEUSI\PlatformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdvertController extends Controller
{
     public function indexAction($page, $name)
    {
        return $this->render('@DEUSIPlatform/Advert/index.html.twig', array('nom' => 'Roger', 'name' => $name, 'advert_id' => 5, 'page' => $page));        
    }
    
    /*
    public function indexAction($page)
    {
        $name = 'bolos';
        return $this->render('@DEUSIPlatform/Advert/index.html.twig', array('nom' => 'Roger', 'name' => $name, 'advert_id' => 5, 'page' => $page));        
    }
    */
    /*
     public function indexAction()
    {
        return $this->render('@DEUSIPlatform/Advert/index.html.twig', array('nom' => 'Roger', 'name' => $name, 'advert_id' => 5));  
     *  // On veut avoir l'URL de l'annonce d'id 5.
        $url = $this->get('router')->generate(
            'deusi_platform_view', // 1er argument : le nom de la route
            array('id' => 5), // 2e argument : les valeurs des paramètres
            UrlGeneratorInterface::ABSOLUTE_URL  // URL absolue
        );
     * OU
     *  $url = $this->generateUrl(
            'deusi_platform_view', // 1er argument : le nom de la route
            array('id' => 5), // 2e argument : les valeurs des paramètres
            UrlGeneratorInterface::ABSOLUTE_URL  // URL absolue
        );
        // $url vaut « /platform/advert/5 »
        return new Response("L'URL de l'annonce d'id 5 est : ".$url);
    }
    */
     
    public function viewAction($id, Request $request)
    {
        // some methods around Request object
        $rep = [
            'nom' => $id,
            'name' => 'Bilou',
            'advert_id' => 5,
            'tag' => $request->query->get('tag'), 
            'accept' => $request->headers->get('Accept'), 
            'request' => $request,
            'encoding' => $request->headers->get('Accept-Encoding'),
            'lang' => $request->headers->get('Accept-Language'),
            'con' => $request->headers->get('Connection'),
            'host' => $request->headers->get('Host'),
            'upgrade' => $request->headers->get('Upgrade-Insecure-Requests'),
            'ua' => $request->headers->get('User-Agent'),
            'ser' => $request->server->get('REQUEST_URI'),
            'meth' => ($request->isMethod('GET')== true)? 'get':0,
            'metho' => ($request->isMethod('POST')== true)? 'post':0,
            'xmlHttp' => ($request->isXmlHttpRequest()== true)? 'xmlHttp':0
        ];
        return $this->render('@DEUSIPlatform/Advert/index.html.twig', $rep);
        // Method to redirect on another url
        //$url = $this->get('router')->generate('deusi_platform_home');  
        // the same but shorter
        //$url = $this->redirectToRoute('deusi_platform_home');    
        //return new RedirectResponse($url);         
    }
    
    public function addAction()
    {
        $name = 'bolos';
        return $this->render('@DEUSIPlatform/Advert/index.html.twig', array('nom' => 'palu', 'name' => $name, 'advert_id' => 5));        
    }
    
    // On récupère tous les paramètres en arguments de la méthode
    // l'underscore devant format en fait un paramétre systéme ce qui implque que symfony va rajouter une entrée Content-Tytpe dans le Header de la réponse
    public function viewSlugAction($slug, $year, $_format)
    {
        return new Response(
            "On pourrait afficher l'annonce correspondant au
            slug '".$slug."', créée en ".$year." et au format ".$_format."."
        );
    }
}