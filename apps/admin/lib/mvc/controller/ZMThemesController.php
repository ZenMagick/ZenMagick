<?php
/*
 * ZenMagick - Smart e-commerce
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


/**
 * Admin controller for themes page.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller
 */
class ZMThemesController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        // strip default theme
        $themes = array();
        foreach (ZMThemes::instance()->getAvailableThemes() as $theme) {
            if (ZMSettings::get('apps.store.themes.default') != $theme->getThemeId()) {
                $themes[] = $theme;
            }
        }

        $themeConfig = ZMThemes::instance()->getThemeConfigList();
        foreach ($themeConfig as $config) {
            // populate with theme instances where possible
            $config->set('theme', ZMThemes::instance()->getThemeForId($config->getThemeId()));
            $config->set('variation', ZMThemes::instance()->getThemeForId($config->getVariationId()));
        }

        return array('themes' => $themes, 'themeConfig' => $themeConfig, 'languages' => ZMLanguages::instance()->getLanguages());
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $themeId = $request->getParameter('themeId');
        $variationId = $request->getParameter('variationId');
        $languageId = $request->getParameter('languageId', 0);

        // check for match with existing language
        $update = null;
        $themeConfig = ZMThemes::instance()->getThemeConfigList();
        foreach ($themeConfig as $config) {
            if ($config->getLanguageId() == $languageId) {
                $update = $config;
                break;
            }
        }
        
        if (null != $update) {
            $update->setThemeId($themeId);
            $update->setVariationId($variationId);
            ZMThemes::instance()->updateThemeConfig($update);
            ZMMessages::instance()->success(_zm('Theme config updated.'));
        }

        return $this->findView('success');
    }

}
