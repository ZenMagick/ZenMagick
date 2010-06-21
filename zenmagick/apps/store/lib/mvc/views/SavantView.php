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
 * A Savant(3) view with theme support and adjustment for theme folder structures (viewDir).
 *
 * @author DerManoMann
 * @package zenmagick.mvc.shared.view
 */
class SavantView extends ZMSavantView {

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
     *
     * <p>The template path will contain each active plugin's base directory, the default theme's content
     * directory and the active theme's content directory.</p>
     */
    public function getTemplatePath($request) {
        $path = array();

        // add plugins as fallback fallback
        foreach (explode(',', ZMSettings::get('zenmagick.core.plugins.groups')) as $group) {
            foreach (ZMPlugins::instance()->getPluginsForGroup($group, Plugin::CONTEXT_STOREFRONT) as $plugin) {
                $path[] = $plugin->getPluginDirectory().'content'.DIRECTORY_SEPARATOR;
            }
        }

        if (ZMSettings::get('isEnableThemeDefaults')) {
            // add default theme as fallback
            $path[] = ZMThemes::instance()->getThemeForId(ZMSettings::get('defaultThemeId'))->getContentDir();
        }
        // add current theme
        $path[] = Runtime::getTheme()->getContentDir();

        return $path;
    }

    /**
     * {@inheritDoc}
     *
     * <p>Same as template path.</p>
     */
    public function getResourcePath($request) {
        return $this->getTemplatePath($request);
        /*
        $path = array();
        if (ZMSettings::get('isEnableThemeDefaults')) {
            $path[] = ZMThemes::instance()->getThemeForId(ZMSettings::get('defaultThemeId'))->getBaseDir();
        }
        $path[] = Runtime::getTheme()->getBaseDir();
        return $path;
        */
    }

}
