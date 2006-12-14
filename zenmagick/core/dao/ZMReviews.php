<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMReviews extends ZMDao {


    // create new instance
    function ZMReviews() {
        parent::__construct();
    }

    // create new instance
    function __construct() {
        $this->ZMReviews();
    }

    function __destruct() {
    }


    // get the number of reviews for a product
    function getReviewCount($product) {
    global $zm_request;
        $query = "select count(*) as count
                from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                where r.products_id = :productId
                  and r.reviews_id = rd.reviews_id
                  and rd.languages_id = :languageId
                  and r.status = '1'";
        $query = $this->db_->bindVars($query, ":productId", $product->getId(), 'integer');
        $query = $this->db_->bindVars($query, ":languageId", $zm_request->getLanguageId(), 'integer');

        $results = $this->db_->Execute($query);
        return $results->fields['count'];
    }

    // get a random review
    function getRandomReviews($productId=null, $max=1) {
    global $zm_request;
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
        $query = $this->db_->bindVars($query, ":languageId", $zm_request->getLanguageId(), 'integer');

        if (null != $productId) {
            $query .= $this->db_->bindVars(" and p.products_id = :productId", ":productId", $productId, 'integer');
        }
        $query .= " limit " . MAX_RANDOM_SELECT_REVIEWS;

        $reviews = array();
        while ($max > count($reviews)) {
            $results = $this->db_->ExecuteRandomMulti($query, $max);
            while (!$results->EOF) {
                $review = $this->_newReview($results->fields);
                array_push($reviews, $review);
                $results->MoveNext();
                if ($max == count($reviews))
                    break;
            }
            if (0 == count($reviews)) {
                if (null == $productId || !zm_setting('isReviewsDefaultToRandom'))
                    break;
                return $this->getRandomReviews(null, $max);
            }
        }
        return $reviews;
    }


    // get all reviews for the given productId
    function getReviewsForProductId($productId) {
    global $zm_request;
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
        $query = $this->db_->bindVars($query, ":languageId", $zm_request->getLanguageId(), 'integer');
        $query = $this->db_->bindVars($query, ":productId", $productId, 'integer');

        $reviews = array();
        $results = $this->db_->Execute($query);
        while (!$results->EOF) {
            $review = $this->_newReview($results->fields);
            array_push($reviews, $review);
            $results->MoveNext();
        }
        return $reviews;
    }


    // get all reviews
    function getAllReviews() {
    global $zm_request;
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
        $query = $this->db_->bindVars($query, ":languageId", $zm_request->getLanguageId(), 'integer');

        $reviews = array();
        $results = $this->db_->Execute($query);
        while (!$results->EOF) {
            $review = $this->_newReview($results->fields);
            array_push($reviews, $review);
            $results->MoveNext();
        }
        return $reviews;
    }


    // get a specific review
    function getReviewForId($reviewId) {
    global $zm_request;
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
        $query = $this->db_->bindVars($query, ":languageId", $zm_request->getLanguageId(), 'integer');
        $query = $this->db_->bindVars($query, ":reviewId", $reviewId, 'integer');

        $results = $this->db_->Execute($query);
        $review = null;
        if (!$results->EOF) {
            $review = $this->_newReview($results->fields);
        }
        return $review;
    }


    function updateViewCount($reviewId) {
        $query = "update " . TABLE_REVIEWS . "
                  set reviews_read = reviews_read+1
                  where reviews_id = :reviewId";
        $query = $this->db_->bindVars($query, ":reviewId", $reviewId, 'integer');

        $result = $this->db_->Execute($sql);
    }


    function _newReview($fields) {
        $review =& $this->create("Review");
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
