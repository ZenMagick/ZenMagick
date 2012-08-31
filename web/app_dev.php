<?php
// @todo using filesystem ACLs is recommended, but what should we do by default?
//umask(0002); // This will let the permissions be 0775
umask(0000); // This will let the permissions be 0777

use ZenMagick\Http\Request;

// @todo .. this is just something to get started.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !in_array(@$_SERVER['REMOTE_ADDR'], array(
        '127.0.0.1',
        '::1',
    ))
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

//$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/AppKernel.php';

$application = new AppKernel('dev', true, 'admin');
$application->loadClassCache();
$request = Request::createFromGlobals();
$response = $application->handle($request);
$response->send();
$application->terminate($request, $response);
