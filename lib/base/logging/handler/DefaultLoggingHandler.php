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
namespace zenmagick\base\logging\handler;

use Exception;
use zenmagick\base\Runtime;
use zenmagick\base\ZMException;
use zenmagick\base\ZMObject;
use zenmagick\base\logging\Logging;
use zenmagick\base\logging\LoggingHandler;


/**
 * Default logging handler.
 *
 * <p>Simple logger writing to the <em>SAPI logging handler</em>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class DefaultLoggingHandler extends ZMObject implements LoggingHandler {
    protected $logLevel;


    /**
     * Create new instance.
     *
     * @param int logLevel Optional custom log level; default is <code>null</code> for none.
     */
    public function __construct($logLevel=null) {
        parent::__construct();
        $this->logLevel = $logLevel;
    }


    /**
     * Do the actual logging.
     *
     * @param string msg The message.
     */
    protected function doLog($msg) {
        error_log(trim(html_entity_decode(strip_tags($msg))), 4);
    }

    /**
     * {@inheritDoc}
     */
    public function setLogLevel($logLevel) {
        $this->logLevel = $logLevel;
    }

    /**
     * {@inheritDoc}
     */
    public function getLogLevel() {
        return $this->logLevel;
    }

    /**
     * {@inheritDoc}
     */
    public function log($msg, $level) {
        if (array_key_exists($level, Logging::$LOG_LEVEL)) {
            $msg = Logging::$LOG_LEVEL[$level] . ': ' . $msg;
        }
        $this->doLog($msg.'<br>');
    }

    /**
     * {@inheritDoc}
     */
    public function dump($obj, $msg, $level) {
        ob_start();
        if (null !== $msg) {
            if (array_key_exists($level, Logging::$LOG_LEVEL)) {
                $msg = Logging::$LOG_LEVEL[$level] . ': ' . $msg;
            }
            echo $msg."\n";
        }
        echo "<pre>";
        if ($obj instanceof Exception) {
            echo implode("\n", ZMException::formatStackTrace($obj->getTrace()));
        } else if ($obj instanceof ZMObject) {
            echo $obj;
        } else {
            echo get_class($obj);
        }
        echo "\n</pre>";
        $info = ob_get_clean();
        $this->doLog($info);
    }

    /**
     * {@inheritDoc}
     */
    public function trace($msg, $level) {
        ob_start();
        if (null !== $msg) {
            if (is_array($msg)) {
                echo "<pre>";
                print_r($msg);
                echo "</pre>";
            } else {
                if (array_key_exists($level, Logging::$LOG_LEVEL)) {
                    $msg = Logging::$LOG_LEVEL[$level] . ': ' . $msg;
                }
                echo '<h3>'.$msg.":</h3>\n";
            }
        }
        $filesystem = $this->container->get('filesystem');
        echo "<pre>";
        echo implode("\n", ZMException::formatStackTrace(debug_backtrace()));
        echo "</pre>";
        $info = ob_get_clean();
        $this->doLog($info);
    }

    /**
     * {@inheritDoc}
     */
    public function logError($line, $info) {
        $this->log($line, Logging::ERROR);
    }

}
