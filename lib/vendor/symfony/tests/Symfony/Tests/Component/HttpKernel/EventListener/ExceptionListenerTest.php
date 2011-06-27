<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\HttpKernel\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Tests\Component\HttpKernel\Logger;

/**
 * ExceptionListenerTest
 *
 * @author Robert Schönthal <seroscho@googlemail.com>
 */
class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $logger = new TestLogger();
        $l = new ExceptionListener('foo', $logger);

        $_logger = new \ReflectionProperty(get_class($l), 'logger');
        $_logger->setAccessible(true);
        $_controller = new \ReflectionProperty(get_class($l), 'controller');
        $_controller->setAccessible(true);

        $this->assertSame($logger, $_logger->getValue($l));
        $this->assertSame('foo', $_controller->getValue($l));
    }

    /**
     * @dataProvider provider
     */
    public function testHandleWithoutLogger($event, $event2)
    {
        // store the current error_log, and disable it temporarily
        $errorLog = ini_set('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $l = new ExceptionListener('foo');
        $l->onKernelException($event);

        $this->assertEquals(new Response('foo'), $event->getResponse());

        try {
            $l->onKernelException($event2);
        } catch(\Exception $e) {
            $this->assertSame('foo', $e->getMessage());
        }

        // restore the old error_log
        ini_set('error_log', $errorLog);
    }

    /**
     * @dataProvider provider
     */
    public function testHandleWithLogger($event, $event2)
    {
        $logger = new TestLogger();

        $l = new ExceptionListener('foo', $logger);
        $l->onKernelException($event);

        $this->assertEquals(new Response('foo'), $event->getResponse());

        try {
            $l->onKernelException($event2);
        } catch(\Exception $e) {
            $this->assertSame('foo', $e->getMessage());
        }

        $this->assertEquals(3, $logger->countErrors());
        $this->assertEquals(3, count($logger->getLogs('crit')));
    }

    public function provider()
    {
        $request = new Request();
        $exception = new \Exception('foo');
        $event = new GetResponseForExceptionEvent(new TestKernel(), $request, 'foo', $exception);
        $event2 = new GetResponseForExceptionEvent(new TestKernelThatThrowsException(), $request, 'foo', $exception);

        return array(
            array($event, $event2)
        );
    }

}

class TestLogger extends Logger implements DebugLoggerInterface
{
    public function countErrors()
    {
        return count($this->logs['crit']);
    }
}

class TestKernel implements HttpKernelInterface
{
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        return new Response('foo');
    }

}

class TestKernelThatThrowsException implements HttpKernelInterface
{
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        throw new \Exception('bar');
    }
}
