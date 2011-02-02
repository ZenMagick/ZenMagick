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

use \zenmagick\base\services\logging\Logging;


/**
 * Echo logging handler.
 *
 * <p>If <code>display_errors</code> is enabled, all logging will be <em>echo'ed</em>.</p>
 *
 * @author DerManoMann
 * @package zenmagick.base.services.logging.handler
 */
class EchoLoggingHandler extends DefaultLoggingHandler {

    /**
     * {@inheritDoc}
     */
    public function log($msg, $level) {
        if (@ini_get('display_errors')) {
            if (array_key_exists($level, Logging::$LOG_LEVEL)) {
                $msg = Logging::$LOG_LEVEL[$level] . ': ' . $msg;
            }
            echo $msg.'<br>';
        }
    }

}
