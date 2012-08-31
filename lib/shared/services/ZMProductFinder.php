<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

use ZenMagick\base\Runtime;
use ZenMagick\base\Toolbox;
use ZenMagick\base\ZMObject;
use ZenMagick\base\database\QueryDetails;

/**
 * Product search.
 *
 * <p>Sorting and filtering is based on the corresponding result list support classes.</p>
 *
 * <p>The setting '<em>apps.store.search.fulltext</em>' may be set to true to make the search SQL
 * use MySQL fulltext rather than simple <em>LIKE</em> queries.</p>
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.utils
 */
class ZMProductFinder extends ZMObject {
    protected $criteria_;
    protected $sortId_;
    protected $descending_;


    /**
     * Create a new instance.
     *
     * @param ZMSearchCriteria criteria Optional search criteria; default is <code>null</code>.
     */
    public function __construct($criteria=null) {
        parent::__construct();
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
     * @return ZenMagick\base\database\QueryDetails Query details for a product id search.
     */
    public function execute() {
        $queryDetails = $this->buildQuery($this->criteria_);
        return $queryDetails;
    }

    /**
     * Build the search SQL.
     *
     * @param ZMSearchCriteria criteria Search criteria.
     * @return ZenMagick\base\database\QueryDetails The search SQL.
     */
    protected function buildQuery($criteria) {
        $args = array();
        $useFulltext = $this->container->get('settingsService')->get('apps.store.search.fulltext', false);

        $select = "SELECT DISTINCT p.products_id";
        if ($criteria->isIncludeTax() && (!Toolbox::isEmpty($criteria->getPriceFrom()) || !Toolbox::isEmpty($criteria->getPriceTo()))) {
            $select .= ", SUM(tr.tax_rate) AS tax_rate";
        }

        $needsP2c =  0 != $criteria->getCategoryId();

        $from = " FROM (%table.products% p
                 LEFT JOIN %table.manufacturers% m USING(manufacturers_id),
                 %table.products_description% pd " .
                 ($needsP2c ? (',
                    %table.categories% c,
                    %table.products_to_categories% p2c') : '') .
                 ") LEFT JOIN %table.meta_tags_products_description% mtpd ON mtpd.products_id= p.products_id AND mtpd.language_id = :languageId";

        $args['languageId'] = $criteria->getLanguageId();

        if ($criteria->isIncludeTax() && (!Toolbox::isEmpty($criteria->getPriceFrom()) || !Toolbox::isEmpty($criteria->getPriceTo()))) {
            $from .= " LEFT JOIN %table.tax_rates% tr ON p.products_tax_class_id = tr.tax_class_id
                       LEFT JOIN %table.zones_to_geo_zones% gz ON tr.tax_zone_id = gz.geo_zone_id
                         AND (gz.zone_country_id IS null OR gz.zone_country_id = 0 OR gz.zone_country_id = :zoneId)
                         AND (gz.zone_id IS null OR gz.zone_id = 0 OR gz.zone_id = :zoneId)";
            $args['countryId'] = $criteria->getCountryId();
            $args['zoneId'] = $criteria->getZoneId();
        }

        $where = " WHERE (";
        if (!$criteria->isSearchAll()) {
            $where .= "p.products_status = 1 AND ";
        }
        $where .= "p.products_id = pd.products_id AND pd.language_id = :languageId";
        if ($needsP2c) {
            $where .= " AND p.products_id = p2c.products_id AND p2c.categories_id = c.categories_id";

            if ($criteria->isIncludeSubcategories()) {
                $where .= " AND p2c.products_id = p.products_id
                            AND p2c.products_id = pd.products_id
                            AND p2c.categories_id in (:categoryId)";
                $category = $this->container->get('categoryService')->getCategoryForId($criteria->getCategoryId(), $this->container->get('session')->getLanguageId());
                $args['categoryId'] = $category->getDecendantIds();
            } else {
                $where .= " AND p2c.products_id = p.products_id
                            AND p2c.products_id = pd.products_id
                            AND pd.language_id = :languageId
                            AND p2c.categories_id = :categoryId";
                $args['categoryId'] = $criteria->getCategoryId();
            }
        }

        if (0 != $criteria->getManufacturerId()) {
            $where .= " AND m.manufacturers_id = :manufacturerId";
            $args['manufacturerId'] = $criteria->getManufacturerId();
        }

        $fulltext_match_order = array();
        if (!Toolbox::isEmpty($criteria->getKeywords())) {
            if ($this->parseSearchString(stripslashes($criteria->getKeywords()), $tokens)) {
                $index = 0;
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
                        // use name for all string operations
                        $name = ++$index.'#name';
                        $args[$name] = '%'.$token.'%';

                        if ($useFulltext) {
                            $where .= "(match(pd.products_name) against (:" .$name.")
                                OR match(p.products_model) against (:".$name.")
                                OR m.manufacturers_name LIKE :".$name."";

                            $fulltext_match_order[] = "match(pd.products_name) against (:" .$name.")+1";
                            $fulltext_match_order[] = "match(p.products_model) against (:" .$name.")+1";
                        } else {
                            $where .= "(pd.products_name LIKE :".$name."
                                OR p.products_model LIKE :".$name."
                                OR m.manufacturers_name LIKE :".$name."";
                        }

                        // search meta tags
                        $where .= " OR (mtpd.metatags_keywords LIKE :".$name."
                                    AND mtpd.metatags_keywords !='')";
                        $where .= " OR (mtpd.metatags_description LIKE :".$name."
                                    AND mtpd.metatags_description !='')";
                        if ($criteria->isIncludeDescription()) {
                            if ($useFulltext) {
                                $where .= " OR match(pd.products_description) against (:".$name.")";
                            } else {
                                $where .= " OR pd.products_description LIKE :".$name."";
                            }
                        }
                        $where .= ')';
                        break;
                    }
                }
                $where .= ")";
            }
        }
        $where .= ')';

