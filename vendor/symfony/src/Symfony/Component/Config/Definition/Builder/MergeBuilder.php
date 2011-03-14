<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Definition\Builder;

/**
 * This class builds merge conditions.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class MergeBuilder
{
    public $parent;
    public $allowFalse;
    public $allowOverwrite;

    /**
     * Constructor
     *
     * @param Symfony\Component\Config\Definition\Builder\NodeBuilder $parent The parent node
     */
    public function __construct($parent)
    {
        $this->parent = $parent;
        $this->allowFalse = false;
        $this->allowOverwrite = true;
    }

    /**
     * Sets whether the node can be unset.
     *
     * @param boolean $allow
     * @return Symfony\Component\Config\Definition\Builder\MergeBuilder
     */
    public function allowUnset($allow = true)
    {
        $this->allowFalse = $allow;

        return $this;
    }

    /**
     * Sets whether the node can be overwritten.
     *
     * @param boolean $deny Whether the overwriting is forbidden or not
     *
     * @return Symfony\Component\Config\Definition\Builder\MergeBuilder
     */
    public function denyOverwrite($deny = true)
    {
        $this->allowOverwrite = !$deny;

        return $this;
    }

    /**
     * Returns the parent node.
     *
     * @return Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function end()
    {
        return $this->parent;
    }
}