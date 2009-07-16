<?php
/*
 * ZenMagick Core - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * <p>A text area form widget.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.widgets.form
 * @version $Id$
 */
class ZMTextAreaFormWidget extends ZMFormWidget {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->setAttributeNames(array('id', 'class', 'cols', 'rows', 'wrap'));
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
        $html = ZMToolbox::instance()->html;
        return '<textarea'.$this->getAttributeString(false).'>'.$html->encode($this->getValue(), false).'</textarea>';
    }

    /**
     * {@inheritDoc}
     */
    public function compare($value) {
        return $value == $this->getValue();
    }

}

?>
