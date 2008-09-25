<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 * Product search.
 *
 * <p>Soring and filtering is based on the corresponding result list support classes.</p>
 *
 * @author DerManoMann
 * @version $Id$
 * @todo additional filter
 */
class ZMProductFinder {
    protected $criteria_;
    protected $sortId_;
    protected $descending_;


    /**
     * Create a new instance.
     *
     * @param ZMSearchCriteria criteria Optional search criteria; default is <code>null</code>.
     */
    function __construct($criteria=null) {
        $this->criteria_ = $criteria;
        $this->sortId_ = null;
        $this->descending_ = false;
    }


    /**
     * Set the search criteria.
     *
     * @param ZMSearchCriteria criteria Search criteria.
     */
    public function setCriteria($criteria) {
        $this->criteria_ = $criteria;
    }

    /**
     * Set the descending flag.
     *
     * @param boolean descending The new value.
     */
    public function setDescending($descending) {
        $this->descending_ = $descending;
    }

    /**
     * Set the sort id.
     *
     * @param string sortId The sort id.
     */
    public function setSortId($sortId) {
        $this->sortId_ = $sortId;
    }

    /**
     * Execute a product search for the given criteria.
     *
     * @return array List of product ids.
     */
    public function execute() {
        $sql = $this->buildSQL($this->criteria_);
        $results = ZMRuntime::getDatabase()->query($sql, array(), TABLE_PRODUCTS);
        $productIds = array();
        foreach ($results as $result) {
            $productIds[] = $result['productId'];
        }
        return $productIds;
    }

    /**
     * Build the search SQL.
     *
     * @param ZMSearchCriteria criteria Search criteria.
     * @return string The search SQL.
     */
    protected function buildSQL($criteria) {
    global $db;

        $select = "SELECT DISTINCT p.products_id";
        if ($criteria->isIncludeTax() && (!ZMTools::isEmpty($criteria->getPriceFrom()) || !ZMTools::isEmpty($criteria->getPriceTo()))) {
            $select .= ", SUM(tr.tax_rate) AS tax_rate";
        }

        $from = " FROM (" . TABLE_PRODUCTS . " p 
                 LEFT JOIN " . TABLE_MANUFACTURERS . " m USING(manufacturers_id), " . 
                 TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c)
                 LEFT JOIN " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd ON mtpd.products_id= p2c.products_id AND mtpd.language_id = :languageId";

        $from = $db->bindVars($from, ':languageId', $criteria->getLanguageId(), 'integer');

        if ($criteria->isIncludeTax() && (!ZMTools::isEmpty($criteria->getPriceFrom()) || !ZMTools::isEmpty($criteria->getPriceTo()))) {
            $from .= " LEFT JOIN " . TABLE_TAX_RATES . " tr ON p.products_tax_class_id = tr.tax_class_id
                       LEFT JOIN " . TABLE_ZONES_TO_GEO_ZONES . " gz ON tr.tax_zone_id = gz.geo_zone_id
                         AND (gz.zone_country_id IS null OR gz.zone_country_id = 0 OR gz.zone_country_id = :zoneId)
                         AND (gz.zone_id IS null OR gz.zone_id = 0 OR gz.zone_id = :zoneId)";
            $from = $db->bindVars($from, ':countryId', $criteria->getCountryId(), 'integer');
            $from = $db->bindVars($from, ':zoneId', $criteria->getZoneId(), 'integer');
        }

        $where = " WHERE (p.products_status = 1 AND p.products_id = pd.products_id AND pd.language_id = :languageId
                     AND p.products_id = p2c.products_id AND p2c.categories_id = c.categories_id";

        $where = $db->bindVars($where, ':languageId', $criteria->getLanguageId(), 'integer');

        if (0 != $criteria->getCategoryId()) {
            if ($criteria->isIncludeSubcategories()) {
                $where .= " AND p2c.products_id = p.products_id
                            AND p2c.products_id = pd.products_id
                            AND p2c.categories_id in (:categoryId)";
                $category = ZMCategories::instance()->getCategoryForId($criteria->getCategoryId());
                $where = ZMDbUtils::bindValueList($where, ':categoryId', $category->getChildIds(), 'integer');
            } else {
                $where .= " AND p2c.products_id = p.products_id
                            AND p2c.products_id = pd.products_id
                            AND pd.language_id = :languageId
                            AND p2c.categories_id = :categoryId";
                $where = $db->bindVars($where, ':categoryId', $criteria->getCategoryId(), 'integer');
                $where = $db->bindVars($where, ':languageId', $criteria->getLanguageId(), 'integer');
            }
        }

