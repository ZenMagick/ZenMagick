<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenCartBundle\Entity\DbCache
 *
 * @ORM\Table(name="db_cache")
 * @ORM\Entity
 */
class DbCache {
    /**
     * @var string $name
     *
     * @ORM\Column(name="cache_entry_name", type="string", length=64, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $name;

    /**
     * @var string $data
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

}
