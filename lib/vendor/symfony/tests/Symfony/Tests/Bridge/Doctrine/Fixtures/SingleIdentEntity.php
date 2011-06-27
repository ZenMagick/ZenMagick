<?php

namespace Symfony\Tests\Bridge\Doctrine\Form\Fixtures;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

/** @Entity */
class SingleIdentEntity
{
    /** @Id @Column(type="integer") */
    protected $id;

    /** @Column(type="string") */
    public $name;

    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }
}
