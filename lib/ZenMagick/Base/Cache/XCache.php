<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace ZenMagick\Base\Cache;


/**
 * xcache caching.
 *
 * <p>Persistent caching using <code>xcache</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class XCache implements Cache {
    const SYSTEM_KEY = "org.zenmagick.core.services.cache.provider.xcache";
    private $group_;
    private $lifetime_;
    private $lastModified_;


    /**
     * Create new instance.
     */
    public function __construct() {
        $this->lifetime_ = 0;
        $this->lastModified_ = time();
    }


    /**
     * {@inheritDoc}
     */
    public function init($group, $config) {
        $this->group_ = $group;
        $this->lifetime_ = $config['cacheTTL'];


        // update system stats
        if (!xcache_isset(self::SYSTEM_KEY)) {
            $system = array();
            $system['groups'] = array();
        } else {
            $system = xcache_get(self::SYSTEM_KEY);
        }
        $system['groups'][$group] = $config;
        xcache_set(self::SYSTEM_KEY, $system, false, 0);
    }


    /**
     * {@inheritDoc}
     */
    public function isAvailable() {
        return function_exists('xcache_info');
    }

    /**
     * {@inheritDoc}
     */
    public function clear() {
        $this->lastModified_ = time();

        // iterate over all entries and match the group prefix
        $groupPrefix = $this->group_.'/';
        for ($ii = 0, $max = xcache_count(XC_TYPE_VAR); $ii < $max; ++$ii) {
            $block = xcache_list(XC_TYPE_VAR, $ii);
            foreach ($block as $entries) {
                foreach ($entries as $entry) {
                    if (0 === strpos($entry['name'], $groupPrefix)) {
                        xcache_unset($entry['name']);
                    }
                }
            }
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function lookup($id) {
        if (!xcache_isset($this->group_.'/'.$id)) {
            return false;
        }
        return xcache_get($this->group_.'/'.$id);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id) {
        $this->lastModified_ = time();
        return xcache_unset($this->group_.'/'.$id);
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $id) {
        $this->lastModified_ = time();
        return xcache_set($this->group_.'/'.$id, $data, $this->lifetime_);
    }

    /**
     * {@inheritDoc}
     */
    public function lastModified() {
        return $this->lastModified_;
    }

    /**
     * {@inheritDoc}
     */
    public function getStats() {
        return array('lastModified' => $this->lastModified(), 'system' => xcache_get(self::SYSTEM_KEY));
    }

    /**
     * {@inheritDoc}
     */
    public function setOption($key, $value) {
    }

}
