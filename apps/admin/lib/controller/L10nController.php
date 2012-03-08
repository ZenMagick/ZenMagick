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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;

use Symfony\Component\Yaml\Yaml;
use zenmagick\base\locales\LocaleScanner;


/**
 * Admin controller for l10n page.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class L10nController extends \ZMController {

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
                $value = Toolbox::asBoolean($value);
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

        $scanner = Beans::getBean('zenmagick\base\locales\LocaleScanner');
        $themeService = $this->container->get('themeService');

        $defaultMap = array();
        if ($vd['includeDefaults']) {
            $themesDir = $themeService->getThemesDir();
            $defaultMap = $scanner->buildL10nMap($themesDir.Runtime::getSettings()->get('apps.store.themes.default'));
        }

        $existingMap = array();
        if ($vd['mergeExisting']) {
            $theme = $themeService->getThemeForId($vd['themeId']);
            $themeMap = $scanner->buildL10nMap($theme->getBaseDir());
            $existingMap = $themeMap;
        }

        $sharedMap = array();
        if ($vd['scanShared']) {
            $sharedMap = $scanner->buildL10nMap(Runtime::getInstallationPath().'shared');
        }

        $pluginsMap = array();
        if ($vd['scanPlugins']) {
            foreach (Runtime::getPluginBasePath() as $path) {
                $pluginsMap = array_merge($pluginsMap, $scanner->buildL10nMap($path));
            }
        }

        $adminMap = array();
        if ($vd['scanAdmin']) {
            $adminLibMap = $scanner->buildL10nMap(Runtime::getApplicationPath().'lib');
            $adminTemplatesMap = $scanner->buildL10nMap(Runtime::getApplicationPath().'templates');
            $adminMap = array_merge($adminLibMap, $adminTemplatesMap);
        }

        $mvcMap = array();
        if ($vd['scanMvc']) {
            $mvcMap = $scanner->buildL10nMap(Runtime::getInstallationPath().'lib');
        }

        $fileMap = array();
        if (null != $vd['themeId']) {
            $theme = $themeService->getThemeForId($vd['themeId']);
            $themeMap = $scanner->buildL10nMap($theme->getBaseDir());
            $storeMap = $scanner->buildL10nMap(Runtime::getInstallationPath().'apps/store');
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
        $scanner = new LocaleScanner();
        if ('yaml' == $request->getParameter('download')) {
            header('Content-Type: text/YAML');
            header('Content-Disposition: attachment; filename=locale.yaml;');
            echo $scanner->map2yaml($data['translations']);
            return null;
        } else if ('po' == $request->getParameter('download')) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename=messages.po;');
            echo $scanner->map2po($data['translations']);
            return null;
        } else if ('pot' == $request->getParameter('download')) {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename=messages.pot;');
            echo $scanner->map2po($data['translations'], true);
            return null;
        }

        return $this->findView(null, $data);
    }

}
