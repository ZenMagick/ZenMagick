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

use zenmagick\base\Beans;

/**
 * Catalog content controller.
 *
 * <p>This class implements a special case <code>findView()</code> method to allow easy
 * redirects to the same page.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.mvc.controller
 */
abstract class ZMCatalogContentController extends ZMController {
    const ACTIVE_CATEGORY = 1;
    const ACTIVE_PRODUCT = 2;
    protected $catalogRequestId_;
    protected $name_;
    private $active_;


    /**
     * Create new instance.
     *
     * @param string requestId Catalog requestId.
     * @param string name The name.
     * @param int active Active flag; example: <code>ACTIVE_CATEGORY|ACTIVE_PRODUCT</code>; default is <code>0</code>.
     */
    function __construct($catalogRequestId, $name, $active=0) {
        parent::__construct($catalogRequestId);
        $this->catalogRequestId_ = $catalogRequestId;
        $this->name_ = $name;
        $this->active_ = $active;
    }


    /**
     * Query whether this content is active for the given request.
     *
     * <p>Subclasses can control this by either setting the active (bit-)flags in the constructor or by
     * overriding this method.</p>
     *
     * @param ZMRequest request The current request.
     * @return boolean <code>true</code> if the plugin requests to be rendered for this request.
     */
    public function isActive($request) {
        $bits = 0;
        if (0 < $request->getCategoryId()) {
            $bits |= self::ACTIVE_CATEGORY;
        }
        if (0 < $request->getProductId()) {
            $bits |= self::ACTIVE_PRODUCT;
        }
        return $this->active_ & $bits;
    }

    /**
     * Query the (catalog) request id this controller handles.
     *
     * @return string The request id this controller is responsible for.
     */
    public function getCatalogRequestId() {
        return $this->catalogRequestId_;
    }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function getName() {
        return $this->name_;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ZMRequest $request) {
        $view = parent::process($request);
        if ($view->get('catalogRedirect')) {
            // some hacky reuse...
            // set url
            $admin2 = $request->getToolbox()->admin2;
            $view->setUrl($admin2->catalog($this));
        }
        return $view;
    }

    /**
     * {@inheritDoc}
     *
     * <p>Adds special handling to <code>'catalog-redirect' == $id</code> to allow proper redirects after POST handling without
     * the subclass having to worry about details.<br>
     * All other parameters will be handled as always.</p>
     */
    public function findView($id=null, $data=array(), $parameter=null) {
        if ('catalog-redirect' == $id) {
            // the property catalogRedirect tags the view as special redirect view...
            return Beans::getBean('ZMRedirectView#requestId=catalog&catalogRedirect=true&parameter='.urlencode($parameter).'&catalogRequestId='.$this->getCalogRequestId());
        }

        return parent::findView($id, $data, $parameter);
    }

}
