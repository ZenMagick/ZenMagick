<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Admin controller for themes page.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller
 */
class ZMThemesController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        // strip default theme
        $themes = array();
        foreach (ZMThemes::instance()->getAvailableThemes() as $theme) {
            if (ZMSettings::get('apps.store.themes.default') != $theme->getThemeId()) {
                $themes[] = $theme;
            }
        }

        // all themes
        $themeConfig = ZMThemes::instance()->getThemeConfigList();

        // strip already mapped languages
        $unmappedLanguages = array();
        $defaultLanguageMapped = false;
        foreach (ZMLanguages::instance()->getLanguages() as $language) {
            $used = false;
            foreach ($themeConfig as $config) {
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

        return $this->findView(null, array('themes' => $themes, 'themeConfig' => $themeConfig, 'unmappedLanguages' => $unmappedLanguages));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        // arrays
        $update = array_flip($request->getParameter('update', array()));
        $delete = array_flip($request->getParameter('delete', array()));
        // single
        $create = $request->getParameter('create');

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
                $themeId = $request->getParameter('themeId');
                $variationId = $request->getParameter('variationId');

                $config->setThemeId($themeId[$languageId]);
                $config->setVariationId($variationId[$languageId]);
                ZMThemes::instance()->updateThemeConfig($config);
                ZMMessages::instance()->success(_zm('Theme mapping updated.'));
            }
            break;
        case  'delete':
            if (null != ($config = $this->getConfigForLanguageId($languageId))) {
                ZMThemes::instance()->deleteThemeConfig($config);
                ZMMessages::instance()->success(_zm('Theme mapping deleted.'));
            }
            break;
        case  'create':
            $themeId = $request->getParameter('newThemeId');
            $variationId = $request->getParameter('newVariationId');
            $languageId = $request->getParameter('newLanguageId', 0);
            $config = new ZMObject(array('themeId' => $themeId, 'variationId' => $variationId, 'languageId' => $languageId));
            ZMThemes::instance()->createThemeConfig($config);
            ZMMessages::instance()->success(_zm('Theme mapping created.'));
            break;
        }

        return $this->findView('success');
    }

    /**
     * Get config for language id.
     *
     * @param int languageId The language id.
     * @return mixed Config or <code>null</code>.
     */
    protected function getConfigForLanguageId($languageId) {
        $themeConfig = ZMThemes::instance()->getThemeConfigList();
        foreach ($themeConfig as $config) {
            if ($config->getLanguageId() == $languageId) {
                return $config;
            }
        }

        return null;
    }

}
