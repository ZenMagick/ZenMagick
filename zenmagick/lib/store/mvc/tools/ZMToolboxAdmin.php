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
     * @param string function The view function name; default is <code>null</code> to use the current.
     * @param string params Query string style parameter; if <code>''</code>.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     */
    public function url($function=null, $params='', $echo=ZM_ECHO_DEFAULT) {
        if (null == $function) {
            $function = $this->getRequest()->getParameter('fkt');
        }

        // XXX: screwed logic
        $url = $this->getToolbox()->net->url('zmPluginPage.php', 'fkt='.$function.'&'.$params, true, false);

        if ($echo) echo $url;
        return $url;
    }

    /**
     * Get plugin page for the given <em>fkt</em> value.
     *
     * @param string fkt The funciton value.
     * @return ZMPluginPage A ready-to-use page or <code>null</code>.
     */
    public function getPluginPageForFkt($fkt) {
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
