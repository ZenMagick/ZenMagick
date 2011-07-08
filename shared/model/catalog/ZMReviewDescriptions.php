<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
use Doctrine\ORM\Mapping AS ORM;

/**
 * A collection of review descriptions (translations)
 *
 * @ORM\Entity
 * @ORM\Table(name="reviews_description")
 */
class ZMReviewDescriptions extends ZMObject {
    /**
     * @var object $review
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="ZMReview", inversedBy="descriptions")
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
     * @var text $text
     * @ORM\Column(name="reviews_text", type="text")
     */
    private $text;

    public function __construct($review, $languageId, $text = '')
    {
        $this->review = $review;
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
}
