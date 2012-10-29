<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace ZenMagick\StorefrontBundle\Controller;

/**
 * Redirect controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RedirectController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $action = $request->query->get('action');
        $goto = $request->query->get('goto');
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
                    $settingsService = $this->container->get('settingsService');
                    if ($settingsService->get('defaultLanguageCode') != $request->getSession()->getLanguageCode()) {
                        $defaultLanguage = $this->container->get('languageService')->getLanguageForCode($settingsService->get('defaultLanguageCode'));
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
