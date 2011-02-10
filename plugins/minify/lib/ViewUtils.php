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
 * Minify view utils implementation.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.minify
 */
class ViewUtils extends ZMViewUtils {
    private $plugin_;


    /**
     * Get the controlling plugin.
     *
     * @return ZMPlugin A plugin or <code>null</code>.
     */
    public function getPlugin() {
        if (null == $this->plugin_) {
            $this->plugin_ = ZMPlugins::instance()->getPluginForId('minify');
        }

        return $this->plugin_;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveResource($filename) {
        $plugin = $this->getPlugin();
        return $plugin->pluginURL('min/f='.parent::resolveResource($filename));
    }

    /**
     * {@inheritDoc}
     */
    public function handleResourceGroup($files, $group, $location) {
        if ('js' == $group) {
            $srcList = array();
            foreach ($files as $info) {
                // use parent method to do proper resolve and not minify twice!
                $srcList[] = parent::resolveResource($info['filename']);
            }
            return '<script type="text/javascript" src="'.$this->getPlugin()->pluginURL('min/f='.implode(',', $srcList)).'"></script>'."\n";
        } else if ('css' == $group) {
            // group by same attributes/prefix/suffix
            $attrGroups = array();
            foreach ($files as $details) {
                $attr = '';
                // merge in defaults
                $details['attr'] = array_merge(array('rel' => 'stylesheet', 'type' => 'text/css', 'prefix' => '', 'suffix' => ''), $details['attr']);
                foreach ($details['attr'] as $name => $value) {
                    // sort to make comparable
                    ksort($details['attr']);
                    if (null !== $value && !in_array($name, array('prefix', 'suffix'))) {
                        $attr .= ' '.$name.'="'.$value.'"';
                    }
                }
                // keep already computed attr string
                $details['attrstr'] = $attr;
                $attrGroupKey = $attr.$details['attr']['prefix'].$details['attr']['suffix'];
                if (!array_key_exists($attrGroupKey, $attrGroups)) {
                    // keep details of first file
                    $attrGroups[$attrGroupKey] = array('details' => $details, 'files' => array($details['filename']));
                } else {
                    // details are the same, so just add filename
                    $attrGroups[$attrGroupKey]['files'][] = $details['filename'];
                }
            }
            $css = '';
            foreach ($attrGroups as $attrGroup) {
                $details = $attrGroup['details'];
                $files = $attrGroup['files'];
                $srcList = array();
                foreach ($files as $filename) {
                    // use parent method to do proper resolve and not minify twice!
                    if (null != ($resolved = parent::resolveResource($filename)) && !empty($resolved)) {
                        $srcList[] = $resolved;
                    }
                }
                if (0 < count($srcList)) {
                    $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';
                    $css .= $details['attr']['prefix'];
                    $css .= '<link'.$details['attrstr'].' href="'.$this->getPlugin()->pluginURL('min/f='.implode(',', $srcList)).'"'.$slash.'>';
                    $css .= $details['attr']['suffix']."\n";
                }
            }
            return $css;
        }

        return null;
    }

}
