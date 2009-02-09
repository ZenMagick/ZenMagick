<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * @package org.zenmagick.rp.ajax.controller
 * @version $Id$
 */
class ZMAjaxCatalogController extends ZMAjaxController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
     * @param int productd The product id.
     * @return void
     */
    public function getProductForIdJSON() {
        $productId = ZMRequest::getParameter('productId', 0);

        $flatObj = $this->flattenObject(ZMProducts::instance()->getProductForId($productId), $this->get('ajaxProductMap'));
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Get products for the given category id.
     *
     * @param int categoryId The categoryId id.
     * @param boolean active Admin only parameter to allow to retrieve inactive products also.
     * @return void
     */
    public function getProductsForCategoryIdJSON() {
        $categoryId = ZMRequest::getParameter('categoryId', 0);
        $activeOnly = true;
        if (ZMSettings::get('isAdmin')) {
            $activeOnly = ZMRequest::getParameter('active', true);
        }

        if (null === ($page = ZMRequest::getParameter('page'))) {
            // return all
            $flatObj = $this->flattenObject(ZMProducts::instance()->getProductsForCategoryId($categoryId, $activeOnly), $this->get('ajaxProductMap'));
        } else {
            // use result list to paginate
            $args = array($categoryId, $activeOnly);
            $resultSource = ZMLoader::make("ObjectResultSource", 'Product', ZMProducts::instance(), "getProductsForCategoryId", $args);
            $resultList = ZMLoader::make("ResultList");
            $resultList->setResultSource($resultSource);
            $resultList->setPageNumber($page);
            if (null !== ($pagination = ZMRequest::getParameter('pagination'))) {
                $resultList->setPagination($pagination);
            }
            $flatObj = $this->flattenObject($resultList, $this->get('ajaxResultListMap'));
        }

        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

}

?>
