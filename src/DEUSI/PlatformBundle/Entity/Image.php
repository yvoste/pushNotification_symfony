<?php

namespace DEUSI\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Image
 *
 * @ORM\Table(name="image")
 * @ORM\Entity(repositoryClass="DEUSI\PlatformBundle\Repository\ImageRepository")
 */
class Image
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;
    
    /**
     * @var alt|null
     *
     * @ORM\Column(name="alt", type="string", length=255, nullable=true)
     */
    private $alt;

     private $file;
    
    public function upload()
    {
      // Si jamais il n'y a pas de fichier (champ facultatif), on ne fait rien
      if (null === $this->file) {
        return;
      }

      // On récupère le nom original du fichier de l'internaute
      $name = $this->file->getClientOriginalName();

      // On déplace le fichier envoyé dans le répertoire de notre choix
      $this->file->move($this->getUploadRootDir(), $name);

      // On sauvegarde le nom de fichier dans notre attribut $url
      $this->url = $this->getUploadDir();

      // On crée également le futur attribut alt de notre balise <img>
      $this->alt = $name;
    }

    public function getUploadDir()
    {
      // On retourne le chemin relatif vers l'image pour un navigateur (relatif au répertoire /web donc)
      return 'uploads/img';
    }

    protected function getUploadRootDir()
    {
      // On retourne le chemin relatif vers l'image pour notre code PHP
      return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }
     
    public function getFile()
    {
      return $this->file;
    }

    public function setFile(UploadedFile $file = null)
    {
      $this->file = $file;
    }
    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url.
     *
     * @param string|null $url
     *
     * @return Image
     */
    public function setUrl($url = null)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set alt.
     *
     * @param string|null $alt
     *
     * @return Image
     */
    public function setAlt($alt = null)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt.
     *
     * @return string|null
     */
    public function getAlt()
    {
        return $this->alt;
    }
}
