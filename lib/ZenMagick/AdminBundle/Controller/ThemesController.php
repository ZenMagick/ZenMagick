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
namespace ZenMagick\AdminBundle\Controller;

use ZenMagick\Base\ZMObject;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;

/**
 * Admin controller for themes page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ThemesController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function processGet($request)
    {
        $themeService = $this->container->get('themeService');
        $themes = array();
        foreach ($themeService->getAvailableThemes() as $theme) {
                $themes[] = $theme;
        }

        // all themes
        $themeConfigList = $themeService->getThemeConfigList();

        return $this->findView(null, array('themes' => $themes, 'themeConfigList' => $themeConfigList));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        $themeService = $this->container->get('themeService');

        $update = $request->request->get('update');
        $delete = $request->request->get('delete');
        $create = $request->request->get('create');

        $action = null;
        if (null != $update) {
            $action = 'update';
        }
        if (null != $delete) {
            $action = 'delete';
        }
        if (null != $create) {
            $action = 'delete';
        }

        switch ($action) {
        case  'update':
            if (null != ($config = $this->getConfigForLanguageId())) {
                $themeId = $request->request->get('themeId');
                $variationId = $request->request->get('variationId');

                $config->setThemeId($themeId);
                $config->setVariationId($variationId);
                $themeService->updateThemeConfig($config);
                $this->messageService->success(_zm('Theme mapping updated.'));
            }
            break;
        case  'delete':
            if (null != ($config = $this->getConfigForLanguageId())) {
                $themeService->deleteThemeConfig($config);
                $this->messageService->success(_zm('Theme mapping deleted.'));
            }
            break;
        case  'create':
            $themeId = $request->request->get('newThemeId');
            $variationId = $request->request->get('newVariationId');
            $config = new ZMObject(array('themeId' => $themeId, 'variationId' => $variationId));
            $themeService->createThemeConfig($config);
            $this->messageService->success(_zm('Theme mapping created.'));
            break;
        }

        $themeService->refreshStatusMap();

        return $this->findView('success');
    }

    /**
     * Get config for language id.
     *
     * @param int languageId The language id.
     * @return mixed Config or <code>null</code>.
     */
    protected function getConfigForLanguageId($languageId=0)
    {
        $themeConfig = $this->container->get('themeService')->getThemeConfigList();
        foreach ($themeConfig as $config) {
            if (true) {
                return $config;
            }
        }

        return null;
    }

}
