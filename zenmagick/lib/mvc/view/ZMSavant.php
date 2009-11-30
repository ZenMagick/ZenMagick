<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * Custom Savant(3).
 *
 * <p>Adds some convenience methods to access resources.</p>
 *
 * <p><strong>ATTENTION:</strong> These methods only make sense if called from
 * within a template.</p>
 *
 * <p>Also, adds support for caching. The config map supports a key <em>cache</em> that
 * is expected to be a class name that implements the following two methods:</p>
 * <dl>
 *   <dt><code>get($tpl)</code></dt>
 *   <dd>Query the cache for the given template name and return the cached contents (if any).
 *     If the template is not cached (yet), or is not allowed to be cached, <code>null</code>
 *     should be returned.</dd>
 *   <dt><code>save($tpl, $result)</code></dt>
 *   <dd>Save the contents of the given template fetch in the cache (if allowed).</dd>
 * </dl>
 *
 * <p>It should be noted that it is the reponsibility of the cache class to decide whether a given
 * template can be cached or not.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.view
 * @version $Id$
 */
class ZMSavant extends Savant3 {

    /**
     * Create a new instance.
     */
    function __construct($config=null) {
        parent::__construct($config);
        if (isset($this->__config['cache']) && !is_object($this->__config['cache'])) {
            $this->__config['cache'] = ZMLoader::make($this->__config['cache']);
        }
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        // no parent destructor!!
    }


    /**
     * Check if the given resource file exists.
     *
     * @param string filename The filename, relative to the template path.
     * @return boolean <code>true</code> if the file exists, <code>false</code> if not.
     */
    public function exists($filename) {
        return !ZMLangUtils::isEmpty($this->findFile('resource', $filename));
    }

    /**
     * Resolve the given resource filename to a fully qualified filename.
     *
     * @param string filename The filename, relative to the template path.
     * @return string A fully qualified filename or <code>null</code>.
     */
    public function path($filename) {
        $path = $this->findFile('resource', $filename);
        return ZMLangUtils::isEmpty($path) ? null : $path;
    }

    /**
     * Convert the given (relative) resource filename into a url.
     *
     * @param string filename The filename, relative to the template path.
     * @return string A url.
     */
    public function url($filename) {
        $path = $this->findFile('resource', $filename);
        return $this->request->getToolbox()->net->absolute($filename);
    }

    /**
     * {@inheritDoc}
     *
     * Adds a hook for flexibe caching.
     */
    public function fetch($tpl = null) {
        // check if caching enabled
        if (isset($this->__config['cache'])) {
            // check for cache hit
            if (null != ($result = call_user_func(array($this->__config['cache'], 'get'), $tpl))) {
                return $result;
            }
        }

        // generate content as usual
        $result = parent::fetch($tpl);

        if (isset($this->__config['cache'])) {
            // offer to cache the result
            call_user_func(array($this->__config['cache'], 'save'), $tpl, $result);
        }

        return $result;
    }

}

?>
