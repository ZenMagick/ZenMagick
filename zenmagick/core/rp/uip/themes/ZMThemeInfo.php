<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Theme info base class.
 *
 * <p>If a <code>parent</code> is set, configuration, layout and a few other settings 
 * will be merged.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.themes
 * @version $Id$
 */
class ZMThemeInfo extends ZMObject {
    private $id_;
    private $info_;
    private $config_;
    private $layout_;
    private $js_default_events_;
    private $js_events_;
    private $parent_;
    private $themeId_;


    /**
     * Create new instance.
     *
     * @param ZMThemeInfo parent Optional parent theme info instance.
     */
    function __construct() {
        parent::__construct();
        $this->parent_ = array();
        $this->info_ = array();
        $this->config_ = array();
        $this->layout_ = array();
        $this->js_default_events_ = array();
        $this->js_events_ = array();
        $this->setDefaultLayout('default_layout');
        $this->setViewsDir("views/");
        $this->setErrorPage('error');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set a parent theme info.
     *
     * <p>This is to allow to use the default theme info configuration.</p>
     *
     * @param ZMThemeInfo parent An optional parent theme info.
     */
    function setParent($parent) {
        $this->parent_ = $parent;
        $this->config_ = array_merge($parent->config_, $this->config_);
        $this->layout_ = array_merge($parent->layout_, $this->layout_);
        $this->js_default_events_ = array_merge($parent->js_default_events_, $this->js_default_events_);
        $this->js_events_ = array_merge($parent->js_events_, $this->js_events_);
    }

    /**
     * Set the theme id.
     *
     * @param int themeId The new theme id.
     */
    function setThemeId($themeId) { $this->themeId_ = $themeId; }

    /**
     * Get the theme id.
     *
     * @return int The theme id.
     */
    function getThemeId() { return $this->themeId_; }

    /**
     * Get the theme name.
     *
     * @return string The theme name.
     */
    function getName() { return $this->info_['name']; }

    /**
     * Set the theme name.
     *
     * @param string name The theme name.
     */
    function setName($name) { $this->info_['name'] = $name; }

    /**
     * Get the theme version.
     *
     * @return string The theme version.
     */
    function getVersion() { return $this->info_['version']; }
    /**
     * Set the theme version.
     *
     * @param string version The theme version.
     */
    function setVersion($version) { $this->info_['version'] = $version; }

    /**
     * Get the theme author.
     *
     * @return string The theme author.
     */
    function getAuthor() { return $this->info_['author']; }

    /**
     * Set the theme author.
     *
     * @param string author The theme author.
     */
    function setAuthor($author) { $this->info_['author'] = $author; }

    /**
     * Get the theme description.
     *
     * @return string The theme description.
     */
    function getDescription() { return $this->info_['description']; }

    /**
     * Set the theme description.
     *
     * @param string description The theme description.
     */
    function setDescription($description) { $this->info_['description'] = $description; }

    /**
     * Get the theme path.
     *
     * @return string The theme path.
     */
    function getPath() { return $this->info_['path']; }

    /**
     * Set the theme path.
     *
     * @param string path The theme name.
     */
    function setPath($path) { $this->info_['path'] = $path; }

    /**
     * Get the error page.
     *
     * @return string The error page.
     */
    function getErrorPage() { return $this->config_['errorpage']; }

    /**
     * Set the error page.
     *
     * @param string name The error page.
     */
    function setErrorPage($name) { $this->config_['errorpage'] = $name; }

    /**
     * Check if the theme has a default layout.
     *
     * @return boolean <code>true</code> if a default layout exists, <code>false</code> if not.
     */
    function hasDefaultLayout() { return null != $this->getDefaultLayout(); }

    /**
     * Get the default layout.
     *
     * @return string The default layout name.
     */
    function getDefaultLayout() {
        if (array_key_exists('default-template', $this->config_)) {
            return $this->config_['default-template'];
        }
        return null;
    }

    /**
     * Set the default layout.
     *
     * @param string name The default layout name.
     */
    function setDefaultLayout($name) { $this->config_['default-template'] = $name; }

    /**
     * Set the layout for the given page.
     *
     * @param string page The page name.
     * @param string name The layout name.
     */
    function setLayout($page, $name) { $this->layout_[$page] = $name; }

    /**
     * Set the views directory.
     *
     * @param string dir The views directory name.
     */
    function setViewsDir($dir) { $this->config_['view-dir'] = $dir; }

    /**
     * Get the views directory.
     *
     * @return string The views directory name.
     */
    function getViewsDir() { return $this->config_['view-dir']; }

    /**
     * Get the layout filename for the given page.
     *
     * @param string page The page.
     * @return string The layout name.
     */
    function getLayoutFor($page) {
        if (array_key_exists($page, $this->layout_)) {
            $this->layout_[$page];
            return $this->layout_[$page];
        } else if ($this->hasDefaultLayout()) {
            return $this->getDefaultLayout();
        }
        // default to no layout
        return $page;
    }

    /**
     * Set the default page event handler.
     *
     * @param string event The event.
     * @param string handler The handler name.
     */
    function setDefaultPageEventHandler($event, $handler) {
        $this->js_default_events_[$event] = $handler;
    }

    /**
     * Set an event handler for a particular page.
     *
     * @param string event The event.
     * @param string page The page to configure.
     * @param string handler The event handler.
     */
    function setPageEventHandler($event, $page, $handler) {
        if (!array_key_exists($event, $this->js_events_)) {
            $this->js_events_[$event] = array();
        }
        $this->js_events_[$event][$page] = $handler;
    }

    /**
     * Check if a event handler exists for the given event and page.
     *
     * @param string even The event.
     * @param string page The page.
     * @return string The handler name or <code>null</code>.
     */
    function hasPageEventHandler($event, $page) {
        return null != $this->getPageEventHandler($event, $page);
    }

    /**
     * Get an event handler for a page.
     *
     * @param string event The event.
     * @param string page The page.
     * @return string The handler name or <code>null</code>.
     */
    function getPageEventHandler($event, $page) {
        $default = '';
        if (array_key_exists($event, $this->js_default_events_)) {
            $default = $this->js_default_events_[$event];
        }
        if ('' == $default && !array_key_exists($event, $this->js_events_)) {
            return null;
        }
        if ('' == $default && !array_key_exists($page, $this->js_events_[$event])) {
            return null;
        }
        return $default . $this->js_events_[$event][$page];
    }
    
}

?>
