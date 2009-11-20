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
 */
?>
<?php


/**
 * HTML utilities.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.toolbox.defaults
 * @version $Id$
 */
class ToolboxHtml extends ZMToolboxHtml {

    /**
     * Creates a HTML <code>&lt;img&gt;</code> tag for the given <code>ZMImageInfo</code>.
     *
     * @param ZMImageInfo imageInfo The image info.
     * @param string format Can be either of <code>ZMProducts::IMAGE_SMALL</code>, <code>ZMProducts::IMAGE_MEDIUM</code> 
     *  or <code>ZMProducts::IMAGE_LARGE</code>; default is <code>>ZMProducts::IMAGE_SMALL</code>.
     * @param mixed parameter Additional parameter for the <code>&lt;mg&gt;</code> tag; can be either
     *  a query string style list of name/value pairs or a map.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A fully formated HTML <code>&lt;img&gt;</code> tag.
     */
    public function image($imageInfo, $format=ZMProducts::IMAGE_SMALL, $parameter='', $echo=ZM_ECHO_DEFAULT) {
        if (null === $imageInfo) {
            return;
        }

        $imageInfo->setParameter($parameter);
        switch ($format) {
        case ZMProducts::IMAGE_LARGE:
            $imgSrc = $imageInfo->getLargeImage();
            break;
        case ZMProducts::IMAGE_MEDIUM:
        default:
            $imgSrc = $imageInfo->getMediumImage();
            break;
        case ZMProducts::IMAGE_SMALL:
            $imgSrc = $imageInfo->getDefaultImage();
            break;
        default:
            throw new ZMException('invalid image format: '.$format);
        }
        if (!ZMLangUtils::startsWith($imgSrc, '/')) {
            $imgSrc = $this->getRequest()->getContext() . $imgSrc;
        }
        $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';
        $html = '<img src="'.$imgSrc.'" alt="'.$this->encode($imageInfo->getAltText(), false).'" ';
        $html .= $imageInfo->getFormattedParameter();
        $html .= $slash.'>';

        if ($echo) echo $html;
        return $html;
    }

    /**
     * Encode a given string to valid HTML.
     *
     * @param string s The string to encode.
     * @param boolean echo If <code>true</code>, the escaped string will be echo'ed as well as returned.
     * @return string The encoded HTML.
     */
    public function encode($s, $echo=ZM_ECHO_DEFAULT) {
        return parent::encode($s, $echo);
    }

    /**
     * Strip HTML tags from the given text.
     *
     * @param string text The text to clean up.
     * @param boolean echo If <code>true</code>, the stripped text will be echo'ed as well as returned.
     * @return string The stripped text.
     */
    public function strip($text, $echo=ZM_ECHO_DEFAULT) {
        $clean = $text;

        $clean = preg_replace('/\r/', ' ', $clean);
        $clean = preg_replace('/\t/', ' ', $clean);
        $clean = preg_replace('/\n/', ' ', $clean);
        $clean= nl2br($clean);

        // update breaks with a space for text displays in all listings with descriptions
        while (strstr($clean, '<br>'))   $clean = str_replace('<br>',   ' ', $clean);
        while (strstr($clean, '<br />')) $clean = str_replace('<br />', ' ', $clean);
        while (strstr($clean, '<br/>'))  $clean = str_replace('<br/>',  ' ', $clean);
        while (strstr($clean, '<p>'))    $clean = str_replace('<p>',    ' ', $clean);
        while (strstr($clean, '</p>'))   $clean = str_replace('</p>',   ' ', $clean);

        // clean general and specific tags:
        $taglist = array('strong','b','u','i','em');
        foreach ($taglist as $tofind) {
            if ($tofind != '') $clean = preg_replace("/<[\/\!]*?" . $tofind . "[^<>]*?>/si", ' ', $clean);
        }

        // remove any double-spaces created by cleanups:
        while (strstr($clean, '  ')) $clean = str_replace('  ', ' ', $clean);

        // remove other html code to prevent problems on display of text
        $clean = strip_tags($clean);

        if ($echo) echo $clean;
        return $clean;
    }

    /**
     * Create a full back link.
     *
     * <p>Return a full HTML <code>&lt;a&gt;</code> tag.</p>
     *
     * <p>Since the link text may be HTML, no HTML escaping is done in this method.</p>
     *
     * @param string text The link text (can be plain text or HTML).
     * @param array attr Optional HTML attribute map; default is an empty array().
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A fully formated HTML <code>&lt;a&gt;</code> tag.
     */
    public function backLink($text, $attr=array(), $echo=ZM_ECHO_DEFAULT) {
        $link = substr(zen_back_link(), 0, -1);
        foreach ($attr as $name => $value) {
            if (null !== $value) {
                $link .= ' '.$name.'="'.$value.'"';
            }
        }
        $link .= '>' .  $text . '</a>';

        if ($echo) echo $link;
        return $link;
    }

