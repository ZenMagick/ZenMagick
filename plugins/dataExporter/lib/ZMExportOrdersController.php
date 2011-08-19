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
 * Export orders controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.dataExporter
 */
class ZMExportOrdersController extends ZMController {
    private $dateFormat_;

    /**
     * Create instance.
     */
    public function __construct() {
        parent::__construct();
        $this->dateFormat_ = ZMLocales::instance()->getLocale()->getFormat('date', 'short');
    }


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $fromDate = $request->getParameter('fromDate');
        $toDate = $request->getParameter('toDate');
        $exportFormat = $request->getParameter('exportFormat');
        // datepicker uses double chars
        $dateFormat = str_replace(array('d', 'm', 'y', 'Y'), array('dd', 'mm', 'yy', 'yy'), $this->dateFormat_);
        return array('fromDate' => $fromDate, 'toDate' => $toDate, 'dateFormat' => $dateFormat);
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $fromDate = $request->getParameter('fromDate');
        $toDate = $request->getParameter('toDate');
        $exportFormat = $request->getParameter('exportFormat');

        $viewData = array();
        if (null != $fromDate) {
            $orderDateFrom = DateTime::createFromFormat($this->dateFormat_.' H:i:s', $fromDate.' 00:00:00');
            if (!empty($toDate)) {
                $orderDateTo = DateTime::createFromFormat($this->dateFormat_.' H:i:s', $toDate.' 00:00:00');
            } else {
                $orderDateTo = new DateTime();
                $toDate = $orderDateTo->format($this->dateFormat_);
            }

            // TODO: use new ZMOrders method
            $sql = "SELECT o.*, s.orders_status_name, ots.value as shippingValue
                    FROM " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . "  ot, " . TABLE_ORDERS_STATUS . " s, " . TABLE_ORDERS_TOTAL . "  ots
                    WHERE date_purchased >= :1#orderDate AND date_purchased < :2#orderDate
                      AND o.orders_id = ot.orders_id
                      AND ot.class = 'ot_total'
                      AND o.orders_id = ots.orders_id
                      AND ots.class = 'ot_shipping'
                      AND o.orders_status = s.orders_status_id
                      AND s.language_id = :languageId
                    ORDER BY orders_id DESC";
            $args = array('languageId' => 1, '1#orderDate' => $orderDateFrom, '2#orderDate' => $orderDateTo);
            $results = ZMRuntime::getDatabase()->query($sql, $args, array(TABLE_ORDERS, TABLE_ORDERS_TOTAL, TABLE_ORDERS_STATUS), 'ZMOrder');

            // prepare data
            $header = array(
                'Date',
                'Order Id',
                'Customer Name',
                'Shipping Country',
                'Products Ordered',
                'Products Net Price',
                'Shipping Cost',
                'Discount Amount',
                'Discount Coupon',
                'Gift Voucher Amount',
                'Order Tax',
                'Order Total'
            );

            $data = array();
            foreach ($results as $order) {
                $orderTotalLines = $order->getOrderTotalLines();
                $shippingAmount = 0;
                $couponAmount = 0;
                $gvAmount = 0;
                foreach ($orderTotalLines as $orderTotalLine) {
                    if ('ot_shipping' == $orderTotalLine->getType()) {
                        $shippingAmount = $orderTotalLine->getAmount();
                    } else if ('ot_coupon' == $orderTotalLine->getType()) {
                        $couponAmount = $orderTotalLine->getAmount();
                    } else if ('ot_gv' == $orderTotalLine->getType()) {
                        $gvAmount = $orderTotalLine->getAmount();
                    }
                }
                $shippingCountry = null;
                if (null != ($shippingAddress = $order->getShippingAddress())) {
                    $shippingCountry = $shippingAddress->getCountry();
                }
                $productIds = array();
                $productPrices = array();
                foreach ($order->getOrderItems() as $orderItem) {
                    $productIds[] = $orderItem->getProductId();
                    $productPrices[] = $orderItem->getCalculatedPrice(false); // no tax
                }
                $row = array(
                    ZMLocaleUtils::dateShort($order->getOrderDate()),
                    $order->getId(),
                    trim($order->getAccount()->getFullName()),
                    (null != $shippingCountry ? $shippingCountry->getName() : ''),
                    implode(',', $productIds),
                    implode(',', $productPrices),
                    $shippingAmount,
                    $couponAmount,
                    $order->get('coupon_code'),
                    $gvAmount,
                    $order->get('order_tax'),
                    $order->getTotal()
                );
                $data[] = $row;
            }

            // additional view data
            $viewData = array('header' => $header, 'data' => $data, 'toDate' => $toDate);

            if ('csv' == $exportFormat) {
                header("Content-type: application/csv");
                header("Content-Disposition: inline; filename=orders.csv");
                echo '"'.implode('", "', $header).'"'."\n";
                foreach ($data as $row) {
                    echo '"'.implode('", "', $row).'"'."\n";
                }
                return null;
            }
        }

        return $this->findView(null, $viewData);
    }

}
