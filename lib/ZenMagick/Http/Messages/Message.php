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
namespace ZenMagick\http\messages;

use ZenMagick\http\session\FlashBag;
/**
 * A single message.
 *
 * <p>References can be anything, but usually would be a field number if it
 * is a validation message. Everything else typically would be <code>null</code>.</p>
 *
 * <p><strong>Note:</strong> The message text is expected to be already localised.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Message {
    private $text_;
    private $type_;
    private $ref_;


    /**
     * Create new message.
     *
     * @param string text The message text; default is an empty string <code>''</code>.
     * @param string type The message type; default is <em>FlashBag::T_MESSAGE</em>.
     * @param string ref The referencing resource; default is <code>FlashBag::REF_GLOBAL</code>.
     */
    public function __construct($text='', $type=FlashBag::T_MESSAGE, $ref=FlashBag::REF_GLOBAL) {
        $this->text_ = $text;
        $this->type_ = $type;
        $this->ref_ = $ref;
    }


    /**
     * Get the message text.
     *
     * @return string The message text.
     */
    public function getText() { return $this->text_; }

    /**
     * Get the message type.
     *
     * @return string The message type.
     */
    public function getType() { return $this->type_; }

    /**
     * Get the message reference.
     *
     * @return string The message reference.
     */
    public function getRef() { return $this->ref_; }

    /**
     * Set the message text.
     *
     * @param string text The message text.
     */
    public function setText($text) { $this->text_ = $text; }

    /**
     * Set the message type.
     *
     * @param string type The message type.
     */
    public function setType($type) { $this->type_ = $type; }

    /**
     * Set the message reference.
     *
     * @param string ref The message reference.
     */
    public function setRef($ref) { $this->ref_ = $ref; }

}
