<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\admin\controller;

use zenmagick\base\Beans;

/**
 * EZPages admin controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EzpagesController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        \ZMUrlManager::instance()->setMapping('ezpages', array(
            'ezpages-overview' => array('template' => 'views/ezpages-overview.php'),
            'ezpages-details' => array('template' => 'views/ezpages-details.php'),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $language = $request->getSelectedLanguage();
        $languageId = $request->getParameter('languageId', $language->getId());
        $resultSource = new \ZMObjectResultSource('ZMEZPage', 'ezPageService', "getAllPages", array($languageId));
        $resultList = $this->container->get("ZMResultList");
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->getParameter('page', 1));
        return array('resultList' => $resultList);
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
                $ezPage = $this->container->get('ezPageService')->getPageForId($ezPageId, $languageId);
            }
            if (null == $ezPage) {
                return $this->findView('error', array('message' => _zm('Invalid page id')));
            }
            return $this->findView('ezpages-details', array('ezPage' => $ezPage));
        }

        return $this->findView('ezpages-overview');
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if ($request->handleDemo()) {
            return $this->findView('success-demo');
        }

        $languageId = $request->getParameter('languageId');
        $ezPageService = $this->container->get('ezPageService');

        $viewId = 'ezpages-overview';
        if (null !== ($ezPageId = $request->getParameter('updateId'))) {
            if (0 == $ezPageId) {
                // create
                $ezPage = Beans::getBean('ZMEZPage');
                Beans::setAll($ezPage, $request->getParameterMap(false));
                $ezPage = $ezPageService->createPage($ezPage);
                if (0 < $ezPage->getId()) {
                    $this->messageService->success('EZPage #'.$ezPage->getId().' saved');
                    $viewId = 'success';
                } else {
                    $this->messageService->error('Could not save page');
                }
            } else if (null != ($ezPage = $ezPageService->getPageForId($ezPageId, $languageId))) {
                // no sanitize!
                Beans::setAll($ezPage, $request->getParameterMap(false));
                $ezPageService->updatePage($ezPage);
                $this->messageService->success('EZPage #'.$ezPageId.' updated');
                $viewId = 'success';
            } else {
                $this->messageService->error('Could not save page - invalid request data');
            }
        } else if (null !== ($ezPageId = $request->getParameter('deleteId'))) {
            $ezPageId = (int)$ezPageId;
            if (null != ($ezPage = $ezPageService->getPageForId($ezPageId, $languageId))) {
                $ezPageService->removePage($ezPage);
                $this->messageService->success('EZPage #'.$ezPage->getId().' deleted');
                $viewId = 'success';
            } else {
                $this->messageService->error('Could not find EZPage to delete: #'.$ezPageId);
            }
        }

        return $this->findView($viewId);
    }

}
