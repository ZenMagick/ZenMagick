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


/**
 * The admin menu.
 *
 * <p>This is a singleton with all methods being static.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.admin.menu
 */
class ZMAdminMenu extends ZMObject {
    /** Plugins menu id. */
    const MENU_PLUGINS = 'configuration-plugins';
    /** Menu id for Catalog Manager tabs. */
    const MENU_CATALOG_MANAGER_TAB = 'catalog-manager-tab';
    private static $items_ = array();


    /**
     * Configure a admin menu item.
     *
     * <p>Possible item data keys:</p>
     * <dl>
     *  <dt>requestId</dt>
     *  <dd>The item's request id</dd>
     *  <dt>parentId</dt>
     *  <dd>Id of the parent</dd>
     *  <dt>id</dt>
     *  <dd>The item id - if not set, the requestId value will be taken</dd>
     *  <dt>title</dt>
     *  <dd>The item title</dd>
     *  <dt>other</dt>
     *  <dd>Optional list of other request Ids that should be treated like this item</dd>
     *  <dt>params</dt>
     *  <dd>Optional URL parameter as per usual</dd>
     * </dl>
     * @param array item The item data.
     */
    public static function setItem($item) {
        $defaults = array('requestId' => null, 'parentId' => null, 'other' => array(), 'params' => '');
        $item = array_merge($defaults, $item);
        if (!array_key_exists('id', $item)) {
          $item['id'] = $item['requestId'];
        }
        self::$items_[] = $item;
    }

    /**
     * Get all items.
     *
     * @return array List of item details.
     */
    public static function getAllItems() {
        return self::$items_;
    }

    /**
     * Get all child items for the given id.
     *
     * @param string parentId The parent id.
     * @return array A list of item data.
     */
    public static function getItemsForParent($parentId) {
        $items = array();
        foreach (self::$items_ as $item) {
            if ($item['parentId'] == $parentId) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Get item for the given id.
     *
     * @param string id The id.
     * @return array The item or <code>null</code>.
     */
    public static function getItemForId($id) {
        foreach (self::$items_ as $item) {
            if ($item['id'] == $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Get root item for the given request id.
     *
     * @param string requestId The request id.
     * @return array The root item or <code>null</code>.
     */
    public static function getRootItemForRequestId($requestId) {
        // first find the item for requestId
        $item = null;
        foreach (self::$items_ as $tmp) {
            if ($tmp['requestId'] == $requestId) {
                $item = $tmp;
                break;
            }
            foreach ($tmp['other'] as $oid) {
                if ($oid == $requestId) {
                    $item = $tmp;
                    break;
                }
            }
            if (null != $item) {
                break;
            }
        }

        if (null == $item) {
            return null;
        }

        $parentId = $item['parentId'];
        while (null != $parentId && null != $item) {
            $item = self::getItemForId($parentId);
            $parentId = null != $item ? $item['parentId'] : null;
        }

        return $item;
    }

}
