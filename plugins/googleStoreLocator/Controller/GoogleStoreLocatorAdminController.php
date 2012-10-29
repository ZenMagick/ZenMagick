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
namespace ZenMagick\plugins\googleStoreLocator\Controller;

use ZenMagick\AdminBundle\Controller\PluginAdminController;

/**
 * Admin controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class GoogleStoreLocatorAdminController extends PluginAdminController
{
    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct('googleStoreLocator');
    }

    /**
     * {@inheritDoc}
     */
    public function getViewData($request)
    {
        return array(
            'adminKey' => $this->getPlugin()->get('adminKey'),
            'location' => $this->getPlugin()->get('location'),
            'zoom' => $this->getPlugin()->get('zoom')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        $plugin = $this->getPlugin();

        foreach ($plugin->getConfigValues() as $configValue) {
            if (null != ($value = $request->request->get($configValue->getName()))) {
                $plugin->set($configValue->getName(), $value);
            }
        }

        $this->messageService->success('Plugin settings updated!');

        return $this->findView('success');
    }

}
