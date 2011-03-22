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
}