        if (0 != $criteria->getManufacturerId()) {
            $where .= " AND m.manufacturers_id = :manufacturerId";
            $where = $db->bindVars($where, ':manufacturerId', $criteria->getManufacturerId(), 'integer');
        }

        if (!ZMTools::isEmpty($criteria->getKeywords())) {
            if (zen_parse_search_string(stripslashes($criteria->getKeywords()), $tokens)) {
                $where .= " AND (";
                foreach ($tokens as $token) {
                    switch ($token) {
                    case '(':
                    case ')':
                    case 'and':
                    case 'or':
                        $where .= " " . $token . " ";
                        break;
                    default:
                        $where .= "(pd.products_name LIKE '%:token%' OR p.products_model LIKE '%:token%' OR m.manufacturers_name LIKE '%:token%'";
                        $where = $db->bindVars($where, ':token', $token, 'noquotestring');

                        // search meta tags
                        $where .= " OR (mtpd.metatags_keywords LIKE '%:token%' AND mtpd.metatags_keywords !='')";
                        $where = $db->bindVars($where, ':token', $token, 'noquotestring');

                        $where .= " OR (mtpd.metatags_description LIKE '%:token%' AND mtpd.metatags_description !='')";
                        $where = $db->bindVars($where, ':token', $token, 'noquotestring');

                        if ($criteria->isIncludeDescription()) {
                            $where .= " OR pd.products_description LIKE '%:token%'";
                            $where = $db->bindVars($where, ':token', $token, 'noquotestring');
                        }
                        $where .= ')';
                        break;
                    }
                }
                $where .= ")";
            }
        }
        $where .= ')';

        if (!ZMTools::isEmpty($criteria->getDateFrom())) {
            $where .= " AND p.products_date_added >= :dateAdded";
            $where = $db->bindVars($where, ':dateAdded', zen_date_raw($criteria->getDateFrom()), 'date');
        }

        if (!ZMTools::isEmpty($criteria->getDateTo())) {
            $where .= " AND p.products_date_added <= :dateAdded";
            $where = $db->bindVars($where, ':dateAdded', zen_date_raw($criteria->getDateTo()), 'date');
        }

        if ($criteria->isIncludeTax()) {
            if ($pfrom) {
                $where .= " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) >= :price)";
                $where = $db->bindVars($where, ':price', $criteria->getPriceFrom(), 'float');
            }
            if ($pto) {
                $where .= " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) <= :price)";
                $where = $db->bindVars($where, ':price', $criteria->getPriceTo(), 'float');
            }
        } else {
            if ($pfrom) {
                $where .= " AND (p.products_price_sorter >= :price)";
                $where = $db->bindVars($where, ':price', $criteria->getPriceFrom(), 'float');
            }
            if ($pto) {
                $where .= " AND (p.products_price_sorter <= :price)";
                $where = $db->bindVars($where, ':price', $criteria->getPriceTo(), 'float');
            }
        }

        if ($criteria->isIncludeTax() && (!ZMTools::isEmpty($criteria->getPriceFrom()) || !ZMTools::isEmpty($criteria->getPriceTo()))) {
            $where .= " GROUP BY p.products_id, tr.tax_priority";
        }

        $sort = ' ORDER BY';
        if (null !== $this->sortId_) {
            switch ($this->sortId_) {
            case 'model':
                $sort .= " p.products_model " . ($this->descending_ ? "DESC" : "") . ", pd.products_name";
                break;
            case 'name':
                $sort .= " pd.products_name " . ($this->descending_ ? "DESC" : "");
                break;
            case 'manufacturer':
                $sort .= " m.manufacturers_name " . ($this->descending_ ? "DESC" : "") . ", pd.products_name";
                break;
            case 'price':
                $sort .= " p.products_price_sorter " . ($this->descending_ ? "DESC" : "") . ", pd.products_name";
                break;
            case 'weight':
                $sort .= " p.products_weight " . ($this->descending_ ? "DESC" : "") . ", pd.products_name";
                break;
            default:
                ZMLogging::instance()->log('invalid sort id: ' . $this->sortId_, ZMLogging::WARN);
               $sort .= " p.products_sort_order,  pd.products_name";
               break;
            }
        } else {
            $sort .= " p.products_sort_order,  pd.products_name";
        }

        $sql = $select . $from . $where . $sort;
        return $sql;
    }

}

?>
