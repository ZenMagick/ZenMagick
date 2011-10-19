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
 * Xinha textarea form widget.
 *
 * @package org.zenmagick.plugins.xinha
 * @author DerManoMann
 */
class ZMXinhaFormWidget extends \ZMTextAreaFormWidget {
    private static $ID_LIST = array();

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
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
        if (null == $this->container->get('pluginService')->getPluginForId('xinha')) {
            // fallback
            return parent::render($request, $view);
        }

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
            $baseUrl = $this->container->get('pluginService')->getPluginForId('xinha')->pluginURL('xinha-0.96.1/');
            $idList = implode("', '", self::$ID_LIST);
            $jsInit = <<<EOT
<script type="text/javascript">
_editor_url  = "$baseUrl"; _editor_lang = "en";
xinha_editors = null; xinha_init = null; xinha_config = null; xinha_plugins = null;
</script>
<script type="text/javascript" src="${baseUrl}XinhaCore.js"></script>
<script type="text/javascript">
// This contains the names of textareas we will make into Xinha editors
xinha_init = xinha_init ? xinha_init : function() {
  xinha_editors = xinha_editors ? xinha_editors : [ '$idList' ];
  xinha_plugins = xinha_plugins ? xinha_plugins : [ 'CharacterMap', 'ContextMenu', 'ListType', 'Stylist', 'Linker', 'TableOperations' ];
  if(!Xinha.loadPlugins(xinha_plugins, xinha_init)) return;
  xinha_config = new Xinha.Config();
  xinha_config.toolbar =
  [
    ["popupeditor"],
    ["separator","formatblock","fontname","fontsize","bold","italic","underline","strikethrough"],
    ["separator","forecolor","hilitecolor","textindicator"],
    ["separator","subscript","superscript"],
    ["linebreak","separator","justifyleft","justifycenter","justifyright","justifyfull"],
    ["separator","insertorderedlist","insertunorderedlist","outdent","indent"],
    ["separator","inserthorizontalrule","createlink","insertimage","inserttable"],
    ["linebreak","separator","undo","redo","selectall","print"], (Xinha.is_gecko ? [] : ["cut","copy","paste","overwrite","saveas"]),
    ["separator","killword","clearfonts","removeformat","toggleborders","splitblock","lefttoright", "righttoleft"],
    ["separator","htmlmode","showhelp","about"]
  ];
  //xinha_config.pageStyleSheets = [ _editor_url + "examples/full_example.css" ];
  xinha_editors = Xinha.makeEditors(xinha_editors, xinha_config, xinha_plugins);
  Xinha.startEditors(xinha_editors);
}
Xinha._addEvent(window,'load', xinha_init);
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
