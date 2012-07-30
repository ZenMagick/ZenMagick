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
namespace zenmagick\apps\admin\controller;

use zenmagick\base\Beans;

/**
 * Content editor controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ContentEditorController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $language = $request->getSelectedLanguage();
        $languageId = $request->query->get('languageId', $language->getId());
        $resultSource = new \ZMObjectResultSource('ZMEZPage', 'ezPageService', "getAllPages", array($languageId, 'static'));
        $resultList = $this->container->get("ZMResultList");
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->query->get('page', 1));
        return array('resultList' => $resultList);
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $language = $request->getSelectedLanguage();
        $languageId = $request->query->get('languageId', $language->getId());
        if (null !== ($ezPageId = $request->query->get('editId'))) {
            $ezPageId = (int)$ezPageId;
            if (0 == $ezPageId) {
                // new
                $ezPage = Beans::getBean('ZMEZPage');
                $ezPage->setStatic(true);
            } else {
                $ezPage = $this->container->get('ezPageService')->getPageForId($ezPageId, $languageId);
            }
            if (null == $ezPage) {
                return $this->findView('error', array('message' => _zm('Invalid id')));
            }
            return $this->findView(null, array('ezPage' => $ezPage));
        }

        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if ($this->handleDemo()) {
            return $this->findView('success-demo');
        }

        $ezPageService = $this->container->get('ezPageService');
        $languageId = $request->request->get('languageId');

        $viewId = null;
        if (null !== ($ezPageId = $request->request->get('updateId'))) {
            if (0 == $ezPageId) {
                // create
                $ezPage = Beans::getBean('ZMEZPage');
                Beans::setAll($ezPage, $request->request->all());
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
                Beans::setAll($ezPage, $request->request->all());
                $ezPage->setStatic(true);
                $ezPageService->updatePage($ezPage);
                $this->messageService->success('Page #'.$ezPageId.' updated');
                $viewId = 'success';
            } else {
                $this->messageService->error('Could not save page - invalid request data');
            }
        } else if (null !== ($ezPageId = $request->request->get('deleteId'))) {
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
