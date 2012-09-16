<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenCartBundle\Entity\CounterHistory
 *
 * @ORM\Table(name="counter_history")
 * @ORM\Entity
 */
class CounterHistory
{
    /**
     * @var string $startdate
     *
     * @ORM\Column(name="startdate", type="string", length=8, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $startDate;

    /**
     * @var integer $counter
     *
     * @ORM\Column(name="counter", type="integer", nullable=true)
     */
    private $counter;

    /**
     * @var integer $sessionCounter
     *
     * @ORM\Column(name="session_counter", type="integer", nullable=true)
     */
    private $sessionCounter;



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
     * @return CounterHistory
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

    /**
     * Set sessionCounter
     *
     * @param integer $sessionCounter
     * @return CounterHistory
     */
    public function setSessionCounter($sessionCounter)
    {
        $this->sessionCounter = $sessionCounter;
        return $this;
    }

    /**
     * Get sessionCounter
     *
     * @return integer
     */
    public function getSessionCounter()
    {
        return $this->sessionCounter;
    }
}
