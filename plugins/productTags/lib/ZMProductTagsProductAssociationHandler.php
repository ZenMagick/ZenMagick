<?php
/*
 * ZenMagick - Smart e-commerce
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
 * Product association handler for <em>tags</em>.
 *
 * <p>Supports <em>languageId</em> parameter in the <code>$args</code> map.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.productTags
 */
class ZMProductTagsProductAssociationHandler implements ZMProductAssociationHandler {

    /**
     * {@inheritDoc}
     */
    public function getType() {
       return "productTags";
    }

    /**
     * {@inheritDoc}
     */
    public function getProductAssociationsForProductId($productId, $args=array(), $all=false) {
        if (array_key_exists('languageId', $args)) {
            $languageId = $args['languageId'];
        } else {
            throw new ZMException('missing languageId');
        }

        $tags = ZMTags::instance()->getTagsForProductId($productId, $languageId);

        $assoc = array();
        foreach (ZMTags::instance()->getProductIdsForTags($tags, $languageId) as $pid) {
            $assoc[] = new ZMProductAssociation($pid);
        }
        
        return $assoc;
    }

}
