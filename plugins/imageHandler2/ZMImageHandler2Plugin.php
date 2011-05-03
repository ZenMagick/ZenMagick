<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 * Plugin to enable support for ImageHandler2 in ZenMagick.
 *
 * @package org.zenmagick.plugins.imageHandler2
 * @author mano
 */
class ZMImageHandler2Plugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('ImageHandler2', 'ImageHandler2 support for ZenMagick', '0.3.4');
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
        $this->addConfigValue('Disable IH img attributes', 'disableIH2Attributes', false, 'Disable IH2 showtrail/hidetrail mouseover handler and styles on img elements',
            'widget@ZMBooleanFormWidget#name=disableIH2Attributes&default=false&label=Disable&style=checkbox');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        zenmagick\base\Runtime::getEventDispatcher()->listen($this);
    }

    /**
     * Add resources.
     */
    public function onViewStart($event) {
        if (!ZMLangUtils::asBoolean($this->get('disableIH2Attributes'))) {
            if (null != ($viewUtils = $event->get('view')->getViewUtils())) {
                $viewUtils->cssFile('ih2/style_imagehover.css');
                $viewUtils->jsFile('ih2/jscript_imagehover.js', ZMViewUtils::HEADER);
            }
        }
    }

}
