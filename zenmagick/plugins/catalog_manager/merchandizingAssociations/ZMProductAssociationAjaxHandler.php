<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Ajax controller for product associations.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.merchandizingAssociations
 * @version $Id$
 * @todo: get working again
 */
class ZMProductAssociationAjaxHandler {

    /**
     * Get product associations for the given product and type.
     *
     * @param mixed target The target Ajax controller (here <code>ZMAjaxCatalogController</code>).
     */
    public function getProductAssociationsForProductIdJSON($target, $request) {
        $productId = $request->getProductId();
        $type = $request->getParameter('type', 0);
        $activeOnly = true;
        if (ZMSettings::get('isAdmin')) {
            $activeOnly = $request->getParameter('active', true);
        }

        $flatObj = $target->flattenObject(ZMProductAssociations::instance()->getProductAssociationsForProductId($productId, $type, $activeOnly), 
                array('sourceId', 'targetId', 'targetProduct' => $target->get('ajaxProductMap')));

        $json = $target->toJSON($flatObj);
        $target->setJSONHeader($json);
    }

}
