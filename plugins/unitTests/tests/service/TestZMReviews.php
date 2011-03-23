<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Test reviews service.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 */
class TestZMReviews extends ZMTestCase {

    /**
     * Validate the given review as the (single) demo review.
     */
    protected function assertReview($review) {
        $this->assertTrue($review instanceof ZMReview);
        if ($this->assertNotNull($review)) {
            $this->assertEqual(1, $review->getId());
            $this->assertEqual(5, $review->getRating());
            $this->assertEqual(19, $review->getProductId());
            $this->assertEqual('There\'s Something About Mary Linked', $review->getProductName());
            $this->assertEqual('dvd/theres_something_about_mary.gif', $review->getProductImage());
            $this->assertEqual('This really is a very funny but old movie!', $review->getText());
            $this->assertEqual(new DateTime('2003-12-23 03:18:19'), $review->getDateAdded());
            $this->assertEqual('Bill Smith', $review->getAuthor());
        }
    }

    /**
     * Test load single review.
     */
    public function testReviewCount() {
        $this->assertEqual(1, ZMReviews::instance()->getReviewCount(19, 1));
        $this->assertEqual(0, ZMReviews::instance()->getReviewCount(2, 1));
    }

    /**
     * Test get random reviews.
     */
    public function testRandom() {
        $reviews = ZMReviews::instance()->getRandomReviews(1);
        $this->assertTrue(is_array($reviews));
        if ($this->assertEqual(1, count($reviews))) {
            $this->assertReview($reviews[0]);
        }
    }

    /**
     * Test get average rating.
     */
    public function testAverageRating() {
        $rating = ZMReviews::instance()->getAverageRatingForProductId(19, 1);
        $this->assertEqual(5.0, $rating);
    }

    /**
     * Test get reviews for product.
     */
    public function testReviewsForProduct() {
        $reviews = ZMReviews::instance()->getReviewsForProductId(19, 1);
        $this->assertTrue(is_array($reviews));
        if ($this->assertEqual(1, count($reviews))) {
            $this->assertReview($reviews[0]);
        }
    }

    /**
     * Test get all reviews.
     */
    public function testGetAll() {
        $reviews = ZMReviews::instance()->getAllReviews(1);
        $this->assertTrue(is_array($reviews));
        if ($this->assertEqual(1, count($reviews))) {
            $this->assertReview($reviews[0]);
        }
    }

    /**
     * Test get review for id.
     */
    public function testGetReview() {
        $review = ZMReviews::instance()->getReviewForId(1, 1);
        if ($this->assertNotNull($review)) {
            $this->assertReview($review);
        }
    }

    /**
     * Test update view count.
     */
    public function testUpdateViewCount() {
        $review = ZMReviews::instance()->getReviewForId(1, 1);
        if ($this->assertNotNull($review)) {
            ZMReviews::instance()->updateViewCount(1);
            $updated = ZMReviews::instance()->getReviewForId(1, 1);
            if ($this->assertNotNull($updated)) {
                $this->assertEqual(($review->getViewCount() + 1), $updated->getViewCount());
            }
        }
    }

    /**
     * Test create review.
     */
    public function testCreateReview() {
        ZMSettings::set('isApproveReviews', false);
        $account = ZMAccounts::instance()->getAccountForId(1);
        if (null != $account) {
            $review = ZMBeanUtils::getBean('ZMReview');
            $review->setProductId(3);
            $review->setRating(4);
            $review->setLanguageId(1);
            $review->setText('some foo');
            $newReview = ZMReviews::instance()->createReview($review, $account, 1);
            $this->assertTrue(0 != $newReview->getId());

            // make sure it is available via the service
            $found = false;
            foreach (ZMReviews::instance()->getReviewsForProductId(3, 1) as $review) {
                if ($review->getId() == $newReview->getId()) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, '%s new review with id: '.$newReview->getId().' not found');

            // cleanup
            $sql = 'DELETE FROM '.TABLE_REVIEWS.' WHERE reviews_id = :reviewId';
            ZMRuntime::getDatabase()->update($sql, array('reviewId' => $newReview->getId()), TABLE_REVIEWS);
            $sql = 'DELETE FROM '.TABLE_REVIEWS_DESCRIPTION.' WHERE reviews_id = :reviewId';
            ZMRuntime::getDatabase()->update($sql, array('reviewId' => $newReview->getId()), TABLE_REVIEWS_DESCRIPTION);
        } else {
            $this->skip('no test account found');
        }
    }

    /**
     * Test approve review.
     */
    public function testApproveReview() {
        $account = ZMAccounts::instance()->getAccountForId(1);
        if (null != $account) {
            $review = ZMBeanUtils::getBean('ZMReview');
            $review->setProductId(3);
            $review->setRating(4);
            $review->setLanguageId(1);
            $review->setText('some foo');
            ZMSettings::set('isApproveReviews', true);
            $newReview = ZMReviews::instance()->createReview($review, $account, 1);
            $this->assertTrue(0 != $newReview->getId());

            // make sure it is *not* available via the service
            $found = false;
            foreach (ZMReviews::instance()->getReviewsForProductId(3, 1) as $review) {
                if ($review->getId() == $newReview->getId()) {
                    $found = true;
                    break;
                }
            }
            $this->assertFalse($found, '%s new review with id: '.$newReview->getId().' is available!');

            // cleanup
            $sql = 'DELETE FROM '.TABLE_REVIEWS.' WHERE reviews_id = :reviewId';
            ZMRuntime::getDatabase()->update($sql, array('reviewId' => $newReview->getId()), TABLE_REVIEWS);
            $sql = 'DELETE FROM '.TABLE_REVIEWS_DESCRIPTION.' WHERE reviews_id = :reviewId';
            ZMRuntime::getDatabase()->update($sql, array('reviewId' => $newReview->getId()), TABLE_REVIEWS_DESCRIPTION);
        } else {
            $this->skip('no test account found');
        }
    }

}
