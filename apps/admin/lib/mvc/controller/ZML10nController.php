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

use zenmagick\base\Runtime;

use Symfony\Component\Yaml\Yaml;


/**
 * Admin controller for l10n page.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller
 */
class ZML10nController extends \ZMController {

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
        $sources = array('storefront' => 'Storefront', 'admin' => 'Admin');
        $source = $request->getParameter('source');
        $options = array();
        foreach ($params as $name => $type) {
            $def = null;
            $token = explode(':', $type);
            if (2 == count($token)) {
                $def = $token[1];
            }
            $value = $request->getParameter($name, $def);
            if ('b' == $token[0]) {
                $value = \ZMLangUtils::asBoolean($value);
            }
            $options[$name] = $value;
        }

        $downloadParamsYaml = http_build_query(array_merge(array('download' => 'yaml'), $options));
        $downloadParamsPo = http_build_query(array_merge(array('download' => 'po'), $options));
        $downloadParamsPot = http_build_query(array_merge(array('download' => 'pot'), $options));

        $vd = array_merge(array(
              'themes' => $this->container->get('themeService')->getAvailableThemes(),
              'source' => $source,
              'sources' => $sources,
              'downloadParamsYaml' => $downloadParamsYaml,
              'downloadParamsPo' => $downloadParamsPo,
              'downloadParamsPot' => $downloadParamsPot
            ),
            $options);

        switch ($vd['source']) {
        case 'admin':
            $vd['includeDefaults'] = $vd['mergeExisting'] = false;
            $vd['scanShared'] = $vd['scanPlugins'] = $vd['scanAdmin'] = $vd['scanMvc'] = true;
            $vd['themeId'] = null;
            break;
        case 'storefront':
            $vd['scanAdmin'] = false;
            $vd['includeDefaults'] = $vd['mergeExisting'] = $vd['scanShared'] = $vd['scanPlugins'] = $vd['scanMvc'] = true;
            if (null == $vd['themeId']) {
                $vd['themeId'] = 'default';
            }
            break;
        }

        return $vd;
    }

    /**
     * Process request and return all relevant data.
     */
    protected function processInternal($request) {
        $vd = $this->getViewData($request);

        $defaultMap = array();
        if ($vd['includeDefaults']) {
            $themesDir = \ZMThemes::getThemesDir();
            $defaultMap = \ZMLocaleUtils::buildL10nMap($themesDir.Runtime::getSettings()->get('apps.store.themes.default'));
        }

        $existingMap = array();
        if ($vd['mergeExisting']) {
            $theme = $this->container->get('themeService')->getThemeForId($vd['themeId']);
            $language = $this->container->get('languageService')->getLanguageForId($vd['languageId']);
            $l10nPath = \ZMFileUtils::mkPath(array($theme->getBaseDir(), 'lang', $language->getDirectory(), 'locale.yaml'));
            if (file_exists($l10nPath)) {
                $existingMap = array('locale.yaml' => Yaml::parse($l10nPath));
            }
        }

        $sharedMap = array();
        if ($vd['scanShared']) {
            $sharedMap = \ZMLocaleUtils::buildL10nMap(Runtime::getInstallationPath().'shared');
        }

        $pluginsMap = array();
        if ($vd['scanPlugins']) {
            foreach (Runtime::getPluginBasePath() as $path) {
                $pluginsMap = array_merge($pluginsMap, \ZMLocaleUtils::buildL10nMap($path));
            }
        }

        $adminMap = array();
        if ($vd['scanAdmin']) {
            $adminLibMap = \ZMLocaleUtils::buildL10nMap(Runtime::getApplicationPath().'lib');
            $adminTemplatesMap = \ZMLocaleUtils::buildL10nMap(Runtime::getApplicationPath().'templates');
            $adminMap = array_merge($adminLibMap, $adminTemplatesMap);
        }

        $mvcMap = array();
        if ($vd['scanMvc']) {
            $mvcMap = \ZMLocaleUtils::buildL10nMap(Runtime::getInstallationPath().'lib');
        }

        $fileMap = array();
        if (null != $vd['themeId']) {
            $theme = $this->container->get('themeService')->getThemeForId($vd['themeId']);
            $themeMap = \ZMLocaleUtils::buildL10nMap($theme->getBaseDir());
            $storeMap = \ZMLocaleUtils::buildL10nMap(Runtime::getInstallationPath().'apps/store');
            $fileMap = array_merge($themeMap, $storeMap);
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
        if ('yaml' == $request->getParameter('download')) {
            header('Content-Type: text/YAML');
            header('Content-Disposition: attachment; filename=locale.yaml;');
            echo \ZMLocaleUtils::map2yaml($data['translations']);
            return null;
        } else if ('po' == $request->getParameter('download')) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename=messages.po;');
            echo \ZMLocaleUtils::map2po($data['translations']);
            return null;
        } else if ('pot' == $request->getParameter('download')) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename=messages.pot;');
            echo \ZMLocaleUtils::map2po($data['translations'], true);
            return null;
        }

        return $this->findView(null, $data);
    }

}
