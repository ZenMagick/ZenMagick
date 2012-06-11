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

use zenmagick\base\Runtime;

/**
 * Ajax controller for JSON catalog data.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.controller.ajax
 */
class ZMAjaxCatalogController extends ZMAjaxController {

    /**
     * Create new instance.
     */
    public function __construct() {
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
        $productId = $request->getProductId();
        $languageId = $request->getParameter('languageId', $request->getSession()->getLanguageId());

        $flatObj = ZMAjaxUtils::flattenObject($this->container->get('productService')->getProductForId($productId, $languageId), $this->get('ajaxProductMap'));
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Get products for the given category id.
     *
     * <p>Request parameter:</p>
     * <ul>
     *  <li>categoryId - The category id</li>
     *  <li>languageId - The language id</li>
     *  <li>active - Admin only parameter to allow to also retrieve inactive products</li>
     * </ul>
     *
     * @param ZMRequest request The current request.
     * @return void
     */
    public function getProductsForCategoryIdJSON($request) {
        $categoryId = $request->getParameter('categoryId', 0);
        $languageId = $request->getParameter('languageId', $request->getSession()->getLanguageId());
        $activeOnly = true;
        if (Runtime::isContextMatch('admin')) {
            $activeOnly = $request->getParameter('active', true);
        }

        if (null === ($page = $request->getParameter('page'))) {
            // return all
            $flatObj = ZMAjaxUtils::flattenObject($this->container->get('productService')->getProductsForCategoryId($categoryId, $activeOnly, $languageId), $this->get('ajaxProductMap'));
        } else {
            // use result list to paginate
            $args = array($categoryId, $activeOnly, $languageId);
            $resultSource = new ZMObjectResultSource('ZMProduct', 'productService', "getProductsForCategoryId", $args);
            $resultList = Runtime::getContainer()->get('ZMResultList');
            $resultList->setResultSource($resultSource);
            $resultList->setPageNumber($page);
            if (null !== ($pagination = $request->getParameter('pagination'))) {
                $resultList->setPagination($pagination);
            }
            $flatObj = ZMAjaxUtils::flattenObject($resultList, $this->get('ajaxResultListMap'));
        }

        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Get products for the given manufacturer id.
     *
     * <p>Request parameter:</p>
     * <ul>
     *  <li>manufacturerId The manufacturer id</li>
     *  <li>languageId - The language id</li>
     *  <li>active - Admin only parameter to allow to also retrieve inactive products</li>
     * </ul>
     *
     * @param ZMRequest request The current request.
     * @return void
     */
    public function getProductsForManufacturerIdJSON($request) {
        $manufacturerId = $request->getParameter('manufacturerId', 0);
        $languageId = $request->getParameter('languageId', $request->getSession()->getLanguageId());
        $activeOnly = true;
        if (Runtime::isContextMatch('admin')) {
            $activeOnly = $request->getParameter('active', true);
        }

        if (null === ($page = $request->getParameter('page'))) {
            // return all
            $flatObj = ZMAjaxUtils::flattenObject($this->container->get('productService')->getProductsForManufacturerId($manufacturerId, $activeOnly, $languageId), $this->get('ajaxProductMap'));
        } else {
            // use result list to paginate
            $args = array($manufacturerId, $activeOnly, $languageId);
            $resultSource = new ZMObjectResultSource('ZMProduct', 'productService', "getProductsForManufacturerId", $args);
            $resultList = Runtime::getContainer()->get('ZMResultList');
            $resultList->setResultSource($resultSource);
            $resultList->setPageNumber($page);
            if (null !== ($pagination = $request->getParameter('pagination'))) {
                $resultList->setPagination($pagination);
            }
            $flatObj = ZMAjaxUtils::flattenObject($resultList, $this->get('ajaxResultListMap'));
        }

        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

}
