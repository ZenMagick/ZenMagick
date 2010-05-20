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
 * Minify view utils implementation.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.minify
 * @version $Id$
 */
class ViewUtils extends ZMViewUtils {
    private $plugin_;


    /**
     * Get the controlling plugin.
     *
     * @return ZMPlugin A plugin or <code>null</code>.
     */
    public function getPlugin() {
        if (null == $this->plugin_) {
            $this->plugin_ = ZMPlugins::instance()->getPluginForId('minify');
        }

        return $this->plugin_;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveResource($filename) {
        $plugin = $this->getPlugin();
        return $plugin->pluginUrl('min/f='.parent::resolveResource($filename));
    }

    /**
     * {@inheritDoc}
     */
    public function handleResourceGroup($files, $group, $location) {
        if ('js' == $group) {
            $srcList = array();
            foreach ($files as $info) {
                // use parent method to do proper resolve and not minify twice!
                $srcList[] = parent::resolveResource($info['filename']);
            }
            $plugin = $this->getPlugin();
            return '<script type="text/javascript" src="'.$plugin->pluginUrl('min/f='.implode(',', $srcList)).'"></script>'."\n";
        } else if ('css' == $group) {
            //TODO: sort css files by attributes and create group lists for each attribute group
            return parent::handleResourceGroup($files, $group, $location);
        }

        return null;
    }

}
