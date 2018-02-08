<?php

namespace DEUSI\PlatformBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Advert
 *
 * @ORM\Table(name="advert")
 * @ORM\Entity(repositoryClass="DEUSI\PlatformBundle\Repository\AdvertRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Advert
{
    /**
    * @ORM\OneToOne(targetEntity="DEUSI\PlatformBundle\Entity\Image", cascade={"persist"})
    */
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255)
     */
    private $author;
    
    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="published", type="boolean")
     */
    private $published = true;
    
    /**
     * @ORM\OneToOne(targetEntity="DEUSI\PlatformBundle\Entity\Image", cascade={"persist"})
     * 
    */
    private $image;
    
    /**
     * @ORM\ManyToMany(targetEntity="DEUSI\PlatformBundle\Entity\Category", cascade={"persist"})
     * 
    */
    private $categories;
    
    /**
     * @ORM\OneToMany(targetEntity="DEUSI\PlatformBundle\Entity\Application", mappedBy="advert")
     * 
     */
    private $applications; // Notez le « s », une annonce est liée à plusieurs candidatures
    
    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
    */
    private $updatedAt;
    
    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
    */
    private $slug;

    public function __construct()
    {
        $this->date = new\Datetime();
        $this->categories = new ArrayCollection();
        //$this->applications = new ArrayCollections();
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
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Advert
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Advert
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set author.
     *
     * @param string $author
     *
     * @return Advert
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return Advert
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set published.
     *
     * @param bool $published
     *
     * @return Advert
     */
    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    /**
     * Get published.
     *
     * @return bool
     */
    public function getPublished()
    {
        return $this->published;
    }    

    /**
     * Set image.
     *
     * @param \DEUSI\PlatformBundle\Entity\Image|null $image
     *
     * @return Advert
     */
    public function setImage(\DEUSI\PlatformBundle\Entity\Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image.
     *
     * @return \DEUSI\PlatformBundle\Entity\Image|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Add category.
     *
     * @param \DEUSI\PlatformBundle\Entity\Category $category
     *
     * @return Advert
     */
    public function addCategory(\DEUSI\PlatformBundle\Entity\Category $category)
    {
        $this->categories[] = $category;

        return $this;
    }

    /**
     * Remove category.
     *
     * @param \DEUSI\PlatformBundle\Entity\Category $category
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeCategory(\DEUSI\PlatformBundle\Entity\Category $category)
    {
        return $this->categories->removeElement($category);
    }

    /**
     * Get categories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Add application.
     *
     * @param \DEUSI\PlatformBundle\Entity\Application $application
     *
     * @return Advert
     */
    public function addApplication(\DEUSI\PlatformBundle\Entity\Application $application)
    {
        $this->applications[] = $application;
        
        // On lie l'annonce à la candidature
        $application->setAdvert($this);

        return $this;
    }

    /**
     * Remove application.
     *
     * @param \DEUSI\PlatformBundle\Entity\Application $application
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeApplication(\DEUSI\PlatformBundle\Entity\Application $application)
    {
        return $this->applications->removeElement($application);
    }

    /**
     * Get applications.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime|null $updatedAt
     *
     * @return Advert
     */
    public function setUpdatedAt($updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
    * @ORM\PreUpdate
    */
    public function updateDate()
    {
      $this->setUpdatedAt(new \Datetime());
    }
    

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return Advert
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return Advert
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
}
