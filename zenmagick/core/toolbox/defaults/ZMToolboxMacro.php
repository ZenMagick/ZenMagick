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
 * Macro utilities.
 *
 * @author mano
 * @package org.zenmagick.toolbox.defaults
 * @version $Id$
 */
class ZMToolboxMacro extends ZMObject {

    /**
     * <code>phpinfo</code> wrapper.
     *
     * @param what What to display (see phpinfo manual for more); default is <code>1</code>.
     * @param boolean echo If <code>true</code>, the info will be echo'ed as well as returned.
     * @return string The <code>phpinfo</code> output minus a few formatting things that break validation.
     */
    public function phpinfo($what=1, $echo=ZM_ECHO_DEFAULT) {
        ob_start();                                                                                                       
        phpinfo($what);                                                                                                       
        $info = ob_get_clean();                                                                                       
        $info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $info);
        $info = str_replace('width="600"', '', $info);

        if ($echo) echo $info;
        return $info;
    }

}

?>
