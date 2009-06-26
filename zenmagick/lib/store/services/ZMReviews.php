<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Reviews.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.services
 * @version $Id: ZMReviews.php 1966 2009-02-14 10:52:50Z dermanomann $
 */
class ZMReviews extends ZMObject {

    /**
     * Create new instance.
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
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Reviews');
    }


    /**
     * Get the number of reviews for the given product (id).
     *
     * @param int productId The product id.
     * @param int languageId Optional language id; default is <code>null</code>
     * @return int The number of published reviews for the product.
     */
    public function getReviewCount($productId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $sql = "SELECT COUNT(*) AS count
                FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                WHERE r.products_id = :productId
                  AND r.reviews_id = rd.reviews_id
                  AND rd.languages_id = :languageId
                  AND r.status = 1";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        $result = Runtime::getDatabase()->querySingle($sql, $args, array(TABLE_REVIEWS, TABLE_REVIEWS_DESCRIPTION), ZMDatabase::MODEL_RAW);
        return null != $result ? $result['count'] : 0;
    }

    /**
     * Get a random review.
     *
     * @param int productId Optional productId to limit reviews to one product; default is <code>null</code>.
     * @param int max Optional result limit; default is <code>null</code> to use the setting <em></em>.
     * @param int languageId Optional language id; default is <code>null</code>
     * @return array List of <code>ZMReview</code> instances.
     */
    public function getRandomReviews($productId=null, $max=null, $languageId=null) {
        $max = null === $max ? ZMSettings::get('maxRandomReviews') : $max;
        
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

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
        foreach (Runtime::getDatabase()->query($sql, $args, array(TABLE_REVIEWS, TABLE_REVIEWS_DESCRIPTION)) as $result) {
            $reviewIds[] = $result['reviewId'];
        }

        if (0 == count($reviewIds)) {
            return array();
        }

        shuffle($reviewIds);

        if (0 < $max && count($productIds) > $max) {
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
        return Runtime::getDatabase()->query($sql, $args, array(TABLE_REVIEWS, TABLE_REVIEWS_DESCRIPTION, TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION), 'Review');
    }

    /**
     * Get the average rating for the given product id.
     *
     * @param int productId The product id.
     * @param int languageId Optional language id; default is <code>null</code>
     * @return float The average rating or <code>null</code> if no ratnig exists.
     */
    public function getAverageRatingForProductId($productId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        // SQL based on Dedek's average rating mod: http://www.zen-cart.com/index.php?main_page=product_contrib_info&cPath=40_47&products_id=595
        $sql = "SELECT AVG(reviews_rating) AS average_rating from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                WHERE r.products_id = :productId
                  AND r.reviews_id = rd.reviews_id
                  AND rd.languages_id = :languageId
                  AND r.status = 1";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        $result = Runtime::getDatabase()->querySingle($sql, $args, array(TABLE_REVIEWS, TABLE_REVIEWS_DESCRIPTION), ZMDatabase::MODEL_RAW);
        return null != $result ? $result['average_rating'] : 0;
    }

    /**
     * Get all reviews for the given product id.
     *
     * @param int productId The product id.
     * @param int languageId Optional language id; default is <code>null</code>
     * @return array List of <code>ZMReview</code> instances.
     */
    public function getReviewsForProductId($productId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
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
                  AND p.products_id = :productId
                ORDER BY date_added DESC";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        return Runtime::getDatabase()->query($sql, $args, array(TABLE_REVIEWS, TABLE_REVIEWS_DESCRIPTION, TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION), 'Review');
    }

    /**
     * Get all published reviews.
     *
     * @param int languageId Optional language id; default is <code>null</code>
     * @return array List of <code>ZMReview</code> instances.
     */
    public function getAllReviews($languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
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
                ORDER BY date_added DESC";
        $args = array('productId' => $productId, 'languageId' => $languageId);
        return Runtime::getDatabase()->query($sql, $args, array(TABLE_REVIEWS, TABLE_REVIEWS_DESCRIPTION, TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION), 'Review');
    }

    /**
     * Get the review for the given review id.
     *
     * @param int reviewId The id of the review to load.
     * @param int languageId Optional language id; default is <code>null</code>
     * @return ZMReview A <code>ZMReview</code> instance or <code>null</code>.
     */
    public function getReviewForId($reviewId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
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
                  AND r.reviews_id = :reviewId";
        $args = array('reviewId' => $reviewId, 'languageId' => $languageId);
        return Runtime::getDatabase()->querySingle($sql, $args, array(TABLE_REVIEWS, TABLE_REVIEWS_DESCRIPTION, TABLE_PRODUCTS, TABLE_PRODUCTS_DESCRIPTION), 'Review');
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
        Runtime::getDatabase()->update($sql, array('reviewId' => $reviewId), TABLE_REVIEWS);
    }

    /**
     * Create a new review.
     *
     * @param ZMReview review The new review.
     * @param ZMAccount author The review author.
     * @param int languageId The language for this review; default is <code>null</code>.
     * @return ZMReview The inserted review (incl. the new id).
     */
    public function createReview($review, $account, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $review->setAuthor($account->getFullName());
        $review->setAccountId($account->getId());
        $review->setLastModified(date(ZMDatabase::DATETIME_FORMAT));
        $review->setDateAdded(date(ZMDatabase::DATETIME_FORMAT));
        $review->setActive(ZMSettings::get('isApproveReviews') ? false : true);
        $review->setLanguageId($languageId);

        $review = Runtime::getDatabase()->createModel(TABLE_REVIEWS, $review);
        Runtime::getDatabase()->createModel(TABLE_REVIEWS_DESCRIPTION, $review);
        return $review;
    }

}

?>
