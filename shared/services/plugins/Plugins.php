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

use zenmagick\base\Runtime;

/**
 * Plugins.
 *
 * <p>The plugin type is controlled by the base directory within the plugins directory.
 * Please note that even though it is valid to create payment, shipping and order_total
 * directories/plugins, zen-cart will not (yet) recognize them as such.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.service
 */
class Plugins extends zenmagick\base\plugins\Plugins {

    /**
     * {@inheritDoc}
     */
    protected function loadStatus() {
        $status = array();
        if (defined('ZENMAGICK_PLUGIN_STATUS')) {
            $status = unserialize(ZENMAGICK_PLUGIN_STATUS);
            if (!is_array($status)) {
                $status = array();
            }
        }

        return $status;
    }

}
