<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2010 zenmagick.org
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
 * View utils.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.utils
 * @version $Id$
 */
class ZMViewUtils {
    const HEADER = 'header';
    const FOOTER = 'footer';
    const NOW = 'now';
    private $resources_;


    /**
     * Create new instance.
     */
    function __construct() {
        //parent::__construct();
        $this->resources_ = array('css' => array(), 'js' => array());
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        //parent::__destruct();
    }


    /*
     * - include .js, inline: top, now, bottom
     * - include .css, incline .css: top only
     *
     * - inline: text or file
     *
     * <link rel="stylesheet" type="text/css" href="<?php echo $this->asUrl('style_jscroller.css') ?>">
     * <script src="<?php echo $this->asUrl('jscroller2-1.5.js') ?>"></script>
     *
     */


    /**
     * Add link to the given CSS file or create inline CSS in the head element of the response.
     *
     * @param ZMView view The current view.
     * @param string filename A relative CSS filename.
     * @param boolean inline Optional flag that can be used to control whether to create a link
     *  or insert CSS inline; default is <code>false</code> to link.
     * @param array attr Optional attribute map; special keys 'prefix' and 'suffix' may be used to wrap.
     */
    public function cssFile($view, $filename, $inline=false, $attr=array()) {
        if (!array_key_exists($filename, $this->resources_['css'])) {
            // avoid duplicates
            $this->resources_['css'][$filename] = array('filename' => $filename, 'inline' => $inline, 'attr' => $attr);
        }
    }

    /**
     * Add the given JavaScript file to the final contents or create script reference (default).
     *
     * @param ZMView view The current view.
     * @param string filename A relative JavaScript filename.
     * @param boolean inline Optional flag that can be used to control whether to create a link
     *  or insert JavaScript inline; default is <code>false</code> to link.
     * @param string position Optional position; either <code>HEADER</code> (default), <code>FOOTER</code> or <code>NOW</code>.
     */
    public function jsFile($view, $filename, $inline=false, $position=self::HEADER) {
        if (array_key_exists($filename, $this->resources_['js'])) {
            // check if we need to do anything else or update the position
            if ($this->resources_['js'][$filename]['done']) {
                ZMLogging::instance()->log('skipping '.$filename.' as already done', ZMLogging::TRACE);
                return;
            }
            if (self::FOOTER == $this->resources_['js'][$filename]['position']) {
                if (self::HEADER == $position) {
                    ZMLogging::instance()->log('upgrading '.$filename.' to HEADER', ZMLogging::TRACE);
                    return;
                }
            }
            // either it's now or same as already registered
        }

        // record details in any case
        $this->resources_['js'][$filename] = array('filename' => $filename, 'inline' => $inline, 'position' => $position, 'done' => false);

        if (self::NOW == $position) {
            $this->resources_['js'][$filename]['done'] = true;
            if ($this->resources_['js'][$filename]['inline']) {
                echo '<script type="text/javascript">',"\n";
                $view->fetch($filename);
                echo '</script>',"\n";
            } else {
                echo '<script type="text/javascript" src="',$this->resolveResource($filename, $view),'"></script>',"\n";
            }
        }
    }

    /**
     * Resolve resource path.
     *
     * <p>This default implementation does nothing but return the result of: <code>$view->asUrl($request, $filename);</code>.</p>
     *
     * @param string filename The (relative) path to the resource.
     * @param ZMView view The current view.
     * @return string The resolved final URL.
     */
    public function resolveResource($filename, $view) {
        $request = $view->getVar('request');
        return $view->asUrl($request, $filename);
    }

    /**
     * Handle all resources of a given group and location.
     *
     * @param array Resource details.
     * @param string group The group; either <code>css</code> or <code>js</code>.
     * @param string location The location; either <code>ZMViewUtils::HEADER</code> or <code>ZMViewUtils::FOOTER</code>.
     * @param ZMView view The current view.
     * @return string The final content ready to be injected into the final contents.
     */
    public function handleResourceGroup($files, $group, $location, $view) {
        $contents = '';

        if ('js' == $group) {
            foreach ($files as $details) {
                $contents .= '<script type="text/javascript" src="'.$this->resolveResource($details['filename'], $view).'"></script>'."\n";
            }
        } else if ('css' == $group) {
            //todo: implement
        }

        return $contents;
    }

    /**
     * Process all resources.
     *
     * @param ZMRequest request The current request.
     * @param ZMView view The current view.
     * @return array Final contents for <em>header</em> and <em>footer</em> or <code>null</code>.
     */
    public function getResourceContents($view) {
        if (0 == count($this->resources_['js']) && 0 == count($this->resources_['css'])) {
            return null;
        }

        // first build separate lists to allow group processing
        $header = array();
        $footer = array();
        foreach ($this->resources_['js'] as $filename => $details) {
            if (!$details['done']) {
                if (self::HEADER == $details['position']) {
                    $header[] = $details;
                } else if (self::FOOTER == $details['position']) {
                    $footer[] = $details;
                }
                $this->resources_['js'][$filename]['done'] = true;
            }
        }

        // process
        $contents = array('header' => '', 'footer' => '');
        //$contents['header'] .= $this->handleResourceGroup($css, 'css', self::HEADER, $view);

        $contents['header'] .= $this->handleResourceGroup($header, 'js', self::HEADER, $view);
        $contents['footer'] .= $this->handleResourceGroup($footer, 'js', self::FOOTER, $view);

        return $contents;
    }


}
