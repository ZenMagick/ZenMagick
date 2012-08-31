<?php
// @todo using filesystem ACLs is recommended, but what should we do by default?
//umask(0002); // This will let the permissions be 0775
umask(0000); // This will let the permissions be 0777

use ZenMagick\Http\Request;

//$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/AppKernel.php';

$application = new AppKernel('prod', false, 'admin');
$application->loadClassCache();
$request = Request::createFromGlobals();
$response = $application->handle($request);
$response->send();
$application->terminate($request, $response);
