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
namespace ZenMagick\apps\admin\controller;

use ZenMagick\Base\Beans;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;

use Symfony\Component\Yaml\Yaml;
use ZenMagick\Base\Locales\LocaleScanner;


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
          'includeDefaults' => 'b', 'mergeExisting' => 'b', 'scanShared' => 'b', 'scanPlugins' => 'b', 'scanAdmin' => 'b'
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

        $downloadParamsPo = http_build_query(array_merge(array('download' => 'po'), $options));
        $downloadParamsPot = http_build_query(array_merge(array('download' => 'pot'), $options));

        $themeService = $this->container->get('themeService');
        $vd = array_merge(array(
              'themes' => $themeService->getAvailableThemes(),
              'source' => $source,
              'sources' => $sources,
              'downloadParamsPo' => $downloadParamsPo,
              'downloadParamsPot' => $downloadParamsPot
            ),
            $options);

        switch ($vd['source']) {
        case 'admin':
            $vd['includeDefaults'] = $vd['mergeExisting'] = false;
            $vd['scanShared'] = $vd['scanPlugins'] = $vd['scanAdmin'] = true;
            $vd['themeId'] = null;
            break;
        case 'storefront':
            $vd['scanAdmin'] = false;
            $vd['includeDefaults'] = $vd['mergeExisting'] = $vd['scanShared'] = $vd['scanPlugins'] = true;
            if (null == $vd['themeId']) {
                $vd['themeId'] = $themeService->getDefaultThemeId();
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

        $scanner = $this->container->get('localeScanner');
        $themeService = $this->container->get('themeService');

        $defaultMap = array();
        $defaultThemeId = $themeService->getDefaultThemeId();
        if ($vd['includeDefaults']) {
            $theme = $themeService->getThemeForId($defaultThemeId);
            $defaultMap = $scanner->buildL10nMap($theme->getBasePath());
        }

        $existingMap = array();
        if ($vd['mergeExisting']) {
            $theme = $themeService->getThemeForId($vd['themeId']);
            $themeMap = $scanner->buildL10nMap($theme->getBasePath());
            $existingMap = $themeMap;
        }

        $kernel = $this->container->get('kernel');
        $sharedMap = array();
        if ($vd['scanShared']) {
            $sharedMap = $scanner->buildL10nMap($kernel->getRootDir().'/lib/shared');
        }

        $pluginsMap = array();
        if ($vd['scanPlugins']) {
            $pluginDirs = $this->container->getParameterBag()->get('zenmagick.base.plugins.dirs');
            foreach ($pluginDirs as $path) {
                $pluginsMap = array_merge($pluginsMap, $scanner->buildL10nMap($path));
            }
        }

        $adminMap = array();
        if ($vd['scanAdmin']) {
            $adminLibMap = $scanner->buildL10nMap($kernel->getApplicationPath());
        }

        $fileMap = array();
        if (null != $vd['themeId']) {
            $theme = $themeService->getThemeForId($vd['themeId']);
            $themeMap = $scanner->buildL10nMap($theme->getBaseDir());
            $storeMap = $scanner->buildL10nMap($kernel->getRootDir().'/apps/store');
            $fileMap = array_merge($themeMap, $storeMap);
        }

        $translations = array_merge($pluginsMap, $sharedMap, $defaultMap, $existingMap, $adminMap, $fileMap);
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
        if ('po' == $request->getParameter('download')) {
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
