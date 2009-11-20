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
 * Simple session handler interface.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc
 * @version $Id$
 */
interface ZMSessionHandler {
    /**
     * Open session handling.
     */
    public function open();

    /**
     * Read session.
     *
     * @param string id The session id.
     */
    public function read($id);

    /**
     * Write session.
     *
     * @param string id The session id.
     * @param mixed data The session data.
     */
    public function write($id, $data);

    /**
     * Destroy session.
     *
     * @param string id The session id.
     */
    public function destroy($id);

    /**
     * Garbage collection callback.
     */
    public function gc();

    /**
     * Close session handling.
     */
    public function close();

}

?>
