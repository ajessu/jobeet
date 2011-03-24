<?php

namespace SfTuts\JobeetBundle\Entity;

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
     * @orm:Column(type="boolean", name="is_public")
     */
    protected $isPublic;

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
     * @orm:Column(type="datetime", name="updated_at")
     */
    protected $updatedAt;

    /**
     * @orm:Column(type="datetime", name="expires_at")
     */
    protected $expiresAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set company
     *
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * Get company
     *
     * @return string $company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set logo
     *
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * Get logo
     *
     * @return string $logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set position
     *
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return string $position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set location
     *
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Get location
     *
     * @return string $location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set howToApply
     *
     * @param string $howToApply
     */
    public function setHowToApply($howToApply)
    {
        $this->howToApply = $howToApply;
    }

    /**
     * Get howToApply
     *
     * @return string $howToApply
     */
    public function getHowToApply()
    {
        return $this->howToApply;
    }

    /**
     * Set token
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Get token
     *
     * @return string $token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set isPublic
     *
     * @param boolean $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * Get isPublic
     *
     * @return boolean $isPublic
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set isActivated
     *
     * @param boolean $isActivated
     */
    public function setIsActivated($isActivated)
    {
        $this->isActivated = $isActivated;
    }

    /**
     * Get isActivated
     *
     * @return boolean $isActivated
     */
    public function getIsActivated()
    {
        return $this->isActivated;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updatedAt
     *
     * @return datetime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set expiresAt
     *
     * @param datetime $expiresAt
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;
    }

    /**
     * Get expiresAt
     *
     * @return datetime $expiresAt
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Set category
     *
     * @param SfTuts\JobeetBundle\Entity\Category $category
     */
    public function setCategory(\SfTuts\JobeetBundle\Entity\Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get category
     *
     * @return SfTuts\JobeetBundle\Entity\Category $category
     */
    public function getCategory()
    {
        return $this->category;
    }
}