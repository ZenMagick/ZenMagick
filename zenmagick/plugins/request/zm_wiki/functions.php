<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 *
 * @version $Id$
 */
?>
<?php

    /**
     * Display the contents of a wiki page.
     *
     * @package org.zenmagick.plugins.zm_wiki
     * @param string page The page name.
     */
    function zm_wiki_display_page($page) {
        $display = 'display';
        displayPage($page, $display);
    }


    /**
     * Wiki admin page.
     *
     * @package org.zenmagick.plugins.zm_wiki
     * @return ZMPluginPage A plugin page or <code>null</code>.
     */
    function zm_wiki_admin() {
        // create contents into output buffer
        ob_start();
        if (ZMRequest::isAdmin()) {
            echo '<a href="'.zm_plugin_admin_url('zm_wiki_admin', 'page=WikiRoot', false).'">WikiRoot</a>';
            echo '&nbsp;<a href="'.zm_plugin_admin_url('zm_wiki_admin', 'page=PageList', false).'">PageList</a>';
            echo '<hr>';
        }
        // use controller to allow us to use custom config settings...
        $controller = ZMLoader::make("WikiController");
        $view = $controller->process();
        // we know it's a function...
        $view->callView();

        // grab contents and clean buffer
        $contents = ob_get_clean();

        return new ZMPluginPage('wiki_admin', zm_l10n_get('Manage Wiki'), $contents);
    }


    /**
     * Page caching strategy that excludes wiki pages.
     *
     * @package org.zenmagick.plugins.zm_wiki
     */
    function zm_wiki_is_page_cacheable() {
        $lastPageCacheStrategy = ZMSettings::get('plugins.zm_wiki.last-page-caching-strategy');
        return 'wiki' == ZMRequest::getPageName() ? false : $lastPageCacheStrategy();
    }


    /**
     * View generator for 'wiki' view.
     *
     * @package org.zenmagick.plugins.zm_wiki
     */
    function zm_view_wiki() {
        $mode = getMode();
        if (!ZMRequest::isAdmin()) {
            $mode = 'display';
        }

        // get the page title
        $title = getTitle();
        if ($mode=="backup") {
            $title = "BackupWiki";
        } else if ($mode=="restore") {
            $title = "RestoreWiki";
        }

        // page contents
        displayPage($title, $mode);

        $canEdit = false;
        switch(ZMSettings::get('plugins.zm_wiki.access.modify')) {
        case 'ALL':
            $canEdit = true;
            break;
        case 'REGISTERED':
            if (ZMRequest::isRegistered()) {
                $canEdit = true;
            }
            break;
        case 'ADMIN':
            if (ZMRequest::isAdmin()) {
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
     * @package org.zenmagick.plugins.zm_wiki
     */
    function zm_view_wiki_edit() {
    global $pawfaliki_config;

        $mode = getMode();
        if (!ZMRequest::isAdmin() && !ZMTools::inArray($mode, 'edit,save,cancel')) {
            $mode = '';
        }

        // get the page title
        $title = getTitle();
        if ($mode=="backup") {
            $title = "BackupWiki";
        } else if ($mode=="restore") {
            $title = "RestoreWiki";
        }

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
    // /zm_plugin_url('wiki;zm_wiki_admin', 'page='.$title, false)/ and two times with $src instead $title...
    // 1,$s/HomePage/WikiRoot/g
    // wikiparse: add line:   $contents = zm_wiki_parse($contents);
    // printWikiSyntax: add line:  zm_wiki_syntax();
    // change <span class=\"wiki_body\" to div
    // rename error( paw_error(
    // plus a few I forgot...


    /**
     * Wiki parser extension for nested lists.
     *
     * @package org.zenmagick.plugins.zm_wiki
     * @param string text The wiki text.
     * @param int maxLevel The maximum level of nesting supported; default is <code>2</code>.
     * @return string The converted HTML.
     */
    function zm_wiki_lists($text, $maxLevel=2) {
        while ($text != ($next = preg_replace('/(\n?[\*#]{1,2}\s+.*)\n>\s+(.*)/', '\1<br>\2', $text))) { $text = $next; }
        // when called after converting HTML chars like '>'...
        while ($text != ($next = preg_replace('/(\n?[\*#]{1,2}\s+.*)\n&gt;\s+(.*)/', '\1<br>\2', $text))) { $text = $next; }

        for ($ii=$maxLevel; $ii > 0; --$ii) {
            $text = preg_replace('/\n?([\*#]{'.$ii.'})\s+(.*)/', '<li\1>\2</li\1>', $text);
            $text = preg_replace('/(<li\*{1,'.$ii.'}>.*<\/li\*{1,'.$ii.'}>)\n?/', '<ul>\1</ul>', $text);
            $text = preg_replace('/(<li\#{1,'.$ii.'}>.*<\/li\#{1,'.$ii.'}>)\n?/', '<ol>\1</ol>', $text);
        }

        for ($ii=$maxLevel; $ii > 0; --$ii) {
            $text = preg_replace('/<li[\*#]{'.$ii.'}>(.*)<\/li[\*#]{'.$ii.'}>/U', '<li>\1</li>', $text);
        }

        return $text;
    }

    /**
     * Wiki parser extension.
     *
     * @package org.zenmagick.plugins.zm_wiki
     * @param string text The wiki text.
     * @return string The converted HTML.
     */
    function zm_wiki_parse($contents) {
        $contents = preg_replace('/\r/', "\n", $contents);
        $contents = preg_replace('/\n\n/', "\n", $contents);

        $patterns = array();
        $replacements = array();

        // more italic
        $patterns[] = "/\/\/([^\/]*)\/\//U";
        $replacements[] = "<i>$1</i>";

        // line breaks
        $patterns[] = "/%%%/";
        $replacements[] = "<br/>";

        // headings
        $patterns[] = '/^===(.*)===$\n?/m';
        $replacements[] = '<h3>$1</h3>';
        $patterns[] = '/^==(.*)==$\n?/m';
        $replacements[] = '<h2>$1</h2>';
        $patterns[] = '/^=(.*)=$\n?/m';
        $replacements[] = '<h1>$1</h1>';
        $patterns[] = '/\+\+\+(.*)\+\+\+/sU';
        $replacements[] = '<code class="block">$1</code>';
        $patterns[] = '/\+\+(.*)\+\+/sU';
        $replacements[] = '<code>$1</code>';

        // substitute simple expressions & final expansion
        $contents = preg_replace( $patterns, $replacements, $contents );

        $contents = zm_wiki_lists($contents);

        return $contents;
    }

    /**
     * Wiki parser extension.
     *
     * @package org.zenmagick.plugins.zm_wiki
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
        echo( "code: <BR>" );
        echo( "code class='block': <BR>" );
        echo( "line break: <BR>" );
        echo("\t\t\t</td>\n");
        echo("\t\t\t<td>");
        echo( "=abc=<BR>" );
        echo( "==abc==<BR>" );
        echo( "===abc===<BR>" );
        echo( "* list or # numbered or ; term : definition<BR>" );
        echo( "> abc<BR>" );
        echo( "//abc//<BR>" );
        echo( "++abc++<BR>" );
        echo( "+++abc+++<BR>" );
        echo( "%%%<BR>" );
        echo("\t\t\t</td>\n");
        echo("\t\t</tr>\n");
        echo("\t</table>\n");
        echo("\t</div>\n");
    }

?>
