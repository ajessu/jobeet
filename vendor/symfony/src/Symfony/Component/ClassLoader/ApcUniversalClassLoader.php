<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\ClassLoader;

require_once __DIR__.'/UniversalClassLoader.php';

/**
 * Class loader utilizing APC to remember where files are.
 *
 * @author Kris Wallsmith <kris.wallsmith@symfony.com>
 */
class ApcUniversalClassLoader extends UniversalClassLoader
{
    private $prefix;

    /**
     * Constructor.
     *
     * @param string $prefix A prefix to create a namespace in APC
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    protected function findFile($class)
    {
        if (false === $file = apc_fetch($this->prefix.$class)) {
            apc_store($this->prefix.$class, $file = parent::findFile($class));
        }

        return $file;
    }
}
