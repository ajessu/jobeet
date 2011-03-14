<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\Twig;

use Assetic\Extension\Twig\AsseticExtension;
use Assetic\Factory\AssetFactory;

/**
 * Assetic integration.
 *
 * @author Kris Wallsmith <kris.wallsmith@symfony.com>
 */
class DynamicExtension extends AsseticExtension
{
    public function getTokenParsers()
    {
        return array(
            new DynamicTokenParser($this->factory, $this->debug),
            new DynamicTokenParser($this->factory, $this->debug, $this->defaultJavascriptsOutput, 'javascripts'),
            new DynamicTokenParser($this->factory, $this->debug, $this->defaultStylesheetsOutput, 'stylesheets'),
        );
    }
}
