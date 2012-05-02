<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 */
namespace zenmagick\apps\store\services\catalog;

use DateTime;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Sale maker.
 *
 * @author DerManoMann
 */
class SalemakerService extends ZMObject {
    private $sales_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->sales_ = null;
    }


    /**
     * Get sale discount type info.
     *
     * @param int productId The product id.
     * @param int categoryId Optional category id; default is <code>null</code> to use the default category.
     * @return array Discount type info.
     */
    public function getSaleDiscountTypeInfo($productId, $categoryId=null) {
        $product = $this->container->get('productService')->getProductForId($productId);
        if (null === $categoryId) {
            $categoryId = $product->getDefaultCategory(null)->getId();
        }

        if (null === $this->sales_) {
            $sql = "SELECT *
                    FROM " . TABLE_SALEMAKER_SALES . "
                    WHERE sale_status = '1'";
            $this->sales_ = \ZMRuntime::getDatabase()->fetchAll($sql, array(), TABLE_SALEMAKER_SALES, \ZMDatabase::MODEL_RAW);
        }

        $hasSale = false;
        $saleDiscount = 0;
        $saleCondition = 0;
        $saleDiscountType = 5; //No Sale or Skip Products with Special
        foreach ($this->sales_ as $result) {
            $categories = explode(',', $result['sale_categories_all']);
            if (in_array($categoryId, $categories)) {
                $hasSale = true;
                $saleDiscount = $result['sale_deduction_value'];
                $saleCondition = $result['sale_specials_condition'];
                $saleDiscountType = $result['sale_deduction_type'];
            }
        }

        if ($hasSale && 0 != $saleCondition) {
            $saleDiscountType = (($saleDiscountType * 100) + ($saleCondition * 10));
        } else {
            $saleDiscountType = 5; //No Sale or Skip Products with Special
        }

        $offers = $product->getOffers();
        if (0 != $offers->getSpecialPrice(false)) {
            $saleDiscountType = ($saleDiscountType * 10) + 9;
        }

        return array('type' => $saleDiscountType, 'amount' => $saleDiscount);
    }

    public function runTasks() {
        $this->scheduleSales();
    }

    /**
     * Start/stop all sales.
     *
     * Stops all sales scheduled for expiration
     * and starts all sales scheduled to be started.
     *
     * @todo replace zen_update_salemaker_product_prices
     */
    public function scheduleSales() {
        $sql = "SELECT sale_id, sale_status, sale_date_start, sale_date_end
                FROM " . TABLE_SALEMAKER_SALES;
        foreach (\ZMRuntime::getDatabase()->fetchAll($sql, array(), TABLE_SALEMAKER_SALES, 'zenmagick\apps\store\model\catalog\SaleMakerSale') as $sale) {
            $dateStart = $sale->getDateStart();
            $dateEnd = $sale->getDateEnd();
            $active = $sale->getStatus();
            // does what part of zen_update_salemaker_product_prices does
            //$saleCategories = array_unique(explode(',', $sale->getCategoriesSelected()));
            if (!$active && null != $dateStart && new DateTime() >= $dateStart) {
                $sale->setStatus(true);
                // @todo update product prices via product price sorter for $saleCategories?
                zen_update_salemaker_product_prices($sale->getId());
                \ZMRuntime::getDatabase()->updateModel(TABLE_SALEMAKER_SALES, $sale);
            }
            // @todo the original code also disabled sales tht haven't started yet. is that something we should worry about?
            if ($active && null != $dateEnd && new DateTime() >= $dateEnd) {
                $sale->setStatus(false);
                // @todo update product prices via product price sorter for $saleCategories?
                zen_update_salemaker_product_prices($sale->getId());
                \ZMRuntime::getDatabase()->updateModel(TABLE_SALEMAKER_SALES, $sale);
            }
        }
    }
}
