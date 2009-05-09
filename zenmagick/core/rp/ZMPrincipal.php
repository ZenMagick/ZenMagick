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
 * This interface represents the abstract notion of a principal, 
 * which can be used to represent any entity, such as an individual, a corporation, and a login id. 
 *
 * @author DerManoMann
 * @package org.zenmagick.rp
 * @version $Id$
 */
interface ZMPrincipal {
    /** Access level registered. */
    const REGISTERED = 'registered';
    /** Access level guest. */
    const GUEST = 'guest';
    /** Access level anonymous. */
    const ANONYMOUS = 'anonymous';


    /**
     * Returns a string representation of this principal. 
     *
     * @return string A string representation of this principal.
     */
    public function getName();

    /**
     * Get the principal type.
     *
     * <p>A principal can be classified using one one of:</p>
     * <ul>
     *  <li>REGISTERED</li>
     *  <li>GUEST</li>
     *  <li>ANONYMOUS</li>
     * </ul>
     *
     * @return string account type.
     */
    public function getType();

}

?>
