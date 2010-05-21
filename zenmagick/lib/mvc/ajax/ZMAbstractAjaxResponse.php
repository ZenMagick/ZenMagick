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

/**
 * Abstract ajax response implementation.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.ajax
 * @version $Id$
 */
abstract class ZMAbstractAjaxResponse extends ZMObject {
    protected $messages_;
    protected $status_;
    protected $data_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->messages_ = array();
        $this->status_ = false;
        $this->data_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
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

}
