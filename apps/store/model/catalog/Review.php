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
namespace ZenMagick\apps\store\model\catalog;

use DateTime;
use ZenMagick\Base\ZMObject;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * A single review.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @ORM\Table(name="reviews")
 * @ORM\Entity
 */
class Review extends ZMObject {
    /**
     * @var integer $id
     * @ORM\Column(name="reviews_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;
    /**
     * @var integer $rating
     *
     * @ORM\Column(name="reviews_rating", type="integer", nullable=true)
     */
    private $rating;
    /**
     * @var integer $accountId
     *
     * @ORM\Column(name="customers_id", type="integer", nullable=true)
     */
    private $accountId;
    /**
     * @var object $descriptions
     * @ORM\OneToMany(targetEntity="ReviewDescriptions", mappedBy="review", cascade={"persist", "remove"})
     */
    private $descriptions;
    /**
     * @var integer $productId
     *
     * @ORM\Column(name="products_id", type="integer", nullable=false)
     */
    private $productId;
    /**
     * @var datetime $dateAdded
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     */
    private $dateAdded;
    /**
     * @var datetime $lastModified
     *
     * @ORM\Column(name="last_modified", type="datetime", nullable=true)
     */
    private $lastModified;

    /**
     * @var string $author
     * @todo nullable=true?
     * @ORM\Column(name="customers_name", type="string", length=64, nullable=false)
     */
    private $author;
    /**
     * @var integer $viewCount
     *
     * @ORM\Column(name="reviews_read", type="integer", nullable=false)
     */
    private $viewCount;

    /**
     * @var integer $status
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    private $status;

    private $productName;
    private $productImage;
    private $languageId;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->rating = 0;
        $this->productId = 0;
        $this->author = null;
        $this->setActive(true);
        $this->setViewCount(0);
        $this->setDateAdded(null);
        $this->setLastModified(new DateTime());
        $this->descriptions = new ArrayCollection();
    }


    public function getReviewId() { return $this->id; }
    /**
     * Get the review id.
     *
     * @return int The review id.
     */
    public function getId() { return $this->id; }

    /**
     * Get the rating.
     *
     * @return int The review rating.
     */
    public function getRating() { return $this->rating; }

    /**
     * Get the view counter.
     *
     * @return int The view counter.
     */
    public function getViewCount() { return $this->viewCount; }

    /**
     * Get the review product id.
     *
     * @return int The review product id.
     */
    public function getProductId() { return $this->productId; }

    /**
     * Get the review account id
     *
     * @return integer $accountId
     */
    public function getAccountId() { return $this->accountId; }
    /**
     * Check if this review is active.
     *
     * @return boolean <code>true</code> if the review is active.
     */
    public function isActive() { return $this->status; }

    /**
     * Get the review product name.
     *
     * @return string The review product name.
     */
    public function getProductName() { return $this->productName; }
    public function getName() { return $this->getProductName; }

    /**
     * Get the review product image.
     *
     * @return string The review product image.
     */
    public function getProductImage() { return $this->productImage; }
    public function getImage() { return $this->productImage; }

    /**
     * Get the review product image info.
     *
     * @return ZMImageInfo The product image info.
     */
    public function getProductImageInfo() {
        return $this->container->get('productService')->getProductForId($this->productId, $this->languageId)->getImageInfo();
    }

    /**
     * Get the review text.
     *
     * @deprecated
     * @return string The review text.
     */
    public function getText() {
        $description = $this->descriptions->get($this->languageId);
        return null != $description ? $description->getText() : '';
    }

    /**
     * Get all available descriptions.
     *
     * @return ArrayCollection List of <code>ReviewDescription</code> instances.
     */
    public function getDescriptions() {
       return $this->descriptions;
    }

    /**
     * Get the date the review was added.
     *
     * @return string The added date.
     */
    public function getDateAdded() { return $this->dateAdded; }

    /**
     * Get the date the review was last modified
     *
     * @return datetime $lastModified
     */
    public function getLastModified() { return $this->lastModified; }

    /**
     * Get the review author.
     *
     * @return string The name of the author.
     */
    public function getAuthor() { return $this->author; }

    /**
     * Get the language id.
     *
     * @deprecated
     * @return int The lanugage id.
     */
    public function getLanguageId() { return $this->languageId; }

    /**
     * Set the review id.
     *
     * @deprecated
     * @param int id The review id.
     */
    public function setId($id) { $this->id = $id; }

    // @todo deprecated doctrine backwards compatbility
    public function setReviewId($id) { $this->setId($id); }

    /**
     * Set the rating.
     *
     * @param int rating The review rating.
     */
    public function setRating($rating) { $this->rating = $rating; }

    /**
     * Set the view counter.
     *
     * @param int viewCount The view counter.
     */
    public function setViewCount($viewCount) { $this->viewCount = $viewCount; }

    /**
     * Set the review product id.
     *
     * @param int productId The review product id.
     */
    public function setProductId($productId) { $this->productId = $productId; }

    /**
     * Set the review account id
     *
     * @param integer $accountId
     */
    public function setAccountId($accountId) { $this->accountId = $accountId; }

    /**
     * Set the reviews active state.
     *
     * @param boolean value <code>true</code> if the review is active.
     */
    public function setActive($status) { $this->status = $status; }

    /**
     * Set the review product name.
     *
     * @param string name The review product name.
     */
    public function setProductName($name) { $this->productName = $name; }
    public function setName($name) { $this->productName = $name; }

    /**
     * Set the review product image.
     *
     * @param string image The review product image.
     */
    public function setProductImage($image) { $this->productImage = $image; }
    public function setImage($image) { $this->productImage = $image; }

    /**
     * Set the review text.
     * @deprecated
     * @param string text The review text.
     */
    public function setText($text) { $this->setDescription($text, $this->languageId); }

    public function setDescription($text, $languageId) {
        // todo: remove
        $this->languageId = $languageId;
        if (!isset($this->descriptions[$languageId])) {
            $this->descriptions[$languageId] = new ReviewDescriptions($this, $languageId, $text);
        } else {
            $this->descriptions[$languageId]->setText($text);
        }
    }

    /**
     * Set the date the review was added.
     *
     * @param string $dateAdded The added date.
     */
    public function setDateAdded($dateAdded) { $this->dateAdded = $dateAdded; }

    /**
     * Set the date the review was modified.
     *
     * @param datetime lastModified the modified date
     */
    public function setLastModified($lastModified) { $this->lastModified = $lastModified; }
    /**
     * Set the review author.
     *
     * @param string author The name of the author.
     */
    public function setAuthor($author) { $this->author = $author; }

    /**
     * Set the language id.
     * @deprecated
     * @param int id The lanugage id.
     */
    public function setLanguageId($languageId) {
        $this->languageId = $languageId;
        $this->setDescription('', $languageId);
    }

}
