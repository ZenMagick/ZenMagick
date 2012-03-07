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
?>
<?php
namespace zenmagick\apps\store\admin\controller;

use zenmagick\base\Beans;

/**
 * Admin controller for a single block group.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class BlockGroupAdminController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        // need themes initialized
        $language = $request->getSession()->getLanguage();
        $this->container->get('themeService')->initThemes($language);

        $blocks = array();
        $blockManager = $this->container->get('blockManager');
        foreach ($blockManager->getProviders() as $provider) {
            foreach ($provider->getBlockList() as $def) {
                $widget = Beans::getBean($def);
                $blocks[$def] = $widget->getTitle();
            }
        }
        $groupName = $request->getParameter('groupName');
        return array(
            'allBlocks' => $blocks,
            'blocks' => $blockManager->getBlocksForId($request, $groupName),
            'groupName' => $groupName
        );
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        return $this->findView();
    }

}
