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

use DateTime;
use ZenMagick\base\Runtime;
use ZenMagick\base\ZMObject;

/**
 * Products on special.
 *
 */
class ProductSpecialsService extends ZMObject {

    public function runTasks() {
        $this->scheduleSpecials();
    }

    /**
     * Start/stop all specials.
     *
     * Stops all specials scheduled for expiration
     * and starts all specials scheduled to be started.
     *
     * @todo finish product price retrieval that takes specials into account
     */
    public function scheduleSpecials() {
        $sql = "SELECT specials_id, products_id, status, expires_date, specials_date_available
                FROM %table.specials%";
        $productService = $this->container->get('productService');
        foreach (\ZMRuntime::getDatabase()->fetchAll($sql, array(), 'specials', 'ZenMagick\apps\store\model\catalog\Special') as $special) {
            $availableDate = $special->getAvailableDate();
            $expiryDate = $special->getExpiryDate();
            $active = $special->getStatus();
            if (!$active && null != $availableDate && new DateTime() >= $availableDate) {
                $special->setStatus(true);
            }
            // @todo the original code also disabled specials that haven't started yet. is that something we should worry about?
            if ($special->getStatus() && null != $expiryDate && new DateTime() >= $expiryDate) {
                $special->setStatus(false);
            }

            // changed ??
            if ($special->getStatus() != $active) {
                \ZMRuntime::getDatabase()->updateModel('specials', $special);
                $productService->updateSortPrice($special->getProductId());
            }
        }
    }
}
