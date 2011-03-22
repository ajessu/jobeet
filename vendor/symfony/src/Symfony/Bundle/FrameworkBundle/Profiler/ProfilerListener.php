<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Profiler;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ProfilerListener collects data for the current request by listening to the onCoreResponse event.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ProfilerListener
{
    protected $container;
    protected $exception;
    protected $onlyException;
    protected $matcher;

    /**
     * Constructor.
     *
     * @param ContainerInterface      $container     A ContainerInterface instance
     * @param RequestMatcherInterface $matcher       A RequestMatcher instance
     * @param Boolean                 $onlyException true if the profiler only collects data when an exception occurs, false otherwise
     */
    public function __construct(ContainerInterface $container, RequestMatcherInterface $matcher = null, $onlyException = false)
    {
        $this->container = $container;
        $this->matcher = $matcher;
        $this->onlyException = $onlyException;
    }

    /**
     * Handles the onCoreRequest event
     *
     * This method initialize the profiler to be able to get it as a scoped
     * service when onCoreResponse() will collect the sub request
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        $this->container->get('profiler');
    }

    /**
     * Handles the onCoreException event.
     *
     * @param GetResponseForExceptionEvent $event A GetResponseForExceptionEvent instance
     */
    public function onCoreException(GetResponseForExceptionEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $this->exception = $event->getException();
    }

    /**
     * Handles the onCoreResponse event.
     *
     * @param FilterResponseEvent $event A FilterResponseEvent instance
     */
    public function onCoreResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        if (null !== $this->matcher && !$this->matcher->matches($event->getRequest())) {
            return $response;
        }

        if ($this->onlyException && null === $this->exception) {
            return;
        }

        $profiler = $this->container->get('profiler');

        if ($parent = $this->container->getCurrentScopedStack('request')) {
            $profiler->setParent($parent['request']['profiler']->getToken());
        }

        $profiler->collect($event->getRequest(), $event->getResponse(), $this->exception);
        $this->exception = null;
    }
}
