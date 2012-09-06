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
namespace ZenMagick\StoreBundle\Widgets\Status;

use ZenMagick\Base\ZMObject;
use ZenMagick\Base\Runtime;
use ZenMagick\StoreBundle\Widgets\StatusCheck;

/**
 * Maintenance status check.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MaintenanceStatusCheck extends ZMObject implements StatusCheck {

    /**
     * {@inheritDoc}
     */
    public function getStatusMessages() {
        $messages = array();

        $settingsService = $this->container->get('settingsService');
        $warnBeforeMaintenance = $settingsService->get('apps.store.warnBeforeMaintenance');
        $downForMaintenance = $settingsService->get('apps.store.downForMaintenance');

        if ($warnBeforeMaintenance && !$downForMaintenance) {
            $configService = $this->container->get('configService');
            $downForMaintenanceDateTime = $configService->getConfigValue('PERIOD_BEFORE_DOWN_FOR_MAINTENANCE');
            $messages[] = array(StatusCheck::STATUS_NOTICE, sprintf(_zm('This website is scheduled to be "<em>Down For Maintenance</em>" on: %s.'), $downForMaintenanceDateTime->getValue()));
        }

        if ($downForMaintenance && !Runtime::isContextMatch('storefront')) {
            $messages[] = array(StatusCheck::STATUS_WARN, _zm('The website is currently "<em>Down For Maintenance</em>" to the public.'));
        }

        return $messages;
    }

}
