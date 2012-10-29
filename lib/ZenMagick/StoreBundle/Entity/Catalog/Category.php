<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ZenMagick\Base\Beans;
use ZenMagick\Base\ZMObject;

/**
 * A single category
 *
 * @ORM\Table(name="categories",
 *  indexes={
 *      @ORM\Index(name="idx_parent_id_cat_id_zen", columns={"parent_id", "categories_id"}),
 *      @ORM\Index(name="idx_status_zen", columns={"categories_status"}),
 *      @ORM\Index(name="idx_sort_order_zen", columns={"sort_order"}),
 *  })
 * @ORM\Entity
 * @author DerManoMann
 */
class Category extends ZMObject {
    /**
     * @var integer $categoryId
     *
     * @ORM\Column(name="categories_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $categoryId;

    /**
     * @var string $image
     *
     * @ORM\Column(name="categories_image", type="string", length=64, nullable=false)
     */
    private $image;

    /**
     * @var integer $parentId
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * @var integer $sortOrder
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    private $sortOrder;

    /**
     * @var \DateTime $dateAdded
     *
     * @ORM\Column(name="date_added", type="datetime", nullable=true)
     */
    private $dateAdded;

    /**
     * @var \DateTime $lastModified
     *
     * @ORM\Column(name="last_modified", type="datetime", nullable=true)
     */
    private $lastModified;

    /**
     * @var boolean active
     *
     * @ORM\Column(name="categories_status", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="categories_id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @var object $descriptions
     * @ORM\OneToMany(targetEntity="CategoryDescriptions", mappedBy="category", cascade={"persist", "remove"})
     */
    private $descriptions;

    private $name;
    private $childrenIds;
    private $description;
    private $languageId;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();

        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->descriptions = new \Doctrine\Common\Collections\ArrayCollection();

