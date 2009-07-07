<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Simple theme view.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.views
 * @version $Id: ZMPageView.php 2349 2009-06-29 03:13:05Z dermanomann $
 */
class ZMPageView extends ZMView {

    /**
     * Create new view.
     *
     * @param string view The view template name; default is <code>null</code>.
     * @deprecated: contructor arguments
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
     * Return the layout name.
     *
     * @return string The layout name or <code>null</code>.
     */
    protected function getLayout() {
        $themeInfo = Runtime::getTheme()->getThemeInfo();
        return $themeInfo->getLayoutFor($this->getName());
    }

    /**
     * Generate view response.
     *
     * @param ZMRequest request The current request.
     */
    public function generate($request) { 
        $_zm_controller = $this->getController();
        if (null != $_zm_controller) {
            // *export* globals from controller into view space
            foreach ($_zm_controller->getGlobals() as $name => $instance) {
                $$name = $instance;
            }
        }
        // and for view data
        foreach ($this->getVars() as $name => $instance) {
            $$name = $instance;
        }

        // TODO: kill! common view variables
        $zm_theme = Runtime::getTheme();
        $_zm_layout = $this->getLayout();
        if (null != $_zm_layout) {
            include Runtime::getTheme()->themeFile($_zm_layout.ZMSettings::get('templateSuffix'));
        } else {
            if ($this->isViewFunction()) { 
                $this->callView(); 
            } else {
                include $this->getViewFilename();
            }
        }
    }

}

?>
