<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
?>
<?php


/**
 * A single download.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.model.order
 * @version $Id$
 */
class ZMDownload extends ZMObject {
    private $id;
    private $orderId;
    private $orderItemId;
    private $productId;
    private $orderDate;
    private $maxDays;
    private $filename;
    private $downloadCount;
    private $status;


    /**
     * Create new total.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the download id.
     *
     * @return int The download id.
     */
    public function getId() { return $this->id; }

    /**
     * Get the order id.
     *
     * @return int The order id.
     */
    public function getOrderId() { return $this->orderId; }

    /**
     * Get the order item id.
     *
     * @return int The order item id.
     */
    public function getOrderItemId() { return $this->orderItemId; }

    /**
     * Get the product id.
     *
     * @return string The product id.
     */
    public function getProductId() { return $this->productId; }

    /**
     * Get the order date.
     *
     * @return date The order date.
     */
    public function getOrderDate() { return $this->orderDate; }

    /**
     * Get the max number of days the download is available.
     *
     * @return int The number of days.
     */
    public function getMaxDays() { return $this->maxDays; }

    /**
     * Get the file name.
     *
     * @return string The file name.
     */
    public function getFilename() { return $this->filename; }

    /**
     * Get the download count.
     *
     * @return int The number of downloads.
     */
    public function getDownloadCount() { return $this->downloadCount; }

    /**
     * Get the status of the corresponding order.
     *
     * @return int The order status.
     */
    public function getStatus() { return $this->status; }

    /**
     * Set the download id.
     *
     * @param int id The download id.
     */
    public function setId($id) { $this->id = $id; }

    /**
     * Set the order id.
     *
     * @param int orderId The order id.
     */
    public function setOrderId($orderId ) { $this->orderId = $orderId; }

    /**
     * Set the order item id.
     *
     * @param int orderItemId The order item id.
     */
    public function setOrderProductId($orderItemId) { $this->orderItemId = $orderItemId; }

    /**
     * Set the product id.
     *
     * @param string productId The product id.
     */
    public function setProductId($productId) { $this->productId = $productId; }

    /**
     * Set the order date.
     *
     * @param date date The order date.
     */
    public function setOrderDate($date) { $this->orderDate = $date; }

    /**
     * Set the max number of days the download is available.
     *
     * @param int maxDays The number of days.
     */
    public function setMaxDays($maxDays) { $this->maxDays = $maxDays; }

    /**
     * Set the filename.
     *
     * @param string filename The filename.
     */
    public function setFilename($filename) { $this->filename = $filename; }

    /**
     * Set the download count.
     *
     * @param int downloadCount The number of downloads.
     */
    public function setDownloadCount($downloadCount) { $this->downloadCount = $downloadCount; }

    /**
     * Set the status of the corresponding order.
     *
     * @param int status The order status.
     */
    public function setStatus($status) { $this->status = $status; }

    /**
     * Check if downloadable.
     *
     * @return boolean <code>true</code> if this download is (still) available for download.
     */
    public function isDownloadable() {
        $datetime = ZMTools::parseDateString($this->orderDate, ZM_DATETIME_FORMAT);
        $expiry_time = mktime(23, 59, 59, $datetime['mm'], $datetime['dd'] + $this->maxDays, $datetime['yyyy']);
        return file_exists(ZMSettings::get('downloadBaseDir').$this->filename)
            && (!$this->isLimited() || (0 < $this->downloadCount && $expiry_time > time()));
    }

    /**
     * Check if this download is limited.
     *
     * @return boolean <code>true</code> if this download is limited by date/count.
     */
    public function isLimited() {
        return 0 != $this->maxDays;
    }

    /**
     * Get the download filesize.
     *
     * @return long The filesize.
     */
    public function getFileSize() {
        return filesize(ZMSettings::get('downloadBaseDir').$this->filename);
    }

}
