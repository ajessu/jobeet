<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation;

/**
 * Represents a cookie
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Cookie
{
    protected $name;
    protected $value;
    protected $domain;
    protected $expire;
    protected $path;
    protected $secure;
    protected $httpOnly;

    public function __construct($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true)
    {
        // from PHP source code
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        if (preg_match("/[,; \t\r\n\013\014]/", $value)) {
            throw new \InvalidArgumentException(sprintf('The cookie value "%s" contains invalid characters.', $name));
        }

        if (empty($name)) {
            throw new \InvalidArgumentException('The cookie name cannot be empty');
        }

        // check if the expiration is valid
        if (!$expire instanceof \DateTime && !is_numeric($expire) && (strtotime($expire) === false || strtotime($expire) === -1)) {
            throw new \InvalidArgumentException('The cookie expiration is not valid');
        }

        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->expire = $expire;
        $this->path = $path;
        $this->secure = (Boolean) $secure;
        $this->httpOnly = (Boolean) $httpOnly;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function getExpire()
    {
        return $this->expire;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function isSecure()
    {
        return $this->secure;
    }

    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * Whether this cookie is about to be cleared
     *
     * @return Boolean
     */
    public function isCleared()
    {
        return $this->expire < time();
    }
}