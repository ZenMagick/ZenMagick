<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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


/**
 * FirePHP ZenMagick logging service.
 *
 * <p>Since FirePHP does only know four level of logging, <em>DEBUG</em> <strong>and</strong> <em>TRACE</em>
 * are mapped to FirePHP's <em>LOG</em> level.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_firephp
 */
class Logging extends ZMLogging {
    protected $LEVEL_MAP = null;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        // resolve before using it
        ZMLoader::resolve('FirePHP');

        // we need to resolve FirePHP before using it, therefore
        // we can't initialize this globally
        $this->LEVEL_MAP = array(
            ZMLogging::ERROR => FirePHP::ERROR,
            ZMLogging::WARN => FirePHP::WARN,
            ZMLogging::INFO => FirePHP::INFO,
            ZMLogging::DEBUG => FirePHP::LOG,
            ZMLogging::TRACE => FirePHP::LOG
      );
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Simple logging function.
     *
     * <p>Messages will either be appended to the webserver's error log or, if a custom
     * error handler is installed, trigger a <em>E_USER_NOTICE</em> error.</p>
     *
     * @param string msg The message to log.
     * @param int level Optional level; default: <code>ZMLogging::INFO</code>.
     */
    public function log($msg, $level=ZMLogging::INFO) {
        if (ZMSettings::get('isLogEnabled') && $level <= ZMSettings::get('logLevel')) {
            FirePHP::getInstance(true)->fb($msg, $this->LEVEL_MAP[$level]);
        }
    }

    /**
     * Simple dump function.
     *
     * @param mixed obj The object to dump.
     * @param string msg An optional message.
     * @param int level Optional level; default: <code>ZMLogging::DEBUG</code>.
     */
    public function dump($obj, $msg=null, $level=ZMLogging::DEBUG) {
        if (ZMSettings::get('isLogEnabled') && $level <= ZMSettings::get('logLevel')) {
            FirePHP::getInstance(true)->fb($obj, $msg, FirePHP::DUMP);
        }
    }

    /**
     * Create a simple stack trace.
     *
     * @param mixed msg An optional string or array.
     * @param int level Optional level; default: <code>ZMLogging::DEBUG</code>.
     */
    public function trace($msg=null, $level=ZMLogging::DEBUG) {
        if (ZMSettings::get('isLogEnabled') && $level <= ZMSettings::get('logLevel')) {
            FirePHP::getInstance(true)->fb($msg, FirePHP::TRACE);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function logError($line) {
        FirePHP::getInstance(true)->fb($line, FirePHP::INFO);
    }

}

?>
