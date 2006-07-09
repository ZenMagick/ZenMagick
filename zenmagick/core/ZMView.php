<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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
 * A view.
 *
 * @author mano
 * @package net.radebatz.zenmagick
 * @version $Id$
 */
class ZMView {
    var $name_;
    var $template_;
    var $content_;
    var $isRedirect_;

    // create new instance
    function ZMView($name, $content, $template=null) {
    global $zm_theme;
        $this->name_ = $name;
        $this->content_ = $content;
        $themeInfo = $zm_theme->getThemeInfo();
        $this->template_ = null != $template ? $template : $themeInfo->getTemplateFor($content);
        $this->isRedirect_ = false;
    }

    // create new instance
    function __construct($name, $content, $template=null) {
        $this->ZMView($name, $content, $template);
    }

    function __destruct() {
    }


    /** view API */

    // the view name
    function getName() { return $this->name_; }

    // the 'main' template name to be used
    function getTemplateName() { return $this->template_; }

    // the content template name; different if view is using tiles
    function getContentName() { return $this->content_; }

    // returns true, if the view is using tiles (i.e. a shared main template)
    // if true, content name and template name are expected to be different
    function isUsingTiles() { return null != $this->template_ && $this->template_ != $this->content_; }

    // returns true, if this view is a redirect view
    function isRedirectView() { return $this->isRedirect_; }
}

?>
