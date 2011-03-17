<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
namespace zenmagick\http;

use zenmagick\base\Runtime;
use zenmagick\http\sacs\SacsManager;

/**
 * HTTP bootstrap listener.
 *
 * @author DerManoMann
 * @package zenmagick.http
 */
class BootstrapListener {

    /**
     * Listen to bootstrap.
     */
    public function onBootstrapDone($event) {
        // mvc mappings
        \ZMUrlManager::instance()->load(file_get_contents(\ZMFileUtils::mkPath(Runtime::getApplicationPath(), 'config', 'url_mappings.yaml')), false);
        // sacs mappings
        SacsManager::instance()->load(\ZMFileUtils::mkPath(Runtime::getApplicationPath(), 'config', 'sacs_mappings.yaml'), false);
        SacsManager::instance()->loadProviderMappings(Runtime::getSettings()->get('zenmagick.http.sacs.mappingProviders'));
    }

    /**
     * Listen to init_request.
     */
    public function onInitRequest($event) {
        $request = $event->get('request');
        // adjust front controller parameter
        if ($request->getFrontController() != Runtime::getSettings()->get('zenmagick.mvc.request.index')) {
             Runtime::getSettings()->set('zenmagick.mvc.request.index', $request->getFrontController());
        }

    }

}
