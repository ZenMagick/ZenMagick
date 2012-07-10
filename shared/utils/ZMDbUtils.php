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
use zenmagick\base\Beans;
use zenmagick\apps\store\admin\utils\SQLRunner;

/**
 * SQL/database utils.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.utils
 */
class ZMDbUtils {

    /**
     * Execute a SQL patch.
     *
     * @param string sql The sql.
     * @param array Result message list.
     * @param boolean Debug flag.
     * @return boolean <code>true</code> for success, <code>false</code> if the execution fails.
     */
    public static function executePatch($sql, $messages, $debug=false) {
        // disable to allow plugins to insert HTML into the database...
        //$sql = ZMSecurityTools::sanitize($sql);
        if (!empty($sql)) {
            $results = SQLRunner::execute_sql($sql, $debug);
            foreach (ZMDbUtils::processPatchResults($results) as $msg) {
                $messages[] = $msg;
            }
            return empty($results['error']);
        }

        return true;
    }

    /**
     * Create a message.
     *
     * @param string msg The message.
     * @param string type The type.
     * @return object instance of <code>zenmagick\http\messages\Message</code>
     */
    private static function createMessage($msg, $type) {
        // @todo just use the messages service throughout
        $message = Beans::getBean('zenmagick\http\messages\Message');
        $message->setText($msg);
        $message->setType($type);
        return $message;
    }

    /**
     * Process SQL patch messages.
     *
     * @param array The execution results.
     * @return array The results converted to messages.
     */
    private static function processPatchResults($results) {
        $messages = array();
        if ($results['queries'] > 0 && $results['queries'] != $results['ignored']) {
            $messages[] = self::createMessage($results['queries'].' statements processed.', 'success');
        } else {
            $messages[] = self::createMessage('Failed: '.$results['queries'].'.', 'error');
        }

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $value) {
                $messages[] = self::createMessage('ERROR: '.$value.'.', 'error');
            }
        }
        if ($results['ignored'] != 0) {
            $messages[] = self::createMessage('Note: '.$results['ignored'].' statements ignored. See "upgrade_exceptions" table for additional details.', 'warn');
        }

        return $messages;
    }

}
