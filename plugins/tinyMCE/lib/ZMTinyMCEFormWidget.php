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

use zenmagick\base\Runtime;

/**
 * TinyMCE textarea form widget.
 *
 * @package org.zenmagick.plugins.tinyMCE
 * @author DerManoMann
 */
class ZMTinyMCEFormWidget extends \ZMTextAreaFormWidget {
    private static $ID_LIST = array();
    private $plugin_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->plugin_ = \ZMPlugins::instance()->getPluginForId('tinyMCE');
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
    public function render($request, $view) {
        if (!$this->plugin_) {
            // fallback
            return parent::render($request, $view);
        }

        // add required js
        $resources = $view->getVar('resources');
        $resources->jsFile('tinymce-3.3.8/jscripts/tiny_mce/tiny_mce.js', \ZMViewUtils::HEADER);

        self::$ID_LIST[] = $this->getId();

        // create init script code at the end once we know all the ids
        Runtime::getEventDispatcher()->listen($this);
        return parent::render($request, $view);
    }

    /**
     * Add init code.
     */
    public function onFinaliseContent($event) {
        if (0 < count(self::$ID_LIST)) {
            $idString = implode(',', self::$ID_LIST);
            $jsInit = <<<EOT
<script>
  tinyMCE.init({
    theme : "advanced",
    mode: "exact",
    elements : "$idString",
    plugins : "paste, save",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align:"left",
    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,"
    + "justifyleft,justifycenter,justifyright,justifyfull,formatselect,"
    + "bullist,numlist,outdent,indent",
    theme_advanced_buttons2 : "link,unlink,anchor,image,separator,"
    +"undo,redo,cleanup,separator,sub,sup,charmap",
    theme_advanced_buttons3 : ""
  });
</script>
EOT;
            $content = $event->get('content');
            $content = preg_replace('/<\/body>/', $jsInit . '</body>', $content, 1);
            $event->set('content', $content);
            // clear to create js only once
            self::$ID_LIST = array();
        }
    }

}
