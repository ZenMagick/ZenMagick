<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\base\cache;

use Memcache;

/**
 * Memcache caching.
 *
 * <p>Persistent caching using <code>memcache</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.base.cache
 */
class MemcacheCache implements Cache {
    const SYSTEM_KEY = "zenmagick.base.cache.memcache";
    private $group_;
    private $memcache_;
    private $lifetime_;
    private $lastModified_;
    private $compress_;


    /**
     * Create new instance.
     */
    public function __construct() {
        $this->memcache_ = null;
        $this->lifetime_ = 0;
        $this->lastModified_ = time();
        $this->compress_ = 0;
    }


    /**
     * Get a ready-to-use <code>Memcache</code> instance.
     *
     * @param array config Optional config values: default is an empty array.
     * @return Memcache A <code>Memcache</code> instance.
     */
    protected function getMemcache($config=array()) {
        if (null == $this->memcache_) {
            $this->memcache_ = new Memcache();
            $config = array_merge(array('host' => 'localhost', 'port' => 11211), $config);
            $this->memcache_->connect($config['host'], $config['port']);
        }
        return $this->memcache_;
    }

    /**
     * {@inheritDoc}
     */
    public function init($group, $config) {
        $this->group_ = $group;
        $this->memcache_ = $this->getMemcache($config);
        $config = array_merge(array('cacheTTL' => 0, 'compress' => false), $config);
        $this->lifetime_ = $config['cacheTTL'];
        $this->compress_ = $config['compress'] ? MEMCACHE_COMPRESSED : 0;

        // update system stats
        $system = $this->memcache_->get(self::SYSTEM_KEY);
        if (!$system) {
            $system = array();
            $system['groups'] = array();
        }
        $system['groups'][$group] = $config;
        $this->memcache_->set(self::SYSTEM_KEY, $system, false, 0);
    }


    /**
     * {@inheritDoc}
     */
    public function isAvailable() {
        return class_exists('Memcache');
    }

    /**
     * {@inheritDoc}
     */
    public function clear() {
        $this->lastModified_ = time();

        // iterate over all entries and match the group prefix
        $groupPrefix = $this->group_.'/';
        foreach ($this->memcache_->getExtendedStats('items') as $host => $hostSummary) {
            foreach ($hostSummary['items'] as $slabId => $details) {
                $slabItems = $this->memcache_->getExtendedStats('cachedump', $slabId, $details['number']);
                $keys = array_keys($slabItems[$host]);
                foreach ($keys as $key) {
                    if (0 === strpos($key, $groupPrefix)) {
                        $this->memcache_->delete($key);
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
        return $this->memcache_->get($this->group_.'/'.$id);
    }

    /**
     * {@inheritDoc}
     */
    public function remove($id) {
        $this->lastModified_ = time();
        return $this->memcache_->delete($this->group_.'/'.$id);
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $id) {
        $this->lastModified_ = time();
        return $this->memcache_->set($this->group_.'/'.$id, $data, $this->compress_, $this->lifetime_);
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
        return array('lastModified' => $this->lastModified(), 'system' => $this->getMemcache()->get(self::SYSTEM_KEY));
    }

    /**
     * {@inheritDoc}
     */
    public function setOption($key, $value) {
    }

}
