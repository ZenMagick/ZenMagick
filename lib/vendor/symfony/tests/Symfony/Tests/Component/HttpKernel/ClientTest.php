<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Tests\Component\HttpKernel;

use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\File\UploadedFile;

require_once __DIR__.'/TestHttpKernel.php';

class TestClient extends Client
{
    protected function getScript($request)
    {
        $script = parent::getScript($request);

        $script = preg_replace('/(\->register\(\);)/', "$0\nrequire_once '".__DIR__."/TestHttpKernel.php';", $script);

        return $script;
    }
}

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testDoRequest()
    {
        $client = new Client(new TestHttpKernel());

        $client->request('GET', '/');
        $this->assertEquals('Request: /', $client->getResponse()->getContent(), '->doRequest() uses the request handler to make the request');

        $client->request('GET', 'http://www.example.com/');
        $this->assertEquals('Request: /', $client->getResponse()->getContent(), '->doRequest() uses the request handler to make the request');
        $this->assertEquals('www.example.com', $client->getRequest()->getHost(), '->doRequest() uses the request handler to make the request');

        $client->request('GET', 'http://www.example.com/?parameter=http://google.com');
        $this->assertEquals('http://www.example.com/?parameter='.urlencode('http://google.com'), $client->getRequest()->getUri(), '->doRequest() uses the request handler to make the request');
    }

    public function testGetScript()
    {
        $client = new TestClient(new TestHttpKernel());
        $client->insulate();
        $client->request('GET', '/');

        $this->assertEquals('Request: /', $client->getResponse()->getContent(), '->getScript() returns a script that uses the request handler to make the request');
    }

    public function testFilterResponseConvertsCookies()
    {
        $client = new Client(new TestHttpKernel());

        $r = new \ReflectionObject($client);
        $m = $r->getMethod('filterResponse');
        $m->setAccessible(true);

        $response = new Response();
        $response->headers->setCookie(new Cookie('foo', 'bar', \DateTime::createFromFormat('j-M-Y H:i:s T', '15-Feb-2009 20:00:00 GMT')->format('U'), '/foo', 'http://example.com', true, true));
        $domResponse = $m->invoke($client, $response);
        $this->assertEquals('foo=bar; expires=Sun, 15-Feb-2009 20:00:00 GMT; domain=http://example.com; path=/foo; secure; httponly', $domResponse->getHeader('Set-Cookie'));

        $response = new Response();
        $response->headers->setCookie(new Cookie('foo', 'bar', \DateTime::createFromFormat('j-M-Y H:i:s T', '15-Feb-2009 20:00:00 GMT')->format('U'), '/foo', 'http://example.com', true, true));
        $response->headers->setCookie(new Cookie('foo1', 'bar1', \DateTime::createFromFormat('j-M-Y H:i:s T', '15-Feb-2009 20:00:00 GMT')->format('U'), '/foo', 'http://example.com', true, true));
        $domResponse = $m->invoke($client, $response);
        $this->assertEquals('foo=bar; expires=Sun, 15-Feb-2009 20:00:00 GMT; domain=http://example.com; path=/foo; secure; httponly, foo1=bar1; expires=Sun, 15-Feb-2009 20:00:00 GMT; domain=http://example.com; path=/foo; secure; httponly', $domResponse->getHeader('Set-Cookie'));
    }

    public function testUploadedFile()
    {
        $source = tempnam(sys_get_temp_dir(), 'source');
        $target = sys_get_temp_dir().'/sf.moved.file';
        @unlink($target);

        $kernel = new TestHttpKernel();
        $client = new Client($kernel);

        $client->request('POST', '/', array(), array(new UploadedFile($source, 'original', 'mime/original', 123, UPLOAD_ERR_OK)));

        $files = $kernel->request->files->all();

        $this->assertEquals(1, count($files));

        $file = $files[0];

        $this->assertEquals('original', $file->getClientOriginalName());
        $this->assertEquals('mime/original', $file->getClientMimeType());
        $this->assertEquals('123', $file->getClientSize());
        $this->assertTrue($file->isValid());

        $file->move(dirname($target), basename($target));

        $this->assertFileExists($target);
        unlink($target);
    }
}
