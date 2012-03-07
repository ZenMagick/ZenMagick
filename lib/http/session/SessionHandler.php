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
namespace zenmagick\http\session;


/**
 * Simple session handler interface.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
interface SessionHandler {

    /**
     * Open session handling.
     *
     * @param string path The save path.
     * @param string name The session name.
     */
    public function open($path, $name);

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
     *
     * @param int lifetime The lifetime.
     */
    public function gc($lifetime);

    /**
     * Close session handling.
     */
    public function close();

    /**
     * Set the expiry time.
     *
     * @param int expiry The expiry time for sessions.
     */
    public function setExpiryTime($expiryTime);

}
