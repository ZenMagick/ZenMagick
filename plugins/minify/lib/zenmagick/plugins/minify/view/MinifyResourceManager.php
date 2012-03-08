<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\plugins\minify\view;

use zenmagick\http\view\ResourceManager;

/**
 * Minify resource manager.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.plugins.minify.view
 */
class MinifyResourceManager extends ResourceManager {
    private $plugin_;


    /**
     * Get the controlling plugin.
     *
     * @return ZMPlugin A plugin or <code>null</code>.
     */
    public function getPlugin() {
        if (null == $this->plugin_) {
            $this->plugin_ = $this->container->get('pluginService')->getPluginForId('minify');
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
     * Handle JS resource group.
     *
     * @param array files The files.
     * @param string group The group name.
     * @param string location The location.
     * @return The content.
     */
    protected function handleJSResourceGroup($files, $group, $location) {
        $plugin = $this->getPlugin();
        $baseFUrl = $plugin->pluginURL('min/f=');
        $urlLimit = $plugin->get('urlLimit');
        $limit = $urlLimit - strlen($baseFUrl);

        // master list with groups of local/default
        $masterList = array();
        // local, can minify
        $srcList = array();
        // default, ignore
        $defaultList = array();

        // keep track of local/default change
        $currentType = null;
        $filesCount = count($files);
        foreach ($files as $ii => $info) {
            $swap = false;

            // use parent method to do proper resolve and not minify twice!
            if (!$this->isExternal($info['filename'])) {
                if (null != ($resolved = parent::resolveResource($info['filename'])) && !empty($resolved)) {
                    $srcList[] = $resolved;
                    if (null == $currentType) {
                        $currentType = 'local';
                    } else if ('local' != $currentType) {
                        $swap = true;
                    }
                }
            } else {
                $defaultList[] = $info;
                if (null == $currentType) {
                    $currentType = 'default';
                } else if ('default' != $currentType) {
                    $swap = true;
                }
            }

            if ($swap) {
                // prepare lookahead
                $nextType = null;
                if (($ii+1) < $filesCount) {
                    $nextType = $this->isExternal($files[$ii+1]['filename']) ? 'default' : 'local';
                }

                // process the current first
                if ('local' == $currentType) {
                    $masterList[] = array('type' => 'local', 'list' => $srcList);
                    $srcList = array();
                    if ($nextType == $currentType) {
                        $masterList[] = array('type' => 'default', 'list' => $defaultList);
                        $defaultList = array();
                    }
                } else {
                    $masterList[] = array('type' => 'default', 'list' => $defaultList);
                    $defaultList = array();
                    if ($nextType == $currentType) {
                        $masterList[] = array('type' => 'local', 'list' => $srcList);
                        $srcList = array();
                    }
                }
                // start again
                $currentType = null;
            }
        }
        // clean up; empty lists are ok
        $masterList[] = array('type' => 'local', 'list' => $srcList);
        $masterList[] = array('type' => 'default', 'list' => $defaultList);


        $contents = '';
        foreach ($masterList as $list) {
            if ('local' == $list['type']) {
                $listList = $this->ensureLimit($list['list'], $limit);
                foreach ($listList as $list) {
                    if (0 < count($list)) {
                        $contents .= '<script type="text/javascript" src="'.$baseFUrl.implode(',', $list).'"></script>'."\n";
                    }
                }
            } else {
                $contents .= parent::handleResourceGroup($list['list'], $group, $location);
            }
        }
        return $contents;
    }

    /**
     * Handle CSS resource group.
     *
     * @param array files The files.
     * @param string group The group name.
     * @param string location The location.
     * @return The content.
     */
    protected function handleCSSResourceGroup($files, $group, $location) {
        $plugin = $this->getPlugin();
        $baseFUrl = $plugin->pluginURL('min/f=');
        $urlLimit = $plugin->get('urlLimit');
        $limit = $urlLimit - strlen($baseFUrl);

        // group by same attributes/prefix/suffix
        $attrGroups = array();
//echo '<br>===files<pre>'; var_dump($files); echo '</pre>';
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
//echo '<br>===attrGroups<pre>'; var_dump($attrGroups); echo '</pre>';

        // handle local/default (external) for each attrGroup!!
        foreach ($attrGroups as $attrGroupKey => $attrGroup) {
//echo '<br>===attrGroup<pre>'; var_dump($attrGroup); echo '</pre>';
            // master list with groups of local/default
            $masterList = array();
            // local, can minify
            $srcList = array();
            // default, ignore
            $defaultList = array();

            // keep track of local/default change
            $currentType = null;
            $filesCount = count($attrGroup['files']);
            foreach ($attrGroup['files'] as $ii => $filename) {
//echo $filename."<BR>";
                $swap = false;

                // use parent method to do proper resolve and not minify twice!
                if (!$this->isExternal($filename)) {
                    if (null != ($resolved = parent::resolveResource($filename)) && !empty($resolved)) {
                        $srcList[] = $resolved;
                        if (null == $currentType) {
                            $currentType = 'local';
                        } else if ('local' != $currentType) {
                            $swap = true;
                        }
                    }
                } else {
                    $defaultList[] = $filename;
                    if (null == $currentType) {
                        $currentType = 'default';
                    } else if ('default' != $currentType) {
                        $swap = true;
                    }
                }

                if ($swap) {
//echo '<br>===srcList<pre>'; var_dump($srcList); echo '</pre>';
//echo '<br>===defaultList<pre>'; var_dump($defaultList); echo '</pre>';
                    // prepare lookahead
                    $nextType = null;
                    if (($ii+1) < $filesCount) {
                        $nextType = $this->isExternal($attrGroup['files'][$ii+1]) ? 'default' : 'local';
                    }

                    // process the current first
                    if ('local' == $currentType) {
                        $masterList[] = array('type' => 'local', 'list' => $srcList);
                        $srcList = array();
                        if ($nextType == $currentType) {
                            $masterList[] = array('type' => 'default', 'list' => $defaultList);
                            $defaultList = array();
                        }
                    } else {
                        $masterList[] = array('type' => 'default', 'list' => $defaultList);
                        $defaultList = array();
                        if ($nextType == $currentType) {
                            $masterList[] = array('type' => 'local', 'list' => $srcList);
                            $srcList = array();
                        }
                    }
                    // start again
                    $currentType = null;
                }
            }
            // clean up; empty lists are ok
//echo '<br>===srcList<pre>'; var_dump($srcList); echo '</pre>';
//echo '<br>===defaultList<pre>'; var_dump($defaultList); echo '</pre>';
            $masterList[] = array('type' => 'local', 'list' => $srcList);
            $masterList[] = array('type' => 'default', 'list' => $defaultList);
//echo '<br>===masterList<pre>'; var_dump($masterList); echo '</pre>';
            $attrGroups[$attrGroupKey]['files'] = $masterList;
        }
//echo '<br>===attrGroups<pre>'; var_dump($attrGroups); echo '</pre>';

        $slash = $this->container->get('settingsService')->get('zenmagick.http.html.xhtml') ? '/' : '';
        $contents = '';
        foreach ($attrGroups as $attrGroupKey => $attrGroup) {
            $details = $attrGroup['details'];
            $attrGroupContent = '';
            foreach ($attrGroup['files'] as $list) {
                if ('local' == $list['type']) {
//echo '<br>===list[list]<pre>'; var_dump($list['list']); echo '</pre>';
                    $listList = $this->ensureLimit($list['list'], $limit);
//echo '<br>===listList<pre>'; var_dump($listList); echo '</pre>';
                    foreach ($listList as $list) {
                        if (0 < count($list)) {
                            $attrGroupContent .= '<link'.$details['attrstr'].' href="'.$baseFUrl.implode(',', $list).'"'.$slash.'>'."\n";
                        }
                    }
                } else {
                    foreach ($list['list'] as $resource) {
                        $attrGroupContent .= '<link'.$details['attrstr'].' href="'.$resource.'"'.$slash.'>'."\n";
                    }
                }
            }

            if (!empty($attrGroupContent)) {
                $attrGroupContent = $details['attr']['prefix']."\n".$attrGroupContent.$details['attr']['suffix']."\n";
            }
            $contents .= $attrGroupContent;
        }

        return $contents;
    }

    /**
     * {@inheritDoc}
     */
    public function handleResourceGroup($files, $group, $location) {
        switch ($group) {
        case 'js':
            return $this->handleJSResourceGroup($files, $group, $location);
        case 'css':
            return $this->handleCSSResourceGroup($files, $group, $location);
        }
        return null;
    }

}
