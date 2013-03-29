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
namespace ZenMagick\StoreBundle\Tests\Services;

use \DateTime;
use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\StoreBundle\Entity\Catalog\Review;
use ZenMagick\ZenMagickBundle\Test\BaseTestCase;

/**
 * Test reviews service.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ReviewServiceTest extends BaseTestCase
{
    /**
     * Validate the given review as the (single) demo review.
     */
    protected function assertReview($review)
    {
        $this->assertTrue($review instanceof Review);
        if ($this->assertNotNull($review)) {
            $this->assertEquals(1, $review->getId());
            $this->assertEquals(5, $review->getRating());
            $this->assertEquals(19, $review->getProductId());
            $this->assertEquals('There\'s Something About Mary Linked', $review->getProductName());
            $this->assertEquals('dvd/theres_something_about_mary.gif', $review->getProductImage());
            $this->assertEquals('This really is a very funny but old movie!', $review->getText());
            $this->assertEquals(new \DateTime('2003-12-23 03:18:19'), $review->getDateAdded());
            $this->assertEquals('Bill Smith', $review->getAuthor());
        }
    }

    /**
     * Test load single review.
     */
    public function testReviewCount()
    {
        $reviewService = $this->get('reviewService');
        $this->assertEquals(1, $reviewService->getReviewCount(19, 1));
        $this->assertEquals(0, $reviewService->getReviewCount(2, 1));
    }

    /**
     * Test get random reviews.
     */
    public function testRandom()
    {
        $reviews = $this->get('reviewService')->getRandomReviews(1);
        $this->assertTrue(is_array($reviews));
        if ($this->assertEquals(1, count($reviews))) {
            $this->assertReview($reviews[0]);
        }
    }

    /**
     * Test get average rating.
     */
    public function testAverageRating()
    {
        $rating = $this->get('reviewService')->getAverageRatingForProductId(19, 1);
        $this->assertEquals(5.0, $rating);
    }

    /**
     * Test get reviews for product.
     */
    public function testReviewsForProduct()
    {
        $reviews = $this->get('reviewService')->getReviewsForProductId(19, 1);
        $this->assertTrue(is_array($reviews));
        if ($this->assertEquals(1, count($reviews))) {
            $this->assertReview($reviews[0]);
        }
    }

    /**
     * Test get all reviews.
     */
    public function testGetAll()
    {
        $reviews = $this->get('reviewService')->getAllReviews(1);
        $this->assertTrue(is_array($reviews));
        if ($this->assertEquals(1, count($reviews))) {
            $this->assertReview($reviews[0]);
        }
    }

    /**
     * Test get review for id.
     */
    public function testGetReview()
    {
        $review = $this->get('reviewService')->getReviewForId(1, 1);
        if ($this->assertNotNull($review)) {
            $this->assertReview($review);
        }
    }

    /**
     * Test update view count.
     */
    public function testUpdateViewCount()
    {
        $reviewService = $this->get('reviewService');
        $review = $reviewService->getReviewForId(1, 1);
        if ($this->assertNotNull($review)) {
            $reviewService->updateViewCount(1);
            $updated = $reviewService->getReviewForId(1, 1);
            if ($this->assertNotNull($updated)) {
                $this->assertEquals(($review->getViewCount() + 1), $updated->getViewCount());
            }
        }
    }

    /**
     * Test create review.
     */
    public function testCreateReview()
    {
        $reviewService = $this->get('reviewService');
        Runtime::getSettings()->set('isApproveReviews', false);
        $account = $this->get('accountService')->getAccountForId(1);
        if (null != $account) {
            $review = Beans::getBean('ZenMagick\StoreBundle\Entity\Catalog\Review');
            $review->setProductId(3);
            $review->setRating(4);
            $review->setDescription('some foo', 1);
            $newReview = $reviewService->createReview($review, $account, 1);
            $this->assertTrue(0 != $newReview->getId());
            // make sure it is available via the service
            $found = false;
            foreach ($reviewService->getReviewsForProductId(3, 1) as $review) {
                if ($review->getId() == $newReview->getId()) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, '%s new review with id: '.$newReview->getId().' not found');
        } else {
            $this->markTestIncomplete('no test account found');
        }
    }

    /**
     * Test approve review.
     */
    public function testApproveReview()
    {
        $reviewService = $this->get('reviewService');
        $account = $this->get('accountService')->getAccountForId(1);
        if (null != $account) {
            $review = Beans::getBean('ZenMagick\StoreBundle\Entity\Catalog\Review');
            $review->setProductId(3);
            $review->setRating(4);
            $review->setLanguageId(1);
            $review->setText('some foo');
            Runtime::getSettings()->set('isApproveReviews', true);
            $newReview = $reviewService->createReview($review, $account, 1);
            $this->assertTrue(0 != $newReview->getId());

            // make sure it is *not* available via the service
            $found = false;
            foreach ($reviewService->getReviewsForProductId(3, 1) as $review) {
                if ($review->getId() == $newReview->getId()) {
                    $found = true;
                    break;
                }
            }
            $this->assertFalse($found, '%s new review with id: '.$newReview->getId().' is available!');
        } else {
            $this->markTestIncomplete('no test account found');
        }
    }

}
