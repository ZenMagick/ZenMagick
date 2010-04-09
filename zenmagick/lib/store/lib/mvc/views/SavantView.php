<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 * @package org.zenmagick.mvc.view
 * @version $Id: SavantView.php 2902 2010-02-16 07:51:36Z dermanomann $
 */
class SavantView extends ZMSavantView {
    private $viewDir_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->setViewDir('views');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the views dir.
     *
     * @return string The views folder name, relative to the content folder.
     */
    public function getViewDir() {
        return $this->viewDir_;
    }

    /**
     * Set the views dir.
     *
     * @param string viewDir The views folder name, relative to the content folder.
     */
    public function setViewDir($viewDir) {
        $this->viewDir_ = $viewDir;
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
                $path[] = $plugin->getPluginDirectory().DIRECTORY_SEPARATOR.'content';
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
     * <p>In contrast to templates, resources are relative to the theme's base directory.</p>
     */
    public function getResourcePath($request) {
        $path = array();
        if (ZMSettings::get('isEnableThemeDefaults')) {
            $path[] = ZMThemes::instance()->getThemeForId(ZMSettings::get('defaultThemeId'))->getBaseDir();
        }
        $path[] = Runtime::getTheme()->getBaseDir();
        return $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplate() {
        return $this->getViewDir().DIRECTORY_SEPARATOR.parent::getTemplate();
    }

}
