<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\WebProfilerBundle;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * WebDebugToolbarListener injects the Web Debug Toolbar.
 *
 * The handle method must be connected to the onCoreResponse event.
 *
 * The WDT is only injected on well-formed HTML (with a proper </body> tag).
 * This means that the WDT is never included in sub-requests or ESI requests.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class WebDebugToolbarListener
{
    protected $kernel;
    protected $templating;
    protected $interceptRedirects;

    public function __construct(HttpKernel $kernel, TwigEngine $templating, $interceptRedirects = false)
    {
        $this->kernel = $kernel;
        $this->templating = $templating;
        $this->interceptRedirects = $interceptRedirects;
    }

    public function onCoreResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $response = $event->getResponse();
	$request = $event->getRequest();

        if ($response->headers->has('X-Debug-Token') && $response->isRedirect() && $this->interceptRedirects) {
            // keep current flashes for one more request
            $request->getSession()->setFlashes($request->getSession()->getFlashes());

            $response->setContent(
                sprintf('<html><head></head><body><h1>This Request redirects to<br /><a href="%1$s">%1$s</a>.</h1><h4>The redirect was intercepted by the web debug toolbar to help debugging.<br/>For more information, see the "intercept-redirects" option of the Profiler.</h4></body></html>',
                $response->headers->get('Location'))
            );
            $response->setStatusCode(200);
            $response->headers->remove('Location');
        }

        if (!$response->headers->has('X-Debug-Token')
            || '3' === substr($response->getStatusCode(), 0, 1)
            || ($response->headers->has('Content-Type') && false === strpos($response->headers->get('Content-Type'), 'html'))
            || 'html' !== $request->getRequestFormat()
            || $request->isXmlHttpRequest()
        ) {
            return;
        }

        $this->injectToolbar($response);
    }

    /**
     * Injects the web debug toolbar into the given Response.
     *
     * @param Response $response A Response instance
     */
    protected function injectToolbar(Response $response)
    {
        if (function_exists('mb_stripos')) {
            $posrFunction = 'mb_strripos';
            $substrFunction = 'mb_substr';
        } else {
            $posrFunction = 'strripos';
            $substrFunction = 'substr';
        }

        $content = $response->getContent();

        if (false !== $pos = $posrFunction($content, '</body>')) {
            $toolbar = "\n".str_replace("\n", '', $this->templating->render('WebProfilerBundle:Profiler:toolbar_js.html.twig', array('token' => $response->headers->get('X-Debug-Token'))))."\n";
            $content = $substrFunction($content, 0, $pos).$toolbar.$substrFunction($content, $pos);
            $response->setContent($content);
        }
    }
}
