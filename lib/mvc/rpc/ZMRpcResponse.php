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

/**
 * RPC response interface.
 *
 * <p>implementations typically would expect the corresponding request instance as only
 * constructor argument.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.rpc
 */
interface ZMRpcResponse {
    const RC_INVALID_CREDENTIALS = 5;
    const RC_NO_CREDENTIALS = 6;


    /**
     * Add a message for the given type.
     *
     * @param string msg The message.
     * @param string type The message type.
     */
    public function addMessage($msg, $type);

    /**
     * Set the status.
     *
     * @param boolean status The status.
     * @param integer code Optional return code; default is <code>0</code>.
     */
    public function setStatus($status, $code=0);

    /**
     * Get the status.
     *
     * @return boolean The status.
     */
    public function getStatus();

    /**
     * Get the return code.
     *
     * @return integer The return code.
     */
    public function getReturnCode();

    /**
     * Set response data.
     *
     * @param mixed data The data.
     */
    public function setData($data);

    /**
     * Return the content type (and encoding).
     *
     * @return string The content type.
     */
    public function getContentType();

    /**
     * Format the response according to this implementation's format.
     *
     * @return string The formatted response.
     */
    public function __toString();

}
