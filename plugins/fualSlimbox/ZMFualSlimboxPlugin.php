<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Plugin to enable support for fual_slimbox in ZenMagick.
 *
 * @package org.zenmagick.plugins.fualSlimbox
 * @author DerManoMann
 */
class ZMFualSlimboxPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Fual Slimbox', 'Fual Slimbox support for ZenMagick', '${plugin.version}');
        $this->setContext(Plugin::CONTEXT_STOREFRONT);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/slimbox_install.sql")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/slimbox_uninstall.sql")), $this->messages_);
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        ZMEvents::instance()->attach($this);
    }

    /**
     * {@inheritDoc}
     */
    public function onZMFinaliseContents($args) {
        $contents = $args['contents'];
        if (false === strpos($contents, 'lightbox')) {
            // no tagged images
            return null;
        }

        $view = $args['view'];
        if ($view instanceof ZMSavantView) {
            $request = $args['request'];
            $fualSO = new FualSlimboxOptions();
            ob_start();
            // create manually as different folder structure
            echo '<link rel="stylesheet" type="text/css" media="screen,projection" href="'.$view->asUrl($request, 'slimbox/stylesheet_slimbox_ex.css').'" />' . "\n";
            echo '<script type="text/javascript" src="'.$view->asUrl($request, 'slimbox/javascript/mootools-release-1.11.slim.js').'"></script>' . "\n";
?>
            <script type="text/javascript">
              var FualSlimboxOptions = new Class({
                initialize: function() {
                  this.transitionType = new Fx.Transition(<?php $fualSO->fual_get_transition() ?>, <?php $fualSO->fual_get_amplitude(); ?>);
                  this.resizeFps = <?php $fualSO->fual_get_fps(); ?>;
                  this.resizeDuration = <?php $fualSO->fual_get_duration(); ?>;
                  this.resizeTransition = this.transitionType.<?php $fualSO->fual_get_ease(); ?>;
                  this.initialWidth = <?php $fualSO->fual_get_width(); ?>;
                  this.initialHeight = <?php $fualSO->fual_get_height(); ?>;
                  this.animateCaption = <?php $fualSO->fual_get_caption(); ?>;
                  this.defaultIframeWidth = <?php $fualSO->fual_get_iwidth(); ?>;
                  this.defaultIframeHeight = <?php $fualSO->fual_get_iheight(); ?>;
                  this.elHide = <?php $fualSO->fual_get_elhide(); ?>;
                  this.displayVar = <?php $fualSO->fual_get_displayvar(); ?>;
                  this.pageOf = <?php $fualSO->fual_get_pageof(); ?>;
                }
              });
            </script>
<?php
            echo '<script type="text/javascript" src="'.$view->asUrl($request, 'slimbox/javascript/slimbox_ex.compressed.js').'"></script>' . "\n";
            if (FUAL_SLIMBOX_NERVOUS != 0) {
                    echo '<script type="text/javascript">var fualNervous = '.FUAL_SLIMBOX_NERVOUS.';</script>';
                echo '<script type="text/javascript" src="'.$view->asUrl($request, 'slimbox/javascript/fual_slimbox.compressed.js').'"></script>' . "\n";
            }

            $contents = preg_replace('/<\/head>/', ob_get_clean().'</head>', $contents, 1);
            $args['contents'] = $contents;
            return $args;
        } else {
            return null;
        }
    }

}
