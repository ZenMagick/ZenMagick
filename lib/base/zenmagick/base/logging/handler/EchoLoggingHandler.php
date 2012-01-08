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

use zenmagick\base\Runtime;
use zenmagick\base\logging\Logging;

/**
 * Echo logging handler.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EchoLoggingHandler extends DefaultLoggingHandler {

    /**
     * {@inheritDoc}
     */
    protected function doLog($msg) {
        echo '['.Runtime::getExecutionTime($eventInfo['timestamp']).'] '.$msg;
    }

}
