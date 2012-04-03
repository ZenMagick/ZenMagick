<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\admin\http\tools;

use zenmagick\base\Runtime;
use zenmagick\http\toolbox\ToolboxTool;
use zenmagick\apps\store\controller\CatalogContentController;

/**
 * Admin related functions.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ToolboxAdmin extends ToolboxTool {

    /**
     * Create a admin page URL.
     *
     * @param string requestId The request id.
     * @param string params Query string style parameter; if <code>''</code>.
     * @param boolean secure Flag to create a secure url; default is <code>true</code>.
     * @return string A full URL.
     */
    public function url($requestId=null, $params='', $secure=true) {
        return $this->getRequest()->url($requestId, $params, $secure);
    }

    /**
     * Create a catalog admin page URL.
     *
     * @param CatalogContentController controller A controller: default is <code>null</code> to use the current <em>catalogRequestId</em>.
     * @param string params Optional additional url parameter; default is <code>null</code>.
     * @return string A full URL.
     */
    public function catalog($controller=null, $params=null) {
        $request = $this->getRequest();
        $ps = '';
        if (null != ($cPath = $request->getCategoryPath())) {
            $ps .= '&cPath='.$cPath;
        }
        if (null != ($productId = $request->getProductId())) {
            $ps .= '&productId='.$productId;
        }
        if (null != $controller && $controller instanceof CatalogContentController) {
            $ps .= '&catalogRequestId='.$controller->getCatalogRequestId();
        } else if (null != ($catalogRequestId = $request->getParameter('catalogRequestId'))) {
            $ps .= '&catalogRequestId='.$catalogRequestId;
        }
        if (null != $params) {
            $ps .= '&'.$params;
        }
        return $this->url('catalog', $ps);
    }

    /**
     * Create a catalog tab admin page URL.
     *
     * @param CatalogContentController controller A controller: default is <code>null</code> to use the current <em>catalogRequestId</em>.
     * @param string params Optional additional url parameter; default is <code>null</code>.
     * @return string A full URL.
     */
    public function catalogTab($controller=null, $params=null) {
        $request = $this->getRequest();
        $ps = '';
        if (null != ($cPath = $request->getCategoryPath())) {
            $ps .= '&cPath='.$cPath;
        }
        if (null != ($productId = $request->getProductId())) {
            $ps .= '&productId='.$productId;
        }
        if (null != $controller && $controller instanceof CatalogContentController) {
            $catalogRequestId = $controller->getCatalogRequestId();
        } else {
            $catalogRequestId = $request->getParameter('catalogRequestId');
        }
        if (null != $params) {
            $ps .= '&'.$params;
        }
        return $this->url($catalogRequestId, $ps);
    }

    /**
     * Create an Ajax URL for the given controller and method.
     *
     * <p><strong>NOTE:</strong> Ampersand are not encoded in this function.</p>
     *
     * @param string controller The controller name without the leading <em>ajax_</em>.
     * @param string method The name of the method to call.
     * @param string params Query string style parameter; if <code>null</code> add all current parameter
     * @return string A complete Ajax URL.
     */
    public function ajax($controller, $method, $params='') {
        $controller = 'ajax_'.$controller;
        $url = str_replace('&amp;', '&', $this->getRequest()->url($controller, $params.'&method='.$method, $this->getRequest()->isSecure()));

        return $url;
    }

    /**
     * Set the page title and create the side nav.
     *
     * <p><strong>NOTE: This method will return the derived page title to be used in a <em>h1</em>.</strong></p>
     *
     * @param string title Optional fixed (sub-)title; default is <code>null</code> for none.
     */
    public function title($title=null) {
        $root = $this->container->get('adminMenu')->getRootItemForRequestId($this->getRequest()->getRequestId());
        $pref = (null != $root) ? $root->getName() : null;
        if (null == $title) {
            $title = $pref;
        } else if (null != $pref) {
            $title = sprintf(_zm("%1s: %2s"), $pref, $title);
        }
        ?><h1><?php echo $title ?></h1><?php
        echo $this->getView()->fetch('sub-menu.php'); echo '<div id="view-container">';
        $title = sprintf(_zm("%1s :: %2s :: ZenMagick Admin"), Runtime::getSettings()->get('storeName'), $title);
        $this->getView()->getResourceManager()->fragment('title', $title);
        return $title;
    }

}
