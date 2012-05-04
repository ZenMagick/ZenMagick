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

use zenmagick\base\Beans;

/**
 * Export orders controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.dataExporter
 */
class ZMExportOrdersController extends ZMController {

    /**
     * Get the date format.
     */
    protected function getDateFormat() {
        return $this->container->get('localeService')->getLocale()->getFormat('date', 'short');
    }

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $fromDate = $request->getParameter('fromDate');
        $toDate = $request->getParameter('toDate');
        $exportFormat = $request->getParameter('exportFormat');
        // datepicker uses double chars
        $dateFormat = str_replace(array('d', 'm', 'y', 'Y'), array('dd', 'mm', 'yy', 'yy'), $this->getDateFormat());
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
            $dateFormat = $this->getDateFormat();
            $orderDateFrom = DateTime::createFromFormat($dateFormat.' H:i:s', $fromDate.' 00:00:00');
            if (!empty($toDate)) {
                $orderDateTo = DateTime::createFromFormat($dateFormat.' H:i:s', $toDate.' 00:00:00');
            } else {
                $orderDateTo = new DateTime();
                $toDate = $orderDateTo->format($dateFormat);
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
            // load as array to save memory
            $results = ZMRuntime::getDatabase()->fetchAll($sql, $args, array(TABLE_ORDERS, TABLE_ORDERS_TOTAL, TABLE_ORDERS_STATUS));

            // prepare data
            $header = array(
                'Date',
                'Order Id',
                'Customer Name',
                'Shipping Country',
                'Products Ordered',
                'Quantity',
                'Unit Products Net Price',
                'Products Line Net Total',
                'Products Net Total',
                'Shipping Cost',
                'Discount Amount',
                'Discount Coupon',
                'Gift Voucher Amount',
                'Payment Type',
                'Order Tax',
                'Order Total'
            );

            $rows = array();
            $order = Beans::getBean('ZMOrder');
            foreach ($results as $ii => $orderData) {
                $order->reset();
                Beans::setAll($order, $orderData);
                $rows[] = $this->processOrder($order);
            }

            // additional view data
            $viewData = array('header' => $header, 'rows' => $rows, 'toDate' => $toDate);

            if ('csv' == $exportFormat) {
                header("Content-type: application/csv");
                header("Content-Disposition: inline; filename=orders.csv");
                ob_start();
                $fp = fopen('php://output', 'w');
                fputcsv($fp, $header);
                foreach ($rows as $orderRows) {
                    foreach ($orderRows as $row) {
                        fputcsv($fp, $row);
                    }
                }
                fclose($fp);
                $csv = ob_get_clean();
                echo $csv;
                return null;
            }
        }

        return $this->findView(null, $viewData);
    }

    /**
     * Process a single order
     *
     * @param ZMOrder order The order.
     * @return array List of rows.
     */
    protected function processOrder(ZMOrder $order) {
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
        unset($orderTotalLines);
        $shippingCountry = null;
        if (null != ($shippingAddress = $order->getShippingAddress())) {
            $shippingCountry = $shippingAddress->getCountry();
        }

        $rows = array();
        // each line has a product,
        // first line also the general details,
        // last line the totals
        $firstRow = true;
        $lastRow = false;
        $productsTotal = 0;
        $orderItems = $order->getOrderItems();
        for ($ii = 0; $ii < count($orderItems); ++$ii) {
            $orderItem = $orderItems[$ii];
            $lastRow = $ii == count($orderItems)-1;
            $row = array();
            if ($firstRow) {
                $row[] = $this->container->get('localeService')->getLocale()->dateShort($order->getOrderDate());
                $row[] = $order->getId();
                $row[] = trim($order->getAccount()->getFullName());
                $row[] = (null != $shippingCountry ? $shippingCountry->getName() : '');
            } else {
                $row[] = '';
                $row[] = '';
                $row[] = '';
                $row[] = '';
            }
            $firstRow = false;

            $quantity = $orderItem->getQuantity();
            $itemTotal = $orderItem->getCalculatedPrice(false); // no tax
            $lineTotal = $itemTotal * $quantity;
            $productsTotal += $lineTotal;
            $row[] = $products[] = $orderItem->getProductId().':'.$orderItem->getName();
            $row[] = $quantity;
            $row[] = $lineTotal/$quantity;
            $row[] = $lineTotal;

            if ($lastRow) {
                $row[] = $productsTotal;
                $row[] = $shippingAmount;
                $row[] = $couponAmount;
                $row[] = $order->get('coupon_code');
                $row[] = $gvAmount;
                $row[] = $order->get('payment_method');
                $row[] = $order->get('order_tax');
                $row[] = $order->getTotal();
            } else {
                $row[] = '';
                $row[] = '';
                $row[] = '';
                $row[] = '';
                $row[] = '';
                $row[] = '';
                $row[] = '';
                $row[] = '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

}
