<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Ajax EZPages admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.mvc.controller.ajax
 * @version $Id$
 */
class ZMAjaxEZPagesAdminController extends ZMAjaxController {
    private $response_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->response_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Report.
     *
     * @param string msg The message.
     * @param string type The message type.
     */
    protected function report($msg, $type) {
        if (!array_key_exists($type, $this->response_)) {
            $response_[$type] = array();
        }
        $this->response_[$type][] = $msg;
    }

    /**
     * Update page property.
     *
     * <p>Request parameter:</p>
     * <ul>
     *  <li>pageId - The id of the ezPage to update.</li>
     *  <li>languageId - The language id.</li>
     *  <li>property - The property to set.</li>
     *  <li>value - The new value.</li>
     * </ul>
     */
    public function setEZPageProperty($request) {
        $pageId = $request->getParameter('pageId');
        $languageId = $request->getParameter('languageId');
        $property = $request->getParameter('property');
        $value = $request->getParameter('value');
        if (in_array($property, array('NewWin', 'SSL', 'header', 'sidebox', 'footer', 'toc'))) {
            $value = ZMLangUtils::asBoolean($value);
        }

        if ($this->updateEZPageProperty($pageId, $languageId, $property, $value)) {
            $this->report('Page updated', 'success');
            $this->response_['pageId'] = $pageId;
            $this->response_['languageId'] = $languageId;
        }

        $flatObj = ZMAjaxUtils::flattenObject($this->response_);
        $json = $this->toJSON($flatObj);
        $this->setJSONHeader($json);
    }

    /**
     * Set the given ezpage property.
     *
     * @param string pageId The ezPage id.
     * @param int languageId The language id.
     * @param string property The property name.
     * @param mixed value The new value.
     * @return boolean <code>true</code> if the status was set, <code>false</code> for any error.
     */
    protected function updateEZPageProperty($pageId, $languageId, $property, $value) {
        $ezPage = ZMEZPages::instance()->getPageForId($pageId, $languageId);
        if (null == $ezPage) {
            $this->report('Invalid ezPage id', 'error');
            $this->response_['ezPageId'] = $pageId;
            $this->response_['languageId'] = $languageId;
            return false;
        }
        ZMBeanUtils::setAll($ezPage, array($property => $value));
        ZMEZPages::instance()->updatePage($ezPage);
        return true;
    }

}
