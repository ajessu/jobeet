<?php

namespace Acme\JobeetBundle\Entity;

/**
 * @orm:Entity
 */
class Category
{
    /**
     * @orm:Id
     * @orm:Column(type="integer")
     * @orm:GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @orm:Column(type="string", length="255", unique=true)
     */
    protected $name;

    /**
     * @orm:Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * @orm:Column(type="datetime", name="expires_at")
     */
    protected $expiresAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }
}