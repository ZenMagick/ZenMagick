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
namespace zenmagick\apps\store\bundles\ZenCartBundle\themes;

use zenmagick\apps\store\themes\Themes;

/**
 * Theme service with with zencart support.
 *
 * @author DerManoMann
 */
class ZencartThemes extends Themes {

    /**
     * {@inheritDoc}
     */
    public function getAvailableThemes() {
        $themes = parent::getAvailableThemes();

        //XXX: try for zc themes
        $themeDirs = $this->getThemeDirList();
        foreach ($this->getZCThemeDirList() as $dir) {
            if (!in_array($dir, $themeDirs)) {
                $theme = $this->container->get('theme');
                $theme->setThemeId($dir);
                $themes[] = $theme;
            }
        }

        return $themes;
    }

    /**
     * Generate a list of all zencart directories.
     *
     * @return array List of all directories.
     */
    protected function getZCThemeDirList() {
        $themes = array();
        $zcPath = $this->container->get('settingsService')->get('apps.store.zencart.path');
        if (false !== ($handle = @opendir($zcPath.'/includes/templates'))) {
            while (false !== ($file = readdir($handle))) {
                if (\ZMLangUtils::startsWith($file, '.')) {
                    continue;
                }
                array_push($themes, $file);
            }
            @closedir($handle);
        }

        return $themes;
    }

}
