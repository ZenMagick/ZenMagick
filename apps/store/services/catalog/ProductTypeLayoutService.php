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
namespace ZenMagick\apps\store\services\catalog;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

/**
 * Product Type Layout  service.
 *
 * @todo reuse most of the config service (configService)
 */
class ProductTypeLayoutService extends ZMObject {

    /**
     * Load all configuration values.
     *
     * @return array Map of all configuration values.
     */
    public function loadAll() {
        $map = array();
        $sql = "SELECT configuration_key, configuration_value FROM %table.product_type_layout%";
        foreach (\ZMRuntime::getDatabase()->fetchAll($sql) as $result) {
            $map[$result['configuration_key']] = $result['configuration_value'];
        }

        return $map;
    }

    /**
     * Define all configuration keys
     *
     * @param bool check Check if the defines exist.
     */
    public function defineAll($check = false) {
        foreach ($this->loadAll() as $key => $value) {
            if ($check && !defined($key)) continue;
            define($key, $value);
        }
    }
}
