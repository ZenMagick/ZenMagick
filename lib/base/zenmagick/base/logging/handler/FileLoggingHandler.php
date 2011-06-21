<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace zenmagick\base\logging\handler;

use zenmagick\base\logging\Logging;

/**
 * File logging handler.
 *
 * <p>If <code>display_errors</code> is enabled, all logging will be <em>echo'ed</em>.</p>
 *
 * @author DerManoMann
 * @package zenmagick.base.logging.handler
 */
class FileLoggingHandler extends DefaultLoggingHandler {
    private $filename;

    /**
     * Set the log filename.
     *
     * @param string filename The log filename.
     */
    public function setFilename($filename) {
        $this->filename = $filename;
    }

    /**
     * {@inheritDoc}
     */
    public function log($msg, $level) {
        if (array_key_exists($level, Logging::$LOG_LEVEL)) {
            $handle = fopen($this->filename, 'ab');
            if ($handle) {
                $line = Logging::$LOG_LEVEL[$level] . ': ' . trim($msg) . "\n";
                fwrite($handle, $line);
                fclose($handle);
            }
        }
    }

}
