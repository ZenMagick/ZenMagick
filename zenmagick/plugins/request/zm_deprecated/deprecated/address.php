<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 *
 * $Id$
 */
?>
<?php

    /**
     * Format an address according to the countries address format.
     *
     * <p>The following values are available for display:</p>
     *
     * <ul>
     *  <li><code>$firstname</code> - The first name</li>
     *  <li><code>$lastname</code> - The last name</li>
     *  <li><code>$company</code> - The company name</li>
     *  <li><code>$street</code> - The street address</li>
     *  <li><code>$streets</code> - Depending on availablility either <code>$street</code> or <code>$street$cr$suburb</code></li>
     *  <li><code>$suburb</code> - The subrub</li>
     *  <li><code>$city</code> - The city</li>
     *  <li><code>$state</code> - The state (either from the list of states or manually entered)</li>
     *  <li><code>$country</code> - The country name</li>
     *  <li><code>$postcode</code>/<code>$zip</code> - The post/zip code</li>
     *  <li><code>$hr</code> - A horizontal line</li>
     *  <li><code>$cr</code> - New line character</li>
     *  <li><code>$statecomma</code> - The sequence <code>$state, </code> (note the trailing space)</li>
     * </ul>
     *
     * <p>If address is <code>null</code>, the localized version of <em>N/A</em> will be returned.</p>
     *
     * <p>All output is HTML encoded.</p>
     *
     * @package org.zenmagick.deprecated
     * @param ZMAddress address The address to format.
     * @param boolean html If <code>true</code>, format as HTML, otherwise plain text.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A fully formatted address that, depending on the <em>html</code> flag, is either HTML or ASCII formatted.
     * @deprecated use the new toolbox instead!
     */
    function zm_format_address($address, $html=true, $echo=ZM_ECHO_DEFAULT) {
        return ZMRequest::instance()->getToolbox()->macro->formatAddress($address, $html, $echo);
    }

?>
