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
namespace apps\store;

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
 * @package apps.store
 */
class StoreEventListener extends ZMObject {

    /**
     * Get config loaded ASAP.
     */
    public function onInitConfigDone($event) {
        foreach ($this->container->get('configService')->loadAll() as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
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
        // load some static files that we still need
        $statics = array(
            'admin,storefront' => array('lib/core/external/zm-pomo-3.0.packed.php', 'lib/core/services/locale/_zm.php'),
            'storefront' => array('shared/store/apps/store/bundles/ZenCartBundle/utils/zencart_overrides.php')
        );
        foreach ($statics as $context => $files) {
            if (Toolbox::isContextMatch($context)) {
                foreach ($files as $static) {
                    $file = Runtime::getInstallationPath().$static;
                    if (file_exists($file)) {
                        require_once $file;
                    }
                }
            }
        }

        $local = Runtime::getInstallationPath().DIRECTORY_SEPARATOR.'local.php';
        if (file_exists($local)) {
            include $local;
        }
    }

}
