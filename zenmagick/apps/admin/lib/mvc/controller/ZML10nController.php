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
 * Admin controller for l10n page.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.controller
 */
class ZML10nController extends ZMController {

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
    public function getViewData($request) {
      //TODO: where from?
      return array('themes' => ZMThemes::instance()->getThemes(),
          'themeId' => '', 'languageId' => 1, 'includeDefaults' => false, 'mergeExisting' => false, 'scanShared' => false, 'scanPlugins' => false, 'scanAdmin' => false, 'scanMvc' => false);
    }

    /**
     * Process request and return all relevant data.
     */
    protected function processInternal($request) {
        //TODO: use
        $themeId = $request->getParameter('themeId');
        $languageId = $request->getParameter('languageId', 1);
        $includeDefaults = ZMLangUtils::asBoolean($request->getParameter('includeDefaults'));
        $mergeExisting = ZMLangUtils::asBoolean($request->getParameter('mergeExisting'));
        $scanShared = ZMLangUtils::asBoolean($request->getParameter('scanShared'));
        $scanPlugins = ZMLangUtils::asBoolean($request->getParameter('scanPlugins'));
        $scanAdmin = ZMLangUtils::asBoolean($request->getParameter('scanAdmin'));
        $scanMvc = ZMLangUtils::asBoolean($request->getParameter('scanMvc'));

        $themesDir = Runtime::getThemesDir();

        $defaultMap = array();
        if ($includeDefaults) {
            $defaultMap = ZMLocaleUtils::buildL10nMap($themesDir.ZMSettings::get('defaultThemeId'));
        }

        $existingMap = array();
        if ($mergeExisting) {
            // TODO: use languageId to resolve path
            $l10nPath = ZMFileUtils::mkPath(array(Runtime::getTheme()->getBaseDir(), 'lang', 'english', 'l10n.yaml'));
            if (file_exists($l10nPath)) {
                $existingMap = array('l10n.yaml' => ZMRuntime::yamlLoad(file_get_contents($l10nPath)));
            }
        }

        $sharedMap = array();
        if ($scanShared) {
            $sharedMap = ZMLocaleUtils::buildL10nMap(ZMRuntime::getInstallationPath().'shared');
        }

        $pluginsMap = array();
        if ($scanPlugins) {
            $pluginsMap = ZMLocaleUtils::buildL10nMap(ZMRuntime::getPluginBasePath());
        }

        $adminMap = array();
        if ($scanAdmin) {
            $adminMap = ZMLocaleUtils::buildL10nMap(ZMRuntime::getApplicationPath().'lib');
        }

        $mvcMap = array();
        if ($scanMvc) {
            $mvcMap = ZMLocaleUtils::buildL10nMap(ZMRuntime::getInstallationPath().'lib');
        }

        $fileMap = array();
        if (null != $themeId) {
            $theme = ZMThemes::instance()->getThemeForId($themeId);
            $fileMap = ZMLocaleUtils::buildL10nMap($theme->getBaseDir());
        }

        $translations = array_merge($pluginsMap, $sharedMap, $defaultMap, $existingMap, $adminMap, $mvcMap, $fileMap);
        return array('translations' => $translations, 'themeId' => $themeId, 'languageId' => $languageId,
          'includeDefaults' => $includeDefaults, 'mergeExisting' => $mergeExisting, 'scanShared' => $scanShared, 'scanPlugins' => $scanPlugins,
          'scanAdmin' => $scanAdmin, 'scanMvc' => $scanMvc);
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $data= $this->processInternal($request);
        if ('full' == $request->getParameter('download')) {
            header('Content-Type: text/YAML');
            header('Content-Disposition: attachment; filename=l10n.yaml;');
            echo ZMLocaleUtils::map2yaml($data['translations']);
            return null;
        }

        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        return $this->findView(null, $this->processInternal($request));
    }

}
