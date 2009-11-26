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
 * Template stuff.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.minify
 * @version $Id: TemplateManager.php 2610 2009-11-20 02:45:25Z dermanomann $
 */
class TemplateManager extends ZMTemplateManager {
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
     * {@inheridDoc}
     */
    public function resolveThemeResource($request, $resource) {
        $plugin = $this->getPlugin();
        return $plugin->pluginUrl('min/f='.parent::resolveThemeResource($request, $resource), false);
    }

}

?>
