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

use Symfony\Component\Yaml\Yaml;

/**
 * Menu loader.
 *
 * @param author DerManoMann
 * @package apps.store.menu
 */
class MenuLoader {
    public function exportOld() {
        ob_start();
        foreach (\ZMAdminMenu::getAllItems() as $item) {
            $id = null;
            $c = false;
            if (array_key_exists('requestId', $item)) {
                $id = $item['requestId'];
            }
            if (array_key_exists('id', $item)) {
                $id = $item['id'];
            }
            echo str_replace('_', '-', $id).':'." {";
            if (array_key_exists('parentId', $item) && '' != $item['parentId']) {
                echo ' parent: '.$item['parentId'];$c = true;
            }
            if (array_key_exists('requestId', $item) && '' != $item['requestId']) {
                if ($c) {echo ',';} echo ' requestId: '.$item['requestId'];$c = true;
            }
            if ($c) {echo ',';}echo ' name: '.$item['title'];
            if (array_key_exists('other', $item) && 0 < count($item['other'])) {
                if ($c) {echo ',';}echo ' alias: ['.implode(',', $item['other'])."]";$c = true;
            }
            echo " }\n";
        }
        return ob_get_clean();
    }

    /**
     * Load menu structure from the given file.
     *
     * @param string source The file/yaml to load.
     * @param Menu menu Optional menu to load/update into; default is <code>null</code>.
     * @return Menu The loaded/updated menu.
     */
    public function load($source, $menu=null) {
        $menu = null != $menu ? $menu: new Menu();

        $items = Yaml::parse($source);
        foreach ($items as $id => $item) {
            if (array_key_exists('type', $item) && 'sep' == $item['type']) {
                $element = new MenuSeparator($id);
            } else {
                $element = new MenuElement($id);
            }
            $parent = null;
            $before = null;
            $after = null;
            foreach (array_keys($item) as $key) {
                switch ($key) {
                case 'parent':
                    $parent = $item[$key];
                    break;
                case 'before':
                    $before = $item[$key];
                    break;
                case 'after':
                    $after = $item[$key];
                    break;
                  default:
                    $m = 'set'.ucwords($key);
                    $element->$m($item[$key]);
                }
            }
            if ($parent) {
                $parent = $menu->getElement($parent);
                if ($before) {
                    $parent->addChild($element, $before, MenuElement::INSERT_BEFORE);
                } else if ($after) {
                    $parent->addChild($element, $after, MenuElement::INSERT_AFTER);
                } else {
                    $parent->addChild($element);
                }
            } else {
                if ($before) {
                    $menu->insertBefore($before, $element);
                } else if ($after) {
                    $menu->insertAfter($after, $element);
                } else {
                    $menu->getRoot()->addChild($element);
                }
            }
        }
        return $menu;
    }

}
