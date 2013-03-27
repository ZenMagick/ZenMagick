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
namespace ZenMagick\StoreBundle\Services\Catalog;

use ZenMagick\Base\ZMObject;

/**
 * Featured Products.
 */
class ProductFeaturedService extends ZMObject
{
    public function runTasks()
    {
        $this->scheduleFeatured();
    }

    /**
     * Start/stop all featured products.
     *
     * Stops all featured products scheduled for expiration
     * and starts all featured products scheduled to be started.
     */
    public function scheduleFeatured()
    {
        $sql = "SELECT featured_id, status, expires_date, featured_date_available
                FROM %table.featured%";
        foreach (\ZMRuntime::getDatabase()->fetchAll($sql, array(), 'featured', 'ZenMagick\StoreBundle\Entity\Catalog\Feature') as $feature) {
            $availableDate = $feature->getAvailableDate();
            $expiryDate = $feature->getExpiryDate();
            $active = $feature->getStatus();
            if (!$active && null != $availableDate && new \DateTime() >= $availableDate) {
                $feature->setStatus(true);
                \ZMRuntime::getDatabase()->updateModel('featured', $feature);
            }
            // @todo the original code also disabled features tht haven't started yet. is that something we should worry about?
            if ($feature->getStatus() && null != $expiryDate && new \DateTime() >= $expiryDate) {
                $feature->setStatus(false);
                \ZMRuntime::getDatabase()->updateModel('featured', $feature);
            }
        }
    }
}
