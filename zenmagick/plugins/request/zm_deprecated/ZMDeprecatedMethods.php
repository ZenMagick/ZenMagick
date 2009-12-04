<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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


/**
 * Handles deprecated methods.
 *
 * <p>Adding new methods requires two three steps:</p>
 * <ol>
 *  <li>Cut & paste method from original class in here</li>
 *  <li>Add to register method similar to other methods</li>
 *  <li>Add new first argument <em>$ref</em> to method and replace <code>$this</code> in method body with <code>$ref</code></li>
 * </ol>
 * @author DerManoMann
 * @package org.zenmagick.plugins.zm_deprecated
 * @version $Id$
 */
class ZMDeprecatedMethods extends ZMObject {

    /**
     * Register methods.
     */
    public static function register() {
        $handler = new ZMDeprecatedMethods();
        ZMObject::attachMethod('getOrderStati', 'ZMOrder', array($handler, 'getOrderStati'));
        ZMObject::attachMethod('getBannerForIndex', 'ZMBanners', array($handler, 'getBannerForIndex'));
        ZMObject::attachMethod('setMapping', 'ZMUrlMapper', array($handler, 'setMapping'));
    }

    /**
     * Get the order status history.
     *
     * @param mixed ref The original target instance.
     * @return array A list of previous order stati.
     * @deprecated Use <code>ZMOrder::getOrderStatusHistory</code> instead.
     */
    public function getOrderStati($ref) { return ZMOrders::instance()->getOrderStatusHistoryForId($ref->getId()); }

    /**
     * Get a <strong>single</strong> banner for the given (zen-cart) index.
     *
     * <p>The index is based on the zen-cart defines for banner; eg: <code>SHOW_BANNERS_GROUP_SET3</code>.
     * Here the index would be three.</p>
     *
     * @param mixed ref The original target instance.
     * @param integer index The zen-cart index.
     * @return mixed A <code>ZMBanner</code> instance or <code>null</code>.
     * @deprecated use getBannerForSet instead
     */
    public function getBannerForIndex($ref, $index) {
        $list = $ref->getBannersForGroupName(ZMSettings::get('bannerGroup'.$index));
        return 0 < count($list) ? $list[0] : null;
    }

    /**
     * Set a mapping.
     *
     * @param mixed ref The original target instance.
     * @param string page The page name; <code>null</code> may be used to lookup shared mappings.
     * @param string viewId The view id; this is the key the controller is using to lookup the view; default is <code>null</code>.
     * @param string view The mapped view name; default is <code>null</code> to default to the value of the parameter <em>page</em>.
     * @param string viewDefinition The view class to be used; default is <code>PageView</code>
     * @param mixed parameter Optional map of name/value pairs to further configure the view; default is <code>null</code>.
     * @param string controllerDefinition Optional controller name; default is the value of the parameter <em>page</em>.
     * @deprecated Use setMappingInfo instead.
     */
    public function setMapping($ref, $page, $viewId=null, $view=null, $viewDefinition='PageView', $parameter=null, $controllerDefinition=null) {
        throw new ZMException("method not supported any more");
    }

}

?>
