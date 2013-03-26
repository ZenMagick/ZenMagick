<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

namespace ZenMagick\StoreBundle\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

/**
 * A single download.
 *
 * @ORM\Table(name="orders_products_download",
 *  indexes={
 *      @ORM\Index(name="idx_orders_id_zen", columns={"orders_id"}),
 *      @ORM\Index(name="idx_orders_products_id_zen", columns={"orders_products_id"}),
 *  })
 * @ORM\Entity
 * @author DerManoMann
 */
class Download extends ZMObject
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="orders_products_download_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $orderId
     *
     * @ORM\Column(name="orders_id", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * @var integer $orderItemId
     *
     * @ORM\Column(name="orders_products_id", type="integer", nullable=false)
     */
    private $orderItemId;

    /**
     * @var string $filename
     *
     * @ORM\Column(name="orders_products_filename", type="string", length=255, nullable=false)
     */
    private $filename;

    /**
     * @var integer $maxDays
     *
     * @ORM\Column(name="download_maxdays", type="smallint", nullable=false)
     */
    private $maxDays;

    /**
     * @var integer $downloadCount
     *
     * @ORM\Column(name="download_count", type="integer", nullable=false)
     */
    private $downloadCount;

    /**
     * @var string $productId
     *
     * @ORM\Column(name="products_prid", type="text", nullable=false)
     */
    private $productId;

    private $orderDate;

    private $status;

    /**
     * Get the download id.
     *
     * @return int The download id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the order id.
     *
     * @return int The order id.
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Get the order item id.
     *
     * @return int The order item id.
     */
    public function getOrderItemId()
    {
        return $this->orderItemId;
    }

    /**
     * Get the product id.
     *
     * @return string The product id.
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Get the order date.
     *
     * @return date The order date.
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * Get the max number of days the download is available.
     *
     * @return int The number of days.
     */
    public function getMaxDays()
    {
        return $this->maxDays;
    }

    /**
     * Get the file name.
     *
     * @return string The file name.
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get the download count.
     *
     * @return int The number of downloads.
     */
    public function getDownloadCount()
    {
        return $this->downloadCount;
    }

    /**
     * Get the status of the corresponding order.
     *
     * @return int The order status.
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the download id.
     *
     * @param int id The download id.
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the order id.
     *
     * @param int orderId The order id.
     */
    public function setOrderId($orderId )
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Set the order item id.
     *
     * @param int orderItemId The order item id.
     */
    public function setOrderProductId($orderItemId)
    {
        $this->orderItemId = $orderItemId;

        return $this;
    }

    /**
     * Set the product id.
     *
     * @param string productId The product id.
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Set the order date.
     *
     * @param date date The order date.
     */
    public function setOrderDate($date)
    {
        $this->orderDate = $date;

        return $this;
    }

    /**
     * Set the max number of days the download is available.
     *
     * @param int maxDays The number of days.
     */
    public function setMaxDays($maxDays)
    {
        $this->maxDays = $maxDays;

        return $this;
    }

    /**
     * Set the filename.
     *
     * @param string filename The filename.
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Set the download count.
     *
     * @param int downloadCount The number of downloads.
     */
    public function setDownloadCount($downloadCount)
    {
        $this->downloadCount = $downloadCount;

        return $this;
    }

    /**
     * Set the status of the corresponding order.
     *
     * @param int status The order status.
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the expiry date for this download.
     *
     * @return DateTime The expiry date.
     */
    public function getExpiryDate()
    {
        $expiry = clone $this->getOrderDate();
        //XX: use DateInterval in PHP5.3
        $expiry->modify('+'.$this->getMaxDays().' day');

        return $expiry;
    }

    /**
     * Check if this download is expired.
     *
     * @return boolean <code>true</code> if this download is expired.
     */
    public function isExpired()
    {
        $now = new \DateTime();
        $snow = $now->format('d-m-Y');
        $sexpiry = $this->getExpiryDate()->format('d-m-Y');

        return $snow > $sexpiry;
    }

    /**
     * Check if downloadable.
     *
     * @return boolean <code>true</code> if this download is (still) available for download.
     */
    public function isDownloadable()
    {
        return file_exists(Runtime::getSettings()->get('downloadBaseDir').'/'.$this->filename)
            && (!$this->isLimited() || (0 < $this->downloadCount && !$this->isExpired()));
    }

    /**
     * Check if this download is limited.
     *
     * @return boolean <code>true</code> if this download is limited by date.
     */
    public function isLimited()
    {
        return 0 != $this->maxDays;
    }

    /**
     * Get the download filesize.
     *
     * @return long The filesize.
     */
    public function getFileSize()
    {
        return filesize(Runtime::getSettings()->get('downloadBaseDir').'/'.$this->filename);
    }

}
