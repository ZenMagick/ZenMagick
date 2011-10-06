<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
namespace zenmagick\apps\store;

use Symfony\Component\Config\FileLocator;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\base\ioc\loader\YamlFileLoader;
use zenmagick\base\ZMObject;

/**
 * Shared store event listener.
 *
 * <p>This is the ZenMagick store bootstrapper.</p>
 *
 * @author DerManoMann
 * @package zenmagick.apps.store
 */
class StoreEventListener extends ZMObject {

    /**
     * Get config loaded ASAP.
     */
    public function onInitConfigDone($event) {
        if (defined('ZC_INSTALL_PATH')) {
            // include some zencart files we need.
            include_once ZC_INSTALL_PATH . 'includes/database_tables.php';
        }
        foreach ($this->container->get('configService')->loadAll() as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }

        // random defines that we might need
        if (!defined('PRODUCTS_OPTIONS_TYPE_SELECT')) {
            define('PRODUCTS_OPTIONS_TYPE_SELECT', 0);
        }
        if (!defined('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL')) {
            define('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL', 0);
        }
        if (!defined('TEXT_PREFIX')) {
            define('TEXT_PREFIX', 'txt_');
        }
        if (!defined('UPLOAD_PREFIX')) {
            define('UPLOAD_PREFIX', 'upload_');
        }

        $defaults = Runtime::getInstallationPath().'shared/defaults.php';
        if (file_exists($defaults)) {
            include $defaults;
        }

        // load email container config once all settings/config is loaded
        $emailConfig = Runtime::getInstallationPath().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'store-email.yaml';
        if (file_exists($emailConfig)) {
            $containerYamlLoader = new YamlFileLoader(Runtime::getContainer(), new FileLocator(dirname($emailConfig)));
            $containerYamlLoader->load($emailConfig);
        }
    }

    /**
     * Keep up support for local.php.
     */
    public function onBootstrapDone($event) {
        // set default
        Runtime::getSettings()->set('zenmagick.base.plugins.dirs', array(
            Runtime::getInstallationPath().'plugins'.DIRECTORY_SEPARATOR,
            Runtime::getInstallationPath().'apps/admin/plugins'.DIRECTORY_SEPARATOR,
            Runtime::getInstallationPath().'apps/store/plugins'.DIRECTORY_SEPARATOR
        ));

        // load some static files that we still need
        $statics = array(
          'lib/core/external/zm-pomo-3.0.packed.php',
          'lib/core/services/locale/_zm.php',
          // admin
          'apps/'.ZM_APP_NAME.'/lib/local.php',
          'apps/'.ZM_APP_NAME.'/lib/menu.php',
          'apps/'.ZM_APP_NAME.'/lib/utils/sqlpatch.php',
          // store
          'apps/'.ZM_APP_NAME.'/lib/email.php',
          'apps/'.ZM_APP_NAME.'/lib/zencart_overrides.php',
        );
        foreach ($statics as $static) {
            $file = Runtime::getInstallationPath().$static;
            if (file_exists($file)) {
                require_once $file;
            }
        }

        $local = Runtime::getInstallationPath().DIRECTORY_SEPARATOR.'local.php';
        if (file_exists($local)) {
            include $local;
        }
    }

}
