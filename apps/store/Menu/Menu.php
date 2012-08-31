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
namespace ZenMagick\apps\store\Menu;

use ZenMagick\Base\ZMObject;


/**
 * A menu.
 *
 * @param author DerManoMann
 */
class Menu extends ZMObject {
    private $root;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->root = new MenuElement();
    }


    /**
     * Get menu element.
     *
     * @param string id The element id.
     * @return MenuElement An element or <code>null</code>.
     */
    public function getElement($id) {
        return $this->root->getNodeForId($id);
    }

    /**
     * Insert element before the id given.
     *
     * @param string id The element id.
     * @parm MenuElement element The element to insert.
     * @return boolean <code>true</code> on success, <code>false</code> otherwise.
     */
    public function insertBefore($id, $element) {
        return $this->insert($id, $element, MenuElement::INSERT_BEFORE);
    }

    /**
     * Insert element after the id given.
     *
     * @param string id The element id.
     * @parm MenuElement element The element to insert.
     * @return boolean <code>true</code> on success, <code>false</code> otherwise.
     */
    public function insertAfter($id, $element) {
        return $this->insert($id, $element, MenuElement::INSERT_AFTER);
    }

    /**
     * Add child to the given element id.
     *
     * @param string id The element id.
     * @parm MenuElement element The element to add.
     * @return boolean <code>true</code> on success, <code>false</code> otherwise.
     */
    public function addChild($id, $element) {
        if (null != ($parent = $this->root->getNodeForId($id))) {
            $parent->addChild($element);
            return true;
        }

        return false;
    }

    /**
     * Insert element before/after the id given.
     *
     * @param string id The element id.
     * @parm MenuElement element The element to insert.
     * @param string mode Controls whether to insert <em>before</em> or <em>after</em> the given id.
     * @return boolean <code>true</code> on success, <code>false</code> otherwise.
     */
    protected function insert($id, $element, $mode) {
        if (null != ($sibling = $this->root->getNodeForId($id))) {
            if (null != ($parent = $sibling->getParent())) {
                $parent->addChild($element, $id, $mode);
                return true;
            }
        }

        return false;
    }

    /**
     * Get root element.
     *
     * <p>The root element is not really part of the menu, but just the container for the root elements.</p>
     *
     * @return MenuElement The root node.
     */
    public function getRoot() {
        return $this->root;
    }

    /**
     * Get the item for the given request id.
     *
     * @param string requestId The request id.
     * @return MenuElement The item or <code>null</code>.
     */
    public function getItemForRequestId($requestId) {
        // find current node
        $nodes = $this->root->findNodes(function ($node) use ($requestId) {
            if ($requestId == $node->getRequestId()) {
                return true;
            }
            if (null !== ($alias = $node->getAlias())) {
                if (in_array($requestId, $alias)) {
                    return true;
                }
            }
            return false;
        });

        if (0 < count($nodes)) {
            return $nodes[0];
        }

        return null;
    }

    /**
     * Get root item for the given request id.
     *
     * @param string requestId The request id.
     * @return MenuElement The root item or <code>null</code>.
     */
    public function getRootItemForRequestId($requestId) {
        if (null != ($node = $this->getItemForRequestId($requestId))) {
            $path = $node->getPath();
            return $this->root->getNodeForId($path[0]);
        }

        return null;
    }

}
