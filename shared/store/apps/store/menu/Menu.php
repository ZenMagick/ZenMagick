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
namespace apps\store\menu;

/**
 * A menu.
 *
 * @param author DerManoMann
 * @package apps.store.menu
 */
class Menu {
    private $root;


    /**
     * Create new instance.
     */
    public function __construct() {
        $this->root = new MenuElement();
    }


    /**
     * Get menu element.
     *
     * @param string id The element id.
     * @return MenuElement An element or <code>null</code>.
     */
    public function getElement($id) {
        return $this->root->getElementForId($id);
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
        if (null != ($parent = $this->root->getElementForId($id))) {
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
        if (null != ($sibling = $this->root->getElementForId($id))) {
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

}
