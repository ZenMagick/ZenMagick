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

use zenmagick\apps\store\themes\Theme;

/**
 * Theme class with zencart support.
 *
 * @author DerManoMann
 */
class ZencartTheme extends Theme {
    private $zencart = false;

    /**
     * {@inheritDoc}
     */
    public function setThemeId($themeId) {
        parent::setThemeId($themeId);
        $configFile = $this->getBaseDir().'/theme.yaml';
        if (!file_exists($configFile)) {
            //XXX: try for zc theme
            $zcPath = $this->container->get('settingsService')->get('apps.store.zencart.path');
            $templatePath = $zcPath.'/includes/templates/'.$themeId;
            if (is_dir($templatePath) && file_exists($templatePath.'/template_info.php')) {
                $this->zencart = true;
                include $templatePath.'/template_info.php';
                if (isset($template_name)) {
                    $config = array();
                    $config['meta'] = array();
                    $config['meta']['name'] = $template_name.' (Zen Cart)';
                    $config['meta']['version'] = $template_version;
                    $config['meta']['author'] = $template_author;
                    $config['meta']['description'] = $template_description;
                    $config['meta']['zencart'] = true;
                    $this->setConfig($config);
                }
            }
        }
    }

    /**
     * Check if this is a zencart theme/template.
     *
     * @return boolean <code>true</code> if this theme is a zencart wrapper.
     */
    public function isZencart() {
        return $this->zencart;
    }

}
