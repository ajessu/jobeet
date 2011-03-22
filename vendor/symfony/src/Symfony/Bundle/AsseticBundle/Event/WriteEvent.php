<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Bernhard Schussek <bernhard.schussek@symfony-project.com>
 */
class WriteEvent extends Event
{
    private $targetPath;

    public function __construct($targetPath = null)
    {
        $this->targetPath = $targetPath;
    }

    public function getTargetPath()
    {
        return $this->targetPath;
    }
}
