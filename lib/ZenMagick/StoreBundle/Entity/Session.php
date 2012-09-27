<?php

namespace ZenMagick\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\StoreBundle\Entity\Session
 *
 * @ORM\Table(name="sessions")
 * @ORM\Entity
 */
class Session
{
    /**
     * @var string $sesskey
     *
     * @ORM\Column(name="sesskey", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var integer $expiry
     *
     * @ORM\Column(name="expiry", type="integer", nullable=false)
     */
    private $time;

    /**
     * @var text $value
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    private $value;


}
