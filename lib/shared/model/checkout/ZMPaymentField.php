<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

use ZenMagick\Base\ZMObject;

/**
 * A form field for a payment type.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model.checkout
 */
class ZMPaymentField extends ZMObject {
    private $label_;
    private $html_;


    /**
     * Create new payment (input) field.
     *
     * @param string label The field label.
     * @param string html The (input) field HTML.
     */
    public function __construct($label, $html) {
        parent::__construct();
        $this->label_ = $label;
        $this->html_ = $html;
    }


    /**
     * Get the field name/label.
     *
     * <p><strong>This may contain HTML, depending on the module.</strong></p>
     *
     * @return string The field name/label.
     */
    public function getLabel() { return $this->label_; }

    /**
     * Get the field HTML.
     *
     * @return string The field HTML.
     */
    public function getHTML() { return $this->html_; }

}
