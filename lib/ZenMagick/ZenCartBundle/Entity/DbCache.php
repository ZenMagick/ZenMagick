<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenCartBundle\Entity\DbCache
 *
 * @ORM\Table(name="db_cache")
 * @ORM\Entity
 */
class DbCache
{
    /**
     * @var string $name
     *
     * @ORM\Column(name="cache_entry_name", type="string", length=64, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $name;

    /**
     * @var blob $data
     *
     * @ORM\Column(name="cache_data", type="blob", nullable=true)
     */
    private $data;

    /**
     * @var integer $created
     *
     * @ORM\Column(name="cache_entry_created", type="integer", nullable=true)
     */
    private $created;



    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set data
     *
     * @param blob $data
     * @return DbCache
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get data
     *
     * @return blob 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set created
     *
     * @param integer $created
     * @return DbCache
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get created
     *
     * @return integer 
     */
    public function getCreated()
    {
        return $this->created;
    }
}