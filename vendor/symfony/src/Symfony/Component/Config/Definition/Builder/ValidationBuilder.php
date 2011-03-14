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
 * This class builds validation conditions.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class ValidationBuilder
{
    public $parent;
    public $rules;

    /**
     * Constructor
     *
     * @param Symfony\Component\Config\Definition\Builder\NodeBuilder $parent
     */
    public function __construct($parent)
    {
        $this->parent = $parent;

        $this->rules = array();
    }

    /**
     * Registers a closure to run as normalization or an expression builder to build it if null is provided.
     *
     * @param \Closure $closure
     *
     * @return Symfony\Component\Config\Definition\Builder\ExprBuilder|Symfony\Component\Config\Definition\Builder\ValidationBuilder
     */
    public function rule(\Closure $closure = null)
    {
        if (null !== $closure) {
            $this->rules[] = $closure;

            return $this;
        }

        return $this->rules[] = new ExprBuilder($this->parent);
    }
}