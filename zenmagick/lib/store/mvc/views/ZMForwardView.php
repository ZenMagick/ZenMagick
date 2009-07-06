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
     *
     * @param string view The view name.
     * @deprecated: contructor arguments
     */
    function __construct($view=null) {
        parent::__construct($view);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Check if this view is valid.
     *
     * @return boolean <code>true</code> if the forward url is not empty.
     */
    public function isValid() {
        $view = $this->getView();
        return !empty($view);
    }

    /**
     * Generate view response.
     *
     * <p>Will do the following:</p>
     * <ul>
     *  <li>Reset the crumbtrail</li>
     *  <li>add the forward view as <em>ZM_PAGE_KEY</em> to the request</li>
     *  <li>call <code>ZMDispatcher::dispatch()</code></li>
     * </ul>
     *
     * @param ZMRequest request The current request.
     */
    public function generate($request) { 
        ZMCrumbtrail::instance()->reset();
        $req = ZMLoader::make('RequestN');
        $req->setRequestId($this->getView());

        ZMDispatcher::dispatch($req);
    }

}

?>
