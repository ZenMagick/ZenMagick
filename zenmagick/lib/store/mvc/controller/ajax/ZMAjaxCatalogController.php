<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Ajax controller for JSON catalog data.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller.ajax
 * @version $Id$
 */
class ZMAjaxCatalogController extends ZMAjaxController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ajaxCatalog');
        $this->set('ajaxProductMap', array(
            'id', 'name', 'description', 'model', 
            'attributes' => array('id', 'type', 'name', 
                'values' => array('id', 'name', 'default')
            )
        ));
        $this->set('ajaxResultListMap', array(
            'pageNumber', 'numberOfResults', 'pagination', 'numberOfPages', 'previousPage', 'nextPage', 'previousPageNumber', 'nextPageNumber',
            'results' => $this->get('ajaxProductMap')
        ));
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get product information for the given product id.
     *
     * <p>Request parameter:</p>
     * <ul>
     *  <li>productd - The product id</li>
     * </ul>
     *
     * @param ZMRequest request The current request.
     * @return void
     */
    public function getProductForIdJSON($request) {
        $productId = $request->getParameter('productId', 0);

        $flatObj = $this->flattenObject(ZMProducts::instance()->getProductForId($productId), $this->get('ajaxProductMap'));
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Get products for the given category id.
     *
     * <p>Request parameter:</p>
     * <ul>
     *  <li>categoryId - The category id</li>
     *  <li>active - Admin only parameter to allow to also retrieve inactive products</li>
     * </ul>
     *
     * @param ZMRequest request The current request.
     * @return void
     */
    public function getProductsForCategoryIdJSON($request) {
        $categoryId = $request->getParameter('categoryId', 0);
        $activeOnly = true;
        if (ZMSettings::get('isAdmin')) {
            $activeOnly = $request->getParameter('active', true);
        }

        if (null === ($page = $request->getParameter('page'))) {
            // return all
            $flatObj = $this->flattenObject(ZMProducts::instance()->getProductsForCategoryId($categoryId, $activeOnly), $this->get('ajaxProductMap'));
        } else {
            // use result list to paginate
            $args = array($categoryId, $activeOnly);
            $resultSource = ZMLoader::make("ObjectResultSource", 'Product', ZMProducts::instance(), "getProductsForCategoryId", $args);
            $resultList = ZMLoader::make("ResultList");
            $resultList->setResultSource($resultSource);
            $resultList->setPageNumber($page);
            if (null !== ($pagination = $request->getParameter('pagination'))) {
                $resultList->setPagination($pagination);
            }
            $flatObj = $this->flattenObject($resultList, $this->get('ajaxResultListMap'));
        }

        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

}

?>
