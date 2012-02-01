<?php
/*
 * ZenMagick Core - Another PHP framework.
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
namespace zenmagick\apps\store\admin\widgets;

use zenmagick\http\widgets\Widget;
use zenmagick\http\view\TemplateView;

/**
 * <p>Display reserved qty.</p>
 *
 * <p>This widget relies on the fact that arbitrary properties can be set to
 * <code>ZMObject</code> based classes. The quick edit plugin does exactly that
 * by setting the current product for each row. That, in turn, is then used to
 * calculate the reserved quantity for the given product.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ReservedQtyWidget extends Widget {
    private static $qtyMap_ = null;


    /**
     * Get stats.
     *
     * @return array Details about reserved quantities.
     */
    protected function getStats() {
        if (null === self::$qtyMap_) {
          $sql = "SELECT op.products_id, SUM(op.products_quantity) AS products_quantity
                  FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_PRODUCTS . " op
                  WHERE o.orders_id = op.orders_id
                    AND o.orders_status NOT IN (:orderStatusId)
                  GROUP BY op.products_id";
          $args = array('orderStatusId' => array(3, 4, 5, 6, 8, 9, 13, 17));

          self::$qtyMap_ = array();
          foreach (\ZMRuntime::getDatabase()->fetchAll($sql, $args, array(TABLE_ORDERS, TABLE_ORDERS_PRODUCTS)) as $result) {
              self::$qtyMap_[$result['productId']] = $result['qty'];
          }
        }
        return self::$qtyMap_;
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, TemplateView $templateView) {
        $stats = $this->getStats();
        $product = $this->getProduct();
        if (array_key_exists($product->getId(), $stats)) {
            return $stats[$product->getId()];
        }
        return 0;
    }

}
