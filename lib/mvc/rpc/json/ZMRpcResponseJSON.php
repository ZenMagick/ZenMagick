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
 * JSON RPC response.
 *
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.rpc.json
 */
class ZMRpcResponseJSON implements ZMRpcResponse {
    private $rpcRequest_;
    private $status_;
    private $messages_;
    private $data_;


    /**
     * Create new instance
     *
     * @param ZMRpcRequest rpcRequest The current RPC request.
     */
    function __construct($rpcRequest) {
        $this->rpcRequest_ = $rpcRequest;
        $this->status_ = true;
        $this->messages_ = array();
        $this->data_ = null;
    }



    /**
     * {@inheritDoc}
     */
    public function addMessage($msg, $type) {
        if (!array_key_exists($type, $this->messages_)) {
            $this->messages_[$type] = array();
        }
        $this->messages_[$type][] = $msg;
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus($status) {
        $this->status_ = $status;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus() {
        return $this->status_;
    }

    /**
     * {@inheritDoc}
     */
    public function setData($data) {
        $this->data_ = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getContentType() {
        return "application/json; charset=UTF-8";
    }

    /**
     * Format as string.
     */
    public function __toString() {
        $response = new stdClass();
        $response->id = $this->rpcRequest_->getId();
        $response->jsonrpc = "2.0";
        if ($this->status_) {
            // ok: put data into result
            $response->result = new stdClass();
            $response->result->data = $this->data_;
            $response->result->messages = $this->messages_;
        } else {
            // error: create default message, put actual messages into data
            $response->error = new stdClass();
            $response->error->code = 0;
            $response->error->message = "failed";
            $response->error->data = new stdClass();
            // possible data
            $response->error->data->data = $this->data_;
            $response->error->data->messages = $this->messages_;
        }

        return json_encode($response);
    }

}
