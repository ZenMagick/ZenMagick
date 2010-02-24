<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Sidebox block contents implementation.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.blockHandler
 * @version $Id$
 */
class ZMSideboxBlockContents extends ZMObject implements ZMBlockContents {
    private $box_;


    /**
     * Create new instance.
     *
     * @param string box The box name/template; default is <code>null</code>.
     */
    function __construct($box=null) {
        parent::__construct();
        $this->setBox($box);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function getName() {
        return "Sidebox: ".str_replace('.php', '', $this->box_);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockContents($args) {
        $request = $args['request'];
        $view = $args['view'];
        return $view->fetch($request, 'boxes/'.$this->box_);
    }

    /**
     * Set the box.
     *
     * @param string box The sidebox template name (incl. the <em>.php</em> suffix).
     */
    public function setBox($box) {
        $this->box_ = $box;
    }

}

?>
