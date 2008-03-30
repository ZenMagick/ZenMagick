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
 * Product facets.
 *
 * <p>All operations involving products assume a productId/name map.</p>
 *
 * @author mano
 * @package org.zenmagick.plugins.zm_product_facets
 * @version $Id$
 */
class ZMProductFacets extends ZMObject {
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
        // register as singleton (just in case)
        // this is to prevent leaking instances, as this plugin does not just resolve the class
        // ProductFacets during init, but uses ZMLoader::make() in order to force cache registration...
        ZMObject::singleton('ProductFacets', $this);
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
        return ZMObject::singleton('ProductFacets');
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
     * <p>This will build a product facet grid based on the currently
     * configured builders.</p>
     * 
     * @return array The facets.
     */
    public function getFacets() {
        if (null === $this->facets_) {
            $key = $this->getCacheKey();

            if (false !== ($facets = $this->cache_->get($key))) {
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
     * @param array selected Map of selected facets and correspoding ids.
     * @return array The resulting products.
     */
    protected function intersect($selected) {
        $facets = $this->getFacets();

        $all = array();
        $index = 0;
        // merge products for each facet
        foreach ($selected as $facet => $ids) {
            $all[$index] = array();
            foreach ($ids as $id) {
                foreach ($facets[$facet][$id]['products'] as $pid => $name) {
                    $all[$index][$pid] = $name;
                }
            }
            ++$index;
        }

        $selected = array();
        $allCount = count($all);
        foreach ($all as $ii => $products) {
            // iterate over each list of products
            foreach ($products as $id => $name) {
                if (isset($selected[$id])) {
                    // either product is already selected 
                    continue;
                }
                // make sure it is in all selected facets
                $matchCount = 0;
                for ($jj=0; $jj < $allCount; ++$jj) {
                    if (isset($all[$jj][$id])) {
                        // product is in jj'th facet
                        ++$matchCount;
                    }
                }
                if ($matchCount == $allCount) {
                    $selected[$id] = $name;
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
        $products = $this->intersect($types);
        return $this->filterWithProducts($products);
    }

    /**
     * Filter facets.
     *
     * @param array products Map of allowed products.
     * @return array Result facets.
     */
    public function filterWithProducts($products) {
        $facets = $this->getFacets();

        $filtered = array();
        foreach ($facets as $type => $facet) {
            $filtered[$type] = array();
            foreach ($facet as $fid => $info) {
                $filtered[$type][$fid] = array();
                $filtered[$type][$fid]['name'] = $info['name'];
                $filtered[$type][$fid]['products'] = array();
                foreach ($products as $id => $name) {
                    if (isset($info['products'][$id])) {
                        $filtered[$type][$fid]['products'][$id] = $name;
                    }
                }
                $filtered[$type][$fid]['productCount'] = count($filtered[$type][$fid]['products']);
            }
        }

        return $filtered;
    }

}

?>
