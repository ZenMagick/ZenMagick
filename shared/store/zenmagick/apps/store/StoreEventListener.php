<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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

use zenmagick\base\Runtime;

/**
 * Shared store event listener.
 *
 * @author DerManoMann
 * @package zenmagick.apps.store
 */
class StoreEventListener {

    /**
     * Keep up support for local.php.
     */
    public function onBootstrapDone($event) {
        // load some static files that we still need
        $statics = array(
          'lib/core/external/zm-pomo-3.0.packed.php',
          'lib/core/services/locale/_zm.php',
          'shared/defaults.php',
          'shared/external/lastRSS.php',
          // store
          'apps/store/lib/email.php',
          'apps/store/lib/zencart_overrides.php',
          // admin
          'apps/'.ZM_APP_NAME.'/lib/local.php',
          'apps/'.ZM_APP_NAME.'/lib/menu.php',
          'apps/'.ZM_APP_NAME.'/lib/utils/sqlpatch.php',
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
