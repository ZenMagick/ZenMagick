<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\plugins\firePHP;

use zenmagick\base\logging\Logging;
use zenmagick\base\logging\handler\DefaultLoggingHandler;

/**
 * FirePHP ZenMagick logging service.
 *
 * <p>Since FirePHP does only know four level of logging, <em>DEBUG</em> <strong>and</strong> <em>TRACE</em>
 * are mapped to FirePHP's <em>LOG</em> level.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.plugins.firePHP
 */
class FirePHPLoggingHandler extends DefaultLoggingHandler {
    private static $LEVEL_MAP = array(
        Logging::ERROR => FirePHP::ERROR,
        Logging::WARN => FirePHP::WARN,
        Logging::INFO => FirePHP::INFO,
        Logging::DEBUG => FirePHP::LOG,
        Logging::TRACE => FirePHP::LOG
    );


    /**
     * {@inheritDoc}
     */
    public function log($msg, $level) {
        if (!headers_sent()) {
            FirePHP::getInstance(true)->fb($msg, self::$LEVEL_MAP[$level]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function dump($obj, $msg=null, $level=Logging::DEBUG) {
        if (!headers_sent()) {
            if ($obj instanceof Exception) {
                FirePHP::getInstance(true)->fb($obj);
            } else {
                FirePHP::getInstance(true)->fb($obj, $msg, FirePHP::DUMP);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function trace($msg=null, $level=Logging::DEBUG) {
        if (!headers_sent()) {
            FirePHP::getInstance(true)->fb($msg, FirePHP::TRACE);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function logError($line, $info) {
        // map error type to log level
        $errTypes = array (
            1 => FirePHP::ERROR,
            2 => FirePHP::WARN,
            4 => FirePHP::ERROR,
            8 => FirePHP::INFO,
            16 => FirePHP::ERROR,
            32 => FirePHP::WARN,
            64 => FirePHP::ERROR,
            128 => FirePHP::WARN,
            256 => FirePHP::ERROR,
            512 => FirePHP::WARN,
            1024 => FirePHP::LOG,
            2048 => FirePHP::LOG,
            4096 => FirePHP::ERROR,
            8192 => FirePHP::LOG,
            16384 => FirePHP::LOG,
        );

        if (!headers_sent()) {
            if (array_key_exists('exception', $info)) {
                FirePHP::getInstance(true)->fb($info['exception']);
            } else {
                FirePHP::getInstance(true)->fb($line, $errTypes[$info['errno']]);
            }
        }
    }

}
