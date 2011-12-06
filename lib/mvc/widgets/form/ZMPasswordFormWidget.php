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
 * A password input form widget.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.widgets.form
 */
class ZMPasswordFormWidget extends ZMTextFormWidget {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->set('autocomplete','off');
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
    public function render($request, $view) {
        $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';
        return '<input type="password"'.$this->getAttributeString($request, false).$slash.'>';
    }

}
