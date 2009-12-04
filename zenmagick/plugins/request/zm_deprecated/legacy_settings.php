<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 *
 * $Id$
 */
?>
<?php

    ZMSettings::addAll(array(
        // legacy API is initializing all zm_ globals
        'isLegacyAPI' => false,

        'isDisplayTimerStats' => DISPLAY_PAGE_PARSE_TIME == 'true',

        // layout
        'isEnableLeftColumn' => COLUMN_LEFT_STATUS != 0,
        'isEnableRightColumn' => COLUMN_RIGHT_STATUS != 0,
        'isShowCrumbtrail' => DEFINE_BREADCRUMB_STATUS == '1',
        'isShowEZHeaderNav' => EZPAGES_STATUS_HEADER == '1',
        'isShowEZBoxesNav' => EZPAGES_STATUS_SIDEBOX == '1',
        'isShowEZFooterNav' => EZPAGES_STATUS_FOOTER == '1',

        'isShowCategoryProductCount' => 'true' == SHOW_COUNTS,

        // account
        'firstNameMinLength' => ENTRY_FIRST_NAME_MIN_LENGTH,
        'lastNameMinLength' => ENTRY_LAST_NAME_MIN_LENGTH,
        'phoneMinLength' => ENTRY_TELEPHONE_MIN_LENGTH,
        'addressMinLength' => ENTRY_STREET_ADDRESS_MIN_LENGTH,
        'postcodeMinLength' => ENTRY_POSTCODE_MIN_LENGTH,
        'cityMinLength' => ENTRY_CITY_MIN_LENGTH,
        'stateMinLength' => ENTRY_STATE_MIN_LENGTH,
        'passwordMinLength' => ENTRY_PASSWORD_MIN_LENGTH,

        // page not found
        'isPageNotFoundDefinePage' => DEFINE_PAGE_NOT_FOUND_STATUS == '1',

        // product notification
        'isCustomerProductNotifications' => CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS == '1',

        // downloads
        'isDownloadsEnabled' => DOWNLOAD_ENABLED == 'true',

        // site map
        'isSiteMapAccountLinks' => SHOW_ACCOUNT_LINKS_ON_SITE_MAP=='Yes',

        // newsletter
        'isEnableUnsubscribeLink' => SHOW_NEWSLETTER_UNSUBSCRIBE_LINK == 'true',

        // contact us
        'isContactUsStoreAddress' => CONTACT_US_STORE_NAME_ADDRESS == '1',

        // flags :)
        'flagMaxColumns' => MAX_LANGUAGE_FLAGS_COLUMNS,

        // max
        'maxRandomReviews' => MAX_RANDOM_SELECT_REVIEWS,
        'globalNewProductsLimit' => SHOW_NEW_PRODUCTS_LIMIT,
        'maxNewProducts' => MAX_DISPLAY_NEW_PRODUCTS,

        // default to random on pages that have products_id set
        'isReviewsDefaultToRandom' => true,

        // deprecated
        'bannerGroup1' => SHOW_BANNERS_GROUP_SET1,
        'bannerGroup2' => SHOW_BANNERS_GROUP_SET2,
        'bannerGroup3' => SHOW_BANNERS_GROUP_SET3,
        'bannerGroup4' => SHOW_BANNERS_GROUP_SET4,
        'bannerGroup5' => SHOW_BANNERS_GROUP_SET5,
        'bannerGroup6' => SHOW_BANNERS_GROUP_SET6,
        'bannerGroup7' => SHOW_BANNERS_GROUP_SET7,
        'bannerGroup8' => SHOW_BANNERS_GROUP_SET8,
        'bannerGroupAll' => SHOW_BANNERS_GROUP_SET_ALL,

    ), false);

?>
