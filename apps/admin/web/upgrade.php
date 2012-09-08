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

// @todo using filesystem ACLs is recommended, but what should we do by default?
//umask(0002); // This will let the permissions be 0775
umask(0000); // This will let the permissions be 0777

use ZenMagick\Http\Application;
use ZenMagick\Http\Request;

$rootDir = realpath(__DIR__.'/../../..');
include_once $rootDir.'/autoload.php';

$environment = isset($_SERVER['ZM_ENVIRONMENT']) ? $_SERVER['ZM_ENVIRONMENT'] : 'prod';
$application = new Application($environment, true, basename(dirname(__DIR__)));
$application->loadClassCache();
$application->boot(array('init'));

try {
    $installer = new ZenMagick\apps\admin\Installation\InstallationPatcher();

    $request = $application->getContainer()->get('request');
    $messageService = $request->getSession()->getFlashBag();
    // Get DB Config first!
    $status = $installer->getPatchForId('importZencartConfigure')->patch();

    $patches = array('importZencartConfigure', 'sqlConfig', 'sacsPermissions', 'sqlAdminRoles', 'sqlAdminPrefs');
    $allSuccess = true;
    foreach ($patches as $patch) {
        $patchObj = $installer->getPatchForId($patch);
        if ($patchObj->isOpen()) {
            $status = $patchObj->patch();
            $messageService->addAll($patchObj->getMessages());
            if ($status) {
                $messageService->success("'".$patchObj->getLabel()."' installed successfully");
            } else {
                $allSuccess = false;
                $messageService->error("Could not install '".$patchObj->getLabel()."'");
            }
        }
    }

    if (!$allSuccess) {
        // @todo better message, but really we need a better approach. This is just temporary.
        $messageService->error('Not all patches were applied successfully. Please try again.');
    } else {
        $msg = 'All required patches were applied. You may now apply any optional'
            .'patches in the \'installation\' page.';
        $messageService->success($msg);
    }
    $request->redirect($request->url('installation'));
} catch (Exception $e) {
    echo $e->getTraceAsString();
    die ($e->getMessage());
}
