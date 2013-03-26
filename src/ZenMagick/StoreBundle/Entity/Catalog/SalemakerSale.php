<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace ZenMagick\StoreBundle\Entity\Catalog;

use ZenMagick\Base\ZMObject;
use ZenMagick\Base\Database\Connection;
use Doctrine\ORM\Mapping AS ORM;

/**
 * An individual sale.
 *
 * @ORM\Table(name="salemaker_sales",
 *  indexes={
 *      @ORM\Index(name="idx_sale_status_zen", columns={"sale_status"}),
 *      @ORM\Index(name="idx_sale_date_start_zen", columns={"sale_date_start"}),
 *      @ORM\Index(name="idx_sale_date_end_zen", columns={"sale_date_end"}),
 *  })
 * @ORM\Entity
 */
class SalemakerSale extends ZMObject
{
    /**
     * @var integer $saleId
     *
     * @ORM\Column(name="sale_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $saleId;
    /**
     * @var smallint $status
     *
     * @ORM\Column(name="sale_status", type="smallint", nullable=false)
     */
    private $status;
    /**
     * @var string $name
     *
     * @ORM\Column(name="sale_name", type="string", length=30, nullable=false)
     */
    private $name;
    /**
     * @var decimal $deductionValue
     *
     * @ORM\Column(name="sale_deduction_value", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $deductionValue;
    /**
     * @var boolean $deductionType
     *
     * @ORM\Column(name="sale_deduction_type", type="smallint", nullable=false)
     */
    private $deductionType;
    /**
     * @var decimal $priceFrom
     *
     * @ORM\Column(name="sale_pricerange_from", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $priceFrom;
    /**
     * @var decimal $priceTo
     *
     * @ORM\Column(name="sale_pricerange_to", type="decimal", precision=15, scale=4, nullable=false)
     */
    private $priceTo;
    /**
     * @var boolean $specialsCondition
     *
     * @ORM\Column(name="sale_specials_condition", type="smallint", nullable=false)
     */
    private $specialsCondition;

    /**
     * @var text $categoriesSelected
     *
     * @ORM\Column(name="sale_categories_selected", type="text", nullable=true)
     */
    private $categoriesSelected;

    /**
     * @var text $categoriesAll
     *
     * @ORM\Column(name="sale_categories_all", type="text", nullable=true)
     */
    private $categoriesAll;
    /**
     * @var date $dateStart
     *
     * @ORM\Column(name="sale_date_start", type="date", nullable=false)
     */
    private $dateStart;
    /**
     * @var date $dateEnd
     *
     * @ORM\Column(name="sale_date_end", type="date", nullable=false)
     */
    private $dateEnd;
    /**
     * @var date $dateAdded
     *
     * @ORM\Column(name="sale_date_added", type="date", nullable=false)
     */
    private $dateAdded;
    /**
     * @var date $dateLastModified
     *
     * @ORM\Column(name="sale_date_last_modified", type="date", nullable=false)
     */
    private $dateLastModified;

    /**
     * @var date $dateStatusChange
     *
     * @ORM\Column(name="sale_date_status_change", type="date", nullable=false)
     */
    private $dateStatusChange;

    public function __construct()
    {
        parent::__construct();
        $this->status = false;
        $this->deductionValue = 0;
        $this->deductionType = 0;
        $this->priceFrom = 0;
        $this->priceTo = 0;
        $this->specialsCondition = 0;
        $this->dateStart = Connection::NULL_DATE;
        $this->dateEnd = Connection::NULL_DATE;
        $this->dateAdded = Connection::NULL_DATE;
        $this->dateLastModified = Connection::NULL_DATE;
        $this->dateStatusChange = Connection::NULL_DATE;
    }

    /**
     * Get id of sale.
     *
     * @return integer $saleId
     * @deprecated (use getId() throughout)
     */
    public function getSaleId()
    {
        return $this->getId();
    }

    /**
     * Get id of sale.
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->saleId;
    }

    /**
     * Get status
     *
     * @return boolean $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get deductionValue
     *
     * @return decimal $deductionValue
     */
    public function getDeductionValue()
    {
        return $this->deductionValue;
    }

    /**
     * Get deductionType
     *
     * @return boolean $deductionType
     */
    public function getDeductionType()
    {
        return $this->deductionType;
    }

