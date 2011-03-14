<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Config\Loader;

use Symfony\Component\Config\FileLocatorInterface;

/**
 * FileLoader is the abstract class used by all built-in loaders that are file based.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class FileLoader extends Loader
{
    protected $locator;
    protected $currentDir;

    /**
     * Constructor.
     */
    public function __construct(FileLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * Adds definitions and parameters from a resource.
     *
     * @param mixed   $resource     A Resource
     * @param string  $type         The resource type
     * @param Boolean $ignoreErrors Whether to ignore import errors or not
     *
     * @return mixed
     */
    public function import($resource, $type = null, $ignoreErrors = false)
    {
        try {
            $loader = $this->resolve($resource, $type);

            if ($loader instanceof FileLoader && null !== $this->currentDir) {
                $resource = $this->locator->locate($resource, $this->currentDir);
            }

            return $loader->load($resource);
        } catch (\Exception $e) {
            if (!$ignoreErrors) {
                throw $e;
            }
        }
    }
}
