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
 * Review service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ReviewService extends ZMObject {
    private $useNickName;

    /**
     * Create instance.
     */
    public function __construct() {
        parent::__construct();
        $this->useNickName = false;
    }


    /**
     * Set the <em>useNickName</em> property.
     *
     * @param boolean value The new value.
     */
    public function setUseNickName($value) {
        $this->useNickName = $value;
    }

    /**
     * Get the number of reviews for the given product (id).
     *
     * @param int productId The product id.
     * @param int languageId Language id.
     * @return int The number of published reviews for the product.
     */
    public function getReviewCount($productId, $languageId) {
        $sql = "SELECT COUNT(*) AS count
                FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                WHERE r.products_id = :productId
                  AND r.reviews_id = rd.reviews_id
                  AND rd.languages_id = :languageId
                  AND r.status = 1";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        $result = \ZMRuntime::getDatabase()->querySingle($sql, $args, array('reviews', 'reviews_description'), \ZMDatabase::MODEL_RAW);
        return null != $result ? $result['count'] : 0;
    }

    /**
     * Get a random review.
     *
     * @param int languageId Language id.
     * @param int productId Optional productId to limit reviews to one product; default is <code>null</code>.
     * @param int max Optional result limit; default is <code>null</code> to use the setting <em></em>.
     * @return array List of <code>Review</code> instances.
     */
    public function getRandomReviews($languageId, $productId=null, $max=null) {
        $max = null === $max ? $this->container->get('settingsService')->get('maxRandomReviews') : $max;

        $sql = "SELECT r.reviews_id
                FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, "
                       . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE p.products_status = 1
                  AND p.products_id = r.products_id
                  AND r.reviews_id = rd.reviews_id
                  AND rd.languages_id = :languageId
                  AND p.products_id = pd.products_id
                  AND pd.language_id = :languageId
                  AND r.status = 1";
        if (null != $productId) {
            $sql .= " AND p.products_id = :productId";
        }
        $sql .= " ORDER BY date_added DESC";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        $reviewIds = array();
        foreach (\ZMRuntime::getDatabase()->fetchAll($sql, $args, array('reviews', 'reviews_description')) as $result) {
            $reviewIds[] = $result['reviewId'];
        }

        if (0 == count($reviewIds)) {
            return array();
        }

        shuffle($reviewIds);

        if (0 < $max && count($reviewIds) > $max) {
            $reviewIds = array_slice($reviewIds, 0, $max);
        }

        $sql = "SELECT r.*, rd.*, p.products_image, pd.products_name
                FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, "
                       . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE p.products_status = 1
                  AND p.products_id = r.products_id
                  AND r.reviews_id = rd.reviews_id
                  AND rd.languages_id = :languageId
                  AND p.products_id = pd.products_id
                  AND pd.language_id = :languageId
                  AND r.status = 1
                  AND r.reviews_id in (:reviewId)
                ORDER BY date_added DESC";
        $args = array('productId' => $productId, 'languageId' => $languageId, 'reviewId' => $reviewIds);
        return \ZMRuntime::getDatabase()->fetchAll($sql, $args, array('reviews', 'reviews_description', 'products', 'products_description'), 'zenmagick\apps\store\model\catalog\Review');
    }

    /**
     * Get the average rating for the given product id.
     *
     * @param int productId The product id.
     * @param int languageId Language id.
     * @return float The average rating or <code>null</code> if no rating exists.
     */
    public function getAverageRatingForProductId($productId, $languageId) {
        // SQL based on Dedek's average rating mod: http://www.zen-cart.com/index.php?main_page=product_contrib_info&cPath=40_47&products_id=595
        $sql = "SELECT AVG(reviews_rating) AS average_rating from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                WHERE r.products_id = :productId
                  AND r.reviews_id = rd.reviews_id
                  AND rd.languages_id = :languageId
                  AND r.status = 1";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        $result = \ZMRuntime::getDatabase()->querySingle($sql, $args, array('reviews', 'reviews_description'), \ZMDatabase::MODEL_RAW);
        return null != $result ? $result['average_rating'] : 0;
    }

    /**
     * Get all reviews for the given product id.
     *
     * @param int productId The product id.
     * @param int languageId Language id.
     * @return array List of <code>Review</code> instances.
     */
    public function getReviewsForProductId($productId, $languageId) {
        $sql = "SELECT r.*, rd.*, p.products_image, pd.products_name
                FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, "
                       . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE p.products_status = 1
                  AND p.products_id = r.products_id
                  AND r.reviews_id = rd.reviews_id
                  AND rd.languages_id = :languageId
                  AND p.products_id = pd.products_id
                  AND pd.language_id = :languageId
                  AND r.status = 1
                  AND p.products_id = :productId
                ORDER BY date_added DESC";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        return \ZMRuntime::getDatabase()->fetchAll($sql, $args, array('reviews', 'reviews_description', 'products', 'products_description'), 'zenmagick\apps\store\model\catalog\Review');
    }

    /**
     * Get all published reviews.
     *
     * @param int languageId Language id.
     * @return array List of <code>Review</code> instances.
     */
    public function getAllReviews($languageId) {
        $sql = "SELECT r.*, rd.*, p.products_image, pd.products_name
                FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, "
                       . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE p.products_status = 1
                  AND p.products_id = r.products_id
                  AND r.reviews_id = rd.reviews_id
                  AND rd.languages_id = :languageId
                  AND p.products_id = pd.products_id
                  AND pd.language_id = :languageId
                  AND r.status = 1
                ORDER BY date_added DESC";
        $args = array('languageId' => $languageId);
        return \ZMRuntime::getDatabase()->fetchAll($sql, $args, array('reviews', 'reviews_description', 'products', 'products_description'), 'zenmagick\apps\store\model\catalog\Review');
    }

    /**
     * Get the review for the given review id.
     *
     * @param int reviewId The id of the review to load.
     * @param int languageId Language id.
     * @return Review A <code>Review</code> instance or <code>null</code>.
     */
    public function getReviewForId($reviewId, $languageId) {
        $sql = "SELECT r.*, rd.*, p.products_image, pd.products_name
                FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, "
                       . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                WHERE p.products_status = 1
                  AND p.products_id = r.products_id
                  AND r.reviews_id = rd.reviews_id
                  AND rd.languages_id = :languageId
                  AND p.products_id = pd.products_id
                  AND pd.language_id = :languageId
                  AND r.status = 1
                  AND r.reviews_id = :reviewId";
        $args = array('reviewId' => $reviewId, 'languageId' => $languageId);
        return \ZMRuntime::getDatabase()->querySingle($sql, $args, array('reviews', 'reviews_description', 'products', 'products_description'), 'zenmagick\apps\store\model\catalog\Review');
    }

    /**
     * Update the view count for the given review id.
     *
     * @param int reviewId The id of the review.
     */
    public function updateViewCount($reviewId) {
        $sql = "UPDATE " . TABLE_REVIEWS . "
                SET reviews_read = reviews_read+1
                WHERE reviews_id = :reviewId";
        \ZMRuntime::getDatabase()->updateObj($sql, array('reviewId' => $reviewId), 'reviews');
    }

    /**
     * Create a new review.
     *
     * @param ZMReview review The new review.
     * @param ZMAccount author The review author.
     * @param int languageId The language for this review.
     * @return ZMReview The inserted review (incl. the new id).
     */
    public function createReview($review, $account, $languageId) {
        $author = ($this->useNickName && 0 < strlen(trim($account->getNickName()))) ? $account->getNickName() : $account->getFullName();
        $review->setAuthor($author);
        $review->setAccountId($account->getId());
        $review->setLastModified(new DateTime());
        $review->setDateAdded(new DateTime());
        $isApproveReviews = $this->container->get('settingsService')->get('isApproveReviews');
        $review->setActive($isApproveReviews ? false : true);

        $review = \ZMRuntime::getDatabase()->createModel('reviews', $review);
        \ZMRuntime::getDatabase()->createModel('reviews_description', $review);
        return $review;
    }

}
