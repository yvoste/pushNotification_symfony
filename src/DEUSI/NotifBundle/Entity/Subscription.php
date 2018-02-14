<?php

namespace DEUSI\NotifBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subscription
 *
 * @ORM\Table(name="subscription")
 * @ORM\Entity(repositoryClass="DEUSI\NotifBundle\Repository\SubscriptionRepository")
 */
class Subscription
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
     * @var string
     *
     * @ORM\Column(name="endpoint", type="string", length=255)
     */
    private $endpoint;

    /**
     * @var string
     *
     * @ORM\Column(name="expirationTime", type="string", length=255)
     */
    private $expirationTime;

    /**
     * @var string
     *
     * @ORM\Column(name="key_p256dh", type="string", length=255)
     */
    private $keyP256dh;

    /**
     * @var string
     *
     * @ORM\Column(name="key_auth", type="string", length=255)
     */
    private $keyAuth;


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
     * Set endpoint.
     *
     * @param string $endpoint
     *
     * @return Subscription
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Get endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Set expirationTime.
     *
     * @param string $expirationTime
     *
     * @return Subscription
     */
    public function setExpirationTime($expirationTime)
    {
        $this->expirationTime = $expirationTime;

        return $this;
    }

    /**
     * Get expirationTime.
     *
     * @return string
     */
    public function getExpirationTime()
    {
        return $this->expirationTime;
    }

    /**
     * Set keyP256dh.
     *
     * @param string $keyP256dh
     *
     * @return Subscription
     */
    public function setKeyP256dh($keyP256dh)
    {
        $this->keyP256dh = $keyP256dh;

        return $this;
    }

    /**
     * Get keyP256dh.
     *
     * @return string
     */
    public function getKeyP256dh()
    {
        return $this->keyP256dh;
    }

    /**
     * Set keyAuth.
     *
     * @param string $keyAuth
     *
     * @return Subscription
     */
    public function setKeyAuth($keyAuth)
    {
        $this->keyAuth = $keyAuth;

        return $this;
    }

    /**
     * Get keyAuth.
     *
     * @return string
     */
    public function getKeyAuth()
    {
        return $this->keyAuth;
    }
}
