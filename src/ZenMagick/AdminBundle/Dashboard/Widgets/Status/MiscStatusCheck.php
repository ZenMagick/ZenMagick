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
namespace ZenMagick\AdminBundle\Dashboard\Widgets\Status;

use ZenMagick\Base\ZMObject;
use ZenMagick\StoreBundle\Widgets\StatusCheck;

/**
 * Misc status check.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MiscStatusCheck extends ZMObject implements StatusCheck
{
    const ACTIVITY_LOG_RECORD_THRESHOLD = 50000;
    const ACTIVITY_LOG_DATE_THRESHOLD = 60;

    /**
     * {@inheritDoc}
     */
    public function getStatusMessages()
    {
        $messages = array();
        $translator = $this->container->get('translator');
        $result = \ZMRuntime::getDatabase()->querySingle('SELECT COUNT(log_id) AS counter from %table.admin_activity_log%', array(), 'admin_activity_log');
        if (0 < $result['counter']) {
            $reset = null;
            if (self::ACTIVITY_LOG_RECORD_THRESHOLD < $result['counter']) {
                $reset = $translator->trans('The Admin Activity Log table has over %count% records and should be cleaned ... ', array('%count%' => self::ACTIVITY_LOG_RECORD_THRESHOLD));
            } else {
                $sql = 'SELECT MIN(access_date) AS access_date FROM %table.admin_activity_log% WHERE access_date < DATE_SUB(CURDATE(), INTERVAL '.self::ACTIVITY_LOG_DATE_THRESHOLD.' DAY)';
                $result = \ZMRuntime::getDatabase()->querySingle($sql);
                if ($result && null != $result['access_date']) {
                    $reset = $translator->trans('The Admin Activity Log table has records more than %count% days old and should be cleaned ... ', array('%count%' => self::ACTIVITY_LOG_DATE_THRESHOLD));
                }
            }
            if ($reset) {
                $messages[] = array(StatusCheck::STATUS_NOTICE, $reset);
            }
        }

        if (!defined('DEFAULT_CURRENCY')) {
            $messages[] = array(StatusCheck::STATUS_WARN, $translator->trans('Please set a default currency.'));
        }
        if (!defined('DEFAULT_LANGUAGE') || DEFAULT_LANGUAGE=='') {
            $messages[] = array(StatusCheck::STATUS_NOTICE, $translator->trans('Please set a default language.'));
        }

        return $messages;
    }

}
