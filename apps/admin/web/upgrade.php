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
use zenmagick\http\HttpApplication;
use zenmagick\base\Runtime;
$rootDir = realpath(__DIR__.'/../../..');
include_once $rootDir.'/lib/base/Application.php';
include_once $rootDir.'/lib/http/HttpApplication.php';

$config = array('appName' => basename(dirname(__DIR__)), 'environment' => (isset($_SERVER['ZM_ENVIRONMENT']) ? $_SERVER['ZM_ENVIRONMENT'] : 'prod'));
$application = new HttpApplication($config);
$application->bootstrap(array('init'));
$installer = new zenmagick\apps\store\admin\installation\InstallationPatcher();

$messageService = Runtime::getContainer()->get('messageService');
// Get DB Config first!
$status = $installer->getPatchForId('importZencartConfigure')->patch();

$patches = array('importZencartConfigure', 'sqlConfig','sacsPermissions', 'sqlAdminRoles', 'sqlAdminPrefs'); 
foreach ($patches as $patch) {
    $patchObj = $installer->getPatchForId($patch);
    if ($patchObj->isOpen()) {
        $status = $patchObj->patch(); 
        $messageService->addAll($patchObj->getMessages());
        if ($status) {
            $messageService->success("'".$patchObj->getLabel()."' installed successfully");
        } else {
            $messageService->error("Could not install '".$patchObj->getLabel()."'");
        }
    }
    // @todo DB_PREFIX must die! It is needed for the sqlConfig patch to run.
    if (!defined('DB_PREFIX')) define('DB_PREFIX', \ZMRuntime::getDatabase()->getPrefix());
}

$request = Runtime::getContainer()->get('request');
$request->redirect($request->url('login'));
