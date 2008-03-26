<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * @author mano
 * @package org.zenmagick.service
 * @version $Id$
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
        return parent::instance('Reviews');
    }


    /**
     * Get the number of reviews for the given product (id).
     *
     * @param int productId The product id.
     * @param int languageId Optional language id; default is <code>null</code>
     * @return int The number of published reviews for the product.
     */
    function getReviewCount($productId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $query = "select count(*) as count
                from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                where r.products_id = :productId
                  and r.reviews_id = rd.reviews_id
                  and rd.languages_id = :languageId
                  and r.status = '1'";
        $query = $db->bindVars($query, ":productId", $productId, 'integer');
        $query = $db->bindVars($query, ":languageId", $languageId, 'integer');

        $results = $db->Execute($query);
        return $results->fields['count'];
    }

    /**
     * Get a random review.
     *
     * @param int productId Optional productId to limit reviews to one product.
     * @param int max Optional result limit; default is <code>1</code>.
     * @param int languageId Optional language id; default is <code>null</code>
     * @return array List of <code>ZMReview</code> instances.
     */
    function getRandomReviews($productId=null, $max=1, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $query = "select r.reviews_id, r.reviews_rating, p.products_id, p.products_image, pd.products_name,
                rd.reviews_text, r.date_added, r.customers_name, r.reviews_read
                from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, "
                       . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                where p.products_status = '1'
                and p.products_id = r.products_id
                and r.reviews_id = rd.reviews_id
                and rd.languages_id = :languageId
                and p.products_id = pd.products_id
                and pd.language_id = :languageId
                and r.status = '1'";
        $query = $db->bindVars($query, ":languageId", $languageId, 'integer');

        if (null != $productId) {
            $query .= $db->bindVars(" and p.products_id = :productId", ":productId", $productId, 'integer');
        }
        $query .= " limit " . MAX_RANDOM_SELECT_REVIEWS;

        $reviews = array();
        while ($max > count($reviews)) {
            $results = $db->ExecuteRandomMulti($query, $max);
            while (!$results->EOF) {
                $review = $this->_newReview($results->fields);
                array_push($reviews, $review);
                $results->MoveNext();
                if ($max == count($reviews))
                    break;
            }
            if (0 == count($reviews)) {
                if (null == $productId || !ZMSettings::get('isReviewsDefaultToRandom'))
                    break;
                return $this->getRandomReviews(null, $max);
            }
        }
        return $reviews;
    }

    /**
     * Get the average rating for the given product id.
     *
     * @param int productId The product id.
     * @param int languageId Optional language id; default is <code>null</code>
     * @return float The average rating or <code>null</code> if no ratnig exists.
     */
    function getAverageRatingForProductId($productId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        // SQL based on Dedek's average rating mod: http://www.zen-cart.com/index.php?main_page=product_contrib_info&cPath=40_47&products_id=595
        $query = "select avg(reviews_rating) as average_rating from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                  where r.products_id = :productId
                  and r.reviews_id = rd.reviews_id
                  and rd.languages_id = :languageId
                  and r.status = '1'";
        $query = $db->bindVars($query, ":productId", $productId, 'integer');
        $query = $db->bindVars($query, ":languageId", $languageId, 'integer');

        $results = $db->Execute($query);

        return $results->EOF ? null : $results->fields['average_rating'];
    }

    /**
     * Get all reviews for the given product id.
     *
     * @param int productId The product id.
     * @param int languageId Optional language id; default is <code>null</code>
     * @return array List of <code>ZMReview</code> instances.
     */
    function getReviewsForProductId($productId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $query = "select r.reviews_id, r.reviews_rating, p.products_id, p.products_image, pd.products_name,
                rd.reviews_text, r.date_added, r.customers_name, r.reviews_read
                from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, "
                       . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                where p.products_status = '1'
                and p.products_id = r.products_id
                and r.reviews_id = rd.reviews_id
                and rd.languages_id = :languageId
                and p.products_id = pd.products_id
                and pd.language_id = :languageId
                and r.status = '1'
                and p.products_id = :productId";
        $query = $db->bindVars($query, ":languageId", $languageId, 'integer');
        $query = $db->bindVars($query, ":productId", $productId, 'integer');

        $reviews = array();
        $results = $db->Execute($query);
        while (!$results->EOF) {
            $review = $this->_newReview($results->fields);
            array_push($reviews, $review);
            $results->MoveNext();
        }
        return $reviews;
    }

    /**
     * Get all published reviews.
     *
     * @param int languageId Optional language id; default is <code>null</code>
     * @return array List of <code>ZMReview</code> instances.
     */
    function getAllReviews($languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $query = "select r.reviews_id, r.reviews_rating, p.products_id, p.products_image, pd.products_name,
                rd.reviews_text, r.date_added, r.customers_name, r.reviews_read
                from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, "
                       . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                where p.products_status = '1'
                and p.products_id = r.products_id
                and r.reviews_id = rd.reviews_id
                and rd.languages_id = :languageId
                and p.products_id = pd.products_id
                and pd.language_id = :languageId
                and r.status = '1'";
        $query = $db->bindVars($query, ":languageId", $languageId, 'integer');

        $reviews = array();
        $results = $db->Execute($query);
        while (!$results->EOF) {
            $review = $this->_newReview($results->fields);
            array_push($reviews, $review);
            $results->MoveNext();
        }
        return $reviews;
    }

    /**
     * Get the review for the given review id.
     *
     * @param int reviewId The id of the review to load.
     * @param int languageId Optional language id; default is <code>null</code>
     * @return ZMReview A <code>ZMReview</code> instance or <code>null</code>.
     */
    function getReviewForId($reviewId, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $query = "select r.reviews_id, r.reviews_rating, p.products_id, p.products_image, pd.products_name,
                rd.reviews_text, r.date_added, r.customers_name, r.reviews_read
                from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, "
                       . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
                where p.products_status = '1'
                and p.products_id = r.products_id
                and r.reviews_id = rd.reviews_id
                and rd.languages_id = :languageId
                and p.products_id = pd.products_id
                and pd.language_id = :languageId
                and r.status = '1'
                and r.reviews_id = :reviewId";
        $query = $db->bindVars($query, ":languageId", $languageId, 'integer');
        $query = $db->bindVars($query, ":reviewId", $reviewId, 'integer');

        $results = $db->Execute($query);
        $review = null;
        if (!$results->EOF) {
            $review = $this->_newReview($results->fields);
        }
        return $review;
    }

    /**
     * Update the view count for the given review id.
     *
     * @param int reviewId The id of the review.
     */
    function updateViewCount($reviewId) {
        $db = ZMRuntime::getDB();
        $query = "update " . TABLE_REVIEWS . "
                  set reviews_read = reviews_read+1
                  where reviews_id = :reviewId";
        $query = $db->bindVars($query, ":reviewId", $reviewId, 'integer');

        $result = $db->Execute($query);
    }

    /**
     * Create a new review.
     *
     * @param ZMReview review The new review.
     * @param ZMAccount author The review author.
     * @param int languageId The language for this review; default is <code>null</code>.
     * @return ZMReview The inserted review (incl. the new id).
     */
    function createReview($review, $account, $languageId=null) {
        if (null === $languageId) {
            $session = ZMRequest::getSession();
            $languageId = $session->getLanguageId();
        }

        $db = ZMRuntime::getDB();
        $sql = "INSERT INTO " . TABLE_REVIEWS . " (products_id, customers_id, customers_name, reviews_rating, date_added, status)
                VALUES (:productsId, :customersId, :customersName, :rating, now(), :status)";
        $sql = $db->bindVars($sql, ':productsId', $review->getProductId(), 'integer');
        $sql = $db->bindVars($sql, ':customersId', $account->getId(), 'integer');
        $sql = $db->bindVars($sql, ':customersName', $account->getFullName(), 'string');
        $sql = $db->bindVars($sql, ':rating', $review->getRating(), 'string');
        $status = ZMSettings::get('isApproveReviews') ? '0' : '1';
        $sql = $db->bindVars($sql, ':status', $status, 'integer');
        $db->Execute($sql);

        $review->id_ = $db->Insert_ID();

        $sql = "INSERT INTO " . TABLE_REVIEWS_DESCRIPTION . " (reviews_id, languages_id, reviews_text)
                VALUES (:insertId, :languagesId, :reviewText)";
        $sql = $db->bindVars($sql, ':insertId', $review->getId(), 'integer');
        $sql = $db->bindVars($sql, ':languagesId', $languageId, 'integer');
        $sql = $db->bindVars($sql, ':reviewText', $review->getText(), 'string');
        $db->Execute($sql);

        return $review;
    }

    /**
     * Create new review.
     */
    function _newReview($fields) {
        $review = ZMLoader::make("Review");
        $review->id_ = $fields['reviews_id'];
        $review->rating_ = $fields['reviews_rating'];
        $review->text_ = $fields['reviews_text'];
        $review->dateAdded_ = $fields['date_added'];
        $review->productId_ = $fields['products_id'];
        $review->productName_ = $fields['products_name'];
        $review->productImage_ = $fields['products_image'];
        $review->author_ = $fields['customers_name'];
        return $review;
    }

}

?>
