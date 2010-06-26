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
        $params = array(
          'themeId' => 's', 'languageId' => 's:1',
          'includeDefaults' => 'b', 'mergeExisting' => 'b', 'scanShared' => 'b', 'scanPlugins' => 'b', 'scanAdmin' => 'b', 'scanMvc' => 'b'
        );
        $options = array();
        foreach ($params as $name => $type) {
            $def = null;
            $token = explode(':', $type);
            if (2 == count($token)) {
                $def = $token[1];
            }
            $value = $request->getParameter($name, $def);
            if ('b' == $token[0]) {
                $value = ZMLangUtils::asBoolean($value);
            }
            $options[$name] = $value;
        }

        $downloadParams = http_build_query(array_merge(array('download' => 'full'), $options));
        return array_merge(array('themes' => ZMThemes::instance()->getThemes(), 'downloadParams' => $downloadParams), $options);
    }

    /**
     * Process request and return all relevant data.
     */
    protected function processInternal($request) {
        $vd = $this->getViewData($request);

        $themesDir = Runtime::getThemesDir();

        $defaultMap = array();
        if ($vd['includeDefaults']) {
            $defaultMap = ZMLocaleUtils::buildL10nMap($themesDir.ZMSettings::get('defaultThemeId'));
        }

        $existingMap = array();
        if ($vd['mergeExisting']) {
            $theme = ZMThemes::instance()->getThemeForId($vd['themeId']);
            $language = ZMLanguages::instance()->getLanguageForId($vd['languageId']);
            $l10nPath = ZMFileUtils::mkPath(array($theme->getBaseDir(), 'lang', $language->getDirectory(), 'l10n.yaml'));
            if (file_exists($l10nPath)) {
                $existingMap = array('l10n.yaml' => ZMRuntime::yamlLoad(file_get_contents($l10nPath)));
            }
        }

        $sharedMap = array();
        if ($vd['scanShared']) {
            $sharedMap = ZMLocaleUtils::buildL10nMap(ZMRuntime::getInstallationPath().'shared');
        }

        $pluginsMap = array();
        if ($vd['scanPlugins']) {
            $pluginsMap = ZMLocaleUtils::buildL10nMap(ZMRuntime::getPluginBasePath());
        }

        $adminMap = array();
        if ($vd['scanAdmin']) {
            $adminMap = ZMLocaleUtils::buildL10nMap(ZMRuntime::getApplicationPath().'lib');
        }

        $mvcMap = array();
        if ($vd['scanMvc']) {
            $mvcMap = ZMLocaleUtils::buildL10nMap(ZMRuntime::getInstallationPath().'lib');
        }

        $fileMap = array();
        if (null != $vd['themeId']) {
            $theme = ZMThemes::instance()->getThemeForId($vd['themeId']);
            $fileMap = ZMLocaleUtils::buildL10nMap($theme->getBaseDir());
        }

        $translations = array_merge($pluginsMap, $sharedMap, $defaultMap, $existingMap, $adminMap, $mvcMap, $fileMap);
        if (0 < count($translations)) {
            $vd['translations'] = $translations;
        }
        return $vd;
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $data = $this->processInternal($request);
        if ('full' == $request->getParameter('download')) {
            header('Content-Type: text/YAML');
            header('Content-Disposition: attachment; filename=l10n.yaml;');
            echo ZMLocaleUtils::map2yaml($data['translations']);
            return null;
        }

        return $this->findView(null, $data);
    }

}