        $dateFormat = $this->container->get('localeService')->getFormat('date', 'short');
        if (!Toolbox::isEmpty($criteria->getDateFrom())) {
            $where .= " AND p.products_date_added >= :1#dateAdded";
            $args['1#dateAdded'] = DateTime::createFromFormat($dateFormat, $criteria->getDateFrom());
        }

        if (!Toolbox::isEmpty($criteria->getDateTo())) {
            $where .= " AND p.products_date_added <= :2#dateAdded";
            $args['2#dateAdded'] = DateTime::createFromFormat($dateFormat, $criteria->getDateTo());
        }

        if ($criteria->isIncludeTax()) {
            if (!Toolbox::isEmpty($criteria->getPriceFrom())) {
                $where .= " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) >= :1#productPrice)";
                $args['1#productPrice'] = $criteria->getPriceFrom();
            }
            if (!Toolbox::isEmpty($criteria->getPriceTo())) {
                $where .= " AND (p.products_price_sorter * IF(gz.geo_zone_id IS null, 1, 1 + (tr.tax_rate / 100)) <= :2#productPrice)";
                $args['2#productPrice'] = $criteria->getPriceTo();
            }
        } else {
            if (!Toolbox::isEmpty($criteria->getPriceFrom())) {
                $where .= " AND (p.products_price_sorter >= :1#productPrice)";
                $args['1#productPrice'] = $criteria->getPriceFrom();
            }
            if (!Toolbox::isEmpty($criteria->getPriceTo())) {
                $where .= " AND (p.products_price_sorter <= :2#productPrice)";
                $args['2#productPrice'] = $criteria->getPriceTo();
            }
        }

        if ($criteria->isIncludeTax() && (!Toolbox::isEmpty($criteria->getPriceFrom()) || !Toolbox::isEmpty($criteria->getPriceTo()))) {
            $where .= " GROUP BY p.products_id, tr.tax_priority";
        }

