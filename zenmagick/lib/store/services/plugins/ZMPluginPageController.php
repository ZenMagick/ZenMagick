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
 * @version $Id: ZMPluginPageController.php 2149 2009-04-13 22:59:14Z dermanomann $
 */
class ZMPluginPageController extends ZMObject {
    private $id_;
    private $title_;
    private $plugin_;
    private $ext_;


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
     * Process a HTTP request.
     *
     * <p>Supported request methods are <code>GET</code> and <code>POST</code>.</p>
     *
     * @return ZMPluginPage A <code>ZMPluginPage</code> instance or <code>null</code>.
     */
    public function process() { 
        if (!ZMSettings::get('isAdmin')) {
            throw new ZMException('illegal access');
        }

        $page = null;
        switch (ZMRequest::getMethod()) {
            case 'GET':
                $page = $this->processGet();
                break;
            case 'POST':
                $page = $this->processPost();
                break;
            default:
                throw new ZMException('unsupported request method: ' . ZMRequest::getMethod());
        }

        return $page;
    }


    /**
     * Process a HTTP GET request.
     * 
     * @return ZMPluginPage A <code>ZMPluginPage</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processGet() {
        return ZMLoader::make('PluginPage', $this->id_, $this->title_);
    }


    /**
     * Process a HTTP POST request.
     * 
     * @return ZMPluginPage A <code>ZMPluginPage</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processPost() { return $this->processGet(); }

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
     * @param array context The page context.
     * @param string viewDir Optional view folder relative to the plugin dir; default is <em>views</em>.
     * @return string The page contents.
     */
    protected function getPageContents($context, $viewDir='views') {
        // make toolbox available too
        $toolbox = ZMToolbox::instance();
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
