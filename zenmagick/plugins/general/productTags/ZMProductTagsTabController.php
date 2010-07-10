<?php
/*
 * ZenMagick - Smart e-commerce
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
 * Admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.productTags
 * @version $Id$
 */
class ZMProductTagsTabController extends ZMController implements ZMCatalogContentController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $languageId = $request->getSelectedLanguage()->getId();
        return array(
            'productTags' => ZMTags::instance()->getTagsForProductId($request->getProductId(), $languageId),
            'allTags' => ZMTags::instance()->getAllTags($languageId)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $productId = $request->getProductId();
        if (0 < $productId && null != ($productTags = $request->getParameter('productTags'))) {
            $languageId = $request->getSelectedLanguage()->getId();
            $tags = array();
            foreach (explode(',', $productTags) as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    // avoid duplicates...
                    $tags[$tag] = $tag;
                }
            }
            ZMTags::instance()->setTagsForProductId($productId, $languageId, $tags);
            ZMMessages::instance()->success(_zm('Tags updated'));
        }

        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function isActive($request) {
        return 0 < $request->getProductId();
    }

    /**
     * {@inheritDoc}
     */
    public function getCatalogRequestId() {
        return 'product_tags_tab';
    }

    /**
     * {@inheritDoc}
     */
    public function getName() {
        return _zm('Tags');
    }

}
