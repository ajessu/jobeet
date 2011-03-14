<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\HttpFoundation;

use Symfony\Bundle\FrameworkBundle\HttpFoundation\SessionListener;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * SessionListenerTest.
 *
 * Tests SessionListener.
 *
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class SessionListenerTest extends \PHPUnit_Framework_TestCase
{
    private $listener;
    private $session;

    public function setUp()
    {
        $this->listener = new SessionListener();
        $this->session  = $this->getSession();
    }

    public function testShouldSaveMasterRequestSession()
    {
        $this->sessionMustBeSaved();

        $this->filterResponse(new Request());
    }

    public function testShouldNotSaveSubRequestSession()
    {
        $this->sessionMustNotBeSaved();

        $this->filterResponse(new Request(), HttpKernelInterface::SUB_REQUEST);
    }

    private function filterResponse(Request $request, $type = HttpKernelInterface::MASTER_REQUEST)
    {
        $request->setSession($this->session);
        $response = new Response();

        $this->assertSame($response, $this->listener->filter(new Event(
            $this, 'core.response', array('request' => $request, 'request_type' => $type)
        ), $response));
    }

    private function sessionMustNotBeSaved()
    {
        $this->session->expects($this->never())
            ->method('save');
    }

    private function sessionMustBeSaved()
    {
        $this->session->expects($this->once())
            ->method('save');
    }

    private function getSession()
    {
        return $this->getMockBuilder('Symfony\Component\HttpFoundation\Session')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
