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

use zenmagick\base\Runtime;

/**
 * Display sources stats.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.howDidYouHear
 */
class ZMHowDidYouHearSourcesStatsController extends ZMController {

    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        if (!ZMLangUtils::asBoolean($request->getParameter('other', false))) {
            $sql = "SELECT count(ci.customers_info_source_id) AS count, s.sources_name AS name, s.sources_id as sourceId
                    FROM " . TABLE_CUSTOMERS_INFO . " ci LEFT JOIN " . TABLE_SOURCES . " s ON s.sources_id = ci.customers_info_source_id
                    GROUP BY s.sources_id
                    ORDER BY ci.customers_info_source_id DESC";
            $isOther = false;
        } else {
          $sql = "SELECT count(ci.customers_info_source_id) as count, so.sources_other_name as name
                  FROM " . TABLE_CUSTOMERS_INFO . " ci, " . TABLE_SOURCES_OTHER . " so
                  WHERE ci.customers_info_source_id = " . ID_SOURCE_OTHER . " AND so.customers_id = ci.customers_info_id
                  GROUP BY so.sources_other_name
                  ORDER BY so.sources_other_name DESC";
            $isOther = true;
        }

        $sourceStats = ZMRuntime::getDatabase()->query($sql, array(), array(TABLE_SOURCES), 'zenmagick\base\ZMObject');
        $resultSource = new ZMArrayResultSource('zenmagick\base\ZMObject', $sourceStats);
        $resultList = Runtime::getContainer()->get('ZMResultList');
        $resultList->setResultSource($resultSource);
        $resultList->setPageNumber($request->getParameter('page', 1));
        return array('resultList' => $resultList, 'isOther' => $isOther);
    }

}
