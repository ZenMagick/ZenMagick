<?php
/**
 * application_top.php Common actions carried out at the start of each page invocation.
 *
 * Initializes common classes & methods. Controlled by an array which describes
 * the elements to be initialised and the order in which that happens.
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: application_top.php 19731 2011-10-09 17:20:30Z wilt $
 */

use zenmagick\http\HttpApplication;
use zenmagick\base\Runtime;
use zenmagick\apps\store\bundles\ZenCartBundle\ZenCartBundle;


if (!class_exists('zenmagick\base\Application')) {
$rootDir = 'zenmagick';
include_once $rootDir.'/lib/base/Application.php';
include_once $rootDir.'/lib/http/HttpApplication.php';
$config = array('appName' => 'storefront', 'environment' => (isset($_SERVER['ZM_ENVIRONMENT']) ? $_SERVER['ZM_ENVIRONMENT'] : 'prod'));
$application = new HttpApplication($config);
$application->bootstrap();
}
global $session_started;
$session_started = true;

if (!defined('IS_ADMIN_FLAG')) { define('IS_ADMIN_FLAG', Runtime::isContextMatch('admin')); }
define('PAGE_PARSE_START_TIME', microtime());

// @todo find a way to restore the original value once all processing by ZenCart is complete.
if (Runtime::getSettings()->get('apps.store.zencart.strictErrorReporting', true)) {
  error_reporting(version_compare(PHP_VERSION, 5.4, '>=') ? E_ALL ^ E_DEPRECATED ^ E_NOTICE ^ E_STRICT : E_ALL ^ E_DEPRECATED ^ E_NOTICE);
}

// set php_self in the local scope
if (!isset($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];

if (Runtime::isContextMatch('admin')) { // @todo we need it, but zm admin doesn't do it.
    Runtime::getContainer()->get('themeService')->initThemes();
}
require __DIR__.'/configure.php';

$request_type = ZMRequest::instance()->isSecure() ? 'SSL' : 'NONSSL';

$autoLoadConfig = array();
$loaderPrefix = isset($loaderPrefix) ? $loaderPrefix : 'config';
$coreLoaderPrefix = in_array($loaderPrefix, array('config', 'paypal_ipn')) ? 'config' : $loaderPrefix;
$files = ZenCartBundle::resolveFiles('includes/auto_loaders/'.$coreLoaderPrefix.'.*.php');

include $files[$coreLoaderPrefix.'.core.php'];
unset($files[$coreLoaderPrefix.'.core.php']);

foreach ($files as $file) {
    include $file;
}

require Runtime::getInstallationPath().'/shared/store/bundles/ZenCartBundle/bridge/includes/autoload_func.php';
