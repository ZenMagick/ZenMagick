<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
     * Helper to generate HTML for product attributes.
     *
     * <p>Usage sample:</p>
     *
     * <code><pre>
     *  &lt;?php $attributes = zm_buildAttributeElements($product); ?&gt;
     *  &lt;?php foreach ($attributes as $attribute) { ?&gt;
     *  &nbsp;&nbsp;  &lt;?php foreach ($attribute['html'] as $option) { ?&gt;
     *  &nbsp;&nbsp;&nbsp;&nbsp;    &lt;p&gt;&lt;?php echo $option ?&gt;&lt;/p&gt;
     *  &nbsp;&nbsp;  &lt;?php } ?&gt;
     *  &lt;?php } ?&gt;
     * </pre></code>
     *
     * @package org.zenmagick.deprecated
     * @param ZMProduct product A <code>ZMProduct</code> instance.
     * @return array An array containing HTML formatted attributes.
     * @deprecated use the new toolbox instead!
     */
    function zm_build_attribute_elements($product) {
        return ZMRequest::instance()->getToolbox()->macro->productAttributes($product);
    }

    /**
     * format offer price
     * @package org.zenmagick.deprecated
     * @deprecated use the new toolbox instead!
     */
    function zm_fmt_price($product, $echo=ZM_ECHO_DEFAULT) {
        return ZMRequest::instance()->getToolbox()->macro->productPrice($product, $echo);
    }

?>
