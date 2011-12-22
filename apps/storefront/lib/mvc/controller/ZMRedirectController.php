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

use zenmagick\base\Runtime;

/**
 * Redirect controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.sf.mvc.controller
 */
class ZMRedirectController extends ZMController {

    /**
     * Process a HTTP GET request.
     *
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processGet($request) {
        $action = $request->getParameter('action');
        $goto = $request->getParameter('goto');
        $bannerService = $this->container->get('bannerService');

        switch ($action) {
        case 'banner':
            $banner = $bannerService->getBannerForId($goto);
            if (null != $banner) {
                $bannerService->updateBannerClickCount($goto);
                return $this->findView('success', array(), array('url' => $banner->getUrl()));
            }
            break;

        case 'url':
            if (null != $goto) {
                return $this->findView('success', array(), array('url' => $goto));
            }
            break;

        case 'manufacturer':
            $manufacturerId = $goto;
            if (0 < $manufacturerId) {
                $manufacturerService = $this->container->get('manufacturerService');
                $manufacturer = $manufacturerService->getManufacturerForId($manufacturerId, $request->getSession()->getLanguageId());

                if (null == $manufacturer || null == $manufacturer->getUrl()) {
                    // try default language if different from session language
                    if (Runtime::getSettings()->get('defaultLanguageCode') != $request->getSession()->getLanguageCode()) {
                        $defaultLanguage = $this->container->get('languageService')->getLanguageForCode(Runtime::getSettings()->get('defaultLanguageCode'));
                        $manufacturer = $manufacturerService->getManufacturerForId($manufacturerId, $defaultLanguage->getId());
                    }
                }

                if (null != $manufacturer && null != $manufacturer->getUrl()) {
                    $manufacturerService->updateManufacturerClickCount($manufacturerId, $request->getSession()->getLanguageId());
                    return $this->findView('success', array(), array('url' => $manufacturer->getUrl()));
                }

            }
            break;
        }

        return $this->findView('error');
    }

}
