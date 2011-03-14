<?php

namespace Acme\JobeetBundle\Entity;

/**
 * @orm:Entity
 */
class Job
{
    /**
     * @orm:Id
     * @orm:Column(type="integer")
     * @orm:GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @orm:ManyToOne(targetEntity="Category")
     * @orm:JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @orm:Column(type="string", length="255")
     */
    protected $type;

    /**
     * @orm:Column(type="string", length="255", nullable=true)
     */
    protected $company;

    /**
     * @orm:Column(type="string", length="255")
     */
    protected $logo;

    /**
     * @orm:Column(type="string", length="255", nullable=true)
     */
    protected $url;

    /**
     * @orm:Column(type="string", length="255", nullable=true)
     */
    protected $position;

    /**
     * @orm:Column(type="string", length="255")
     */
    protected $location;

    /**
     * @orm:Column(type="string", length="4000")
     */
    protected $description;

    /**
     * @orm:Column(type="string", length="4000", name="how_to_apply")
     */
    protected $howToApply;

    /**
     * @orm:Column(type="string", length="255", unique=true)
     */
    protected $token;

    /**
     * @orm:Column(type="boolean")
     */
    protected $is_public;

    /**
     * @orm:Column(type="boolean", name="is_activated")
     */
    protected $isActivated;

    /**
     * @orm:Column(type="string", length="255")
     */
    protected $email;

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