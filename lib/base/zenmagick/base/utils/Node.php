<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\base\utils;

use zenmagick\base\ZMObject;


/**
 * Basic node.
 *
 * @param author DerManoMann
 * @package zenmagick.base.utils
 */
class Node extends ZMObject {
    const INSERT_BEFORE = "before";
    const INSERT_AFTER = "after";
    private $id;
    private $name;
    private $parent;
    private $children;


    /**
     * Create instance.
     *
     * @param string id Optional id; default is <code>null</code>.
     * @param string name Optional name; default is an empty string <code>''</code>.
     */
    public function __construct($id=null, $name='') {
        parent::__construct();
        $this->id = $id;
        $this->name = $name;
        $this->parent = null;
        $this->children = array();
    }


    /**
     * Set id.
     *
     * @param string id The node id.
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Get id.
     *
     * @return string The node id.
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string name The node name.
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get name.
     *
     * @return string The node name.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set parent.
     *
     * @param Node node The parent node.
     */
    public function setParent($node) {
        $this->parent = $node;
    }

    /**
     * Get parent.
     *
     * @return Node The parent node.
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Add child.
     *
     * @param Node child The new child.
     * @param string siblingId Optional relative sibling id; required when setting the mode; default is <code>null</code>.
     * @param string mode Optional insert mode; default is <code>null</code> to append.
     */
    public function addChild(Node $child, $siblingId=null, $mode=null) {
        $siblingIndex = null;
        if (null != $siblingId) {
            // validate and lookup position
            $siblingIndex = 0;
            foreach ($this->children as $ii => $c) {
                if ($c->getId() == $siblingId) {
                    $siblingIndex = $ii;
                    break;
                }
            }
        }

        $child->setParent($this);
        if (null == $siblingId || null == $mode || null === $siblingId || 0 == count($this->children)) {
            $this->children[] = $child;
        } else {
            switch ($mode) {
            case self::INSERT_BEFORE:
                array_splice($this->children, $siblingIndex, 1, array($child, $this->children[$siblingIndex]));
                break;
            case self::INSERT_AFTER:
                array_splice($this->children, $siblingIndex, 1, array($this->children[$siblingIndex], $child));
                break;
            default:
                $this->children[] = $child;
            }
        }
    }

    /**
     * Get children.
     *
     * @return array List of <code>Node</code> instances.
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * Get node for id.
     *
     * <p>Find and return the node for the given id.</p>
     *
     * @param string id The id of the node.
     * @return Node The node for the given id or <code>null</code>.
     */
    public function getElementForId($id) {
        // try all children first
        foreach ($this->children as $child) {
            if ($child->getId() == $id) {
                return $child;
            }
        }

        // try deeper decendants
        foreach ($this->children as $child) {
            if (null != ($node = $child->getElementForId($id))) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Check if this node has children.
     *
     * @return boolean <code>true</code> if, and only if, this node has children.
     */
    public function hasChildren() {
        return 0 < count($this->children);
    }

    /**
     * Get the *path* to this node.
     *
     * @param boolean includeSelf Optional flag to include this nodes id as well; default is <code>true</code>.
     * @return array List of node ids leading to this node.
     */
    public function getPath($includeSelf=true) {
        $path = array();

        $current = $includeSelf ? $this : $this->parent;
        while (null != $current && null != $current->getId()) {
            $path[] = $current->getId();
            $current = $current->getParent();
        }

        return array_reverse($path);
    }

}
