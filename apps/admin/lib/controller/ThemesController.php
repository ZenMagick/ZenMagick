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
namespace zenmagick\apps\store\admin\controller;

use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Admin controller for themes page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ThemesController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $themeService = $this->container->get('themeService');
        // strip default theme
        $themes = array();
        foreach ($themeService->getAvailableThemes() as $theme) {
            if (Runtime::getSettings()->get('apps.store.themes.default') != $theme->getId()) {
                $themes[] = $theme;
            }
        }

        // all themes
        $themeConfigList = $themeService->getThemeConfigList();

        // strip already mapped languages
        $unmappedLanguages = array();
        $defaultLanguageMapped = false;
        foreach ($this->container->get('languageService')->getLanguages() as $language) {
            $used = false;
            foreach ($themeConfigList as $config) {
                if ($config->getLanguageId() == 0) {
                    $defaultLanguageMapped = true;
                }
                if ($config->getLanguageId() == $language->getId()) {
                    $used = true;
                    break;
                }
            }
            if (!$used) {
                $unmappedLanguages[] = $language;
            }
        }
        // if default not mapped, add
        if (!$defaultLanguageMapped) {
            $unmappedLanguages = array_merge(array(new ZMObject(array('id' => 0, 'name' => _zm('Default (All)')))), $unmappedLanguages);
        }

        return $this->findView(null, array('themes' => $themes, 'themeConfigList' => $themeConfigList, 'unmappedLanguages' => $unmappedLanguages));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $themeService = $this->container->get('themeService');

        // arrays
        $update = array_flip($request->request->get('update', array()));
        $delete = array_flip($request->request->get('delete', array()));
        // single
        $create = $request->request->get('create');

        $action = null;
        if (1 == count($update)) {
            // update, so let's find the language
            $languageId = array_pop($update);
            $action = 'update';
        } else if (1 == count($delete)) {
            // delete, so let's find the language
            $languageId = array_pop($delete);
            $action = 'delete';
        } else if (null != $create) {
            $action = 'create';
        }

        switch ($action) {
        case  'update':
            if (null != ($config = $this->getConfigForLanguageId($languageId))) {
                // arrays
                $themeId = $request->request->get('themeId');
                $variationId = $request->request->get('variationId');

                $config->setThemeId($themeId[$languageId]);
                $config->setVariationId($variationId[$languageId]);
                $themeService->updateThemeConfig($config);
                $this->messageService->success(_zm('Theme mapping updated.'));
            }
            break;
        case  'delete':
            if (null != ($config = $this->getConfigForLanguageId($languageId))) {
                $themeService->deleteThemeConfig($config);
                $this->messageService->success(_zm('Theme mapping deleted.'));
            }
            break;
        case  'create':
            $themeId = $request->request->get('newThemeId');
            $variationId = $request->request->get('newVariationId');
            $languageId = $request->request->get('newLanguageId', 0);
            $config = new ZMObject(array('themeId' => $themeId, 'variationId' => $variationId, 'languageId' => $languageId));
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
    protected function getConfigForLanguageId($languageId) {
        $themeConfig = $this->container->get('themeService')->getThemeConfigList();
        foreach ($themeConfig as $config) {
            if ($config->getLanguageId() == $languageId) {
                return $config;
            }
        }

        return null;
    }

}
