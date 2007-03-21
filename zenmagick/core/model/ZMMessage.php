<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * A single message.
 *
 * @author mano
 * @package net.radebatz.zenmagick.model
 * @version $Id$
 */
class ZMMessage extends ZMModel {
    var $text_;
    var $type_;


    /**
     * Create new message.
     *
     * @param string text The message text.
     * @param string type The message type.
     */
    function ZMMessage($text, $type='error') {
        parent::__construct();

        $this->text_ = $text;
        $this->type_ = $type;
    }

    /**
     * Create new message.
     *
     * @param string text The message text.
     * @param string type The message type.
     */
    function __construct($text, $type='error') {
        $this->ZMMessage($text, $type);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the message text.
     *
     * @return string The message text.
     */
    function getText() { return $this->text_; }

    /**
     * Get the message type.
     *
     * @return string The message type.
     */
    function getType() { return $this->type_; }

}

?>
