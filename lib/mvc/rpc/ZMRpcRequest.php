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

/**
 * RPC request interface.
 *
 * <p>Generic (R)emote (P)rocedure (C)all request interface, irrespective of the underlying format.</p>
 *
 * <p>Implementations are expected to accept an instance of <code>Request</code> as single constructor
 * argument.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.rpc
 */
interface ZMRpcRequest {

    /**
     * Get the underlying request.
     *
     * @return Request The request.
     */
    public function getRequest();

    /**
     * Get the request id.
     *
     * @return int The request id.
     */
    public function getId();

    /**
     * Get the method name to execute.
     *
     * @return string The method name to be called.
     */
    public function getMethod();

    /**
     * Get the payload.
     *
     * @return mixed The actual data..
     */
    public function getData();

    /**
     * Create a matching response object.
     *
     * @return ZMRpcResponse A response object that can be used to create a valid response.
     */
    public function createResponse();

}
