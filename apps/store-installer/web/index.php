<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
use zenmagick\base\Application;
use zenmagick\http\Request;
$rootDir = realpath(__DIR__.'/../../..');
include_once $rootDir.'/autoload.php';

$config = array('context' => basename(dirname(__DIR__)));
$environment = isset($_SERVER['ZM_ENVIRONMENT']) ? $_SERVER['ZM_ENVIRONMENT'] : 'prod';
$application = new Application($environment, true, $config);
$request = new Request(); // @todo use createFromGlobals
$response = $application->handle($request);
$response->send();
$application->terminate($request, $response);
