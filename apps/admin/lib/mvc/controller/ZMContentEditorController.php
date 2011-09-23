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
 * Content editor controller.
 *
 * @author DerManoMann
 * @package zenmagick.store.admin.mvc.controller
 */
class ZMContentEditorController extends ZMController {

    /**
     * {@inheritDoc}
     */
    public function preProcess($request) {
        ZMUrlManager::instance()->setMapping('content_editor', array(
            'overview' => array('template' => 'content_overview'),
            'details' => array('template' => 'ezpages-details'),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $language = $request->getSelectedLanguage();
        $ezPageService = $this->container->get('ezPageService');
        $languageId = $request->getParameter('languageId', $language->getId());
        if (null !== ($ezPageId = $request->getParameter('editId'))) {
            $ezPageId = (int)$ezPageId;
            if (0 == $ezPageId) {
                // new
                $ezPage = Beans::getBean('ZMEZPage');
                $ezPage->setStatic(true);
            } else {
                $ezPage = $ezPageService->getPageForId($ezPageId, $languageId);
            }
            if (null == $ezPage) {
                return $this->findView('error', array('message' => _zm('Invalid id')));
            }
            return $this->findView('details', array('ezPage' => $ezPage));
        }

        $resultSource = new ZMObjectResultSource('ZMEZPage', $ezPageService, "getAllPages", array($languageId, 'static'));
        $resultList = $this->container->get("ZMResultList");
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->getParameter('page', 1));

        return $this->findView('overview', array('resultList' => $resultList));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if ($request->handleDemo()) {
            return $this->findView('success-demo');
        }

        $ezPageService = $this->container->get('ezPageService');
        $languageId = $request->getParameter('languageId');

        $viewId = 'overview';
        if (null !== ($ezPageId = $request->getParameter('updateId'))) {
            if (0 == $ezPageId) {
                // create
                $ezPage = Beans::getBean('ZMEZPage');
                Beans::setAll($ezPage, $request->getParameterMap(false));
                $ezPage->setStatic(true);
                $ezPage = $ezPageService->createPage($ezPage);
                if (0 < $ezPage->getId()) {
                    $this->messageService->success('Page #'.$ezPage->getId().' saved');
                    $viewId = 'success';
                } else {
                    $this->messageService->error('Could not save page');
                }
            } else if (null != ($ezPage = $ezPageService->getPageForId($ezPageId, $languageId))) {
                // no sanitize!
                Beans::setAll($ezPage, $request->getParameterMap(false));
                $ezPage->setStatic(true);
                $ezPageService->updatePage($ezPage);
                $this->messageService->success('Page #'.$ezPageId.' updated');
                $viewId = 'success';
            } else {
                $this->messageService->error('Could not save page - invalid request data');
            }
        } else if (null !== ($ezPageId = $request->getParameter('deleteId'))) {
            $ezPageId = (int)$ezPageId;
            if (null != ($ezPage = $ezPageService->getPageForId($ezPageId, $languageId))) {
                $ezPageService->removePage($ezPage);
                $this->messageService->success('Page #'.$ezPage->getId().' deleted');
                $viewId = 'success';
            } else {
                $this->messageService->error('Could not find Page to delete: #'.$ezPageId);
            }
        }

        return $this->findView($viewId);
    }

}
