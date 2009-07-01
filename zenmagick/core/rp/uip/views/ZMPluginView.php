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
 * Simple plugin view.
 *
 * <p>This view allows to display templates (full layouts or views) that are located in 
 * a plugin folder.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.uip.views
 * @version $Id$
 */
class ZMPluginView extends ZMPageView {
    protected $plugin_;


    /**
     * Create new theme view view.
     *
     * @param string view The view name.
     * @param ZMPlugin plugin The plugin; default is <code>null</code>.
     * @deprecated: contructor arguments
     */
    function __construct($view=null, $plugin=null) {
        parent::__construct($view);
        $this->setPlugin($plugin);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the corresponding plugin.
     *
     * @param ZMPlugin plugin The plugin.
     */
    public function setPlugin($plugin) {
        if (is_object($plugin)) {
            $this->plugin_ = $plugin;
        } else {
            // assume string
            $this->plugin_ = ZMPlugins::instance()->getPluginForId($plugin);
        }
        $this->setVar('plugin', $this->plugin_);
    }

    /**
     * Check if this view is valid.
     *
     * @return boolean <code>true</code> if the view is valid, <code>false</code> if not.
     */
    public function isValid() {
        return true;
    }

    /**
     * Returns the full view filename to be included by a template.
     *
     * @return string The full view filename.
     */
    public function getViewFilename() {
        $plugin = $this->plugin_;
        $subdir = $this->getSubdir();
        if (null != $subdir) {
            $subdir .= '/';
        } else {
            $subdir = '';
        }

        return $plugin->getPluginDirectory() . $subdir . $this->getName() . ZMSettings::get('templateSuffix');
    }

}

?>
