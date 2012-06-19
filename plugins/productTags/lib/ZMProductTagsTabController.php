<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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


use zenmagick\apps\store\controller\CatalogContentController;

/**
 * Admin controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.productTags
 */
class ZMProductTagsTabController extends CatalogContentController {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('product_tags_tab', _zm('Tags'), self::ACTIVE_PRODUCT);
    }


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $languageId = $request->getSelectedLanguage()->getId();
        $tagService = $this->container->get('tagService');
        return array(
            'currentProductTags' => $tagService->getTagsForProductId($request->getProductId(), $languageId),
            'allTags' => $tagService->getAllTags($languageId)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $productId = $request->getProductId();
        if (0 < $productId && null != ($currentProductTags = $request->request->get('currentProductTags'))) {
            $languageId = $request->getSelectedLanguage()->getId();
            $tags = array();
            foreach (explode(',', $currentProductTags) as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    // avoid duplicates...
                    $tags[$tag] = $tag;
                }
            }
            $this->container->get('tagService')->setTagsForProductId($productId, $languageId, $tags);
            $this->messageService->success(_zm('Tags updated'));
        }

        return $this->findView('catalog-redirect');
    }

}
