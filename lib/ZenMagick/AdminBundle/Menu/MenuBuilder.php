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

namespace ZenMagick\AdminBundle\Menu;

use ZenMagick\Base\Toolbox;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MenuBuilder
{
    private $factory;
    private $topItem;
    private $container;

    /**
     * @param FactoryInterface   $factory
     * @param ContainerInterface $menu
     */
    public function __construct(FactoryInterface $factory, ContainerInterface $container)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    /**
     * Get all ZM menu data.
     *
     * @todo refactor this back into a loader system
     */
    protected function getMenuData()
    {
        $settingsService = $this->container->get('settingsService');
        $menuFiles = $settingsService->get('apps.store.admin.menus');
        $menuData = array();

        // @todo support relative and absolute paths (and also placeholder paths)
        foreach ($menuFiles as $menuFile) {
            $menuData = Toolbox::arrayMergeRecursive($menuData, Yaml::parse($this->container->getParameter('zenmagick.root_dir').'/'.$menuFile));
        }
        $contextConfigLoader = $this->container->get('contextConfigLoader');
        foreach ($contextConfigLoader->getMenus() as $pluginMenu) {
            $menuData = Toolbox::arrayMergeRecursive($menuData, $pluginMenu);
        }
        $configGroups = $this->container->get('configService')->getConfigGroups();
        foreach ($configGroups as $group) {
            if ($group->isVisible()) {
                $id = strtolower($group->getName());
                $id = str_replace(' ', '-', $id);
                $id = str_replace('/', '-', $id);
                $menuData['children']['configuration']['children']['configuration-legacy']['children'][$id] = array(
                    'label' => $group->getName(),
                    'route' => 'legacy_config',
                    'routeParameters' => array('groupId' => $group->getId())
                );
            }
        }
        return $menuData;
    }

    public function createMainMenu(Request $request)
    {
        $menu = $this->factory->createFromArray($this->getMenuData());

        $current = $this->findCurrentPage($menu);
        if (null !== $current) {
            $array = $current->getBreadCrumbsArray();
            if (!isset($array[1]['item'])) {
                $this->topItem = 'dashboard';
            } else {
                $this->topItem = $array[1]['item']->getName();
            }
        }
        // @todo set this externally
        $menu->setChildrenAttributes(array('id' => 'main-menu'));

        return $menu;
    }

    public function createSubMenu(Request $request)
    {
        // @todo don't create the menu again
        $menu = $this->factory->createFromArray($this->getMenuData());
        $current = $menu->getChild($this->topItem);

        // @todo set this externally.
        $current->setChildrenAttributes(array('id' => 'sub-common'));

        return $current;
    }

    /**
     * Find the current active route
     *
     * @param MenuItem tree of MenuItem
     * @see https://github.com/KnpLabs/KnpMenuBundle/issues/122#issuecomment-11162312
     */
    protected function findCurrentPage($menu)
    {
        $matcher = $this->container->get('knp_menu.matcher');
        $voter = $this->container->get('knp_menu.voter.router');
        $matcher->addVoter($voter);

        $treeIterator = new \RecursiveIteratorIterator(
            new \Knp\Menu\Iterator\RecursiveItemIterator(
                new \ArrayIterator(array($menu))
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $iterator = new \Knp\Menu\Iterator\CurrentItemFilterIterator($treeIterator, $matcher);

        $current = null;
        foreach ($iterator as $item) {
            $item->setCurrent(true);
            $current = $item;
            break;
        }

        return $current;
    }
}
