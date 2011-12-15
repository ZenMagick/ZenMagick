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

use zenmagick\base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Configuration group.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model
 * @ORM\Table(name="configuration_group")
 * @ORM\Entity
 */
class ZMConfigGroup extends ZMObject {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="configuration_group_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @var string $name
     *
     * @ORM\Column(name="configuration_group_title", type="string", length=64, nullable=false)
     */
    private $name;
    /**
     * @var string $description
     *
     * @ORM\Column(name="configuration_group_description", type="string", length=255, nullable=false)
     */
    private $description;
    /**
     * @var integer $sortOrder
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    private $sortOrder;
    /**
     * @var boolean $visible
     *
     * @ORM\Column(name="visible", type="boolean", nullable=true)
     */
    private $visible;


    /**
     * Create new config group.
     */
    public function __construct() {
        parent::__construct();
        $this->name = null;
        $this->description = null;
        $this->sortOrder = 0;
        $this->visible = false;
    }


    /**
     * Get the id.
     *
     * @return integer $id The id.
     */
    public function getId() { return $this->id; }

    /**
     * Get the name.
     *
     * @return string $name The name.
     */
    public function getName() { return $this->name; }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription() { return $this->description; }

    /**
     * Get sortOrder
     *
     * @return integer $sortOrder
     */
    public function getSortOrder() { return $this->sortOrder; }

    /**
     * Get the visible flag.
     *
     * @return boolean $visible The flag.
     */
    public function isVisible() { return $this->visible; }

    /**
     * Set the id.
     *
     * @param string $id The id.
     */
    public function setId($id) { $this->id = $id; }

    /**
     * Set the name.
     *
     * @param string $name The name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description) { $this->description = $description; }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     */
    public function setSortOrder($sortOrder) { $this->sortOrder = $sortOrder; }

    /**
     * Set the visible flag.
     *
     * @param boolean $visible The new value.
     */
    public function setVisible($visible) { $this->visible = $visible; }
}
