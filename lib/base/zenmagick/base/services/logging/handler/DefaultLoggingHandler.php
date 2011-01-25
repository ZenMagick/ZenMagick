<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
namespace zenmagick\base\services\logging\handler;

/**
 * Default logging handler.
 *
 * <p>Simple logger writing to the <em>SAPI logging handler</em>.</p>
 *
 * <p>Additionally, if <code>display_errors</code> is enabled, all logging will be <em>echo'ed</em> as well.</p>
 *
 * @author DerManoMann
 * @package zenmagick.base.services.logging.handler
 */
class DefaultLoggingHandler implements \zenmagick\base\services\logging\LoggingHandler {
    public static $LABEL = array('NONE', 'ERROR', 'WARN', 'INFO', 'DEBUG', 'TRACE');

    /**
     * Do the actual logging.
     *
     * @param string msg The message.
     */
    protected function doLog($msg) {
        if (@ini_get('display_errors')) {
            echo $msg;
        }
        error_log(trim(html_entity_decode(strip_tags($msg))), 4);
    }

    /**
     * {@inheritDoc}
     */
    public function log($msg, $level) {
        if (array_key_exists($level, self::$LABEL)) {
            $msg = self::$LABEL[$level] . ': ' . $msg;
        }
        $this->doLog($msg.'<br>');
    }

    /**
     * {@inheritDoc}
     */
    public function dump($obj, $msg, $level) {
        ob_start();
        if (null !== $msg) {
            echo '<h3>'.$msg.":</h3>\n";
        }
        echo "<pre>";
        var_dump($obj);
        echo "</pre>";
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
                echo '<h3>'.$msg.":</h3>\n";
            }
        }
        $root = \ZMFileUtils::normalizeFilename(\ZMRuntime::getInstallationPath());
        echo "<pre>";
        foreach (debug_backtrace() as $line) {
            echo ' ';
            if (isset($line['class'])) {
                echo $line['class'].'::';
            }
            if (isset($line['file'])) {
                $file = \ZMFileUtils::normalizeFilename($line['file']);
                $lineNumber = $line['line'];
                $location = '#'.$line['line'].':'.$file;
            } else {
                $location = 'no source';
            }
            // make filename relative
            $file = str_replace($root, '', $file);
            $class = array_key_exists('class', $line) ? $line['class'].'::' : '';
            echo $class.$line['function'].' ('.$location.")\n";
        }
        echo "</pre>";
        $info = ob_get_clean();
        $this->doLog($info);
    }

    /**
     * {@inheritDoc}
     */
    public function logError($line, $info) {
        $this->log($line, \zenmagick\base\services\logging\Logging::ERROR);
    }

}
