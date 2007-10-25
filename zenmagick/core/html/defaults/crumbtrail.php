<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
     * Helper to format a given <code>ZMCrumbtrail</code>.
     *
     * @package org.zenmagick.html.defaults
     * @param ZMCrumbtrail crumbtrail A <code>ZMCrumbtrail</code> instance.
     * @param string sep A separator string.
     * @return string A fully HTML formatted crumbtrail.
     */
    function zm_build_crumbtrail($crumbtrail, $sep) {
        $html = '<div id="crumbtrail">';
        $first = true;
        foreach ($crumbtrail->getCrumbs() as $crumb) {
            if (!$first) $html .= $sep;
            $first = false;
            if (null != $crumb->getURL()) {
                $html .= '<a href="'.$crumb->getURL().'">'.zm_htmlencode(zm_l10n_get($crumb->getName()), false).'</a>';
            } else {
				        $html .= zm_htmlencode(zm_l10n_get($crumb->getName()), false);
            }
        }
		    $html .= '</div>';
        return $html;
    }

?>
