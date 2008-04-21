<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * Plugin for smarty support.
 *
 * @package org.zenmagick.plugins.zm_smarty
 * @author DerManoMann
 * @version $Id$
 */
class zm_smarty extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ZenMagick Smarty', 'Support for Smarty based themes', '${plugin.version}');
        $this->setLoaderSupport('FOLDER');
        $this->setKeys(array('smartyDir'));
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        $this->addConfigValue('Smarty Installation Folder', 'smartyDir', '', 'Path to your Smarty installation');
    }

    /**
     * Init.
     */
    function init() {
        parent::init();

        // do not echo HTML per default
        ZMSettings::set('isEchoHTML', false);

        $smartyDir = $this->get('smartyDir');
        if (empty($smartyDir)) {
            $smartyDir = $this->getPluginDir().'/smarty/';
        }
        define('SMARTY_DIR', $smartyDir.'/libs/');

        ZMSettings::set('templateSuffix', '.tpl');
        ZMSettings::set('isEnableThemeDefaults', false);

        // make sure PageView is loaded as PopupView extends it
        if (!class_exists('PageView')) {
            ZMLoader::make('PageView', 'dummy');
        }
    }


    /**
     * Get a ready-to-use Smarty instance.
     *
     * @return Smarty A <code>Smarty</code> instance.
     */
    function &getSmarty() {
        // use included version
        require_once(SMARTY_DIR.'Smarty.class.php');

        // generate view using Smarty templating
        $smarty = new Smarty();
        $theme = ZMRuntime::getTheme();
        $themeRoot = $theme->getRootDir();

        // main dirs
        $smarty->template_dir = $themeRoot.'content';
        $smarty->compile_dir = $themeRoot.'templates_c';
        $smarty->cache_dir = $themeRoot.'/cache';
        $smarty->config_dir = $themeRoot.'/configs';

        // plugins; add custom ZenMagick plugins
        $smarty->plugins_dir = array(
            'plugins', // the default under SMARTY_DIR
            $this->getPluginDir().'zm_plugins',
            $theme->getRootDir().'plugins'
        );

        // all settings as map
        $smarty->assign('zm_setting', ZMSettings::getAll());

        // use callback for futher settings
        if (function_exists('zms_smarty_config')) {
            $smarty = zms_smarty_config($smarty);
        }

        return $smarty;
    }

}

?>
