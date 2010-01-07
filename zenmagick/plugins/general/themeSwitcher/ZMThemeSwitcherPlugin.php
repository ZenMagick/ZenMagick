<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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

/**
 * Allow users to switch between themes.
 *
 * @package org.zenmagick.plugins.themeSwitcher
 * @author DerManoMann
 * @version $Id$
 */
class ZMThemeSwitcherPlugin extends Plugin implements ZMRequestHandler {
    /** query param key for new theme id. */
    const SESS_THEME_KEY = 'themeId';


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Theme Switcher', 'Allow users to select a theme');
        $this->setContext(Plugin::CONTEXT_STOREFRONT);
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
    public function initRequest($request) {
        ZMEvents::instance()->attach($this);

        $session = $request->getSession();
        if (null != ($themeId = $request->getParameter('themeId'))) {
            $session->setValue(self::SESS_THEME_KEY, $themeId);
        }

        if (null != ($themeId = $session->getValue(self::SESS_THEME_KEY))) {
            Runtime::setThemeId($themeId);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onZMFinaliseContents($args) {
        $request = $args['request'];
        $contents = $args['contents'];

        if (false !== strpos($contents, zm_l10n_get('Switch theme: '))) {
            // already done, do not change
            return null;
        }

        $defaultConfig = null;
        if (!ZMSettings::exists('plugins.themeSwitcher.themes')) {
            // iterate over all themes and build default config
            $defaultConfig = '';
            foreach (ZMThemes::instance()->getThemes() as $theme) {
                $defaultConfig .= $theme->getThemeId().':'.$theme->getName().',';
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
                $link = '<a href="'.$request->getToolbox()->net->url(null, 'themeId='.$details[0], $request->isSecure(), false).'">'.$details[1].'</a>';
                if ($details[0] == Runtime::getThemeId()) {
                    $link = '<strong style="text-decoration:underline">'.$link.'</strong>';
                }
                $links .= $link;
            }
        }
        if (!ZMLangUtils::isEmpty($links)) {
            $switch =  '<div id="style-switcher" style="text-align:right;padding:2px 8px;">' . zm_l10n_get('Switch theme: ') . $links . '</div>';
            $contents =  preg_replace('/(<body[^>]*>)/', '\1'.$switch, $contents, 1);
        }

        $args['contents'] = $contents;
        return $args;
    }

}

?>
