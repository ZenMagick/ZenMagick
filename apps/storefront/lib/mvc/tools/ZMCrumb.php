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

use zenmagick\base\ZMObject;

/**
 * A crumbtrail crumb.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.store.sf.mvc.tools
 */
class ZMCrumb extends ZMObject {
    private $name_;
    private $url_;


    /**
     * Create a new crumbtrail crumb.
     *
     * @param string name The name; default is <code>null</code>.
     * @param string url Optional url; default is <code>null</code>.
     */
    function __construct($name=null, $url=null) {
        parent::__construct();
        $this->name_ = $name;
        $this->url_ = $url;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the name.
     *
     * @return string The crumb's name.
     */
    public function getName() { return $this->name_; }

    /**
     * Get the url (if any).
     *
     * @return string The crumb's url or <code>null</code>.
     */
    public function getURL() { return $this->url_; }

    /**
     * Set the name.
     *
     * @param string name The crumb's name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Set the url.
     *
     * @param string url The crumb's url.
     */
    public function setURL($url) { $this->url_ = $url; }

}
