<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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
?>
<?php
use zenmagick\http\HttpApplication;
use zenmagick\base\Runtime;
$rootDir = realpath(__DIR__.'/../../..');
include_once $rootDir.'/lib/base/Application.php';
include_once $rootDir.'/lib/http/HttpApplication.php';

$config = array('appName' => basename(dirname(__DIR__)), 'environment' => (isset($_SERVER['ZM_ENVIRONMENT']) ? $_SERVER['ZM_ENVIRONMENT'] : 'prod'));
$application = new HttpApplication($config);
$application->bootstrap(array('init'));
$installer = new zenmagick\apps\store\admin\installation\InstallationPatcher();

// Get DB Config first!
$installer->getPatchForId('importZencartConfigure')->patch();

if(!defined('DB_PREFIX')) define('DB_PREFIX', \ZMRuntime::getDatabase()->getPrefix());

$patches = array('sqlConfig','sacsPermissions', 'sqlAdminRoles', 'sqlAdminPrefs'); 
foreach ($patches as $patch) {
    $patchObj = $installer->getPatchForId($patch);
    if ($patchObj->isOpen()) {
        $patchObj->patch(); 
    }
}
die('installed patches');
$application->serve();
