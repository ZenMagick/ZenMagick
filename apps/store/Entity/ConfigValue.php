<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace ZenMagick\apps\store\Entity;

use ZenMagick\Base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Configuration value.
 *
 * @author DerManoMann
 * @ORM\Table(name="configuration")
 * @ORM\Entity
 */
class ConfigValue extends ZMObject {
    /**
     * @var integer $id
     *
     * @ORM\Column(name="configuration_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @var text $name
     *
     * @ORM\Column(name="configuration_title", type="text", nullable=false)
     */
    private $name;
    /**
     * @var string $key
     *
     * @ORM\Column(name="configuration_key", type="string", length=255, nullable=false)
     */
    private $key;
    /**
     * @var text $value
     *
     * @ORM\Column(name="configuration_value", type="text", nullable=false)
     */
    private $value;
    /**
     * @var text $description
     *
     * @ORM\Column(name="configuration_description", type="text", nullable=false)
     */
    private $description;
    /**
     * @var integer $groupId
     *
     * @ORM\Column(name="configuration_group_id", type="integer", nullable=false)
     */
    private $groupId;
    /**
     * @var integer $sortOrder
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    private $sortOrder;
    /**
     * @var datetime $lastModified
     *
     * @ORM\Column(name="last_modified", type="datetime", nullable=true)
     */
    private $lastModified;
    /**
     * @var datetime $dateAdded
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;
    /**
     * @var text $useFunction
     *
     * @ORM\Column(name="use_function", type="text", nullable=true)
     */
    private $useFunction;
    /**
     * @var text $setFunction
     *
     * @ORM\Column(name="set_function", type="text", nullable=true)
     */
    private $setFunction;


    /**
     * Create new config value.
     */
    public function __construct() {
        parent::__construct();
        $this->name = null;
        $this->description = null;
        $this->key = null;
        $this->value = null;
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
     * @return text $name The name.
     */
    public function getName() { return $this->name; }

    /**
     * Get the key.
     *
     * @return string $key The key.
     */
    public function getKey() { return $this->key; }

    /**
     * Get the value.
     *
     * @return mixed $value The value.
     */
    public function getValue() { return $this->value; }

    /**
     * Get the description.
     *
     * @return text $description The description.
     */
    public function getDescription() { return $this->description; }

    /**
     * Get groupId
     *
     * @return integer $GroupId
     */
    public function getGroupId() { return $this->groupId; }

    /**
     * Get sortOrder
     *
     * @return integer $sortOrder
     */
    public function getSortOrder() { return $this->sortOrder; }

    /**
     * Get lastModified
     *
     * @return datetime $lastModified
     */
    public function getLastModified() { return $this->lastModified; }

    /**
     * Get dateAdded
     *
     * @return datetime $dateAdded
     */
    public function getDateAdded() { return $this->dateAdded; }

    /**
     * Get useFunction
     *
     * @return text $useFunction
     */
    public function getUseFunction() { return $this->useFunction; }

    /**
     * Get the set function.
     *
     * @return text $setFunction The set function.
     * @deprecated
     */
    public function getSetFunction() { return $this->setFunction; }

    /**
     * Check if a set function is set or not.
     *
     * @return boolean <code>true</code> if a set function is configured, <code>false<code> if not.
     */
    public function hasSetFunction() {
        return !empty($this->setFunction);
    }

    /**
     * Set the id.
     *
     * @param string id The id.
     */
    public function setId($id) { $this->id = $id; }

    /**
     * Set the name.
     *
     * @param text $name The name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set the key.
     *
     * @param string $key The key.
     */
    public function setKey($key) { $this->key = $key; }

    /**
     * Set the value.
     *
     * @param mixed $value The value.
     */
    public function setValue($value) { $this->value = $value; }

    /**
     * Set the description.
     *
     * @param text $description The description.
     */
    public function setDescription($description) { $this->description = $description ; }

    /**
     * Set groupId
     *
     * @param integer $groupId
     */
    public function setGroupId($groupId) { $this->groupId = $groupId; }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     */
    public function setSortOrder($sortOrder) { $this->sortOrder = $sortOrder; }

    /**
     * Set lastModified
     *
     * @param datetime $lastModified
     */
    public function setLastModified($lastModified) { $this->lastModified = $lastModified; }

    /**
     * Set dateAdded
     *
     * @param datetime $dateAdded
     */
    public function setDateAdded($dateAdded) { $this->dateAdded = $dateAdded; }

    /**
     * Set useFunction
     *
     * @param text $useFunction
     */
    public function setUseFunction($useFunction) { $this->useFunction = $useFunction; }

    /**
     * Set the set function.
     *
     * @param text $setFunction function The use function.
     * @deprecated
     */
    public function setSetFunction($setFunction) { $this->setFunction = $setFunction; }
}
