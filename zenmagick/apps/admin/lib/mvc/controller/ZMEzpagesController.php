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
        ZMSettings::set('isEZPagesLangSupport', true);

        ZMUrlManager::instance()->setMapping('ezpages', array(
            'ezpages-overview' => array('template' => 'ezpages-overview'),
            'ezpages-details' => array('template' => 'ezpages-details'),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        if (null !== ($ezPageId = $request->getParameter('editId'))) {
            $ezPageId = (int)$ezPageId;
            $languageId = $request->getParameter('languageId');
            if (0 == $ezPageId) {
                $ezPage = ZMLoader::make('EZPage');
            } else {
                $ezPage = ZMEZPages::instance()->getPageForId($ezPageId, $languageId);
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

        if (null !== ($ezPageId = $request->getParameter('updateId'))) {
            $ezPageId = (int)$ezPageId;
            $languageId = $request->getParameter('languageId');
            if (0 == $ezPageId) {
                // create
                $ezPage = ZMLoader::make('EZPage');
                ZMBeanUtils::setAll($ezPage, $request->getParameterMap(false));
                $ezPage = ZMEZPages::instance()->createPage($ezPage);
                if (0 < $ezPage->getId()) {
                    ZMMessages::instance()->success('EZPage #'.$ezPage->getId().' saved');
                } else {
                    ZMMessages::instance()->error('Could not save page');
                }
            } else if (null != ($ezPage = ZMEZPages::instance()->getPageForId($ezPageId, $languageId))) {
                // no sanitize!
                ZMBeanUtils::setAll($ezPage, $request->getParameterMap(false));
                ZMEZPages::instance()->updatePage($ezPage);
                ZMMessages::instance()->success('EZPage #'.$ezPageId.' updated');
            } else {
                ZMMessages::instance()->error('Could not save page - invalid request data');
            }
        } else if (null !== ($ezPageId = $request->getParameter('deleteId'))) {
            $ezPageId = (int)$ezPageId;
            if (null != ($ezPage = ZMEZPages::instance()->getPageForId($ezPageId, $languageId))) {
                ZMEZPages::instance()->removePage($ezPage);
                ZMMessages::instance()->success('EZPage #'.$ezPage->getId().' deleted');
            } else {
                ZMMessages::instance()->success('Could not find EZPage to delete: #'.$ezPage->getId());
            }
        }

        return $this->findView('ezpages-overview');
    }

}
