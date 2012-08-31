<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace ZenMagick\apps\store\toolbox;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMException;
use ZenMagick\Http\Toolbox\ToolboxTool;

/**
 * HTML utilities.
 *
 * @author DerManoMann
 */
class ToolboxHtml extends ToolboxTool {

    /**
     * Encode a given string to valid HTML.
     *
     * @param string s The string to encode.
     * @return string The encoded HTML.
     */
    public static function encode($s) {
        $charset = Runtime::getSettings()->get('zenmagick.http.html.charset');
        $s = html_entity_decode($s, ENT_QUOTES, $charset);
        $s = htmlentities($s, ENT_QUOTES, $charset);
        return $s;
    }

    /**
     * Convert text based user input into HTML.
     *
     * @param string s The input string.
     * @return string HTML formatted text.
     */
    public static function text2html($s) {
        $html = str_replace("\r\n", '<br>', $s);
        $html = str_replace("\n", '<br>', $s);
        $html = str_replace("\r", '', $html);
        return $html;
    }

    /**
     * Truncate text.
     *
     * @param string s The text.
     * @param int max The number of allowed characters; default is <em>0</em> for all.
     * @param string more Optional string that will be appended to indicate that the text was truncated; default is <em>...</em>.
     * @return string The (possibly) truncated text.
     */
    public static function more($s, $max=0, $more=" ...") {
        $text = $s;
        if (0 != $max && strlen($text) > $max) {
            $pos = strpos($text, ' ', $max-10);
            if (!($pos === false)) {
                $text = substr($text, 0, $pos+1);
            }
            $text .= $more;
        }

        return $text;
    }

    /**
     * Get the HTML base dir.
     *
     * <p>If <em>dir</em> is not configured for the active locale, the setting 'zenmagick.mvc.html.dir' is used.</p>
     *
     * @return string Either <em>ltr</em> or <em>rtl<em>, depending on the locale.
     */
    public function getDir() {
        $dir = $this->container->get('localeService')->getFormat('dir');
        return null != $dir ? $dir : Runtime::getSettings()->get('zenmagick.mvc.html.dir', 'ltr');
    }

    /**
     * Creates a HTML <code>&lt;img&gt;</code> tag for the given <code>ZMImageInfo</code>.
     *
     * @param ZMImageInfo imageInfo The image info.
     * @param string format Can be either of <code>ZMProducts::IMAGE_SMALL</code>, <code>ZMProducts::IMAGE_MEDIUM</code>
     *  or <code>ZMProducts::IMAGE_LARGE</code>; default is <code>>ZMProducts::IMAGE_SMALL</code>.
     * @param mixed parameter Additional parameter for the <code>&lt;mg&gt;</code> tag; can be either
     *  a query string style list of name/value pairs or a map.
     * @return string A fully formated HTML <code>&lt;img&gt;</code> tag.
     */
    public function image($imageInfo, $format=\ZMProducts::IMAGE_SMALL, $parameter='') {
        if (null === $imageInfo) {
            return;
        }

        $imageInfo->setParameter($parameter);
        switch ($format) {
        case \ZMProducts::IMAGE_LARGE:
            $imgSrc = $imageInfo->getLargeImage();
            break;
        case \ZMProducts::IMAGE_MEDIUM:
        default:
            $imgSrc = $imageInfo->getMediumImage();
            break;
        case \ZMProducts::IMAGE_SMALL:
            $imgSrc = $imageInfo->getDefaultImage();
            break;
        default:
            throw new ZMException('invalid image format: '.$format);
        }
        if (0 !== strpos($imgSrc, '/')) {
            $imgSrc = $this->getRequest()->getContext() . '/' . $imgSrc;
        }
        $slash = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? '/' : '';
        $html = '<img src="'.$imgSrc.'" alt="'.$this->encode($imageInfo->getAltText()).'" ';
        $html .= $imageInfo->getFormattedParameter();
        $html .= $slash.'>';

        return $html;
    }

