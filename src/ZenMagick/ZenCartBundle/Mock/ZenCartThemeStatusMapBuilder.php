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
namespace ZenMagick\ZenCartBundle\Mock;

use ZenMagick\StoreBundle\Themes\ThemeStatusMapBuilder;

/**
 * Theme service with with zencart support.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZenCartThemeStatusMapBuilder extends ThemeStatusMapBuilder
{
    /**
     * Collect a list of all theme folders.
     *
     * @return array List of folders that contain valid themes.
     */
    protected function getPathIdMap()
    {
        $pathIdMap = parent::getPathIdMap();

        foreach ($this->getBasePath() as $basePath) {
            if (!file_exists($basePath) || !is_dir($basePath)) {
                continue;
            }

            foreach (new \DirectoryIterator($basePath) as $templateInfo) {
                if ($templateInfo->isDir() && !$templateInfo->isDot()) {
                    $id = $templateInfo->getFilename();
                    $path = $templateInfo->getPathname();
                    $infoFile = $path.'/template_info.php';
                    if (!array_key_exists($id, $pathIdMap) && file_exists($infoFile)) {
                        include $infoFile;
                        if (isset($template_name)) {
                            $config = array();
                            $config['meta'] = array();
                            $config['meta']['name'] = $template_name.' (Zen Cart)';
                            $config['meta']['version'] = $template_version;
                            $config['meta']['author'] = $template_author;
                            $config['meta']['description'] = $template_description;
                            $config['meta']['zencart'] = true;
                            $pathIdMap[$id] = array(
                                'path' => $path,
                                'id' => $id,
                                'class' => $this->defaultThemeClass,
                                'config' => $config,
                                'locales' => array()
                            );
                        }
                    }
                }
            }
        }

        return $pathIdMap;
    }

}
