<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenCartBundle\Entity\Counter
 *
 * @ORM\Table(name="counter")
 * @ORM\Entity
 */
class Counter
{
    /**
     * @var string $startdate
     *
     * @ORM\Column(name="startdate", type="string", length=8, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $startDate;

    /**
     * @var integer $counter
     *
     * @ORM\Column(name="counter", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $counter;



    /**
     * Set startDate
     *
     * @param string $startDate
     * @return Counter
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * Get startDate
     *
     * @return string 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set counter
     *
     * @param integer $counter
     * @return Counter
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;
        return $this;
    }

    /**
     * Get counter
     *
     * @return integer 
     */
    public function getCounter()
    {
        return $this->counter;
    }
}