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


/**
 * Minify view utils implementation.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.minify
 */
class MinifyViewUtils extends ZMViewUtils {
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
    public function resolveResource($resource) {
        if ($this->isExternal($resource)) {
            return $resource;
        }
        $plugin = $this->getPlugin();
        return $plugin->pluginURL('min/f='.parent::resolveResource($resource));
    }

    /**
     * Ensure the given URIs concatenated together to no exceed the limit.
     *
     * @param array uriList List of URIs.
     * @param int limit The limit.
     * @return array One or more lists of URIs where each will be below the url limit.
     */
    protected function ensureLimit($uriList, $limit) {
        $s = implode(',', $uriList);
        $cnt = count($uriList);
        if (strlen($s) > $limit && 1 < $cnt) {
            // split
            return array_merge(
                $this->ensureLimit(array_slice($uriList, 0, $cnt / 2), $limit),
                $this->ensureLimit(array_slice($uriList, $cnt / 2), $limit)
            );
        }

        return array($uriList);
    }

    /**
     * {@inheritDoc}
     */
    public function handleResourceGroup($files, $group, $location) {
        $plugin = $this->getPlugin();
        $baseFUrl = $plugin->pluginURL('min/f=');
        $urlLimit = $plugin->get('urlLimit');
        $limit = $urlLimit - strlen($baseFUrl);
        if ('js' == $group) {
            $srcList = array();
            $defaultList = array();
            foreach ($files as $info) {
                // use parent method to do proper resolve and not minify twice!
                if (!$this->isExternal($info['filename'])) {
                    if (null != ($resolved = parent::resolveResource($info['filename'])) && !empty($resolved)) {
                        $srcList[] = $resolved;
                    }
                } else {
                    $defaultList[] = $info;
                }
            }
            $contents = '';
            $listList = $this->ensureLimit($srcList, $limit);
            foreach ($listList as $list) {
                if (0 < count($list)) {
                    $contents .= '<script type="text/javascript" src="'.$baseFUrl.implode(',', $list).'"></script>'."\n";
                }
            }
            $contents .= parent::handleResourceGroup($defaultList, $group, $location);
            return $contents;
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
            // todo:  handle external sources
            $css = '';
            foreach ($attrGroups as $attrGroup) {
                $details = $attrGroup['details'];
                $files = $attrGroup['files'];
                $srcList = array();
                $defaultList = array();
                foreach ($files as $filename) {
                    // use parent method to do proper resolve and not minify twice!
                    if (null != ($resolved = parent::resolveResource($filename)) && !empty($resolved)) {
                        $srcList[] = $resolved;
                    }
                }
                $listList = $this->ensureLimit($srcList, $limit);
                foreach ($listList as $list) {
                    if (0 < count($list)) {
                        $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';
                        $css .= $details['attr']['prefix'];
                        $css .= '<link'.$details['attrstr'].' href="'.$this->getPlugin()->pluginURL('min/f='.implode(',', $srcList)).'"'.$slash.'>';
                        $css .= $details['attr']['suffix']."\n";
                    }
                }
            }
            return $css;
        }

        return null;
    }

}
