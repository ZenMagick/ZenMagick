<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * A Savant(3) view with theme support.
 *
 * <p>Also, this view allows to distinguish between layout and view.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.view
 * @version $Id$
 */
class SavantView extends ZMSavantView {
    private $viewDir_;
    private $layout_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->setLayout(false);
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
     * Set the layout name.
     *
     * @param string layout The layout name.
     */
    public function setLayout($layout) {
        $this->layout_ = $layout;
    }

    /**
     * Return the layout name.
     *
     * @return string The layout name or <code>null</code>.
     */
    public function getLayout() {
        if (null === $this->layout_) {
            return $this->layout_;
        }

        // default
        return Runtime::getTheme()->getLayoutFor($this->getTemplate());
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
    public function generate($request) {
        $savant = $this->getSavant($request);

        // put all vars into local scope
        $savant->assign($this->getVars());

        // load template...
        $template = null;
        try {
            // TODO: kill! common view variables
            $zm_theme = Runtime::getTheme();
            $savant->assign(array('zm_theme' => $zm_theme));
            $savant->assign(array('view' => $this));
            if (null != ($layout = $this->getLayout())) {
                $template = $layout;
                $view = $this->getViewDir().DIRECTORY_SEPARATOR.$this->getTemplate().ZMSettings::get('zenmagick.mvc.templates.ext', '.php');
                $savant->assign(array('viewTemplate' => $view));
            } else {
                $template = $this->getViewDir().DIRECTORY_SEPARATOR.$this->getTemplate();
            }
            return $savant->fetch($template.ZMSettings::get('zenmagick.mvc.templates.ext', '.php'));
        } catch (Exception $e) {
            ZMLogging::instance()->dump($e, 'failed to fetch template: '.$template, ZMLogging::ERROR);
            throw new ZMException('failed to fetch template: '.$template, 0, $e);
        }
    }

}

?>
