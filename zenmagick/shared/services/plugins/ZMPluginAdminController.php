<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Plugin admin controller base class.
 *
 * <p>The default implementation will use the <code>SimplePluginFormView</code> view
 * to generate a simple plugin config form.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.plugins
 */
class ZMPluginAdminController extends ZMController {
    private $title_;
    private $plugin_;


    /**
     * Create a new instance.
     *
     * @param string id The id.
     * @param string title The page title.
     * @param mixed plugin The parent plugin.
     */
    function __construct($id, $title=null, $plugin) {
        parent::__construct($id);
        $this->title_ = null != $title ? $title : $id;
        $this->plugin_ = $plugin;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the plugin.
     *
     * @param mixed plugin A <code>ZMPlugin</code> instance or plugin id.
     */
    public function setPlugin($plugin) { 
        $this->plugin_ = $plugin;
    }

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
     * {@inheritDoc}
     */
    public function processGet($request) {
        return ZMLoader::make('SimplePluginFormView', $this->getPlugin(), $request->getParameter('fkt'));
    }

    /**
     * Create a configured admin view.
     *
     * @param ZMRequest request The current request.
     * @param array context Additional context map; default is an empty array.
     * @param string template Optional template name; default is <code>null</code> to use the controller id.
     * @return ZMView A (redirect) view.
     */
    public function getPluginAdminView($request, $context=array(), $template=null) {
        $view = ZMLoader::make('AdminView');
        $view->setTemplatePath(array($this->getPlugin()->getPluginDirectory().DIRECTORY_SEPARATOR.'content'));
        $view->setTemplate(null != $template ? $template : $this->getId());
        $view->setVars($context);
        $view->setVar('plugin', $this->getPlugin());
        return $view;
    } 

    /**
     * Create a configured redirect view.
     *
     * @param ZMRequest request The current request.
     * @param string requestId Redirect request id.
     * @return ZMView A (redirect) view.
     */
    public function getRedirectViewForId($request, $requestId) {
        $view = ZMLoader::make('RedirectView');
        $view->setUrl($request->getToolbox()->admin->url($requestId, 'fkt='.$this->getId()));
        return $view;
    }

    /**
     * Create a plugin admin redirect view.
     *
     * @param ZMRequest request The current request.
     * @param string requestId Optional redirect request id; default is <em>plugin_page</code>.
     * @return ZMView A (redirect) view.
     */
    public function getRedirectView($request) {
        return $this->getRedirectViewForId($request, 'plugin_page');
    }

    /**
     * Create a catalog manager redirect view.
     *
     * @param ZMRequest request The current request.
     * @param string requestId Optional redirect request id; default is <em>plugin_page</code>.
     * @return ZMView A (redirect) view.
     */
    public function getCatalogManagerRedirectView($request) {
        return $this->getRedirectViewForId($request, 'catalog_manager');
    }

}
