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
 * Plugin page controller base class.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services.plugins
 * @version $Id$
 */
class ZMPluginPageController extends ZMObject {
    private $id_;
    private $title_;
    private $plugin_;
    private $ext_;
    private $pluginPageClass_;


    /**
     * Create a new instance.
     *
     * @param string id The id.
     * @param string title The page title.
     * @param string plugin The parent plugin name.
     * @param string ext Optional template file extension; default is <em>.php</em>.
     */
    function __construct($id, $title=null, $plugin, $ext='.php') {
        parent::__construct();
        $this->id_ = $id;
        $this->title_ = null != $title ? $title : $id;
        $this->plugin_ = $plugin;
        $this->ext_ = $ext;
        $this->pluginPageClass_ = 'PluginPage';
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
     * Set the plugin page class.
     *
     * @param string pluginPageClass The class name.
     */
    public function setPluginPageClass($pluginPageClass) { $this->pluginPageClass_ = $pluginPageClass; }

    /**
     * Get the plugin page class.
     *
     * @return string The class name.
     */
    public function getPluginPageClass() { return $this->pluginPageClass_; }

    /**
     * {@inheritDoc}
     */
    public function process($request) { 
        if (!ZMSettings::get('isAdmin')) {
            throw new ZMException('illegal access');
        }

        $page = null;
        switch ($request->getMethod()) {
            case 'GET':
                $page = $this->processGet($request);
                break;
            case 'POST':
                $page = $this->processPost($request);
                break;
            default:
                throw new ZMException('unsupported request method: ' . $request->getMethod());
        }

        return $page;
    }


    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        return ZMLoader::make( $this->pluginPageClass_, $this->id_, $this->plugin_, $this->title_);
    }


    /**
     * {@inheritDoc}
     */
    public function processPost($request) { return $this->processGet($request); }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    protected function getPlugin() {
        return ZMPlugins::instance()->getPluginForId($this->plugin_, true);
    }

    /**
     * Evaluate template and return contents.
     *
     * @param ZMRequest request The current request.
     * @param array context The page context.
     * @param string viewDir Optional view folder relative to the plugin dir; default is <em>views</em>.
     * @return string The page contents.
     */
    protected function getPageContents($request, $context, $viewDir='views') {
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

        $template = file_get_contents($this->getPlugin()->getPluginDirectory().$viewDir.'/'.$this->getId().$this->ext_);
        ob_start();
        eval('?>'.$template);
        return ob_get_clean();
    }

}

?>
