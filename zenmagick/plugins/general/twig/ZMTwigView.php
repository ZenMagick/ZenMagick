<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * A Twig view.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.twig
 * @version $Id$
 */
class ZMTwigView extends ZMView {
    private $viewDir_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->setViewDir('views');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the views dir.
     *
     * @return string The views folder name, relative to the content folder.
     */
    public function getViewDir() {
        return $this->viewDir_;
    }

    /**
     * Set the views dir.
     *
     * @param string viewDir The views folder name, relative to the content folder.
     */
    public function setViewDir($viewDir) {
        $this->viewDir_ = $viewDir;
    }

    /**
     * {@inheritDoc}
     *
     * <p>To allow theme inheritance, both the default and active theme's content folders are returned.</p>
     */
    public function getTemplatePath($request) {
        $path = array();
        if (ZMSettings::get('isEnableThemeDefaults')) {
            $path[] = ZMThemes::instance()->getThemeForId(ZMSettings::get('defaultThemeId'))->getContentDir();
        }
        $path[] = Runtime::getTheme()->getContentDir();
        return $path;
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($request, $template) {
        // load template...
        try {
            $loader = new Twig_Loader_Filesystem($this->getTemplatePath());
            $twig = new Twig_Environment($loader, array(
              'cache' => ZMSettings::get('zenmagick.core.cache.provider.file.baseDir').'twig'.DIRECTORY_SEPARATOR,
            ));

            $twigTemplate = $twig->loadTemplate($this->getViewDir().DIRECTORY_SEPARATOR.$template);
            return $twigTemplate->render($this->getVars());
        } catch (Exception $e) {
            ZMLogging::instance()->dump($e, 'failed to fetch template: '.$template, ZMLogging::ERROR);
            throw new ZMException('failed to fetch template: '.$template, 0, $e);
        }
    }

}

?>
