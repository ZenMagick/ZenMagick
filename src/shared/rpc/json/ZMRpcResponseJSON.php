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
 * JSON RPC response.
 *
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.rpc.json
 */
class ZMRpcResponseJSON implements ZMRpcResponse
{
    private $rpcRequest;
    private $status;
    private $messages;
    private $data;
    private $returnCode;

    /**
     * Create new instance
     *
     * @param ZMRpcRequest rpcRequest The current RPC request; default is <code>null</code>.
     */
    public function __construct($rpcRequest=null)
    {
        $this->rpcRequest = $rpcRequest;
        $this->status = true;
        $this->messages = array();
        $this->data = null;
        $this->returnCode = 0;
    }

    /**
     * Set the corresponding RPC request.
     *
     * @param ZMRpcRequest rpcRequest The current RPC request; default is <code>null</code>.
     */
    public function setRpcRequest($rpcRequest)
    {
        $this->rpcRequest = $rpcRequest;
    }

    /**
     * {@inheritDoc}
     */
    public function addMessage($msg, $type)
    {
        if (!array_key_exists($type, $this->messages)) {
            $this->messages[$type] = array();
        }
        $this->messages[$type][] = $msg;
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus($status, $code=0)
    {
        $this->status = $status;
        $this->returnCode = $code;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritDoc}
     */
    public function getReturnCode()
    {
        return $this->returnCode;
    }

    /**
     * {@inheritDoc}
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getContentType()
    {
        return "application/json; charset=UTF-8";
    }

    /**
     * Format as string.
     */
    public function __toString()
    {
        $response = new stdClass();
        $response->id = $this->rpcRequest->getId();
        $response->jsonrpc = "2.0";
        if ($this->status) {
            // ok: put data into result
            $response->result = new stdClass();
            $response->result->data = $this->data;
            $response->result->messages = $this->messages;
        } else {
            // error: create default message, put actual messages into data
            $response->error = new stdClass();
            $response->error->code = $this->returnCode;
            $response->error->message = "failed";
            $response->error->data = new stdClass();
            // possible data
            $response->error->data->data = $this->data;
            $response->error->data->messages = $this->messages;
        }

        return json_encode($response);
    }

}
