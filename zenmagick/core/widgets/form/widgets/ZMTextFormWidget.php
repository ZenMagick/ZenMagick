<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * <p>A single line text input form widget.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.widgets.form.widgets
 * @version $Id$
 */
class ZMTextFormWidget extends ZMFormWidget {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
    public function render() {
        // XXX: move to utils? 
        // generic attributes
        $attrNames = array('id', 'size', 'maxlength');
        $attr = '';
        $html = ZMToolbox::instance()->html;
        foreach ($this->properties_ as $name => $value) {
            if (in_array($name, $attrNames)) {
                $attr .= ' '.$name.'="'.$html->encode($value, false).'"';
            }
        }

        // name
        $attr .= ' name="'.$this->getName().'"';

        // value
        $attr .= ' value="'.$html->encode($this->getValue(), false).'"';

        $slash = ZMSettings::get('isXHTML') ? '/' : '';
        return '<input type="text"'.$attr.$slash.'>';
    }

    /**
     * {@inheritDoc}
     */
    public function compare($value) {
        return $valule == $this->getValue();
    }

}

?>
