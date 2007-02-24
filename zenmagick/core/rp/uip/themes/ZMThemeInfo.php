<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * Theme info base class.
 *
 * <p>If a <code>parent</code> is set, configuration, layout and a few other settings 
 * will be merged.</p>
 *
 * @author mano
 * @package net.radebatz.zenmagick.rp.uip.themes
 * @version $Id$
 */
class ZMThemeInfo extends ZMObject {
    var $id_;
    var $info_;
    var $config_;
    var $layout_;
    var $js_default_events_;
    var $js_events_;
    var $parent_;


    /**
     * Default c'tor.
     *
     * @param ZMThemeInfo parent Optional parent theme info instance.
     */
    function ZMThemeInfo() {
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
     * Default c'tor.
     *
     * @param ZMThemeInfo parent Optional parent theme info instance.
     */
    function __construct() {
        $this->ZMThemeInfo();
    }

    /**
     * Default d'tor.
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
    function setParent(&$parent) {
        $this->parent_ =& $parent;
        $this->config_ = array_merge($parent->config_, $this->config_);
        $this->layout_ = array_merge($parent->layout_, $this->layout_);
        $this->js_default_events_ = array_merge($parent->js_default_events_, $this->js_default_events_);
        $this->js_events_ = array_merge($parent->js_events_, $this->js_events_);
    }

    // getter/setter
    function setThemeId($themeId) { $this->themeId_ = $themeId; }
    function getThemeId() { return $this->themeId_; }
    function getName() { return $this->info_['name']; }
    function setName($name) { $this->info_['name'] = $name; }
    function getVersion() { return $this->info_['version']; }
    function setVersion($version) { $this->info_['version'] = $version; }
    function getAuthor() { return $this->info_['author']; }
    function setAuthor($author) { $this->info_['author'] = $author; }
    function getDescription() { return $this->info_['description']; }
    function setDescription($text) { $this->info_['description'] = $text; }
    function getPath() { return $this->info_['path']; }
    function setPath($text) { $this->info_['path'] = $text; }

    function getErrorPage() { return $this->config_['errorpage']; }
    function setErrorPage($name) { $this->config_['errorpage'] = $name; }
    function hasDefaultLayout() { return null != $this->getDefaultLayout(); }
    function getDefaultLayout() {
        if (array_key_exists('default-template', $this->config_)) {
            return $this->config_['default-template'];
        }
        return null;
    }
    function setDefaultLayout($name) { $this->config_['default-template'] = $name; }
    function setLayout($page, $name) { $this->layout_[$page] = $name; }

    function setViewsDir($dir) { $this->config_['view-dir'] = $dir; }
    function getViewsDir() { return $this->config_['view-dir']; }

    function getTemplateFor($name) {
        if (array_key_exists($name, $this->layout_)) {
            $this->layout_[$name];
            return $this->layout_[$name];
        } else if ($this->hasDefaultLayout()) {
            return $this->getDefaultLayout();
        }
        // default to no layout
        return $name;
    }

    function setDefaultPageEventHandler($event, $handler) {
        $this->js_default_events_[$event] = $handler;
    }

    function setPageEventHandler($event, $page, $handler) {
        if (!array_key_exists($event, $this->js_events_)) {
            $this->js_events_[$event] = array();
        }
        $this->js_events_[$event][$page] = $handler;
    }

    function hasPageEventHandler($event, $page) {
        return null != $this->getPageEventHandler($event, $page);
    }

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
