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
 * @package org.zenmagick.rp.uip
 * @version $Id$
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
     */
    public function generate() { 
        $controller = $this->getController();
        // *export* globals from controller into view space
        foreach ($controller->getGlobals() as $name => $instance) {
            $$name = $instance;
        }

        // common view variables
        $zm_view = $this;
        $zm_theme = Runtime::getTheme();

        $layout = $this->getLayout();
        if (null != $layout) {
            include Runtime::getTheme()->themeFile($layout.ZMSettings::get('templateSuffix'));
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
