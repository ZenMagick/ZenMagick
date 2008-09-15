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
 * ZenMagick logging service.
 *
 * <p>The <em>dump</em> and <em>trace</em> methods are browser oriented and
 * will generate HTML in the response page.</p>
 *
 * <p>Browser output depends on the PHP ini setting <em>display_errors</em>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.service
 */
class ZMLogging extends ZMObject {
    const NONE = 0;
    const ERROR = 1;
    const WARN = 2;
    const INFO = 3;
    const DEBUG = 4;
    const TRACE = 5;
    const ALL = 99999;


    /**
     * Create new instance.
     */
    function __construct() {
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton(ZMSettings::get('loggingProvider'));
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
            if (ZMSettings::get('isZMErrorHandler')) {
                trigger_error($msg, E_USER_NOTICE);
            } else {
                error_log($msg);
            }
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
            ob_start();
            if (null !== $msg) {
                echo '<h3>'.$msg.":</h3>\n";
            }
            var_dump($obj);
            $info = ob_get_clean();
            if (@ini_get('display_errors')) {
                echo $info;
            }
            $this->log(trim(html_entity_decode(strip_tags($info))), $level);
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
            ob_start();
            if (null !== $msg) {
                if (is_array($msg)) {
                    echo "<pre>";
                    print_r($msg);
                    echo "</pre>";
                } else {
                    echo '<h3>'.$msg.":</h3>\n";
                }
            }
            echo "<pre>";
            foreach (debug_backtrace() as $line) {
                echo ' ';
                if (isset($line['class'])) {
                    echo $line['class'].'::';
                }
                echo $line['function'].' (#'.$line['line'].':'.$line['file'].")\n";
            }
            echo "</pre>";
            $info = ob_get_clean();
            if (@ini_get('display_errors')) {
                echo $info;
            }
            $this->log(trim(html_entity_decode(strip_tags($info))), $level);
        }
    }

    /**
     * PHP error handler callback.
     *
     * <p>if configured, this method will append all messages to the file
     * configured with <em>zmLogFilename</em>.</p>
     * 
     * <p>If no file is configured, the regular webserver error file will be used.</p>
     *
     * @param int errno The error level.
     * @param string errstr The error message.
     * @param string errfile The source filename.
     * @param int errline The line number.
     * @param array errcontext All variables of scope when error triggered.
     */
    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) { 
        // get current level
        $level = error_reporting(E_ALL);
        error_reporting($level);
        // disabled or not configured?
        if (0 == $level || $errno != ($errno&$level)) {
            return;
        }

        $time = date("d M Y H:i:s"); 
        // Get the error type from the error number 
        $errtypes = array (1    => "Error",
                           2    => "Warning",
                           4    => "Parsing Error",
                           8    => "Notice",
                           16   => "Core Error",
                           32   => "Core Warning",
                           64   => "Compile Error",
                           128  => "Compile Warning",
                           256  => "User Error",
                           512  => "User Warning",
                           1024 => "User Notice",
                           2048 => "Strict",
                           4096 => "Recoverable Error"
        ); 


        if (isset($errtypes[$errno])) {
            $errlevel = $errtypes[$errno]; 
        } else {
            $errlevel = "Unknown";
        }

        $line = "\"$time\",\"$errfile: $errline\",\"($errlevel) $errstr\"\r\n"; 
        if (null != ($handle = fopen(ZMSettings::get('zmLogFilename'), "a"))) {
            fputs($handle, $line); 
            fclose($handle); 
        } else {
            error_log($line);
        }
    } 

}

?>
