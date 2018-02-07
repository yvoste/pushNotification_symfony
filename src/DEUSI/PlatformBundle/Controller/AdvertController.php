<?php

// src/DEUSI/PlatformBundle/Controller/AdvertController.php

namespace DEUSI\PlatformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DEUSI\PlatformBundle\Entity\Advert;
use DEUSI\PlatformBundle\Entity\Image;
use DEUSI\PlatformBundle\Entity\Application;
use DEUSI\PlatformBundle\Entity\Category;

class AdvertController extends Controller
{
    public function menuAction()
    {
      // On fixe en dur une liste ici, bien entendu par la suite
      // on la récupérera depuis la BDD !
      $listAdverts = array(
        array('id' => 2, 'title' => 'Recherche développeur Symfony'),
        array('id' => 5, 'title' => 'Mission de webmaster'),
        array('id' => 9, 'title' => 'Offre de stage webdesigner')
      );

      return $this->render('@DEUSIPlatform/Advert/menu.html.twig', array(
        // Tout l'intérêt est ici : le contrôleur passe
        // les variables nécessaires au template !
        'listAdverts' => $listAdverts
      ));
    }
    
    public function indexAction($page, $name)
    {
        // $name it's a relic of tests it's not used
        if($page < 1){
            // if $page is inferior to 1 this trigger an exception
            // an error page 404 appears that we can customized later
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');            
        }
        // Ici, on récupérera la liste des annonces, puis on la passera au template
        // Notre liste d'annonce en dur
        $listAdverts = array(
          array(
            'title'   => 'Recherche développpeur Symfony',
            'id'      => 1,
            'author'  => 'Alexandre',
            'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
            'date'    => new \Datetime()),
          array(
            'title'   => 'Mission de webmaster',
            'id'      => 2,
            'author'  => 'Hugo',
            'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
            'date'    => new \Datetime()),
          array(
            'title'   => 'Offre de stage webdesigner',
            'id'      => 3,
            'author'  => 'Mathieu',
            'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
            'date'    => new \Datetime())
        );
        // Mais pour l'instant, on ne fait qu'appeler le template
        return $this->render('@DEUSIPlatform/Advert/index.html.twig', array('listAdverts' => $listAdverts));        
    }   
     
    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /*
        // On récupère le repository
        $repository = $this->getDoctrine()
            ->getManager()
             ->getRepository('DEUSIPlatformBundle:Advert')
            ;

        // On récupère l'entité correspondante à l'id $id
         $advert = $repository->find($id);
         */
        // It's the samee as above but shorter
        $advert = $em->getRepository('DEUSIPlatformBundle:Advert')->find($id)
            ;
        // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
        // ou null si l'id $id  n'existe pas, d'où ce if :
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
        
        // On récupère la liste des candidatures de cette annonce
        $listApplications = $em->getRepository('DEUSIPlatformBundle:Application')
           ->findBy(array('advert' => $advert))
        ;
        // Le render ne change pas, on passait avant un tableau, maintenant un objet
        return $this->render('@DEUSIPlatform/Advert/view.html.twig', array(
            'advert' => $advert,
            'listApplications' => $listApplications
         ));
    }
    
    public function addAction(Request $request)
    {
        // Create entitie
        $advert = new Advert();
        $advert->setTitle('Recherche développeur Symfony.');
        $advert->setAuthor('Alexandre');
        $advert->setContent("Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…");
        //$advert->setContent("bla");
        
        // On peut ne pas définir ni la date ni la publication,
        // car ces attributs sont définis automatiquement dans le constructeur
        
         // Création de l'entité Image
        $image = new Image();
        $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
        $image->setAlt('Job de rêve');
        
        // On lie l'image à l'annonce
        $advert->setImage($image);
        
        // Création d'une première candidature
        $application1 = new Application();
        $application1->setAuthor('Marine');
        $application1->setContent("J'ai toutes les qualités requises.");

        // Création d'une deuxième candidature par exemple
        $application2 = new Application();
        $application2->setAuthor('Pierre');
        $application2->setContent("Je suis très motivé.");

        // On lie les candidatures à l'annonce
        $application1->setAdvert($advert);
        $application2->setAdvert($advert);

        // On récupère l'EntityManager
        $em = $this->getDoctrine()->getManager();

        // Étape 1 : On « persiste » l'entité
        $em->persist($advert);
        
         // Étape 1 bis : si on n'avait pas défini le cascade={"persist"},
        // on devrait persister à la main l'entité $image
        // $em->persist($image);
        
        // Étape 1 ter : pour cette relation pas de cascade lorsqu'on persiste Advert, car la relation est
        // définie dans l'entité Application et non Advert. On doit donc tout persister à la main ici.
        $em->persist($application1);
        $em->persist($application2);

        // Étape 2 : On « flush » tout ce qui a été persisté avant
        $em->flush();

      
        // On récupère le service pour checked si le contetn n'est pas un spam
        $antispam = $this->container->get('deusi_platform.antispam');
        
        if ($antispam->isSpam($advert->getContent())) {
          throw new \Exception('Votre message a été détecté comme spam !');
        }
    
    // Ici le message n'est pas un spam
        // La gestion d'un formulaire est particulière, mais l'idée est la suivante :

        // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
        if ($request->isMethod('POST')) {
            // Ici, on s'occupera de la création et de la gestion du formulaire

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            // Puis on redirige vers la page de visualisation de cettte annonce
            return $this->redirectToRoute('deusi_platform_view', array('advert_id' => $advert->getId()));
        }

        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('@DEUSIPlatform/Advert/add.html.twig', array('advert' => $advert));
        
    }
    
    public function editAction($id, Request $request)
    {
        // Ici, on récupérera l'annonce correspondante à $id
        $em = $this->getDoctrine()->getManager();

        // On récupère l'annonce $id
        $advert = $em->getRepository('DEUSIPlatformBundle:Advert')->find($id);
        
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // Même mécanisme que pour l'ajout
        if ($request->isMethod('POST')) {
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('deusi_platform_view', array('id' => 5));
        }

        // La méthode findAll retourne toutes les catégories de la base de données
        $listCategories = $em->getRepository('DEUSIPlatformBundle:Category')->findAll();
        
        // On boucle sur les catégories pour les lier à l'annonce
        /*
        foreach ($listCategories as $category) {
          $advert->addCategory($category);
        }
        */
        // Pour persister le changement dans la relation, il faut persister l'entité propriétaire
        // Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

        // Étape 2 : On déclenche l'enregistrement
        $em->flush();
        
        return $this->render('@DEUSIPlatform/Advert/edit.html.twig', array(
            'advert' => $advert
         ));
    }

    public function deleteAction($id)
    {
        // Ici, on récupérera l'annonce correspondant à $id

        // Ici, on gérera la suppression de l'annonce en question
         $advert = array(
            'id'      => $id
        );
        return $this->render('@DEUSIPlatform/Advert/delete.html.twig', array('advert' => $advert));
    }
}