    /**
     * Get priceFrom
     *
     * @return decimal $priceFrom
     */
    public function getPriceFrom()
    {
        return $this->priceFrom;
    }

    /**
     * Get priceTo
     *
     * @return decimal $priceTo
     */
    public function getPriceTo()
    {
        return $this->priceTo;
    }

    /**
     * Get specialsCondition
     *
     * @return boolean $specialsCondition
     */
    public function getSpecialsCondition()
    {
        return $this->specialsCondition;
    }

    /**
     * Get categoriesSelected
     *
     * @return text $categoriesSelected
     */
    public function getCategoriesSelected()
    {
        return $this->categoriesSelected;
    }

    /**
     * Get categoriesAll
     *
     * @return text $categoriesAll
     */
    public function getCategoriesAll()
    {
        return $this->categoriesAll;
    }

    /**
     * Get dateStart
     *
     * @return date $dateStart
     */
    public function getDateStart()
    {
        return $this->dateStart == Connection::NULL_DATE ? null : $this->dateStart;
    }

    /**
     * Get dateEnd
     *
     * @return date $dateEnd
     */
    public function getDateEnd()
    {
        return $this->dateEnd == Connection::NULL_DATE ? null : $this->dateEnd;
    }

    /**
     * Get dateAdded
     *
     * @return date $dateAdded
     */
    public function getDateAdded()
    {
        return $this->dateAdded == Connection::NULL_DATE ? null : $this->dateAdded;
    }

    /**
     * Get dateLastModified
     *
     * @return date $dateLastModified
     */
    public function getDateLastModified()
    {
        return $this->dateLastModified == Connection::NULL_DATE ? null : $this->dateLastModified;
    }

    /**
     * Get dateStatusChange
     *
     * @return date $dateStatusChange
     */
    public function getDateStatusChange()
    {
        return $this->dateStatusChange == Connection::NULL_DATE ? null : $this->dateStatusChange;
    }

    /**
     * Set sale id
     *
     * @param int $id
     */
    public function setSaleId($id)
    {
        $this->saleId = $id;
    }

    /**
     * Set status
     *
     * @param boolean $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
        $this->setDateStatusChange(new \DateTime());
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set deductionValue
     *
     * @param decimal $deductionValue
     */
    public function setDeductionValue($deductionValue)
    {
        $this->deductionValue = $deductionValue;

        return $this;
    }

    /**
     * Set deductionType
     *
     * @param boolean $deductionType
     */
    public function setDeductionType($deductionType)
    {
        $this->deductionType = $deductionType;

        return $this;
    }

    /**
     * Set priceFrom
     *
     * @param decimal $priceFrom
     */
    public function setPriceFrom($priceFrom)
    {
        $this->priceFrom = $priceFrom;

        return $this;
    }

    /**
     * Set priceTo
     *
     * @param decimal $priceTo
     */
    public function setPriceTo($priceTo)
    {
        $this->priceTo = $priceTo;

        return $this;
    }

    /**
     * Set specialsCondition
     *
     * @param boolean $specialsCondition
     */
    public function setSpecialsCondition($specialsCondition)
    {
        $this->specialsCondition = $specialsCondition;

        return $this;
    }

    /**
     * Set categoriesSelected
     *
     * @param text $categoriesSelected
     */
    public function setCategoriesSelected($categoriesSelected)
    {
        $this->categoriesSelected = $categoriesSelected;

        return $this;
    }

    /**
     * Set categoriesAll
     *
     * @param text $categoriesAll
     */
    public function setCategoriesAll($categoriesAll)
    {
        $this->categoriesAll = $categoriesAll;

        return $this;
    }

    /**
     * Set dateStart
     *
     * @param date $dateStart
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * Set dateEnd
     *
     * @param date $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Set dateAdded
     *
     * @param date $dateAdded
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Set dateLastModified
     *
     * @param date $dateLastModified
     */
    public function setDateLastModified($dateLastModified)
    {
        $this->dateLastModified = $dateLastModified;

        return $this;
    }

    /**
     * Set dateStatusChange
     *
     * @param date $dateStatusChange
     */
    public function setDateStatusChange($dateStatusChange)
    {
        $this->dateStatusChange = $dateStatusChange;

        return $this;
    }
}
