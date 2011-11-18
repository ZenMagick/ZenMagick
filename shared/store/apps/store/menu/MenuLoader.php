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
        foreach (ZMAdminMenu::getAllItems() as $item) {
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
    }

    public function load($filename) {
        $menu = new Menu();

        $items = Yaml::parse($filename);
        foreach ($items as $id => $item) {
            $element = new MenuElement($id);
            $parent = null;
            foreach (array_keys($item) as $key) {
                switch ($key) {
                case 'parent':
                    $parent = $item[$key];
                    break;
                  default:
                    $m = 'set'.ucwords($key);
                    $element->$m($item[$key]);
                }
            }
            if ($parent) {
                $menu->getElement($parent)->addChild($element);
            } else {
                $menu->getRoot()->addChild($element);
            }
        }
        $this->dump($menu->getRoot());
    }

    public function dump($elem, $l=1) {
        $indent = '&nbsp;';
        for ($ii=0; $ii < $l; ++$ii) {
            $indent .= '&nbsp;&nbsp;';
        }
        echo $indent.' * '.$elem->getName()."<br>";
        foreach ($elem->getChildren() as $child) {
            $this->dump($child, $l+1);
        }

    }

}
