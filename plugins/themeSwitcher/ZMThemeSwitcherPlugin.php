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

/**
 * Allow users to switch between themes.
 *
 * @package org.zenmagick.plugins.themeSwitcher
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMThemeSwitcherPlugin extends Plugin {
    /** query param key for new theme id. */
    const SESS_THEME_KEY = 'themeId';


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Theme Switcher', 'Allow users to select a theme');
        $this->setContext('storefront');
    }


    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        zenmagick\base\Runtime::getEventDispatcher()->listen($this);
    }

    /**
     * Handle init request.
     */
    public function onInitRequest($event) {
        $request = $event->get('request');

        $session = $request->getSession();
        if (null != ($themeId = $request->getParameter('themeId'))) {
            $session->setValue(self::SESS_THEME_KEY, $themeId);
        }

        $languageId = $session->getLanguageId();
        if (null != ($themeId = $session->getValue(self::SESS_THEME_KEY))) {
            $themeService = $this->container->get('themeService');
            $themeChain = array();
            $themeChain[] = $themeService->getThemeForId(ZMSettings::get('apps.store.themes.default'), true);
            $themeChain[] = $themeService->getThemeForId($themeId, $languageId);
            $themeService->setThemeChain($languageId, $themeChain);
        }
    }

    /**
     * Inject html.
     */
    public function onFinaliseContent($event) {
        $content = $event->get('content');

        // id on main div
        if (false !== strpos($content, 'theme-switcher')) {
            // already done, do not change
            return;
        }

        if (!$event->has('view')) {
            // zc template active?
            return;
        }

        $request = $event->get('request');
        $themeService = $this->container->get('themeService');

        $defaultConfig = null;
        if (!ZMSettings::exists('plugins.themeSwitcher.themes')) {
            // iterate over all themes and build default config
            $defaultConfig = '';
            foreach ($themeService->getAvailableThemes() as $theme) {
                if (!$theme->getConfig('zencart')) {
                    $defaultConfig .= $theme->getThemeId().':'.$theme->getName().',';
                }
            }
        }

        $themes = explode(',', ZMSettings::get('plugins.themeSwitcher.themes', $defaultConfig));

        // prepare theme details list
        $themeList = array();
        foreach ($themes as $themeConfig) {
            if (!ZMLangUtils::isEmpty(trim($themeConfig))) {
                // themeId:name
                $details = explode(':', $themeConfig);
                if (2 > count($details)) {
                    // default
                    $details[1] = $details[0];
                }

                // create url
                $url = $request->url(null, null, $request->isSecure());
                $hasParams = false !== strpos($url, '?');
                $url .= ($hasParams ? '&' : '?') . 'themeId='.$details[0];

                $themeChain = $themeService->getThemeChain($request->getSession()->getLanguageId());
                $currentTheme = array_pop($themeChain);
                $active = $details[0] == $currentTheme->getThemeId();

                $themeList[] = array('url' => $url, 'name' => $details[1], 'active' => $active);
            }
        }

        if (null != ($view = $event->get('view'))) {
            $view->setVar('themeList', $themeList);
            $switcherMarkup = $view->fetch($request, 'theme-switcher.php');
            if (!empty($switcherMarkup)) {
                $content =  preg_replace('/(<body[^>]*>)/', '\1'.$switcherMarkup, $content, 1);
                $event->set('content', $content);
            }
        }
    }

}
