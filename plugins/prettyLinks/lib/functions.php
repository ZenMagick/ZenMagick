<?php
/*
 * ZenMagick - Smart e-commerce
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
?>
<?php


    /**
     * Add a custom mapping for pretty link generation.
     *
     * <p>The converter function will be called with two parameters; the current page name
     * and as second parameter a complete map of query parameters.</p>
     *
     * @package org.zenmagick.plugins.zm_pretty_links
     * @param string view The view name (ie. the page name as referred to by the parameter <code>Runtime::getSettings()->get('zenmagick.http.request.idName')</code>)
     * @param mixed convert Function converting the view name to a pretty link; default is <code>null</code>
     *  which will be interpreted as using the view name.
     * @param array params List of query parameters to append as part of the pretty link.
     */
    function zm_pretty_links_set_mapping($view, $convert=null, $params=array(), $exclude=array()) {
    global $_zm_pretty_link_map;

        if (!isset($_zm_pretty_link_map)) {
            $_zm_pretty_link_map = array();
        }

        $_zm_pretty_link_map[$view] = array('convert' => $convert, 'params' => $params, 'exclude' => $exclude);
    }

?>
