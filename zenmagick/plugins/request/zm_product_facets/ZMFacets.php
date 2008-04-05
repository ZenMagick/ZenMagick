<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Facets.
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_product_facets
 * @version $Id$
 */
class ZMFacets extends ZMObject {
    private $cache_;
    // the facet
    private $facet_;
    // single facets as added
    private $facets_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->facet_ = null;
        $this->facets_ = array();
        $this->cache_ = ZMCaches::instance()->getCache('facets', array('cacheTTL' => 300));
        // register as singleton (just in case)
        // this is to prevent leaking instances, as this plugin does not just resolve the class
        // ProductFacets during init, but uses ZMLoader::make() in order to force cache registration...
        ZMObject::singleton('Facets', $this);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Facets');
    }


    /**
     * Add a facet.
     *
     * @param Facet facet The new facet.
     */
    public function addFacet($facet) {
        $this->facets_[$facet->getId()] = $facet;
    }

    /**
     * Get the cache key.
     *
     * @return string The cache key based on the current configuration.
     */
    protected function getCacheKey() {
        ksort($this->facetBuilder_);
        $language = ZMRuntime::instance()->getLanguage();
        return 'facets-'.serialize($this->facetBuilder_).":".$language->getId();
    }

    /**
     * Get the facet map.
     *
     * <p>This will build a facet grid based on the currently
     * added facets.</p>
     * 
     * @return array The facet.
     */
    public function getMap() {
        if (null === $this->facet_) {
            $key = $this->getCacheKey();

            if (false !== ($facet = $this->cache_->get($key))) {
                $this->facet_ = unserialize($facet);
            } else {
                // build new
                $this->facet_ = array();
                foreach ($this->facets_ as $id => $facet) {
                    $this->facet_[$id] = $facet->getMap();
                }
                $this->cache_->save(serialize($this->facet_), $key);
            }
        }

        return $this->facet_;
    }

    /**
     * Intersect facets.
     *
     * @param array selected Map of selected facets and correspoding ids.
     * @return array The resulting facet hits.
     */
    protected function intersect($selected) {
        $facet = $this->getMap();

        $all = array();
        $index = 0;
        // merge hits for each facet
        foreach ($selected as $facet => $ids) {
            $all[$index] = array();
            foreach ($ids as $id) {
                foreach ($facets[$facet][$id]['hits'] as $hid => $hit) {
                    $all[$index][$hid] = $hit;
                }
            }
            ++$index;
        }

        $selected = array();
        $allCount = count($all);
        foreach ($all as $ii => $hits) {
            // iterate over each list of hits
            foreach ($hits as $hid => $hit) {
                if (isset($selected[$hid])) {
                    // either hit is already selected 
                    continue;
                }
                // make sure it is in all selected facets
                $matchCount = 0;
                for ($jj=0; $jj < $allCount; ++$jj) {
                    if (isset($all[$jj][$hid])) {
                        // hit is in jj'th facet
                        ++$matchCount;
                    }
                }
                if ($matchCount == $allCount) {
                    $selected[$hid] = $hit;
                }
            }
        }

        return $selected;
    }

    /**
     * Filter facets by type.
     *
     * @param array types Map of type/type ids.
     * @return array Result facets.
     */
    public function filterWithTypes($types) {
        $hits = $this->intersect($types);
        return $this->filterWithProducts($hits);
    }

    /**
     * Filter facets.
     *
     * @param array hits Map of allowed hits.
     * @return array Result facets.
     */
    public function filterWithProducts($hits) {
        $facets = $this->getFacets();

        $filtered = array();
        foreach ($facets as $type => $facet) {
            $filtered[$type] = array();
            foreach ($facet as $fid => $info) {
                $filtered[$type][$fid] = array();
                $filtered[$type][$fid]['name'] = $info['name'];
                $filtered[$type][$fid]['hits'] = array();
                foreach ($hits as $hid => $hit) {
                    if (isset($info['hits'][$hid])) {
                        $filtered[$type][$fid]['hits'][$hid] = $hit;
                    }
                }
                $filtered[$type][$fid]['hitCount'] = count($filtered[$type][$fid]['hits']);
            }
        }

        return $filtered;
    }

}

?>
