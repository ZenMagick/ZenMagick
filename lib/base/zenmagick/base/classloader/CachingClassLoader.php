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
namespace zenmagick\base\classloader;

/**
 * A class loader supporting caching.
 *
 * <p>Allows to cache configuration for a given path.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.base.classloader
 */
class CachingClassLoader extends ClassLoader {

    /**
     * Export
     *
     * @return mixed All relevant data.
     */
    public function export() {
        return array(
            'namespaces' => $this->getNamespaces(),
            'prefixes' => $this->getPrefixes(),
            'defaults' => $this->getDefaults()
        );
    }

    /**
     * Import
     *
     * @param mixed data The data to import.
     */
    public function import(array $data) {
        $this->addNamespaces($data['namespaces']);
        $this->addPrefixes($data['prefixes']);
        $this->addDefaults($data['defaults']);
    }

    /**
     * Check if a cache file exists for a given path.
     *
     * @param string path The path.
     */
    public function hasCacheForPath($path) {
        return file_exists(realpath($path.'/classloader.cache'));
    }

    /**
     * Get cache for path (if any).
     *
     * @param string path The path.
     * @return array The cache or <code>null</code>.
     */
    public function getCacheForPath($path) {
        if (!$this->hasCacheForPath($path)) {
            return null;
        }

        return unserialize(file_get_contents(realpath($path.'/classloader.cache')));
    }

    /**
     * Export current settings to the given path.
     *
     * @param string path The path.
     */
    public function exportToPath($path) {
        $data = $this->export();
        file_put_contents($path.'/classloader.cache', serialize($data));
    }

    /**
     * {@inheritDoc}
     */
    public function addConfig($path) {
        if (null === ($cache = $this->getCacheForPath($path))) {
            parent::addConfig($path);
            return;
        }

        $this->import($cache);
    }

}
