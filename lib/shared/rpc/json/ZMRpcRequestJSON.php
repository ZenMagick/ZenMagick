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

use ZenMagick\Base\Beans;

/**
 * RPC request using JSON.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMRpcRequestJSON
{
    private $request_;
    private $json_;

    /**
     * Create new instance
     *
     * @param ZenMagick\Http\Request request The current request; default is <code>null</code>.
     */
    public function __construct($request=null)
    {
        $this->request_ = $request;
        $this->json_ = json_decode(trim(file_get_contents('php://input')));

    }

    /**
     * Set the request.
     *
     * @param ZenMagick\Http\Request request The current request.
     */
    public function setRequest($request)
    {
        $this->request_ = $request;
    }
    /**
     * {@inheritDoc}
     */
    public function getRequest()
    {
        return $this->request_;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->json_->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return $this->json_->method;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return $this->json_->params;
    }

    /**
     * {@inheritDoc}
     */
    public function createResponse()
    {
        $rpcResponse = Beans::getBean('ZMRpcResponseJSON');
        $rpcResponse->setRpcRequest($this);

        return $rpcResponse;
    }

}