        $this->setId(0);
        $this->parentId = 0;
        $this->name = null;
        $this->active = false;
        $this->childrenIds = array();
        $this->image = null;
        $this->languageId = 1;
        $this->setDateAdded(null);
        $this->setLastModified(null);
    }

    /**
     * Get the id.
     *
     * @return int The category id.
     */
    public function getId() { return $this->categoryId; }

    /**
     * Get the id.
     *
     * @return int The category id.
     */
    public function getCategoryId() { return $this->categoryId; }

    /**
     * Get the parent category (if any).
     *
     * @return ZenMagick\StoreBundle\Entity\Catalog\Category The parent category or <code>null</code>.
     */
    public function getParent() {
        return 0 != $this->parentId ? $this->container->get('categoryService')->getCategoryForId($this->parentId, $this->languageId) : null;
    }

    /**
     * Get the parent category id (if any).
     *
     * @return int The parent category id or <code>0</code>.
     */
    public function getParentId() { return $this->parentId; }

    /**
     * Checks if the catgory has a parent.
     *
     * @return boolean <code>true</code> if this category has a parent, <code>false</code> if not.
     */
    public function hasParent() { return 0 != $this->parentId; }

    /**
     * Get the category name.
     *
     * @return string The category name.
     */
    public function getName() { return $this->name; }

    /**
     * Checks if this category is active; ie. visible in the storefront.
     *
     * @return boolean <code>true</code> if this category is active, <code>false</code> if not.
     */
    public function isActive() { return $this->active; }

    /**
     * Set the active flag.
     *
     * @param boolean active <code>true</code> if this category is active, <code>false</code> if not.
     */
    public function setActive($active) { $this->active = $active; }

    /**
     * Checks if this category has children.
     *
     * @return boolean <code>true</code> if this category has children, <code>false</code> if not.
     */
    public function hasChildren() { return 0 < count($this->childrenIds); }

    /**
     * Get the child categories of this category.
     *
     * @return array A list of <code>ZenMagick\StoreBundle\Entity\Catalog\Category</code> instances.
     */
    public function getChildren() {
        return $this->container->get('categoryService')->getCategories($this->languageId, $this->childrenIds);
    }

    /**
     * Add a child category.
     *
     * @param mixed child Either a category or category id.
     */
    public function addChild($child) {
        $id = ($child instanceof Category)  ? $child->getId() : $child;
        if (!in_array($id, $this->childrenIds)) {
            $this->childrenIds[] = $id;
        }
    }

    /**
     * Remove a child category.
     *
     * @param mixed child Either a category or category id.
     */
    public function removeChild($child) {
        $cid = ($child instanceof Category)  ? $child->getId() : $child;
        $tmp = array();
        foreach ($this->childrenIds as $id) {
            if ($id != $cid) {
                $tmp[] = $id;
            }
        }
        $this->childrenIds = $tmp;
    }

    /**
     * Get the category description.
     *
     * @return string The description.
     */
    public function getDescription() { return $this->description; }

    /**
     * Get the sort order.
     *
     * @return int The sort order.
     */
    public function getSortOrder() { return $this->sortOrder; }

    /**
     * Get the category image (if any).
     *
     * @return string The image name.
     */
    public function getImage() { return $this->image; }

    /**
     * Get the categories image ino instance (if any).
     *
     * @return ZMImageInfo The <code>ZMImageInfo</code> for this categorie's image, or <code>null</code>.
     */
    public function getImageInfo() {
        if (null == $this->image) {
            return null;
        }

        $imageInfo = Beans::getBean('ZMImageInfo');
        $imageInfo->setAltText($this->name);
        $imageInfo->setDefaultImage($this->image);
        return $imageInfo;
    }

    /**
     * Get the category path.
     *
     * @return array The category path as array of category ids with the last element being the products category.
     */
    public function getPath() {
        $path = array();
        array_push($path, $this->categoryId);
        $parent = $this->getParent();
        while (null !== $parent) {
            array_push($path, $parent->getId());
            $parent = $parent->getParent();
        }
        return array_reverse($path);
    }

    /**
     * Set the id.
     *
     * @param int id The category id.
     */
    public function setId($id) { $this->categoryId = $id; }

    /**
     * Set the id.
     *
     * @param int id The category id.
     */
    public function setCategoryId($id) { $this->categoryId = $id; }

    /**
     * Set the parent category id.
     *
     * @param int parentId The parent category id.
     */
    public function setParentId($parentId) { $this->parentId = $parentId; }

    /**
     * Set the category name.
     *
     * @param string name The category name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set the category description.
     *
     * @param string description The description.
     */
    public function setDescription($description) { $this->description = $description; }

    /**
     * Set the sort order.
     *
     * @param int sortOrder The sort order.
     */
    public function setSortOrder($sortOrder) { $this->sortOrder = $sortOrder; }

    /**
     * Set the category image (if any).
     *
     * @param string image The image name.
     */
    public function setImage($image) { $this->image = $image; }

    /**
     * Get the language id.
     *
     * @return int The language id.
     */
    public function getLanguageId() { return $this->languageId; }

    /**
     * Set the language id.
     *
     * @param int languageId The language id.
     */
    public function setLanguageId($languageId) { $this->languageId = $languageId; }

    /**
     * Get a list of <strong>all</strong> decendant category ids.
     *
     * <p>This is a recursive function. If you only want the direct children use <code>getChildren()</code>.</p>
     *
     * @param boolean includeSelf Optional flag to include this category in the list; default is <code>true</code>.
     * @return array A list of category ids.
     */
    public function getDecendantIds($includeSelf=true) {
        $ids = array();
        if ($includeSelf) {
            $ids[] = $this->categoryId;
        }
        foreach ($this->getChildren() as $child) {
            $childIds = $child->getDecendantIds(true);
            $ids = array_merge($ids, $childIds);
        }
        return $ids;
    }

    /**
     * Get a list of allowed product types.
     *
     * <p>An empty list means no restrictions.</p>
     *
     * @return array List of allowed product type ids (might be empty).
     */
    public function getProductTypeIds() {
        return $this->container->get('categoryService')->getProductTypeIds($this->getId());
    }

    /**
     * Get meta tag details if available.
     *
     * @param int languageId The language id.
     * @return ZenMagick\StoreBundle\Entity\Catalog\MetaTagDetails The details or <code>null</code>.
     */
    public function getMetaTagDetails($languageId) {
        return $this->container->get('categoryService')->getMetaTagDetailsForId($this->getId(), $languageId);
    }

}
