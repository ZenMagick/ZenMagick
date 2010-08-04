<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 * @package zenmagick.store.admin.mvc.controller.ajax
 */
class ZMAjaxEZPagesAdminController extends ZMRpcController {

    /**
     * Set page property.
     */
    public function setEZPageProperty($rpcRequest) {
        $data = $rpcRequest->getData();
        $pageId = $data->pageId;
        $languageId = $data->languageId;
        $property = $data->property;
        $value = $data->value;

        if (in_array($property, array('NewWin', 'SSL', 'header', 'sidebox', 'footer', 'toc'))) {
            $value = ZMLangUtils::asBoolean($value);
        }

        $rpcResponse = $rpcRequest->createResponse();

        if (null != ($ezPage = ZMEZPages::instance()->getPageForId($pageId, $languageId))) {
            ZMBeanUtils::setAll($ezPage, array($property => $value));
            ZMEZPages::instance()->updatePage($ezPage);
            $rpcResponse->setStatus(true);
        } else {
            $rpcResponse->setStatus(false);
        }

        return $rpcResponse;
    }

}
