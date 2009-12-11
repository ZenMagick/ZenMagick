<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * @package org.zenmagick.store.mvc.views
 * @version $Id$
 */
class ZMPluginView extends SavantView {
    protected $plugin_;


    /**
     * Create new theme view view.
     */
    function __construct() {
        parent::__construct();
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
     * {@inheritDoc}
     */
    public function getTemplatePath($request) {
        // add plugin as (last) valid option
        return array_merge(array($this->plugin_->getPluginDirectory()), parent::getTemplatePath($request));
    }

}

?>
