<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Xinha textarea form widget.
 *
 * @package org.zenmagick.plugins.xinha
 * @author DerManoMann
 * @version $Id$
 */
class ZMXinhaFormWidget extends ZMTextAreaFormWidget {
    private $plugin_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->plugin_ = ZMPlugins::instance()->getPluginForId('xinha');
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

        $baseUrl = $this->plugin_->pluginURL('xinha-0.9.5/');

        // add required js
        //$request->getToolbox()->utils->jsTop($baseUrl.'XinhaCore.js');

        $id = $this->getId();
        $height = (1.3 * $this->getRows()).'em';
        $width = (1.1 * $this->getCols()).'em';

        $jsInit = <<<EOT
<script type="text/javascript">
_editor_url  = "$baseUrl"; _editor_lang = "en";
xinha_editors = null; xinha_init = null; xinha_config = null; xinha_plugins = null;
</script>
<script type="text/javascript" src="${baseUrl}XinhaCore.js"></script>
<script type="text/javascript">
// This contains the names of textareas we will make into Xinha editors
xinha_init = xinha_init ? xinha_init : function() {
  xinha_editors = xinha_editors ? xinha_editors : [ '$id' ];
  xinha_plugins = xinha_plugins ? xinha_plugins : [ 'CharacterMap', 'ContextMenu', 'ListType', 'Stylist', 'Linker', 'TableOperations' ];
  if(!Xinha.loadPlugins(xinha_plugins, xinha_init)) return;
  xinha_config = new Xinha.Config();
  xinha_config.width  = '$width';
  xinha_config.height = '$height';
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

        return $jsInit.parent::render($request);
    }

}

?>
