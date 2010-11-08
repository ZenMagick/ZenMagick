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
 * Sources admin.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.howDidYouHear
 */
class ZMHowDidYouHearSourcesAdminController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $sql = "SELECT s.sources_name AS name, s.sources_id as sourceId
                FROM " . TABLE_SOURCES . " s
                ORDER BY s.sources_name ASC";
        $sourceStats = ZMRuntime::getDatabase()->query($sql, array(), array(TABLE_SOURCES), 'ZMObject');
        $resultSource = ZMLoader::make("ArrayResultSource", 'ZMObject', $sourceStats);
        $resultList = ZMBeanUtils::getBean("ResultList");
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->getParameter('page', 1));
        return array('resultList' => $resultList, 'isOther' => $isOther);
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $action = $request->getParameter('action');
        if ('create' == $action) {
            $name = $request->getParameter('source');
            if (!empty($name)) {
                ZMRuntime::getDatabase()->createModel(TABLE_SOURCES, array('sources_name' => $name));
                ZMMessages::instance()->success('Source "'.$name.'" created.');
            }
        } else if ('delete' == $action) {
            $sourceId = $request->getParameter('sourceId');
            if (!empty($sourceId)) {
                $model = ZMRuntime::getDatabase()->loadModel(TABLE_SOURCES, array('sources_id' => $sourceId));
                if (null !== $model) {
                    ZMRuntime::getDatabase()->removeModel(TABLE_SOURCES, array('sources_id' => $sourceId));
                    ZMMessages::instance()->success('Source "'.$model['sources_name'].'" deleted.');
                }
            }
        }
        return $this->findView('success');
    }

}
