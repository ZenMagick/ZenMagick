<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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


/**
 * Category access; both flat and as a tree.
 *
 * @author mano
 * @package net.radebatz.zenmagick.dao
 * @version $Id$
 */
class ZMCategories extends ZMDao {
    // current path
    var $path_;
    // category type
    var $type_;

    // flat list
    var $categories_;
    // tree structure
    var $tree_;


    /**
     * Default c'tor.
     */
    function ZMCategories($path = null, $type = null) {
        parent::__construct();

        $this->path_ = (null !== $path ? $path : array());
        $this->type_ = $type;
        $this->categories_ = null;
        $this->tree_ = null;
        // required to make category->getPath() work
        $this->_buildTree();
    }

    /**
     * Default c'tor.
     */
    function __construct($path = null, $type = null) {
        $this->ZMCategories($path, $type);
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the active path.
     *
     * @param array path The current path array.
     */
    function setPath($path) { $this->path_ = $path; }

    /**
     * Return the current category.
     * <p>This should rather be in <code>ZMRequest</code>.</p>
     *
     * @return ZMCategory The current category or <code>null</code>.
     */
    function getActiveCategory() {
        return null !== $this->path_ && 0 < count($this->path_) ?
                $this->getCategoryForId(end($this->path_)) : null;
    }

    /**
     * Return the current category id.
     * <p>This should rather be in <code>ZMRequest</code>.</p>
     *
     * @return int The current category id or <code>0</code>.
     */
    function getActiveCategoryId() {
        return null !== $this->path_ && 0 < count($this->path_) ?
                end($this->path_) : 0;
    }

    /**
     * Get the default category for the given product id.
     * <p>This will return the first mapped category.</p>
     *
     * @param int productId The product id.
     * @return ZMCategory The default category (or <code>null</code>).
     */
    function getDefaultCategoryForProductId($productId) {
        $sql = "SELECT categories_id
                FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                WHERE c products_id = :productId";
        $sql = $this->db_->bindVars($query, ":productId", $productId, 'integer');
        $results = $this->db_->Execute($sql);

        $category = null;
        if (!$results->EOF) {
            $category =& $this->getCategoryForId($results->fields);
        }
        
        return $category;
    }

    // returns true if categories have been loaded
    function loaded() { return null !== $this->categories_; }

    // returns true if active categories are available
    function hasActive() {
        // quick check to avoid db access
        if ($this->loaded()) { return 0 < count($this->categories_); }

        $results = $this->db_->Execute("select categories_id from " . TABLE_CATEGORIES . " where categories_status = 1 limit 1");
        return 0 < $results->RecordCount();
    }

    // get all categories
    function getCategories() {
        if (!$this->loaded()) { $this->_load(); }
        return $this->categories_;
    }

    // get the categorie tree
    function getCategoryTree() {
        if (null === $this->tree_) {
            $this->_buildTree();
        }
        return $this->tree_;
    }

    function getCategoryForId($categoryId, $languageId=null) {
    global $zm_runtime;

        if (null != $languageId && $languageId != $zm_runtime->getLanguageId()) {
            // not preloaded
            $query = "select c.categories_id, cd.categories_name, c.parent_id, cd.categories_description, c.categories_image, c.sort_order
                      from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                      where c.categories_id = cd.categories_id
                      and c.categories_id = :categoryId
                      and cd.language_id = :languageId
                      and c.categories_status = '1'
                      order by sort_order, cd.categories_name";
            $query = $this->db_->bindVars($query, ":categoryId", $categoryId, 'integer');
            $query = $this->db_->bindVars($query, ":languageId", $languageId, 'integer');

            $results = $this->db_->Execute($query);

            $category = null;
            if (!$results->EOF) {
                $category =& $this->_newCategory($results->fields);
            }
            return $category;
        }

        if (!$this->loaded()) { $this->_load(); }
        $cat =& $this->categories_[$categoryId];
        return $cat;
    }


    // load all categories
    function _load() {
    global $zm_runtime;

        // load all straight away - should be faster to sort them later on
        $query = "select c.categories_id, cd.categories_name, c.parent_id, cd.categories_description, c.categories_image, c.sort_order
                  from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                  where c.categories_id = cd.categories_id
                  and cd.language_id = :languageId
                  and c.categories_status = '1'
                  order by sort_order, cd.categories_name";
        $query = $this->db_->bindVars($query, ":languageId", $zm_runtime->getLanguageId(), "integer");
        $results = $this->db_->Execute($query, '', true, 150);

        $this->categories_ = array();
        while (!$results->EOF) {
            $category =& $this->_newCategory($results->fields);

            // apply path
            foreach ($this->path_ as $catId) {
                if ($catId == $category->id_)
                    $category->active_ = true;
            }
            $this->categories_[$category->id_] = $category;

            $results->MoveNext();
		    }
    }

    // parse categories into tree structure (alternative for PHP5 only)
    function _buildTree_v5() {
        if (!$this->loaded()) { $this->_load(); }
        $this->tree_ = array();

        // create all children and parents
        foreach ($this->categories_ as $id => $category) {
            if (0 == $category->getParentId()) { continue; }
            $parent = $this->categories_[$category->getParentId()];

            // add links for parent and child
            $parent->addChild($category);
            $category->setParent($parent);
        }

        foreach ($this->categories_ as $id => $category) {
            if (!$category->hasParent()) {
                array_push($this->tree_, $category);
            }
        }
    }


    // parse categories into tree structure (PHP4 and PHP5)
    function _buildTree() {
        if (!$this->loaded()) { $this->_load(); }
        $this->tree_ = array();

        // create instances using node id as name (suffix)
        foreach ($this->categories_ as $category) {
            $nname = "n".$category->id_;
            $$nname = $category;
        }

        // keep track of processed nodes
        $processed = array();

        // find leafs and process
        $max = count($this->categories_);
        while (count($processed) != $max) {
            foreach ($this->categories_ as $category) {
                $nname = "n".$category->id_;
                if (array_key_exists($nname, $processed))
                    continue;

                // find out if node is parent
                $isParent = false;
                foreach ($this->categories_ as $node) {
                    $pnname = "n".$node->parentId_;
                    $pnnname = "n".$node->id_;
                    if ($nname == $pnname && !array_key_exists($pnnname, $processed)) {
                        // is parent
                        $isParent = true;
                        break;
                    }
                }

                // process if leaf
                if (!$isParent) {
                    $pnname = "n".$$nname->parentId_;
                    // check for valid parent
                    if ("n0" != $pnname) {
                        $$nname->parent_ = $$pnname;
                        $$pnname->addChild($$nname);
                    } else {
                      $this->tree_[$$nname->id_] = $$nname;
                    }

                    // mark as processed
                    $processed[$nname] = $nname;
                }
            }
        }
        
        // sort
        foreach ($this->categories_ as $category) {
            $nname = "n".$category->id_;
            usort($$nname->children_, array($this, "_nodeCompare"));
            $this->categories_[$category->id_] =& $$nname;
        }
        usort($this->tree_, array($this, "_nodeCompare"));
    }

    function _nodeCompare($n1, $n2) {
        return ($n1->id_ == $n2->id_ ? 0 : ($n1->id_ > $n2->id_) ? +1 : -1);
    }

    // build category
    function _newCategory($fields) {
        $category =& $this->create("Category", $fields['categories_id'], $fields['parent_id'], $fields['categories_name'], false);
        $category->description_ = $fields['categories_description'];
        $category->sortOrder_ = $fields['sort_order'];
        $category->image_ = $fields['categories_image'];
        return $category;
    }

}

?>
