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

use zenmagick\base\Runtime;

/**
 * Sources admin.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.howDidYouHear
 */
class ZMHowDidYouHearSourcesAdminController extends ZMController {

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $sql = "SELECT s.sources_name AS name, s.sources_id as sourceId
                FROM %table.sources% s
                ORDER BY s.sources_name ASC";
        $sourceStats = ZMRuntime::getDatabase()->fetchAll($sql, array(), array('sources'), 'zenmagick\base\ZMObject');
        $resultSource = new ZMArrayResultSource('zenmagick\base\ZMObject', $sourceStats);
        $resultList = Runtime::getContainer()->get("ZMResultList");
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->query->get('page', 1));
        return array('resultList' => $resultList);
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $action = $request->request->get('action');
        if ('create' == $action) {
            $name = $request->request->get('source');
            if (!empty($name)) {
                ZMRuntime::getDatabase()->createModel('sources', array('sources_name' => $name));
                $this->messageService->success('Source "'.$name.'" created.');
            }
        } else if ('delete' == $action) {
            $sourceId = $request->request->get('sourceId');
            if (!empty($sourceId)) {
                $model = ZMRuntime::getDatabase()->loadModel('sources', array('sources_id' => $sourceId));
                if (null !== $model) {
                    ZMRuntime::getDatabase()->removeModel('sources', array('sources_id' => $sourceId));
                    $this->messageService->success('Source "'.$model['sources_name'].'" deleted.');
                }
            }
        }
        return $this->findView('success');
    }

}
