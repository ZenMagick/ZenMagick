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
?>
<?php

use zenmagick\base\ZMException;

/**
 * Database exception.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package org.zenmagick.core.database
 */
class ZMDatabaseException extends ZMException {

    /**
     * Create new instance.
     *
     * @param string message The message; default is <code>null</code>.
     * @param int code The exception code; default is <em>0</em>.
     * @param Exception previous The original exception (if any) for chaining; default is <code>null</code>.
     */
    function __construct($message=null, $code=0, $previous=null) {
        parent::__construct($message, $code, $previous);
    }

}
