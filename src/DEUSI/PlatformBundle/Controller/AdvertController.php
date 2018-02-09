<?php

// src/DEUSI/PlatformBundle/Controller/AdvertController.php

namespace DEUSI\PlatformBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DEUSI\PlatformBundle\Form\AdvertType;
use DEUSI\PlatformBundle\Form\AdvertEditType;
use DEUSI\PlatformBundle\Form\ImageType;
use DEUSI\PlatformBundle\Form\CategoryType;
use DEUSI\PlatformBundle\Entity\Advert;
use DEUSI\PlatformBundle\Entity\Image;
use DEUSI\PlatformBundle\Entity\Application;
use DEUSI\PlatformBundle\Entity\Category;
use DEUSI\PlatformBundle\Entity\AdvertSkill;



class AdvertController extends Controller
{
    public function menuAction($limit)
    {
        $em = $this->getDoctrine()->getManager();

        $listAdverts = $em->getRepository('DEUSIPlatformBundle:Advert')->findBy(
          array(),                 // Pas de critère
          array('date' => 'desc'), // On trie par date décroissante
          $limit,                  // On sélectionne $limit annonces
          0                        // À partir du premier
        );

        return $this->render('@DEUSIPlatform/Advert/menu.html.twig', array(
          'listAdverts' => $listAdverts
        ));
    }
    
    public function indexAction($page)
    {
        if ($page < 1) {
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }

        // Ici je fixe le nombre d'annonces par page à 3
        // Mais bien sûr il faudrait utiliser un paramètre, et y accéder via $this->container->getParameter('nb_per_page')
        $nbPerPage = 1;

        // On récupère notre objet Paginator
        $listAdverts = $this->getDoctrine()
          ->getManager()
          ->getRepository('DEUSIPlatformBundle:Advert')
          ->getAdverts($page, $nbPerPage)
        ;

        // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
        $nbPages = ceil(count($listAdverts) / $nbPerPage);

        // Si la page n'existe pas, on retourne une 404
        if ($page > $nbPages) {
          throw $this->createNotFoundException("La page ".$page." n'existe pas.");
        }

        // On donne toutes les informations nécessaires à la vue
        return $this->render('@DEUSIPlatform/Advert/index.html.twig', array(
          'listAdverts' => $listAdverts,
          'nbPages'     => $nbPages,
          'page'        => $page,
        ));
    }   
     
    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        // Pour récupérer une seule annonce, on utilise la méthode find($id)
        $advert = $em->getRepository('DEUSIPlatformBundle:Advert')->find($id);

        // $advert est donc une instance de OC\PlatformBundle\Entity\Advert
        // ou null si l'id $id n'existe pas, d'où ce if :
        if (null === $advert) {
          throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        // Récupération de la liste des candidatures de l'annonce
        $listApplications = $em
          ->getRepository('DEUSIPlatformBundle:Application')
          ->findBy(array('advert' => $advert))
        ;

        // Récupération des AdvertSkill de l'annonce
        $listAdvertSkills = $em
          ->getRepository('DEUSIPlatformBundle:AdvertSkill')
          ->findBy(array('advert' => $advert))
        ;

        return $this->render('@DEUSIPlatform/Advert/view.html.twig', array(
          'advert'           => $advert,
          'listApplications' => $listApplications,
          'listAdvertSkills' => $listAdvertSkills,
        ));
    }
    
    public function addAction(Request $request)
    {
            // On crée un objet Advert
        $advert = new Advert();
        
        // Ici, on préremplit avec la date d'aujourd'hui, par exemple
        // Cette date sera donc préaffichée dans le formulaire, cela facilite le travail de l'utilisateur
        $advert->setDate(new \Datetime());
        //$form   = $this->get('form.factory')->create(AdvertType::class, $advert);
        $form   = $this->createForm(AdvertType::class, $advert);
/*
        // J'ai raccourci cette partie, car c'est plus rapide à écrire !
        $form = $this->get('form.factory')->createBuilder(FormType::class, $advert)
          ->add('date',      DateType::class)
          ->add('title',     TextType::class)
          ->add('content',   TextareaType::class)
          ->add('author',    TextType::class)
          ->add('published', CheckboxType::class, array('required' => false))
          ->add('save',      SubmitType::class)
          ->getForm()
        ;
*/
        // Si la requête est en POST
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid())
        {          
            // c'est elle qui déplace l'image là où on veut les stocker
            $advert->getImage()->upload();
            // On enregistre notre objet $advert dans la base de données, par exemple
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->persist($advert->getImage());
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            // On redirige vers la page de visualisation de l'annonce nouvellement créée
            return $this->redirectToRoute('deusi_platform_view', array('id' => $advert->getId()));
          
        }

        // À ce stade, le formulaire n'est pas valide car :
        // - Soit la requête est de type GET, donc le visiteur vient d'arriver sur la page et veut voir le formulaire
        // - Soit la requête est de type POST, mais le formulaire contient des valeurs invalides, donc on l'affiche de nouveau
        return $this->render('@DEUSIPlatform/Advert/add.html.twig', array(
          'form' => $form->createView(),
        ));
      
        
    }
    
    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('DEUSIPlatformBundle:Advert')->find($id);

        if (null === $advert) {
          throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }

        $form = $this->createForm(AdvertEditType::class, $advert);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            // Inutile de persister ici, Doctrine connait déjà notre annonce
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

          return $this->redirectToRoute('deusi_platform_view', ['id' => $advert->getId()]);
        }

        return $this->render('@DEUSIPlatform/Advert/edit.html.twig', 
                [
                    'advert' => $advert,
                    'form' => $form->createView(),
                ]
        );
    }

    public function deleteAction(Request $request, $id)
    {
          $em = $this->getDoctrine()->getManager();

          $advert = $em->getRepository('DEUSIPlatformBundle:Advert')->find($id);

          if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
          }

          // On crée un formulaire vide, qui ne contiendra que le champ CSRF
          // Cela permet de protéger la suppression d'annonce contre cette faille
          $form = $this->get('form.factory')->create();

          if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->remove($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

            return $this->redirectToRoute('deusi_platform_home');
          }

          return $this->render('DEUSIPlatform/Advert/delete.html.twig', array(
            'advert' => $advert,
            'form'   => $form->createView(),
          ));
    }
    public function testAction()
    {
      $advert = new Advert;

      $advert->setDate(new \Datetime());  // Champ « date » OK
      $advert->setTitle('abc');           // Champ « title » incorrect : moins de 10 caractères
      //$advert->setContent('blabla');    // Champ « content » incorrect : on ne le définit pas
      $advert->setAuthor('A');            // Champ « author » incorrect : moins de 2 caractères

      // On récupère le service validator
      $validator = $this->get('validator');

      // On déclenche la validation sur notre object
      $listErrors = $validator->validate($advert);

      // Si $listErrors n'est pas vide, on affiche les erreurs
      if(count($listErrors) > 0) {
        // $listErrors est un objet, sa méthode __toString permet de lister joliement les erreurs
        return new Response((string) $listErrors);
      } else {
        return new Response("L'annonce est valide !");
      }
    }
       
    
    
}

