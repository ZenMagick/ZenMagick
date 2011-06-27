<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Tests\Component\Security\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\HttpUtils;

class HttpUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateRedirectResponse()
    {
        $utils = new HttpUtils($generator = $this->getUrlGenerator());

        // absolute path
        $response = $utils->createRedirectResponse($this->getRequest(), '/foobar');
        $this->assertTrue($response->isRedirect('http://localhost/foobar'));
        $this->assertEquals(302, $response->getStatusCode());

        // absolute URL
        $response = $utils->createRedirectResponse($this->getRequest(), 'http://symfony.com/');
        $this->assertTrue($response->isRedirect('http://symfony.com/'));

        // route name
        $utils = new HttpUtils($generator = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface'));
        $generator
            ->expects($this->any())
            ->method('generate')
            ->with('foobar', array(), true)
            ->will($this->returnValue('http://localhost/foo/bar'))
        ;
        $response = $utils->createRedirectResponse($this->getRequest(), 'foobar');
    }

    public function testCreateRequest()
    {
        $utils = new HttpUtils($this->getUrlGenerator());

        // absolute path
        $request = $this->getRequest();
        $request->server->set('Foo', 'bar');
        $subRequest = $utils->createRequest($request, '/foobar');

        $this->assertEquals('GET', $subRequest->getMethod());
        $this->assertEquals('/foobar', $subRequest->getPathInfo());
        $this->assertEquals('bar', $subRequest->server->get('Foo'));

        // route name
        $subRequest = $utils->createRequest($this->getRequest(), 'foobar');
        $this->assertEquals('/foo/bar', $subRequest->getPathInfo());

        // absolute URL
        $subRequest = $utils->createRequest($this->getRequest(), 'http://symfony.com/');
        $this->assertEquals('/', $subRequest->getPathInfo());
    }

    public function testCheckRequestPath()
    {
        $utils = new HttpUtils($this->getUrlGenerator());

        $this->assertTrue($utils->checkRequestPath($this->getRequest(), '/'));
        $this->assertFalse($utils->checkRequestPath($this->getRequest(), '/foo'));

        $this->assertFalse($utils->checkRequestPath($this->getRequest(), 'foobar'));
        $this->assertTrue($utils->checkRequestPath($this->getRequest('/foo/bar'), 'foobar'));
    }

    private function getUrlGenerator()
    {
        $generator = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $generator
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue('/foo/bar'))
        ;

        return $generator;
    }

    private function getRequest($path = '/')
    {
        return Request::create($path, 'get');
    }
}
