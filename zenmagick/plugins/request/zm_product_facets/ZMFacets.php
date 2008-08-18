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
    private $facetBuilder_;
    private $cache_;
    private $facets_ = null;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->facetBuilder_ = array();
        $this->cache_ = ZMCaches::instance()->getCache('facets', array('cacheTTL' => 300));
        $this->facets_ = null;
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
     * Add facet builder.
     *
     * @param string type The facet type.
     * @param string function A function name.
     */
    public function addFacetBuilder($type, $function) {
        $this->facetBuilder_[$type] = $function;
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
     * <p>This will build an entries facet grid based on the currently
     * configured builders.</p>
     * 
     * @param boolean rebuild Optional flag to rebuild all facets; default is <code>false</code>.
     * @return array The facets.
     */
    public function getFacets($rebuild=false) {
        if (null === $this->facets_ || $rebuild) {
            $key = $this->getCacheKey();

            if (!$rebuild && false !== ($facets = $this->cache_->get($key))) {
                $this->facets_ = unserialize($facets);
            } else {
                // build new
                $this->facets_ = array();
                foreach ($this->facetBuilder_ as $type => $function) {
                    if (null != ($facet = $function($type))) {
                        $this->facets_[$type] = $facet;
                    }
                }
                $this->cache_->save(serialize($this->facets_), $key);
            }
        }

        return $this->facets_;
    }

    /**
     * Intersect facets.
     *
     * @param array types Map of selected types and correspoding ids.
     * @return array The resulting entries.
     */
    protected function intersect($types) {
        $facets = $this->getFacets();

        $all = array();
        $index = 0;
        // merge entries for each facet
        foreach ($types as $type => $ids) {
            $all[$index] = array();
            foreach ($ids as $id) {
                foreach ($facets[$type][$id]['entries'] as $pid => $entry) {
                    $all[$index][$pid] = $entry;
                }
            }
            ++$index;
        }

        $selected = array();
        $allCount = count($all);
        foreach ($all as $ii => $entries) {
            // iterate over each list of entries
            foreach ($entries as $id => $entry) {
                if (isset($selected[$id])) {
                    // either entry is already selected 
                    continue;
                }
                // make sure it is in all selected facets
                $matchCount = 0;
                for ($jj=0; $jj < $allCount; ++$jj) {
                    if (isset($all[$jj][$id])) {
                        // entry is in jj'th facet
                        ++$matchCount;
                    }
                }
                if ($matchCount == $allCount) {
                    $selected[$id] = $entry;
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
        $entries = $this->intersect($types);
        return $this->filterWithEntries($entries);
    }

    /**
     * Filter facets.
     *
     * @param array entries Map of allowed entries.
     * @return array Result facets.
     */
    public function filterWithEntries($entries) {
        $facets = $this->getFacets();

        $filtered = array();
        foreach ($facets as $type => $facet) {
            $filtered[$type] = array();
            foreach ($facet as $fid => $info) {
                $filtered[$type][$fid] = array();
                $filtered[$type][$fid]['name'] = $info['name'];
                $filtered[$type][$fid]['entries'] = array();
                foreach ($entries as $id => $entry) {
                    if (isset($info['entries'][$id])) {
                        $filtered[$type][$fid]['entries'][$id] = $entry;
                    }
                }
            }
        }

        return $filtered;
    }

}

?>