    /**
     * Create a full HTML &lt;a&gt; tag pointig to an ezpage.
     *
     * <p>Since the link text may be HTML, no HTML escaping is done on the <code>$text</code> parameter.</p>
     *
     * @param integer id The EZ page id.
     * @param string text Optional link text; default is <code>null</code> to use the ezpage title as link text.
     * @param array attr Optional HTML attribute map; default is an empty array().
     * @param boolean echo If <code>true</code>, the link will be echo'ed as well as returned.
     * @return string A full HTML link.
     */
    public function ezpageLink($id, $text=null, $attr=array(), $echo=ZM_ECHO_DEFAULT) {
        $toolbox = $this->getToolbox();
        $page = ZMEZPages::instance()->getPageForId($id);
        $link = '<a href="' . $toolbox->net->ezpage($page, false) . '"' . $this->hrefTarget($page->isNewWin(), false);
        foreach ($attr as $name => $value) {
            if (null !== $value) {
                $link .= ' '.$name.'="'.$value.'"';
            }
        }
        $link .=  '>' . (null == $text ? $this->encode($page->getTitle(), false) : $text) . '</a>';

        if ($echo) echo $link;
        return $link;
    }

    /**
     * Create a HTML <code>&lt;a&gt;</code> tag with the product image of the
     * given product.
     *
     * <p>This one will return a fully encoded HTML <code>&lt;img&gt;</code> tag.</p>
     *
     * @param ZMProduct product A product.
     * @param int categoryId Optional category id.
     * @param array attr Optional HTML attribute map; default is <code>null</code>.
     * @param string format Can be either of <code>ZMProducts::IMAGE_SMALL</code>, <code>ZMProducts::IMAGE_MEDIUM</code> 
     *  or <code>ZMProducts::IMAGE_LARGE</code>; default is <code>ZMProducts::IMAGE_SMALL</code>.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A fully formated HTML <code>&lt;a&gt;</code> tag.
     */
    public function productImageLink($product, $categoryId=null, $attr=null, $format=ZMProducts::IMAGE_SMALL, $echo=ZM_ECHO_DEFAULT) {
        $defaults = array('class' => 'product');
        if (null === $attr) {
            $attr = $defaults;
        } else {
            $attr = array_merge($defaults, $attr);
        }

        $toolbox = $this->getToolbox();
        $html = '<a href="'.$toolbox->net->product($product->getId(), $categoryId, false).'"';
        foreach ($attr as $name => $value) {
            if (null !== $value) {
                $html .= ' '.$name.'="'.$value.'"';
            }
        }
        $html .= '>'.$this->image($product->getImageInfo(), $format, '', false);
        $html .= '</a>';

        if ($echo) echo $html;
        return $html;
    }

    /**
     * Create a HTML <code>target</code> or <code>onclick</code> attribute for a HTML &lt;a&gt; tag.
     *
     * <p>Behaviour is controlled with the <em>ZenMagick</em> setting <code>isJSTarget</code>.</p>
     *
     * @param boolean newWin If <code>true</code>, HTML for opening in a new window will be created.
     * @param boolean echo If <code>true</code>, the formatted text will be echo'ed as well as returned.
     * @return string A preformatted attribute in the form ' name="value"'
     */
    public function hrefTarget($newWin=true, $echo=ZM_ECHO_DEFAULT) {
        $text = $newWin ? (ZMSettings::get('isJSTarget') ? ' onclick="newWin(this); return false;"' : ' target="_blank"') : '';

        if ($echo) echo $text;
        return $text;
    }

    /**
     * Truncate text.
     *
     * @param string s The text.
     * @param int max The number of allowed characters; default is <em>0</em> for all.
     * @param string more Optional string that will be appended to indicate that the text was truncated; default is <em>...</em>.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string The (possibly) truncated text.
     */
    public function more($s, $max=0, $more=" ...", $echo=ZM_ECHO_DEFAULT) {
        return parent::more($s, $max, $more, $echo);
    }

    /**
     * Show form field specific error messages.
     *
     * <p>The generated <code>ul</code> tag will have the value <em>[$name]Info</em> as id, and
     * a class of <em>fieldMsg</em>.
     * Each <code>li</code> will have the type as class assigned.</p>
     *
     * <p>Messages are HTML escaped/encoded but no further localization is done.</p>
     *
     * @param string name The field name.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string HTML unordered list of messages or <code>null</code>.
     */
    public function fieldMessages($name, $echo=ZM_ECHO_DEFAULT) {
        if (!ZMMessages::instance()->hasMessages($name)) {
            return null;
        }

        $html = '<ul id="'.$name.'Info" class="fieldMsg">';
        foreach (ZMMessages::instance()->getMessages($name) as $msg) {
            $html .= '<li class="'.$msg->getType().'">'.$this->encode($msg->getText(), false).'</li>';
        }
        $html .= '</ul>';

        if ($echo) echo $html;
        return $html;
    }

    /**
     * Get optional onload handler attribute for the current page.
     *
     * <p>The generated HTML is in the form <code> onload="SOME JAVASCRIPT"</code>.</p>
     *
     * @param string page The page name; default is <code>null<code> for the current page.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A complete onload attribute incl. value or an empty string.
     * @deprecated
     */
    public function onload($page=null, $echo=ZM_ECHO_DEFAULT) {
        $page = null == $page ? $this->getRequest()->getRequestId() : $page;

        $onload = '';
        $themeInfo = Runtime::getTheme()->getThemeInfo();
        if ($themeInfo->hasPageEventHandler('onload', $page)) {
            $onload = ' onload="' . $themeInfo->getPageEventHandler('onload', $page) . '"';
        }

        if ($echo) echo $onload;
        return $onload;
    }

}

?>
