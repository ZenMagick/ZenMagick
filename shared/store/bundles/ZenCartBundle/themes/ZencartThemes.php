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

use zenmagick\base\Runtime;
use zenmagick\apps\store\themes\Themes;
use zenmagick\base\utils\Executor;

/**
 * Theme service with with zencart support.
 *
 * @author DerManoMann
 */
class ZencartThemes extends Themes {
    private $zencart;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        Runtime::getEventDispatcher()->listen($this);
        $this->zencart = false;
    }


    /**
     * {@inheritDoc}
     */
    public function setThemeChain($languageId, $themeChain) {
        parent::setThemeChain($languageId, $themeChain);
        $this->zencart = false;
        foreach ($themeChain as $theme) {
            if ($theme->isZencart()) {
                $this->zencart = true;
                break;
            }
        }
    }

    /**
     * Theme resolved listener.
     *
     * @param Event event The event.
     */
    public function onThemeResolved($event) {
        $this->zencart = false;
        if (!$event->has('themeChain')) {
            $this->zencart = true;
            return;
        }
        $themeChain = $event->get('themeChain');
        foreach ($themeChain as $theme) {
            if ($theme->isZencart()) {
                $this->zencart = true;
                break;
            }
        }
    }

    /**
     * Event listener.
     *
     * @param Event event The event.
     */
    public function onControllerProcessStart($event) {
        $request = $event->get('request');
        if ($this->zencart && null != ($dispatcher = $request->getDispatcher())) {
            $settingsService = $this->container->get('settingsService');
            $settingsService->set('zenmagick.http.view.defaultLayout', 'zc_storefront_layout.php');

            // TODO: do we need a custom controller here???
            $executor = new Executor(array($this->container->get('zenmagick\apps\store\bundles\ZenCartBundle\controller\ZencartStorefrontController'), 'process'), array($request));
            $dispatcher->setControllerExecutor($executor);
        }
    }

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
                if (0 === strpos($file, '.')) {
                    continue;
                }
                array_push($themes, $file);
            }
            @closedir($handle);
        }

        return $themes;
    }

}