    /**
     * Strip HTML tags from the given text.
     *
     * @param string text The text to clean up.
     * @return string The stripped text.
     */
    public function strip($text) {
        $clean = $text;

        $clean = preg_replace('/\r/', ' ', $clean);
        $clean = preg_replace('/\t/', ' ', $clean);
        $clean = preg_replace('/\n/', ' ', $clean);
        $clean= nl2br($clean);

        // update breaks with a space
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

        return $clean;
    }

    /**
     * Create a full back link.
     *
     * <p><strong>NOTE: This has been changed to point to the store homepage.</strong></p>
     * <p>Return a full HTML <code>&lt;a&gt;</code> tag.</p>
     *
     * <p>Since the link text may be HTML, no HTML escaping is done in this method.</p>
     *
     * @param string text The link text (can be plain text or HTML).
     * @param array attr Optional HTML attribute map; default is an empty array().
     * @return string A fully formated HTML <code>&lt;a&gt;</code> tag.
     * @deprecated
     */
    public function backLink($text, $attr=array()) {
        $link = '<a href="'.$this->getToolbox()->net->url('index', '', false).'"';

        foreach ($attr as $name => $value) {
            if (null !== $value) {
                $link .= ' '.$name.'="'.$value.'"';
            }
        }
        $link .= '>' .  $text . '</a>';

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
     * @return string A full HTML link.
     */
    public function ezpageLink($id, $text=null, $attr=array()) {
        $toolbox = $this->getToolbox();
        $page = $this->container->get('ezPageService')->getPageForId($id, $this->getRequest()->getSession()->getLanguageId());
        $link = '<a href="' . $toolbox->net->ezPage($page) . '"' . $this->hrefTarget($page->isNewWin());
        foreach ($attr as $name => $value) {
            if (null !== $value) {
                $link .= ' '.$name.'="'.$value.'"';
            }
        }
        $link .=  '>' . (null == $text ? $this->encode($page->getTitle()) : $text) . '</a>';

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
     * @return string A fully formated HTML <code>&lt;a&gt;</code> tag.
     */
    public function productImageLink($product, $categoryId=null, $attr=null, $format=\ZMProducts::IMAGE_SMALL) {
        $defaults = array('class' => 'product');
        if (!is_array($attr)) {
            $attr = $defaults;
        } else {
            $attr = array_merge($defaults, $attr);
        }

        $toolbox = $this->getToolbox();
        $html = '<a href="'.$toolbox->net->product($product->getId(), $categoryId).'"';
        foreach ($attr as $name => $value) {
            if (null !== $value) {
                $html .= ' '.$name.'="'.$value.'"';
            }
        }
        $html .= '>'.$this->image($product->getImageInfo(), $format, '');
        $html .= '</a>';

        return $html;
    }

    /**
     * Create a HTML <code>target</code> or <code>onclick</code> attribute for a HTML &lt;a&gt; tag.
     *
     * <p>Behaviour is controlled with the <em>ZenMagick</em> setting <code>isJSTarget</code>.</p>
     *
     * @param boolean newWin If <code>true</code>, HTML for opening in a new window will be created.
     * @return string A preformatted attribute in the form ' name="value"'
     */
    public function hrefTarget($newWin=true) {
        $text = $newWin ? (Runtime::getSettings()->get('isJSTarget') ? ' onclick="newWin(this); return false;"' : ' target="_blank"') : '';

        return $text;
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
    public function fieldMessages($name) {
        $flashBag = $this->getRequest()->getSession()->getFlashBag();
        if (!$flashBag->hasMessages($name)) {
            return null;
        }

        $html = '<ul id="'.$name.'Info" class="fieldMsg">';
        foreach ($flashBag->getMessages($name) as $msg) {
            $html .= '<li class="'.$msg->getType().'">'.$this->encode($msg->getText()).'</li>';
        }
        $html .= '</ul>';

        return $html;
    }

}
