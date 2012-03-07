<?php
/*
 * ZenMagick - Another PHP framework.
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
?>
<?php
namespace zenmagick\base\logging;

/**
 * Logging handler.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
interface LoggingHandler {

    /**
     * Get the log level for this handler.
     *
     * @return int The log level or <code>null</code> to use the global level.
     */
    public function getLogLevel();

    /**
     * Set the custom log level for this handler.
     *
     * @param int logLevel The new custom log level.
     */
    public function setLogLevel($logLevel);

    /**
     * Log message.
     *
     * @param string msg The message to log.
     * @param int level The log level.
     */
    public function log($msg, $level);

    /**
     * Dump data and instances.
     *
     * @param mixed obj The object to dump.
     * @param string msg Dump message.
     * @param int level The log level.
     */
    public function dump($obj, $msg, $level);

    /**
     * Log the current stack.
     *
     * @param string msg A message.
     * @param int level The log level.
     */
    public function trace($msg, $level);

    /**
     * A callback function for PHP error logging.
     *
     * @param string line The pre-fromatted log line [as per <code>formatLog(..)</code>].
     * @param array info All available log information.
     */
    public function logError($line, $info);

}
