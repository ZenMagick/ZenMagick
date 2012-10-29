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
namespace ZenMagick\StoreBundle\Menu;

use Symfony\Component\Yaml\Yaml;

/**
 * Menu loader.
 *
 * @param author DerManoMann
 */
class MenuLoader
{
    /**
     * Load menu structure from the given file.
     *
     * @param string source The file/yaml to load.
     * @param Menu menu Optional menu to load/update into; default is <code>null</code>.
     * @return Menu The loaded/updated menu.
     */
    public function load($source, $menu=null)
    {
        $menu = null != $menu ? $menu: new Menu();

        if (is_array($source)) {
            $items = $source;
        } else {
            $items = Yaml::parse($source);
        }
        foreach ($items as $id => $item) {
            if (array_key_exists('type', $item) && 'sep' == $item['type']) {
                $element = new MenuSeparator($id);
            } else {
                $element = new MenuElement($id);
            }
            // always initialize alias
            $element->setAlias(array());
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
                } elseif ($after) {
                    $parent->addChild($element, $after, MenuElement::INSERT_AFTER);
                } else {
                    $parent->addChild($element);
                }
            } else {
                if ($before) {
                    $menu->insertBefore($before, $element);
                } elseif ($after) {
                    $menu->insertAfter($after, $element);
                } else {
                    $menu->getRoot()->addChild($element);
                }
            }
        }

        return $menu;
    }

}