/* PART ONE
 * class AdvertController extends Controller
{
     public function indexAction($page, $name)
    {
        return $this->render('@DEUSIPlatform/Advert/index.html.twig', array('nom' => 'Roger', 'name' => $name, 'advert_id' => 5, 'page' => $page));        
    }
    
    /* To watch the URL to the article where id == X
    * public function indexAction()
    *{     
    *   $url = $this->get('router')->generate(
    *       'deusi_platform_view', // 1er argument : le nom de la route
    *       array('id' => 5), // 2e argument : les valeurs des paramètres
    *      UrlGeneratorInterface::ABSOLUTE_URL  // URL absolue
    *   );
    * the same but shorter
    *   $url = $this->generateUrl(
    *       'deusi_platform_view', // 1er argument : le nom de la route
    *       array('id' => 5), // 2e argument : les valeurs des paramètres
    *       UrlGeneratorInterface::ABSOLUTE_URL  // URL absolue
    *   );
    *    // $url == « /platform/advert/5 »
    *    return new Response("The id 5 advertisement URL is : ".$url);
    }
    
     
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
        //
        // retreiving session
        //$session = $request->getSession();

        // Retrieving content of variable user_id
        //$userId = $session->get('user_id');

        // Define a new value for this variable
       // $session->set('user_id', 92);
        
        // Retrieving content of new variable user_id
       // $userNId = $session->get('user_id');

        // Don't forget to send the response
        //return new Response("<body>".$userId."Je suis une page de test, je n'ai rien à dire".$userNId."</body>");
        
        
        /*
        *    Method to redirect on another url
        *$url = $this->get('router')->generate('deusi_platform_home');  
        *    the same but shorter
        *$url = $this->redirectToRoute('deusi_platform_home');    
        *return new RedirectResponse($url);
        *
        */
        
        /* 
         *   Modify Content_Type in header to return a JSON instead of HTML
         *$response = new Response(json_encode(array('id' => $id)));
         *$response->headers->set('Content-Type', 'application/json');
         *return $reponse
         *   The same but shorter
         *return new JsonResponse(array('id' => $id));
         * 
         
    }
    
    public function addAction(Request $request)
    {
        //$name = 'bolos';
        //return $this->render('@DEUSIPlatform/Advert/index.html.twig', array('nom' => 'palu', 'name' => $name, 'advert_id' => 5));
        
        $session = $request->getSession();    
        // Method to really add the article
    
        // But let's do as if it were
        $session->getFlashBag()->add('info', 'Annonce bien enregistrée');

         // Le « flashBag » est ce qui contient les messages flash dans la session
        // Il peut bien sûr contenir plusieurs messages :
        $session->getFlashBag()->add('info', 'Oui oui, elle est bien enregistrée !');

        // Puis on redirige vers la page de visualisation de cette annonce
        return $this->redirectToRoute('deusi_platform_view', array('id' => 5));
        
    }
    
    // On récupère tous les paramètres en arguments de la méthode
    // l'underscore devant format en fait un paramétre systéme
    // ce qui implque que symfony va rajouter une entrée Content-Tytpe dans le Header de la réponse
    public function viewSlugAction($slug, $year, $_format)
    {
        return new Response(
            "On pourrait afficher l'annonce correspondant au
            slug '".$slug."', créée en ".$year." et au format ".$_format."."
        );
    }
}
 * 
 * 
 
 */