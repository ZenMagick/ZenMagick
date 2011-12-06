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

use zenmagick\base\Runtime;
use zenmagick\base\logging\Logging;


/**
 * View utils.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.utils
 */
class ZMViewUtils extends ZMObject {
    const HEADER = 'header';
    const FOOTER = 'footer';
    const NOW = 'now';
    private $resources_;
    private $view_;
    private $resourcesAsTemplates_;


    /**
     * Create new instance.
     *
     * @param ZMView view The current view; default is <code>null</code>.
     */
    public function __construct($view=null) {
        parent::__construct();
        $this->view_ = $view;
        $this->resources_ = array('css' => array(), 'js' => array());
        $this->resourcesAsTemplates_ = false;
    }


    /**
     * Set the '<em>resources as templates</em>' flag.
     *
     * <p>If set, resources will be looked up in the same location as templates.</p>
     *
     * @param boolean value The new value.
     */
    public function setResourcesAsTemplates($value) {
        $this->resourcesAsTemplates_ = $value;
    }

    /**
     * Get the '<em>resources as templates</em>' flag.
     *
     * <p>If <code>true</code>, resources will be looked up in the same location as templates.</p>
     *
     * @return boolean The value.
     */
    public function getResourcesAsTemplates() {
        return $this->resourcesAsTemplates_;
    }

    /**
     * Set the associated view.
     *
     * @param ZMView view The view.
     */
    public function setView($view) {
        $this->view_ = $view;
    }

    /**
     * Get the associated view.
     *
     * @return ZMView The view.
     */
    public function getView() {
        return $this->view_;
    }

    /**
     * Add link to the given CSS file or create inline CSS in the head element of the response.
     *
     * @param string filename A relative CSS filename.
     * @param boolean inline Optional flag that can be used to control whether to create a link
     *  or insert CSS inline; default is <code>false</code> to link.
     * @param array attr Optional attribute map; special keys 'prefix' and 'suffix' may be used to wrap.
     */
    public function cssFile($filename, $inline=false, $attr=array()) {
        if (!array_key_exists($filename, $this->resources_['css'])) {
            // avoid duplicates
            $this->resources_['css'][$filename] = array(
                'filename' => $filename,
                'inline' => $inline,
                'external' => $this->isExternal($filename),
                'attr' => $attr
            );
        }
    }

    /**
     * Add the given JavaScript file to the final contents or create script reference (default).
     *
     * @param string filename A relative JavaScript filename.
     * @param boolean inline Optional flag that can be used to control whether to create a link
     *  or insert JavaScript inline; default is <code>false</code> to link.
     * @param string position Optional position; either <code>HEADER</code> (default), <code>FOOTER</code> or <code>NOW</code>.
     */
    public function jsFile($filename, $position=self::HEADER, $inline=false) {
        if (array_key_exists($filename, $this->resources_['js'])) {
            // check if we need to do anything else or update the position
            if ($this->resources_['js'][$filename]['done']) {
                Runtime::getLogging()->log('skipping '.$filename.' as already done', Logging::TRACE);
                return;
            }
            if (self::FOOTER == $this->resources_['js'][$filename]['position']) {
                if (self::HEADER == $position) {
                    Runtime::getLogging()->log('upgrading '.$filename.' to HEADER', Logging::TRACE);
                    return;
                }
            }
            // either it's now or same as already registered
        }

        // record details in any case
        $this->resources_['js'][$filename] = array(
            'filename' => $filename,
            'inline' => $inline,
            'position' => $position,
            'external' => $this->isExternal($filename),
            'done' => false
        );

        if (self::NOW == $position) {
            $this->resources_['js'][$filename]['done'] = true;
            if ($this->resources_['js'][$filename]['inline']) {
                echo '<script type="text/javascript">',"\n";
                $this->view_->fetch($filename);
                echo '</script>',"\n";
            } else {
                // avoid empty src
                if (null != ($src = $this->resolveResource($filename)) && !empty($src)) {
                    echo '<script type="text/javascript" src="',$src,'"></script>',"\n";
                }
            }
        }
    }

    /**
     * Resolve resource path.
     *
     * <p>This default implementation does nothing but return the result of: <code>$view->asUrl($request, $filename, ZMView::RESOURCE);</code>.</p>
     *
     * @param string resource The (relative) path to the resource.
     * @param ZMView view The current view.
     * @return string The resolved final URL.
     */
    public function resolveResource($resource) {
        if ($this->isExternal($resource)) {
            return $resource;
        }

        if ('/' == $resource[0]) {
            // absolute path
            return $resource;
        } else {
            $request = $this->view_->getVar('request');
            return $this->view_->asUrl($request, $resource, $this->resourcesAsTemplates_ ? ZMView::TEMPLATE : ZMView::RESOURCE);
        }
    }

    /**
     * Check if a given resource is external.
     *
     * @param string resource The resource.
     * @return boolean <code>true</code> if the resource is external.
     */
    public function isExternal($resource) {
        return 0 === strpos($resource, '//') || false !==  strpos($resource, '://');
    }

    /**
     * Handle all resources of a given group and location.
     *
     * @param array Resource details.
     * @param string group The group; either <code>css</code> or <code>js</code>.
     * @param string location The location; either <code>ZMViewUtils::HEADER</code> or <code>ZMViewUtils::FOOTER</code>.
     * @return string The final content ready to be injected into the final contents.
     */
    public function handleResourceGroup($files, $group, $location) {
        $contents = '';

        if ('js' == $group) {
            foreach ($files as $details) {
                // avoid empty src
                if (null != ($src = $this->resolveResource($details['filename'])) && !empty($src)) {
                    $contents .= '<script type="text/javascript" src="'.$src.'"></script>'."\n";
                }
            }
        } else if ('css' == $group) {
            $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';
            $css = '';
            foreach ($files as $details) {
                if (null != ($href = $this->resolveResource($details['filename'])) && !empty($href)) {
                    // merge in defaults
                    $attr = '';
                    $details['attr'] = array_merge(array('rel' => 'stylesheet', 'type' => 'text/css', 'prefix' => '', 'suffix' => ''), $details['attr']);
                    foreach ($details['attr'] as $name => $value) {
                        if (null !== $value && !in_array($name, array('prefix', 'suffix'))) {
                            $attr .= ' '.$name.'="'.$value.'"';
                        }
                    }
                    $css .= $details['attr']['prefix'];
                    $css .= '<link'.$attr.' href="'.$href.'"'.$slash.'>';
                    $css .= $details['attr']['suffix']."\n";
                }
            }
            $contents .= $css;
        }

        return $contents;
    }

    /**
     * Process all resources.
     *
     * @return array Final contents for <em>header</em> and <em>footer</em> or <code>null</code>.
     */
    public function getResourceContents() {
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
        if (0 == count($header) && 0 == count($footer) && 0 == count($this->resources_['css'])) {
            return null;
        }

        // process
        $contents = array('header' => '', 'footer' => '');

        $contents['header'] .= $this->handleResourceGroup($this->resources_['css'], 'css', self::HEADER);

        $contents['header'] .= $this->handleResourceGroup($header, 'js', self::HEADER);
        $contents['footer'] .= $this->handleResourceGroup($footer, 'js', self::FOOTER);

        return $contents;
    }

}
