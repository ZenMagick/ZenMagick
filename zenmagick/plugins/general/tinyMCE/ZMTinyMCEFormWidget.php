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
 * TinyMCE textarea form widget.
 *
 * @package org.zenmagick.plugins.tinyMCE
 * @author DerManoMann
 */
class ZMTinyMCEFormWidget extends ZMTextAreaFormWidget {
    private $plugin_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->plugin_ = ZMPlugins::instance()->getPluginForId('tinyMCE');
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
    public function render($request) {
        if (!$this->plugin_) {
            // fallback
            return parent::render($request);
        }

        $baseUrl = $this->plugin_->pluginURL('tinymce-3.3.8/');

        // add required js
        $request->getToolbox()->utils->jsTop($baseUrl.'jscripts/tiny_mce/tiny_mce.js');

        $id = $this->getId();
        $height = (1.3 * $this->getRows()).'em';
        $width = (1.1 * $this->getCols()).'em';

        $jsInit = <<<EOT
<script type="text/javascript">
  tinyMCE.init({
    theme : "advanced",
    mode: "exact",
    elements : "$id",
    plugins : "paste, save",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align:"left",
    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,"
    + "justifyleft,justifycenter,justifyright,justifyfull,formatselect,"
    + "bullist,numlist,outdent,indent",
    theme_advanced_buttons2 : "link,unlink,anchor,image,separator,"
    +"undo,redo,cleanup,separator,sub,sup,charmap",
    theme_advanced_buttons3 : "",
    height:"$height",
    width:"$width"
  });
</script>
EOT;

        return $jsInit.parent::render($request);
    }

}
