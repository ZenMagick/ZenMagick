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
 * Admin related functions.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.tools
 * @version $Id$
 */
class ZMToolboxAdmin extends ZMToolboxTool {

    /**
     * Create a plugin admin page URL.
     *
     * @param string requestId The request id.
     * @param string params Query string style parameter; if <code>''</code>.
     * @param boolean secure Flag to create a secure url; default is <code>true</code>.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     */
    public function url($requestId=null, $params='', $secure=true, $echo=ZM_ECHO_DEFAULT) {
        if (null == $requestId) {
            $requestId = $this->getRequest()->getRequestId();
        }

        $url = $this->getToolbox()->net->url('zmIndex.php', 'main_page='.$requestId.'&'.$params, $secure, false);

        if ($echo) echo $url;
        return $url;
    }

    /**
     * Get plugin page for the given <em>fkt</em> value.
     *
     * @param ZMRequest request The current request.
     * @param string fkt The funciton value.
     * @return ZMPluginPage A ready-to-use page or <code>null</code>.
     */
    public function getPluginPageForFkt($request, $fkt) {
        $page = null;

        // try to resolve plugin page controller
        $controllerClass = ZMLoader::makeClassname($fkt);
        if (ZMLoader::resolve($controllerClass)) {
            $controller = ZMLoader::make($controllerClass);
            $page = $controller->process($request);
        } else if (ZMLoader::resolve($controllerClass.'Controller')) {
            $controller = ZMLoader::make($controllerClass.'Controller');
            $page = $controller->process($request);
        } else if (function_exists($fkt)) {
            ob_start();
            $page = $fkt(); 
            $contents = ob_get_clean();
            if (!empty($contents)) {
                $page->setContents($contents);
            }
        }

        return $page;
    }

}

?>
