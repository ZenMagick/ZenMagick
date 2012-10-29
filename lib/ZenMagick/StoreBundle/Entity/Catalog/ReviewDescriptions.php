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
namespace ZenMagick\StoreBundle\Entity\Catalog;

use ZenMagick\Base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * A collection of review descriptions (translations)
 *
 * @ORM\Entity
 * @ORM\Table(name="reviews_description")
 */
class ReviewDescriptions extends ZMObject {
    /**
     * @var object $review
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ZenMagick\StoreBundle\Entity\Catalog\Review", inversedBy="descriptions")
     * @ORM\JoinColumn(name="reviews_id", referencedColumnName="reviews_id")
     */
    private $review;

    /**
     * @var integer $languageId
     * @ORM\Column(name="languages_id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $languageId;

    /**
     * @var string $title
     * @ORM\Column(name="title", type="string")
     */
    private $title;

    /**
     * @var text $text
     * @ORM\Column(name="reviews_text", type="text")
     */
    private $text;

    public function __construct($review, $languageId = 1, $text = '')
    {
        $this->review = $review;
        $this->title = '';
        $this->setLanguageId($languageId);
        $this->setText($text);
    }

    /**
     * Get reviewId
     *
     * @return integer $reviewId
     */
    public function getReviewId()
    {
        return $this->review->getReviewId();
    }

    public function setLanguageId($languageId)
    {
        $this->languageId = $languageId;
    }

    /**
     * Get languageId
     *
     * @return integer $languageId
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param text $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return text $text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set review
     *
     * @param  ZenMagick\StoreBundle\Entity\Catalog\Review $review
     * @return ReviewDescriptions
     */
    public function setReview(\ZenMagick\StoreBundle\Entity\Catalog\Review $review)
    {
        $this->review = $review;
        return $this;
    }

    /**
     * Get review
     *
     * @return ZenMagick\StoreBundle\Entity\Catalog\Review
     */
    public function getReview()
    {
        return $this->review;
    }
}
