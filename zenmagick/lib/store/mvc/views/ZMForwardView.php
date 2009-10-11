<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Forward view.
 *
 * <p>This will forward the request to the given controller without a redirect.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.views
 * @version $Id: ZMForwardView.php 2139 2009-04-07 00:50:48Z dermanomann $
 */
class ZMForwardView extends ZMView {

    /**
     * Create a new forward view.
     */
    function __construct() {
        parent::__construct();
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
    public function isValid() {
        return !ZMlangUtils::isEmpty($this->getTemplate());
    }

    /**
     * {@inheritDoc}
     */
    public function generate($request) { 
        ZMCrumbtrail::instance()->reset();
        $req = ZMLoader::make('Request');
        $req->setParameterMap($request->getParameterMap());
        // uh uh, bad naming...
        $req->setRequestId($this->getView());

        ZMDispatcher::dispatch($req);
        return null;
    }

}

?>
