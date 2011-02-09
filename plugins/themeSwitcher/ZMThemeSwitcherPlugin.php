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
 * Allow users to switch between themes.
 *
 * @package org.zenmagick.plugins.themeSwitcher
 * @author DerManoMann
 */
class ZMThemeSwitcherPlugin extends Plugin {
    /** query param key for new theme id. */
    const SESS_THEME_KEY = 'themeId';


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Theme Switcher', 'Allow users to select a theme');
        $this->setContext(Plugin::CONTEXT_STOREFRONT);
        $this->setLoaderPolicy(ZMPlugin::LP_NONE);
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
            $themeChain = array();
            $themeChain[] = ZMThemes::instance()->getThemeForId(ZMSettings::get('apps.store.themes.default'));
            $themeChain[] = ZMThemes::instance()->getThemeForId($themeId);
            ZMThemes::instance()->setThemeChain($languageId, $themeChain);
        }
    }

    /**
     * Inject html.
     */
    public function onFinaliseContents($event, $contents) {
        $request = $event->get('request');

        if (false !== strpos($contents, _zm('Switch theme: '))) {
            // already done, do not change
            return null;
        }

        $defaultConfig = null;
        if (!ZMSettings::exists('plugins.themeSwitcher.themes')) {
            // iterate over all themes and build default config
            $defaultConfig = '';
            foreach (ZMThemes::instance()->getAvailableThemes() as $theme) {
                if (!$theme->getConfig('zencart')) {
                    $defaultConfig .= $theme->getThemeId().':'.$theme->getName().',';
                }
            }
        }
        $themes = explode(',', ZMSettings::get('plugins.themeSwitcher.themes', $defaultConfig));
        $links = '';
        foreach ($themes as $themeConfig) {
            if (!ZMLangUtils::isEmpty(trim($themeConfig))) {
                // themeId:name
                $details = explode(':', $themeConfig);
                if (2 > count($details)) {
                    // default
                    $details[1] = $details[0];
                }
                if (!empty($links)) {
                    $links .= '&nbsp;|&nbsp;';
                }

                // create url
                $url = $request->url(null, null, $request->isSecure());
                $hasParams = false !== strpos($url, '?');
                $url .= ($hasParams ? '&' : '?') . 'themeId='.$details[0];

                $link = '<a href="'.$url.'">'.$details[1].'</a>';
                $themeChain = ZMThemes::instance()->getThemeChain($request->getSession()->getLanguageId());
                $currentTheme = array_pop($themeChain);
                if ($details[0] == $currentTheme->getThemeId()) {
                    $link = '<strong style="text-decoration:underline">'.$link.'</strong>';
                }
                $links .= $link;
            }
        }
        if (!ZMLangUtils::isEmpty($links)) {
            $switch =  '<div id="style-switcher" style="text-align:right;padding:2px 8px;">' . _zm('Switch theme: ') . $links . '</div>';
            $contents =  preg_replace('/(<body[^>]*>)/', '\1'.$switch, $contents, 1);
        }

        return $contents;
    }

}
