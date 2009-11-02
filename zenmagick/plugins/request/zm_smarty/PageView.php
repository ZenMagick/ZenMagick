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
 * Theme view using the Smarty templating engine.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_smarty
 * @version $Id$
 */
class PageView extends ZMPageView {

    /**
     * Create new theme view view.
     *
     * @param string view The page (view) name.
     */
    function __construct($view=null) {
        parent::__construct($view);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Generate view response.
     *
     * @param ZMRequest request The current request.
     */
    public function generate($request) { 
        $plugin = ZMPlugins::instance()->getPluginForId('zm_smarty');

        // get smarty instance
        $smarty = $plugin->getSmarty();

        // *export* globals from controller into template space
        $controller = $this->getController();
        foreach ($controller->getGlobals() as $name => $instance) {
            $smarty->assign($name, $instance);
        }

        $smarty->assign('zm_view', $this);
        $smarty->assign('zm_theme', Runtime::getTheme());

        // function proxy 
        $smarty->assign('zm', ZMLoader::make('FunctionProxy'));
        // toolbox tools
        foreach ($request->getToolbox()->getTools() as $name => $tool) {
            $smarty->assign($name, $tool);
        }

        $template = $this->getLayout();
        if (null != $template) {
            // layout template will include the view using this variable
            $smarty->assign('view_name', $this->getViewFilename());
            $smarty->display($template.ZMSettings::get('templateSuffix'));
        } else {
            $smarty->display($this->getName().ZMSettings::get('templateSuffix'));
        }
    }

}

?>
