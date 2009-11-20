<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 *  will generate HTML in the response page.</p>
 *
 * <p>Browser output depends on the PHP ini setting <em>display_errors</em>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.core.services.misc
 * @version $Id$
 */
class ZMLogging extends ZMObject {
    private static $LABEL = array('NONE', 'ERROR', 'WARN', 'INFO', 'DEBUG', 'TRACE');
    /** Log level: Disabled. */
    const NONE = 0;
    /** Log level: Error. */
    const ERROR = 1;
    /** Log level: Warning. */
    const WARN = 2;
    /** Log level: Info. */
    const INFO = 3;
    /** Log level: Debug. */
    const DEBUG = 4;
    /** Log level: Trace. */
    const TRACE = 5;
    /** Log level: ALL. */
    const ALL = 99999;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Logging');
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
        if (ZMSettings::get('zenmagick.core.logging.enabled') && $level <= ZMSettings::get('zenmagick.core.logging.level')) {
            if (array_key_exists($level, self::$LABEL)) {
                $msg = self::$LABEL[$level] . ': ' . $msg;
            }
            if (ZMSettings::get('zenmagick.core.logging.handleErrors')) {
                @trigger_error($msg, E_USER_NOTICE);
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
        if (ZMSettings::get('zenmagick.core.logging.enabled') && $level <= ZMSettings::get('zenmagick.core.logging.level')) {
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
        if (ZMSettings::get('zenmagick.core.logging.enabled') && $level <= ZMSettings::get('zenmagick.core.logging.level')) {
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
            $root = ZMFileUtils::normalizeFilename(ZMRuntime::getInstallationPath());
            echo "<pre>";
            foreach (debug_backtrace() as $line) {
                echo ' ';
                if (isset($line['class'])) {
                    echo $line['class'].'::';
                }
                $file = ZMFileUtils::normalizeFilename($line['file']);
                // make filename relative
                $file = str_replace($root, '', $file);
                $class = array_key_exists('class', $line) ? $line['class'].'::' : '';
                echo $class.$line['function'].' (#'.$line['line'].':'.$file.")\n";
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
     * Format error handler log line.
     *
     * @param int errno The error level.
     * @param string errstr The error message.
     * @param string errfile The source filename.
     * @param int errline The line number.
     * @param array errcontext All variables of scope when error triggered.
     * @return string A formatted log line.
     */
    protected function formatLog($errno, $errstr, $errfile, $errline, $errcontext) {
        $time = date("d M Y H:i:s");
        // Get the error names from the error number
        $errTypes = array (
        1 => "Error",
        2 => "Warning",
        4 => "Parsing Error",
        8 => "Notice",
        16 => "Core Error",
        32 => "Core Warning",
        64 => "Compile Error",
        128 => "Compile Warning",
        256 => "User Error",
        512 => "User Warning",
        1024 => "User Notice",
        2048 => "Strict",
        4096 => "Recoverable Error",
        8192 => "Deprecated",
        16384 => "User Deprecated",
        );

        if (isset($errTypes[$errno])) {
            $errlevel = $errTypes[$errno];
        } else {
            $errlevel = "Unknown";
        }

        return "\"$time\",\"$errfile: $errline\",\"($errlevel) $errstr\"\r\n";
    }

    /**
     * A callback function that can be overriden to implement custom logging.
     *
     * @param string line The pre-fromatted log line.
     * @param array info All available log information.
     */
    public function logError($line, $info) {
        $logfile = ZMSettings::get('zenmagick.core.logging.filename');
        if (null != ($handle = fopen($logfile, "a"))) {
            fputs($handle, $line);
            fclose($handle);
            ZMFileUtils::setFilePerms($logfile);
        } else {
            error_log($line);
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
        // set back
        error_reporting($level);
        // disabled or not configured?
        if (0 == $level || $errno != ($errno & $level)) {
            return;
        }
        // convert all into an easy to handle array
        $info = array('errno' => $errno, 'msg' => $errstr, 'file' => $errfile, 'line' => $errline, 'context' => $errcontext);

        $line = $this->formatLog($errno, $errstr, $errfile, $errline, $errcontext);
        $this->logError($line, $info);
    }

    /**
     * PHP exception handler callback.
     *
     * @param Exception e The exception.
     */
    public function exceptionHandler($e) {
        $this->logError('Uncaught exception: '.$e->getMessage(), array('errno' => E_ERROR, 'context' => array('exception' => $e)));
    }

}

?>
