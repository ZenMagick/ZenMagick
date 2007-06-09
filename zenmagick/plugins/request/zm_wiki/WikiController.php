<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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

define ('ZM_FILENAME_WIKI', 'wiki');


/**
 * Request controller for wiki pages.
 *
 * @author mano
 * @package net.radebatz.zenmagick.plugins.zm_wiki
 * @version $Id$
 */
class WikiController extends ZMController {

    /**
     * Default c'tor.
     */
    function WikiController() {
    global $zm_wiki, $pawfaliki_config;

        parent::__construct();

        // include here to get access to the plugin dir...
        include($zm_wiki->getPluginDir()."/pawfaliki.php");

        $pawfaliki_config['GENERAL']['TITLE'] = zm_setting('storeName');
        $pawfaliki_config['GENERAL']['HOMEPAGE'] = zm_l10n_get("WikiRoot");
        $pawfaliki_config['LOCALE']['HOMEPAGE_LINK'] = "[[WikiRoot]]"; // link to the homepage
        $pawfaliki_config['GENERAL']['ADMIN'] = zm_setting('storeEmail');
        $pawfaliki_config['GENERAL']['CSS'] = '';
        $pawfaliki_config['GENERAL']['PAGES_DIRECTORY'] = DIR_FS_CATALOG."wiki/files/";
        $pawfaliki_config['GENERAL']['TEMP_DIRECTORY'] = DIR_FS_CATALOG."wiki/tmp/";

        // SYNTAX: Wiki editing syntax
        $pawfaliki_config['SYNTAX']['WIKIWORDS'] = false; // Auto-generation of links from WikiWords
        $pawfaliki_config['SYNTAX']['AUTOCREATE'] = true; // Display ? next to wiki pages that don't exist yet.
        $pawfaliki_config['SYNTAX']['HTMLCODE'] = true; // Allows raw html using %% tags

        // BACKUP: Backup & Restore settings
        $pawfaliki_config['BACKUP']['ENABLE'] = false; // Enable backup & restore

        // RSS: RSS feed
        $pawfaliki_config['RSS']['ENABLE'] = false; // Enable rss support (http://mywiki.example?format=rss)

        // CHANGES: email page changes
        $pawfaliki_config['EMAIL']['ENABLE'] = false; // do we email page changes?

        // LICENSES: pages with special licenses
        $pawfaliki_config['LICENSE']['DEFAULT'] = "noLicense";
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->WikiController();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Process a HTTP GET request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processGet() {
    global $zm_request, $zm_crumbtrail, $zm_wiki;

        $zm_crumbtrail->clear();
        $zm_crumbtrail->addCrumb("Wiki", zm_href(ZM_FILENAME_WIKI, '', false));
        $page = $zm_request->getParameter('page');
        if (null != $page) {
            $zm_crumbtrail->addCrumb(zm_format_title($page));
        }

        return $this->create("PluginView", zm_view_wiki, $zm_wiki);
    }


    /**
     * Process a HTTP POST request.
     * 
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    function processPost() {
    global $zm_request, $zm_crumbtrail;

        $zm_crumbtrail->clear();
        $zm_crumbtrail->addCrumb("Wiki", zm_href(ZM_FILENAME_WIKI, '', false));
        $page = $zm_request->getParameter('page');
        if (null != $page) {
            $zm_crumbtrail->addCrumb(zm_format_title($page));
        }

        return $this->create("PluginView", zm_view_wiki_edit, $zm_wiki);
    }

}

/**
 * View generator for 'wiki' view.
 *
 * @package net.radebatz.zenmagick.plugins.zm_wiki
 */
function zm_view_wiki() {
global $zm_request;

    $mode = 'display';

    // get the page title
    $title = getTitle();

    // page contents
    displayPage($title, $mode);

    $canEdit = false;
    switch(zm_setting('plugin.zm_wiki.restriction')) {
    case 'ALL':
        $canEdit = true;
        break;
    case 'REGISTERED':
        if (!$zm_request->isGuest()) {
            $canEdit = true;
        }
        break;
    case 'ADMIN':
        if ($zm_request->isAdmin()) {
            $canEdit = true;
        }
        break;
    }
    if ($canEdit) {
        // page controls
        displayControls($title, $mode);
    }
}

/**
 * View generator for 'wiki_edit' view.
 *
 * @package net.radebatz.zenmagick.plugins.zm_wiki
 */
function zm_view_wiki_edit() {
global $pawfaliki_config;

    $mode = getMode();
    if (!zm_is_in_array($mode, 'edit,save,cancel')) {
        $mode = '';
    }

    // get the page title
    $title = getTitle();

    // get the page contents
    $contents = updateWiki($mode, $title, $pawfaliki_config);

    // page contents
    displayPage($title, $mode, $contents);

    // page controls
    displayControls($title, $mode);
}


/*+++++++++++++++++++++++ Pawfaliki extensions +++++++++++++++++++++*/
// changes to pawfaliki.php
// add line: $PAWFALIKI_FUNCTIONS_ONLY = true;
// 1,$s/$config/$pawfaliki_config/g
// 1,$s/page=/main_page=wiki&amp;page=/g
// 1,$s/HomePage/WikiRoot/g
// wikiparse: add line:   $contents = zm_wiki_parse($contents);
// printWikiSyntax: add line:  zm_wiki_syntax();
// change <span class=\"wiki_body\" to div


/**
 * Wiki parser extension for nested lists.
 *
 * @package net.radebatz.zenmagick.plugins.zm_wiki
 * @param string text The wiki text.
 * @return string The converted HTML.
 */
function zm_wiki_lists($text) {
		//create <######>..</######> pseudo tags for string replacement, level 3 down to 1
		$text = preg_replace( '/\n([\*#;]{3,3})(.*?)\n(?![#\*;]{3,3})/si', "\n<\\1\\1l>\\1\\2</\\1\\1l>\n", $text );
		$text = preg_replace( '/\n([\*#;]{2,2})(?![\*#];)(.*?)\n(?!([#\*;]{2,3}|<[#\*;]{6,6}l>))/si', "\n<\\1\\1\\1l>\\1\\2</\\1\\1\\1l>\n", $text );
		$text = preg_replace( '/\n([\*#;])(?![\*#;])(.*?)\n(?!([#\*;]{1,3}|<[#\*;]{6,6}l>))/si', "\n<\\1\\1\\1\\1\\1\\1l>\\1\\2</\\1\\1\\1\\1\\1\\1l>\n", $text );
		//convert pseudo tags into HTML list tags
		$text = str_replace( array('######l>', '******l>', ';;;;;;l>'), array('ol>', 'ul>', 'dl>'), $text );
		//create **valid**list item tags <li> and <dt>,<dd>
		$text = preg_replace( '/(\n<[uo]l>|\n)[\*#]{1,3}\s*([^\n<]*)/si', '\1<li>\2</li>', $text );
		$text = preg_replace( '/(\n<dl>|\n)[;]{1,3}\s*(\[{0,2})(.+?)(\]{0,2})\s*:\s*([^\n<]*)/si', '\1<dt><a name="\3">\2\3\4</a></dt><dd>\5</dd>', $text );
    // get rid of line feeds to avoid line br tags later on
		$text = preg_replace( '/<\/(li|ul|ol)>\n/si', '</\1>', $text );
		$text = preg_replace( '/\r?\n?<\/li/si', '</li', $text );
    return $text;
}

/**
 * Wiki parser extension.
 *
 * @package net.radebatz.zenmagick.plugins.zm_wiki
 * @param string text The wiki text.
 * @return string The converted HTML.
 */
function zm_wiki_parse($contents) {
	  $contents = zm_wiki_lists($contents);

    $patterns = array();
    $replacements = array();

    // more italic
    $patterns[] = "/\/\/([^\/]*[^\/]*)\/\//";
    $replacements[] = "<i>$1</i>";

    // headings
    $patterns[] = "/===([^=]*[^=]*)===/";
    $replacements[] = "<h3>$1</h3>";
    $patterns[] = "/==([^=]*[^=]*)==/";
    $replacements[] = "<h2>$1</h2>";
    $patterns[] = "/=([^=]*[^=]*)=/";
    $replacements[] = "<h1>$1</h1>";

    // substitute simple expressions & final expansion
    $contents = wikiEval( preg_replace( $patterns, $replacements, $contents ) );

    return $contents;
}

/**
 * Wiki parser extension.
 *
 * @package net.radebatz.zenmagick.plugins.zm_wiki
 * @param string text The wiki text.
 * @return string The converted HTML.
 */
function zm_wiki_syntax() {
global $pawfaliki_config;

    echo("\t<div class=\"wikisyntax\">\n");
    echo("\t<table>\n");
    echo("\t\t<tr>\n");
    echo("\t\t\t<td align=\"right\">");
    echo( "heading1 text: <BR>" );
    echo( "heading2 text: <BR>" );
    echo( "heading3 text: <BR>" );
    echo( "lists (unordered, ordered, definition list): <BR>" );
    echo( "indenting: <BR>" );
    echo( "italic text: <BR>" );
    echo("\t\t\t</td>\n");
    echo("\t\t\t<td>");
    echo( "=abc=<BR>" );
    echo( "==abc==<BR>" );
    echo( "===abc===<BR>" );
    echo( "* list or # numbered or ; term : definition<BR>" );
    echo( "> abc<BR>" );
    echo( "//abc//<BR>" );
    echo("\t\t\t</td>\n");
    echo("\t\t</tr>\n");
    echo("\t</table>\n");
    echo("\t</div>\n");
}

?>
