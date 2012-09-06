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
namespace ZenMagick\apps\admin\Dashboard\Widgets\Status;

use DateTime;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;
use ZenMagick\StoreBundle\Widgets\StatusCheck;

/**
 * Misc status check.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class MiscStatusCheck extends ZMObject implements StatusCheck {
    const ACTIVITY_LOG_RECORD_THRESHOLD = 50000;
    const ACTIVITY_LOG_DATE_THRESHOLD = 60;

    /**
     * {@inheritDoc}
     */
    public function getStatusMessages() {
        $messages = array();

        $result = \ZMRuntime::getDatabase()->querySingle('SELECT COUNT(log_id) AS counter from %table.admin_activity_log%', array(), 'admin_activity_log');
        if (0 < $result['counter']) {
            $reset = null;
            if (self::ACTIVITY_LOG_RECORD_THRESHOLD < $result['counter']) {
                $reset = sprintf(_zm('The Admin Activity Log table has over %s records and should be cleaned ... '), self::ACTIVITY_LOG_RECORD_THRESHOLD);
            } else {
                $sql = 'SELECT MIN(access_date) AS access_date FROM %table.admin_activity_log% WHERE access_date < DATE_SUB(CURDATE(), INTERVAL '.self::ACTIVITY_LOG_DATE_THRESHOLD.' DAY)';
                $result = \ZMRuntime::getDatabase()->querySingle($sql);
                if ($result && null != $result['access_date']) {
                    $reset = sprintf(_zm('The Admin Activity Log table has records more than %s days old and should be cleaned ... '), self::ACTIVITY_LOG_DATE_THRESHOLD);
                }
            }
            if ($reset) {
                $messages[] = array(StatusCheck::STATUS_NOTICE, $reset);
            }
        }

        if (!defined('DEFAULT_CURRENCY')) { $messages[] = array(StatusCheck::STATUS_WARN, _zm('Please set a default currency.')); }
        if (!defined('DEFAULT_LANGUAGE') || DEFAULT_LANGUAGE=='') { $messages[] = array(StatusCheck::STATUS_NOTICE, _zm('Please set a default language.')); }

        return $messages;
    }

}
