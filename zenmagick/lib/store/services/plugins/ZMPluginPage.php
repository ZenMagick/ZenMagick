<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Container for all data/information related to a plugin admin page.
 *
 * <p>Contents may either be set directly, or via setting a view (file)name. The name
 * will then be resolved relative to the <em>views</em> folder in the plugin directory.</p>
 *
 * <p>If the view name is not set either, the plugin id will be used.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services.plugins
 * @version $Id$
 */
class ZMPluginPage extends ZMObject {
    private $id_;
    private $plugin_;
    private $title_;
    private $contents_;
    private $header_;
    private $refresh_;
    private $viewName_;


    /**
     * Create a new plugin page.
     *
     * @param string id The id.
     * @param mixed plugin A <code>ZMPlugin</code> instance or plugin id.
     * @param string title The title.
     * @param string contents The page contents.
     * @param string header Optional code to be injected into the header; default is <code>null</code>.
     * @param boolean refresh Optional flag to indicate that a page refresh is required; default is <code>false</code>.
     */
    function __construct($id, $plugin, $title, $contents=null, $header='', $resfresh=false) {
        parent::__construct();
        $this->id_ = $id;
        $this->setPlugin($plugin);
        $this->title_ = $title;
        $this->contents_ = $contents;
        $this->header_ = $header;
        $this->refresh_ = $refresh;
        $this->viewName_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the id.
     *
     * @return string The page id.
     */
    public function getId() { return $this->id_; }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    public function getPlugin() {
        if (!is_object($this->plugin_)) {
            $this->plugin_ = ZMPlugins::instance()->getPluginForId($this->plugin_);
        }

        return $this->plugin_;
    }

    /**
     * Get the title.
     *
     * @return string The page title.
     */
    public function getTitle() { return $this->title_; }

    /**
     * Get the contents.
     *
     * @param ZMRequest request The current request.
     * @return string The page contents.
     */
    public function getContents($request) {
        if (null === $this->contents_) {
            // 2nd chance...
            $viewName = $this->viewName_;
            if (null == $viewName) {
                // 3rd chance...
                $viewName = $this->getPlugin()->getId();
            }
            $this->contents_ = $this->getPageContents($request, $viewName);
        }

        return $this->contents_;
    }

    /**
     * Evaluate template and return contents.
     *
     * @param ZMRequest request The current request.
     * @param string viewName The view name.
     * @param array context The page context; default is an empty array.
     * @param string viewDir Optional view folder relative to the plugin dir; default is <em>views</em>.
     * @return string The page contents.
     */
    protected function getPageContents($request, $viewName, $context=array(), $viewDir='views') {
        // some basics
        $session = $request->getSession();

        // make toolbox available too
        $toolbox = $request->getToolbox();
        foreach ($toolbox->getTools() as $name => $tool) {
            $$name = $tool;
        }

        // custom context variables
        foreach ($context as $name => $value) {
            $$name = $value;
        }

        // the plugin
        $plugin = $this->getPlugin();

        $template = file_get_contents($this->getPlugin()->getPluginDirectory().$viewDir.DIRECTORY_SEPARATOR.$viewName.'.php');
        ob_start();
        eval('?>'.$template);
        return ob_get_clean();
    }

    /**
     * Get the header code.
     *
     * @return string The header code.
     */
    public function getHeader() { return $this->header_; }

    /**
     * Get the view name.
     *
     * @return string The view name or <code>null</code>.
     */
    public function getViewName() { return $this->viewName_; }

    /**
     * Set the id.
     *
     * @param string id The page id.
     */
    public function setId($id) { $this->id_ = $id; }

    /**
     * Set the plugin.
     *
     * @param mixed plugin A <code>ZMPlugin</code> instance or plugin id.
     */
    public function setPlugin($plugin) { 
        $this->plugin_ = $plugin;
    }

    /**
     * Set the title.
     *
     * @param string title The page title.
     */
    public function setTitle($title) { $this->title_ = $title; }

    /**
     * Set the contents.
     *
     * @param string contents The page contents.
     */
    public function setContents($contents) { $this->contents_ = $contents; }

    /**
     * Set the header code.
     *
     * @param string header The header code.
     */
    public function setHeader($header) { $this->header_ = $header; }

    /**
     * Set the view name.
     *
     * @parma string viewnName The view name.
     */
    public function setViewName($viewName) { $this->viewName_ = $viewName; }

    /**
     * Set the refresh flag.
     *
     * @param boolean refresh The new value.
     */
    public function setRefresh($refresh) { $this->refresh_ = $refresh; }

    /**
     * Get the refresh flag.
     *
     * @return boolean The value.
     */
    public function isRefresh() { return $this->refresh_; }

}

?>
