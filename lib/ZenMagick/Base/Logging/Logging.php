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
namespace ZenMagick\Base\Logging;

use Symfony\Bridge\Monolog\Logger;

/**
 * The ZenMagick logging service.
 *
 * @see https://github.com/Seldaek/monolog/blob/master/README.mdown for level descriptions.
 *
 * <p>Logging manager. The actual logging is delegated to all configured logging handlers.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Logging extends Logger {
    /** Log level: Warning. */
    const WARN = 300;
    /** Log level: Trace. */
    const TRACE = 100;

    /**
     * Log error.
     *
     * Alias for <code>err</code>
     * @param string message The message to log.
     * @param array context (unimplemented)
     */
    public function error($message, array $context = array()) {
        $this->err($message, $context);
    }

    /**
     * Add a message by log level.
     *
     * Just like <code>parent::addRecord</code>
     * but with reversed arguments.
     *
     * @param string message
     * @param int level
     */
    public function log($message, $level=self::INFO) {
        $this->addRecord($level, $message);
    }
}
