<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * <p>References can be anything, but usually would be a field number if it
 * is a validation message. Everything else typically would be <code>null</code>.</p>
 *
 * <p><strong>Note:</strong> The message text needs ot be localised.</p>
 *
 * @author mano
 * @package org.zenmagick.model
 * @version $Id$
 */
class ZMMessage extends ZMModel {
    var $text_;
    var $type_;
    var $ref_;


    /**
     * Create new message.
     *
     * @param string text The message text.
     * @param string type The message type; default is <em>msg</em>.
     * @param string ref The referencing resource; default is <code>global</code>.
     */
    function __construct($text, $type='msg', $ref='global') {
        parent::__construct();

        $this->text_ = $text;
        $this->type_ = $type;
        $this->ref_ = $ref;
    }

    /**
     * Destruct instance.
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

    /**
     * Get the message reference.
     *
     * @return string The message reference.
     */
    function getRef() { return $this->ref_; }

}

?>
