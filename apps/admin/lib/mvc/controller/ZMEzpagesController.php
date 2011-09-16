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

use zenmagick\base\Beans;

/**
 * EZPages admin controller.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller
 */
class ZMEzpagesController extends ZMController {

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        ZMUrlManager::instance()->setMapping('ezpages', array(
            'ezpages-overview' => array('template' => 'ezpages-overview'),
            'ezpages-details' => array('template' => 'ezpages-details'),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $language = $request->getSelectedLanguage();
        $languageId = $request->getParameter('languageId', $language->getId());
        if (null !== ($ezPageId = $request->getParameter('editId'))) {
            $ezPageId = (int)$ezPageId;
            if (0 == $ezPageId) {
                $ezPage = Beans::getBean('ZMEZPage');
            } else {
                $ezPage = ZMEZPages::instance()->getPageForId($ezPageId, $languageId);
            }
            if (null == $ezPage) {
                return $this->findView('error', array('message' => _zm('Invalid page id')));
            }
            return $this->findView('ezpages-details', array('ezPage' => $ezPage));
        }

        $resultSource = new ZMObjectResultSource('ZMEZPage', ZMEZPages::instance(), "getAllPages", array($languageId));
        $resultList = $this->container->get("ZMResultList");
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->getParameter('page', 1));

        return $this->findView('ezpages-overview', array('resultList' => $resultList));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if ($request->handleDemo()) {
            return $this->findView('success-demo');
        }

        $languageId = $request->getParameter('languageId');

        $viewId = 'ezpages-overview';
        if (null !== ($ezPageId = $request->getParameter('updateId'))) {
            if (0 == $ezPageId) {
                // create
                $ezPage = Beans::getBean('ZMEZPage');
                Beans::setAll($ezPage, $request->getParameterMap(false));
                $ezPage = ZMEZPages::instance()->createPage($ezPage);
                if (0 < $ezPage->getId()) {
                    $this->messageService->success('EZPage #'.$ezPage->getId().' saved');
                } else {
                    $this->messageService)->error('Could not save page');
                }
            } else if (null != ($ezPage = ZMEZPages::instance()->getPageForId($ezPageId, $languageId))) {
                // no sanitize!
                Beans::setAll($ezPage, $request->getParameterMap(false));
                ZMEZPages::instance()->updatePage($ezPage);
                $this->messageService->success('EZPage #'.$ezPageId.' updated');
            } else {
                $this->messageService->error('Could not save page - invalid request data');
            }
        } else if (null !== ($ezPageId = $request->getParameter('deleteId'))) {
            $ezPageId = (int)$ezPageId;
            if (null != ($ezPage = ZMEZPages::instance()->getPageForId($ezPageId, $languageId))) {
                ZMEZPages::instance()->removePage($ezPage);
                $this->messageService->success('EZPage #'.$ezPage->getId().' deleted');
                $viewId = 'success';
            } else {
                $this->messageService->error('Could not find EZPage to delete: #'.$ezPageId);
            }
        }

        return $this->findView($viewId);
    }

}
