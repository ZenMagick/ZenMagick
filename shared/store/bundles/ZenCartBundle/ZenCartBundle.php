<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace zenmagick\apps\store\bundles\ZenCartBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\apps\store\bundles\ZenCartBundle\utils\EmailEventHandler;

/**
 * Zencart support bundle.
 *
 * @author DerManoMann
 */
class ZenCartBundle extends Bundle {
    const ZENCART_ADMIN_FOLDER = 'ZENCART_ADMIN_FOLDER';

    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container) {
        parent::build($container);
    }

    /**
     * {@inheritDoc}
     */
    public function boot() {
        define('ZC_INSTALL_PATH', dirname(Runtime::getInstallationPath()).DIRECTORY_SEPARATOR);

        $eventDispatcher = Runtime::getEventDispatcher();
        $eventDispatcher->addListener('init_config_done', array($this, 'onInitConfigDone'), 5);
        $eventDispatcher->addListener('init_done', array($this, 'onContainerReady'));
        $eventDispatcher->addListener('generate_email', array(Beans::getBean('zenmagick\apps\store\bundles\ZenCartBundle\utils\EmailEventHandler'), 'onGenerateEmail'));
        $eventDispatcher->addListener('create_account', array($this, 'onCreateAccount'));
        $eventDispatcher->addListener('login_success', array($this, 'onLoginSuccess'));

        $zcClassLoader = new ZenCartClassLoader();
        $zcClassLoader->register();

        // random defines that we might need
        if (!defined('PRODUCTS_OPTIONS_TYPE_SELECT')) { define('PRODUCTS_OPTIONS_TYPE_SELECT', 0); }
        if (!defined('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL')) { define('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL', 0); }
        if (!defined('TEXT_PREFIX')) { define('TEXT_PREFIX', 'txt_'); }
        if (!defined('UPLOAD_PREFIX')) { define('UPLOAD_PREFIX', 'upload_'); }
    }

    /**
     * Load configure.php to get the required store-container settings if required.
     *
     * @todo This will eventually be deprecated once we have an installer to write store-config.yaml
     */
    protected function prepareConfig() {
        $settingsService = $this->container->get('settingsService');

        $current = $settingsService->get('apps.store.database.default', array());

        if (!defined('DB_PREFIX')) define('DB_PREFIX', $current['prefix']);

        if (defined('ENABLE_SSL_ADMIN')) $settingsService->set('zenmagick.http.request.secure', 'true' == ENABLE_SSL_ADMIN);
        if (defined('ENABLE_SSL')) $settingsService->set('zenmagick.http.request.secure', 'true' == ENABLE_SSL);

        // download base folder
        $downloadBaseDir = !defined('DIR_FS_DOWNLOAD') ? ZC_INSTALL_PATH . 'download/' : DIR_FS_DOWNLOAD;
        $settingsService->set('downloadBaseDir', $downloadBaseDir);
    }

    /**
     * Prepare db config
     */
    public function onInitConfigDone($event) {
        $this->prepareConfig();
        if (Runtime::isContextMatch('admin') || (defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG)) {
            $folder = $this->container->get('configService')->getConfigValue(self::ZENCART_ADMIN_FOLDER);
            if (null != $folder) {
                $this->container->get('settingsService')->set('apps.store.zencart.admindir', $folder);
            }
            Runtime::getSettings()->set('zenmagick.base.context', 'admin');
        }


        // include overrides for zen_href_link and zen_mail*
        require_once __DIR__ . '/utils/zencart_overrides.php';
        // include some zencart files we need.
        include_once ZC_INSTALL_PATH . 'includes/database_tables.php';
    }

    /**
     * Handle things that require a request.
     */
    public function onContainerReady($event) {
        $request = $event->get('request');
        if (Runtime::isContextMatch('admin') || (defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG)) {
            $settingsService = $this->container->get('settingsService');
            $settingsService->set('apps.store.baseUrl', 'http://'.$request->getHostname().str_replace('zenmagick/apps/admin/web', '', $request->getContext()));
        }

        if (Runtime::isContextMatch('admin') && defined('EMAIL_ENCODING_METHOD') && null == $request->getRequestId()) {
            // old zc admin?
            $request->setRequestId(str_replace('.php', '', $request->getFrontController()));
        }
    }

    /**
     * Periodic stuff zencart needs to do.
     */
    private function zenSessionStuff() {
        if (function_exists('zen_session_recreate')) {
            // yay!
            if (!function_exists('whos_online_session_recreate')) {
                function whos_online_session_recreate($old_session, $new_session) { }
            }
            zen_session_recreate();
        }
    }

    /**
     * Login event handler.
     */
    public function onLoginSuccess($event) {
        $this->zenSessionStuff();
    }

    /**
     * Create account event handler.
     */
    public function onCreateAccount($event) {
        $this->zenSessionStuff();
    }

}
