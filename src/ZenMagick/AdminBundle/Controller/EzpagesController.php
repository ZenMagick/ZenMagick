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
namespace ZenMagick\AdminBundle\Controller;

use ZenMagick\Base\Beans;
use ZenMagick\ZenMagickBundle\Controller\DefaultController;
use ZenMagick\StoreBundle\Entity\EZPage;

/**
 * EZPages admin controller.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class EzpagesController extends DefaultController
{
    /**
     * {@inheritDoc}
     */
    public function getViewData($request)
    {
        $language = $request->getSelectedLanguage();
        $languageId = $request->getParameter('languageId', $language->getId());
        $args = array($languageId);
        if ($request->query->get('static')) {
            $args[] = 'static';
        } else {
            $args[] = 'all';
        }

        $resultSource = new \ZMObjectResultSource('ZenMagick\StoreBundle\Entity\EZPage', 'ezPageService', "getAllPages", $args);
        $resultList = Beans::getBean('ZMResultList');

        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->query->get('page', 1));

        return array('resultList' => $resultList);
    }

    /**
     * {@inheritDoc}
     *
     * @todo split new, edit, list into different methods
     */
    public function processGet($request)
    {
        $language = $request->getSelectedLanguage();
        $languageId = $request->query->get('languageId', $language->getId());

        $route = $request->attributes->get('_route');
        $routeParams = $request->attributes->get('_route_params');
        if ('ezpages_new' == $route) {
            $ezPage = Beans::getBean('ZenMagick\StoreBundle\Entity\EZPage');
        } elseif ('ezpages_edit' == $route) {
            $ezPageId = $routeParams['id'];
            $ezPage = $this->container->get('ezPageService')->getPageForId($ezPageId, $languageId);
            if (null == $ezPage) {
                return $this->findView('error', array('message' => _zm('Invalid page id')));
            }
        }

        if (isset($ezPage)) {
            return $this->findView(null, array('ezPage' => $ezPage));
        }

        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request)
    {
        $languageId = $request->request->get('languageId');
        $ezPageService = $this->container->get('ezPageService');

        $viewId = null;
        if (null !== ($ezPageId = $request->request->get('id'))) {
            if (0 == $ezPageId) {
                // create
                $ezPage = Beans::getBean('ZenMagick\StoreBundle\Entity\EZPage');
                Beans::setAll($ezPage, $request->request->all());
                $ezPage = $ezPageService->createPage($ezPage);
                if (0 < $ezPage->getId()) {
                    $this->get('session.flash_bag')->success('EZPage #'.$ezPage->getId().' saved');
                    $viewId = 'success';
                } else {
                    $this->get('session.flash_bag')->error('Could not save page');
                }
            } elseif (null != ($ezPage = $ezPageService->getPageForId($ezPageId, $languageId))) {
                // no sanitize!
                Beans::setAll($ezPage, $request->request->all());
                $ezPageService->updatePage($ezPage);
                $this->get('session.flash_bag')->success('EZPage #'.$ezPageId.' updated');
                $viewId = 'success';
            } else {
                $this->get('session.flash_bag')->error('Could not save page - invalid request data');
            }
        } elseif (null !== ($ezPageId = $request->request->get('deleteId'))) {
            $ezPageId = (int) $ezPageId;
            if (null != ($ezPage = $ezPageService->getPageForId($ezPageId, $languageId))) {
                $ezPageService->removePage($ezPage);
                $this->get('session.flash_bag')->success('EZPage #'.$ezPage->getId().' deleted');
                $viewId = 'success';
            } else {
                $this->get('session.flash_bag')->error('Could not find EZPage to delete: #'.$ezPageId);
            }
        }

        return $this->findView($viewId);
    }

}
