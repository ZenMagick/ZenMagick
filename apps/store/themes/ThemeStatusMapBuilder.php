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
namespace zenmagick\apps\store\themes;

use DirectoryIterator;
use Symfony\Component\Yaml\Yaml;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Builder for cacheable theme status map.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ThemeStatusMapBuilder extends ZMObject {
    const DEFAULT_THEME_CLASS = 'zenmagick\apps\store\themes\Theme';
    protected $basePath;
    protected $themeService;
    protected $defaultThemeClass;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->basePath = array(
            Runtime::getInstallationPath().'/themes'
        );
        $this->defaultThemeClass = self::DEFAULT_THEME_CLASS;
    }


    /**
     * Set the theme service.
     *
     * @param ThemeService themeService The theme service instance.
     */
    public function setThemeService($themeService) {
        $this->themeService = $themeService;
    }

    /**
     * Set the default theme class.
     *
     * @param string class The class name.
     */
    public function setDefaultThemeClass($class) {
        $this->defaultThemeClass = $class;
    }

    /**
     * Set the theme base path.
     *
     * @param array basePath List of base paths to look for themes.
     */
    public function setBasePath($path) {
        $this->basePath = $path;
    }

    /**
     * Get the base path.
     *
     * @return array The path array.
     */
    public function getBasePath() {
        return $this->basePath;
    }

    /**
     * Build a complete status map.
     *
     * @return array Theme status map.
     */
    public function buildStatusMap() {
        $themeList = $this->buildThemeList();
        $themeChains = $this->buildThemeChains($themeList);
        return array(
            'themeList' => $themeList,
            'themeChains' => $themeChains
        );
    }

    /**
     * Collect a list of all theme folders.
     *
     * @return array List of folders that contain valid themes.
     */
    protected function getPathIdMap() {
        $folder = array();
        foreach ($this->basePath as $basePath) {
            foreach (new DirectoryIterator($basePath) as $fileInfo) {
                if ($fileInfo->isDir() && !$fileInfo->isDot()) {
                    $path = $fileInfo->getPathname();
                    $configFile = sprintf('%s/theme.yaml', $path);
                    if (file_exists($configFile)) {
                        $id = $fileInfo->getFilename();

                        // figure out available locales
                        $locales = array();
                        $localeBaseDir = sprintf('%s/locale', $path);
                        if (file_exists($localeBaseDir) && is_dir($localeBaseDir)) {
                            foreach (new DirectoryIterator($localeBaseDir) as $localeInfo) {
                                if ($localeInfo->isDir() && !$localeInfo->isDot()) {
                                    $locales[$localeInfo->getFilename()] = $localeInfo->getPathname();
                                }
                            }
                        }

                        $folder[$id] = array(
                            'path' => $path,
                            'id' => $id,
                            'class' => $this->defaultThemeClass,
                            'config' => array(),
                            'configFile' => $configFile,
                            'locales' => $locales,
                        );
                    }
                }
            }
        }
        return $folder;
    }

    /**
     * Generate a full map of themes and their base path.
     *
     * @return array List with theme details.
     */
    protected function buildThemeList() {
        $themeList = array();
        foreach ($this->getPathIdMap() as $basePath => $themeInfo) {
            if (array_key_exists('configFile', $themeInfo)) {
                $themeInfo['config'] = Yaml::parse($themeInfo['configFile']);
            }
            $themeList[$themeInfo['id']] = $themeInfo;
        }

        return $themeList;
    }

    /**
     * Get current theme chains.
     *
     * @param array themeList List of themes.
     * @return array List of theme chains in increasing order of importance.
     */
    public function buildThemeChains($themeList) {
        $baseThemeId = $this->container->get('settingsService')->get('apps.store.themes.default');
        $themeChains = array();
        foreach ($this->themeService->getThemeConfigList() as $themeConfig) {
            $themeChain = array($baseThemeId);
            $themeId = $themeConfig->getThemeId();
            if (array_key_exists($themeId, $themeList)) {
                $themeChain[] = $themeId;
            }
            $themeId = $themeConfig->getVariationId();
            if (array_key_exists($themeId, $themeList)) {
                $themeChain[] = $themeId;
            }
            $themeChains[$themeConfig->getLanguageId()] = $themeChain;
        }

        // ensure we always have a chain for 0
        if (!array_key_exists(0, $themeChains)) {
            $themeChains[0] = array($baseThemeId);
        }
        return $themeChains;
    }

}
