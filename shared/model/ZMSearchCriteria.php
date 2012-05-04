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

use zenmagick\base\Toolbox;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Search criteria.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.model
 */
class ZMSearchCriteria extends ZMObject {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();

        $settingsService = Runtime::getContainer()->get('settingsService');
        $this->set('includeTax', $settingsService->get('showPricesTaxIncluded'));
        $this->set('countryId', $settingsService->get('storeCountry'));
        $this->set('zoneId', $settingsService->get('storeCountry'));
        $this->set('languageId', $settingsService->get('storeDefaultLanguageId'));
        $this->set('searchAll', false);
    }


    /**
     * Get the keyword(s).
     *
     * @param string default A default value.
     * @return string The search term.
     */
    public function getKeywords($default='') { return $this->get('keywords', $default); }

    /**
     * Get the include description flag.
     *
     * @param string default A default value.
     * @return boolean <code>true</code> if descriptions should be searched too.
     */
    public function isIncludeDescription($default=true) { return Toolbox::asBoolean($this->get('includeDescription', $default)); }

    /**
     * Get the category.
     *
     * @param string default A default value.
     * @return integer The category id.
     */
    public function getCategoryId($default=0) { return $this->get('categoryId', $default); }

    /**
     * Get the include subcategories flag.
     *
     * @param string default A default value.
     * @return boolean <code>true</code> if subcategories should be searched too.
     */
    public function isIncludeSubcategories($default=true) { return Toolbox::asBoolean($this->get('includeSubcategories', $default)); }

    /**
     * Get the manufacturer.
     *
     * @param string default A default value.
     * @return integer The manufacturer id.
     */
    public function getManufacturerId($default='') { return $this->get('manufacturerId', $default); }

    /**
     * Get the from date.
     *
     * @param string default A default value.
     * @return string The from date.
     */
    public function getDateFrom($default='') { return $this->get('dateFrom', $default); }

    /**
     * Get the to date.
     *
     * @param string default A default value.
     * @return string The to date.
     */
    public function getDateTo($default='') { return $this->get('dateTo', $default); }

    /**
     * Set the from date.
     *
     * @param string date The from date.
     */
    public function setDateFrom($date) {
        if ($this->container->get('localeService')->getLocale()->getFormat('date', 'short-ui-format') == $date) {
            $date = '';
        }
        $this->set('dateFrom', $date);
    }

    /**
     * Set the to date.
     *
     * param return string date The to date.
     */
    public function setDateTo($date) {
        if ($this->container->get('localeService')->getLocale()->getFormat('date', 'short-ui-format') == $date) {
            $date = '';
        }
        $this->set('dateTo', $date);
    }

    /**
     * Set the price from.
     *
     * @param string default A default value.
     * @return string The price from.
     */
    public function getPriceFrom($default='') { return $this->get('priceFrom', $default); }

    /**
     * Get the price to.
     *
     * @param string default A default value.
     * @return string The price to.
     */
    public function getPriceTo($default='') { return $this->get('priceTo', $default); }

    /**
     * Check if prices are tax inclusive.
     *
     * @return boolean <code>true</code> if included, <code>false</code> if not.
     */
    public function isIncludeTax() { return Toolbox::asBoolean($this->get('includeTax')); }

    /**
     * Get the country for tax calculations (if required).
     *
     * @return integer The country id.
     */
    public function getCountryId() { return $this->get('countryId'); }

    /**
     * Get the zone for tax calculations (if required).
     *
     * @return integer The zone id.
     */
    public function getZoneId() { return $this->get('zoneId', 0); }

    /**
     * Get the language id.
     *
     * @return integer The language id.
     */
    public function getLanguageId() { return $this->get('languageId'); }

    /**
     * Check if only active products should be searched or all.
     *
     * @return boolean <code>true</code> if all products should be searched, <code>false</code> for active only.
     */
    public function isSearchAll() { return $this->get('searchAll'); }

    /**
     * Set the search all flag.
     *
     * @param boolean value <code>true</code> if all products should be searched, <code>false</code> for active only.
     */
    public function setSearchAll($value) { $this->set('searchAll', $value); }

}
