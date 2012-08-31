<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 ZenMagick
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

namespace ZenMagick\themes\test\utils;

use ZenMagick\Base\Database\QueryDetails;

/**
 * Simple custom product search.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.utils
 */
class ProductFinder extends \ZMProductFinder {

    /**
     * Build the search SQL.
     *
     * @param ZMSearchCriteria criteria Search criteria.
     * @return ZenMagick\Base\Database\QueryDetails The search SQL.
     */
    protected function buildQuery($criteria) {
        $select = "SELECT pd.products_id, ";
        $from = " FROM %table.products% p, %table.products_description% pd ";
        $sort = ' ORDER BY weight DESC';
        $where = ' WHERE';

        // get token
        $this->parseSearchString(stripslashes($criteria->getKeywords()), $tokens);

        $weight = '';
        $weightTemplate = 'IF (products_name LIKE "@@@%", 20, IF (products_name LIKE "%@@@%", 10, 0)) + IF (products_description LIKE "%@@@%", 5, 0)';

        $whereTemplate = '(products_name LIKE "%@@@%" OR products_description LIKE "%@@@%")';
        foreach ($tokens as $token) {
            switch ($token) {
                case 'and':
                case 'or':
                    $weight .= " + ";
                case '(':
                case ')':
                    $where .= " " . $token . " ";
                    break;
                default:
                    $where .= str_replace('@@@', $token, $whereTemplate);
                    $weight .= str_replace('@@@', $token, $weightTemplate);
            }
        }
        $where .= " AND p.products_id = pd.products_Id AND p.products_status = 1 ";

        $sql = $select . $weight . ' AS weight'. $from . $where . $sort;
        $tables = array('products_description');
        return new QueryDetails(ZMRuntime::getDatabase(), $sql, array(), $tables, null, 'pd.products_id');
    }

}

?>
