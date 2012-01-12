<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\apps\store\admin\controller;

use zenmagick\base\Beans;
use zenmagick\base\Toolbox;

/**
 * Ajax EZPages admin controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class AjaxEZPagesAdminController extends \ZMRpcController {

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
            $value = Toolbox::asBoolean($value);
        }

        $rpcResponse = $rpcRequest->createResponse();

        $ezPageService = $this->container->get('ezPageService');
        if (null != ($ezPage = $ezPageService->getPageForId($pageId, $languageId))) {
            Beans::setAll($ezPage, array($property => $value));
            $ezPageService->updatePage($ezPage);
            $rpcResponse->setStatus(true);
        } else {
            $rpcResponse->setStatus(false);
        }

        return $rpcResponse;
    }

}
