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
        'fe80::1',
        '::1',
    ))
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

//$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/AppKernel.php';

// @todo remove context from here altogether!
$context = 'storefront';
if (0 === strpos($_SERVER['REQUEST_URI'], '/admin') || 0 === strpos($_SERVER['REQUEST_URI'], '/app_dev.php/admin')) {
    $context = 'admin';
}
$kernel = new AppKernel('dev', true, $context);
//kernel->loadClassCache();
Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
