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
use zenmagick\base\Runtime;
use zenmagick\http\HttpApplication;

  $rootDir = __DIR__;
  include_once $rootDir.'/lib/base/Application.php';
  include_once $rootDir.'/lib/http/HttpApplication.php';

  if (!defined('ZM_APP_PATH')) {
      // app location relative to zenmagick installation
      define('ZM_APP_PATH', '/apps/storefront');
  }

  try {
      $config = array('appName' => basename(ZM_APP_PATH), 'environment' => (isset($_SERVER['ZM_ENVIRONMENT']) ? $_SERVER['ZM_ENVIRONMENT'] : 'prod'));
      $application = new HttpApplication($config);
      $application->bootstrap();

      $container = Runtime::getContainer();
      $_zm_request = $request = $container->get('request');
      // allow seo rewriters to fiddle with the request
      $request->urlDecode();

      // make sure we use the appropriate protocol (HTTPS, for example) if required
      $container->get('sacsManager')->ensureAccessMethod($request);

      // form validation
      $validationConfig = Runtime::getApplicationPath().'/config/validation.yaml';
      if ($container->has('validator') && file_exists($validationConfig)) {
          $container->get('validator')->load(file_get_contents(Toolbox::resolveWithEnv($validationConfig)));
      }

      // load stuff that really needs to be global!
      if (Runtime::getSettings()->get('zenmagick.base.plugins.enabled', true)) {
          foreach (Runtime::getContainer()->get('pluginService')->getAllPlugins(ZMSettings::get('zenmagick.base.context')) as $plugin) {
              foreach ($plugin->getGlobal($request) as $file) {
                  include_once $_file;
              }
          }
      }
    } catch (Exception $e) {
        echo '<pre>';
        echo $e->getTraceAsString();
        echo '</pre>';
        die(sprintf('init storefront failed: %s', $e->getMessage()));
    }