        $sort = " ORDER BY\n";
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
                Runtime::getLogging()->warn('invalid sort id: ' . $this->sortId_);
               $sort .= " p.products_sort_order,  pd.products_name";
               break;
            }
        } else {
            if ($useFulltext) {
                $sort .= join('*', $fulltext_match_order) . ' DESC, ';
            }
            $sort .= " p.products_sort_order,  pd.products_name";
        }

        $sql = $select . $from . $where . $sort;
        $tables = array('products', 'products_description', 'manufacturers', 'categories', 'tax_rates', 'zones_to_geo_zones');
        return new QueryDetails(ZMRuntime::getDatabase(), $sql, $args, $tables, null, 'p.products_id');
    }

    /**
     * Parse search string into individual objects
     *
     * This is a copy of ZenCart's zen_parse_search_string
     * and is thus copyrighted by ZenCart.
     *
     * We need this so functions_general.php is no longer
     * required to use the ProductFinder.
     *
     * @copyright ZenCart Development Team
     * @link http://www.zen-cart.com
     * @license GPL-2
     * @todo replace as soon as possible
     * @todo ZCSMELL
     * @todo ZCLICENSE
     */
    protected function parseSearchString($search_str = '', &$objects) {
        $search_str = trim(strtolower($search_str));

        // Break up $search_str on whitespace; quoted string will be reconstructed later
        $pieces = preg_split('/[[:space:]]+/', $search_str);
        $objects = array();
        $tmpstring = '';
        $flag = '';

        for ($k=0; $k<count($pieces); $k++) {
            while (substr($pieces[$k], 0, 1) == '(') {
                $objects[] = '(';
                if (strlen($pieces[$k]) > 1) {
                    $pieces[$k] = substr($pieces[$k], 1);
                } else {
                    $pieces[$k] = '';
                }
            }

            $post_objects = array();

            while (substr($pieces[$k], -1) == ')')  {
                $post_objects[] = ')';
                if (strlen($pieces[$k]) > 1) {
                    $pieces[$k] = substr($pieces[$k], 0, -1);
                } else {
                    $pieces[$k] = '';
                }
            }

            // Check individual words

            if ( (substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"') ) {
                $objects[] = trim($pieces[$k]);

                for ($j=0; $j<count($post_objects); $j++) {
                    $objects[] = $post_objects[$j];
                }
            } else {
                /* This means that the $piece is either the beginning or the end of a string.
                   So, we'll slurp up the $pieces and stick them together until we get to the
                   end of the string or run out of pieces.
                */

                // Add this word to the $tmpstring, starting the $tmpstring
                $tmpstring = trim(preg_replace('/"/', ' ', $pieces[$k]));

                // Check for one possible exception to the rule. That there is a single quoted word.
                if (substr($pieces[$k], -1 ) == '"') {
                    // Turn the flag off for future iterations
                    $flag = 'off';

                    $objects[] = trim($pieces[$k]);

                    for ($j=0; $j<count($post_objects); $j++) {
                        $objects[] = $post_objects[$j];
                    }

                    unset($tmpstring);

                    // Stop looking for the end of the string and move onto the next word.
                    continue;
                }

                // Otherwise, turn on the flag to indicate no quotes have been found attached to this word in the string.
                $flag = 'on';

                // Move on to the next word
                $k++;

                // Keep reading until the end of the string as long as the $flag is on

                while ( ($flag == 'on') && ($k < count($pieces)) ) {
                    while (substr($pieces[$k], -1) == ')') {
                        $post_objects[] = ')';
                        if (strlen($pieces[$k]) > 1) {
                            $pieces[$k] = substr($pieces[$k], 0, -1);
                        } else {
                            $pieces[$k] = '';
                        }
                    }

                    // If the word doesn't end in double quotes, append it to the $tmpstring.
                    if (substr($pieces[$k], -1) != '"') {
                        // Tack this word onto the current string entity
                        $tmpstring .= ' ' . $pieces[$k];

                        // Move on to the next word
                        $k++;
                        continue;
                    } else {
                        /* If the $piece ends in double quotes, strip the double quotes, tack the
                           $piece onto the tail of the string, push the $tmpstring onto the $haves,
                           kill the $tmpstring, turn the $flag "off", and return.
                        */
                        $tmpstring .= ' ' . trim(preg_replace('/"/', ' ', $pieces[$k]));

                        // Push the $tmpstring onto the array of stuff to search for
                        $objects[] = trim($tmpstring);

                        for ($j=0; $j<count($post_objects); $j++) {
                            $objects[] = $post_objects[$j];
                        }

                        unset($tmpstring);

                         // Turn off the flag to exit the loop
                        $flag = 'off';
                    }
                }
            }
        }

        // add default logical operators if needed
        $temp = array();
        for($i=0; $i<(count($objects)-1); $i++) {
            $temp[] = $objects[$i];
            if ( ($objects[$i] != 'and') &&
                      ($objects[$i] != 'or') &&
                      ($objects[$i] != '(') &&
                      ($objects[$i+1] != 'and') &&
                      ($objects[$i+1] != 'or') &&
                      ($objects[$i+1] != ')') ) {
                $temp[] = !defined('ADVANCED_SEARCH_DEFAULT_OPERATOR') ? 'and' : ADVANCED_SEARCH_DEFAULT_OPERATOR;
            }
        }
        $temp[] = $objects[$i];
        $objects = $temp;

        $keyword_count = 0;
        $operator_count = 0;
        $balance = 0;
        for($i=0; $i<count($objects); $i++) {
            if ($objects[$i] == '(') $balance --;
            if ($objects[$i] == ')') $balance ++;
            if ( ($objects[$i] == 'and') || ($objects[$i] == 'or') ) {
                $operator_count ++;
            } elseif ( ($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')') ) {
                $keyword_count ++;
            }
        }

        if ( ($operator_count < $keyword_count) && ($balance == 0) ) {
            return true;
        } else {
            return false;
        }
    }
}
