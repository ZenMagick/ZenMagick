<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Protions Copyright (c) 2003 The zen-cart developers
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

    $_ZM_SETTINGS = array();
    // these must be the first four entries
    $_ZM_SETTINGS['isEnableZenMagick'] = true;
    $_ZM_SETTINGS['logLevel'] = 2; // 1=error; 2=warn; 3=info
    $_ZM_SETTINGS['isLogEnabled'] = 0 != $_ZM_SETTINGS['logLevel'];
    $_ZM_SETTINGS['isDieOnError'] = false;

    // ZM permalinks
    $_ZM_SETTINGS['isZMPermalinks'] = false;
    // default to default in ZM themes
    $_ZM_SETTINGS['isEnableThemeDefaults'] = true;

    // main layout
    $_ZM_SETTINGS['isEnableLeftColumn'] = COLUMN_LEFT_STATUS != 0;
    $_ZM_SETTINGS['isEnableRightColumn'] = COLUMN_RIGHT_STATUS != 0;

    $_ZM_SETTINGS['isSortAttributesByName'] = PRODUCTS_OPTIONS_SORT_ORDER != '0';
    // sort attribute values
    $_ZM_SETTINGS['isSortAttributeValuesByPrice'] = PRODUCTS_OPTIONS_SORT_BY_PRICE != '1';

    // default to random on pages that have products_id set
    $_ZM_SETTINGS['isReviewsDefaultToRandom'] = true;

    // max orders in account overview
    $_ZM_SETTINGS['accountOrderHistoryLimit'] = 3;

    // max result list
    $_ZM_SETTINGS['maxProductResultList'] = MAX_DISPLAY_PRODUCTS_LISTING;
    // max best sellers
    $_ZM_SETTINGS['maxBestSellers'] = MAX_DISPLAY_BESTSELLERS;

    // new products limit
    $_ZM_SETTINGS['newProductsLimit'] = SHOW_NEW_PRODUCTS_LIMIT;
    // max new products
    $_ZM_SETTINGS['maxNewProducts'] = MAX_RANDOM_SELECT_NEW;

    // show privacy message
    $_ZM_SETTINGS['isPrivacyMessage'] = DISPLAY_PRIVACY_CONDITIONS == 'true';

    // ask DOB
    $_ZM_SETTINGS['isAccountDOB'] = ACCOUNT_DOB == 'true';
    // ask company
    $_ZM_SETTINGS['isAccountCompany'] = ACCOUNT_COMPANY == 'true';
    // ask state
    $_ZM_SETTINGS['isAccountState'] = ACCOUNT_STATE == 'true';
    // ask newsletter
    $_ZM_SETTINGS['isAccountNewsletter'] = ACCOUNT_NEWSLETTER_STATUS != 0;
    // ask referral
    $_ZM_SETTINGS['isAccountReferral'] = CUSTOMERS_REFERRAL_STATUS == 2;

    // account
    $_ZM_SETTINGS['firstNameMinLength'] = ENTRY_FIRST_NAME_MIN_LENGTH;
    $_ZM_SETTINGS['lastNameMinLength'] = ENTRY_LAST_NAME_MIN_LENGTH;
    $_ZM_SETTINGS['emailMinLength'] = ENTRY_EMAIL_MIN_LENGTH;
    $_ZM_SETTINGS['phoneMinLength'] = ENTRY_TELEPHONE_MIN_LENGTH;
    $_ZM_SETTINGS['addressMinLength'] = ENTRY_STREET_ADDRESS_MIN_LENGTH;
    $_ZM_SETTINGS['postcodeMinLength'] = ENTRY_POSTCODE_MIN_LENGTH;
    $_ZM_SETTINGS['cityMinLength'] = ENTRY_CITY_MIN_LENGTH;
    $_ZM_SETTINGS['stateMinLength'] = ENTRY_STATE_MIN_LENGTH;
    $_ZM_SETTINGS['passwordMinLength'] = ENTRY_PASSWORD_MIN_LENGTH;

    // default customer approval setting
    $_ZM_SETTINGS['defaultCustomerApproval'] = CUSTOMERS_APPROVAL_AUTHORIZATION;

    // product notification
    $_ZM_SETTINGS['isCustomerProductNotifications'] = CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS == '1';

    // downloads
    $_ZM_SETTINGS['isDownloadsEnabled'] = DOWNLOAD_ENABLED == 'true';

    // email advisory
    $_ZM_SETTINGS['emailAdvisory'] = str_replace('-', '', EMAIL_ADVISORY);

    // default/store currency
    $_ZM_SETTINGS['defaultCurrency'] = DEFAULT_CURRENCY;

    // site map
    $_ZM_SETTINGS['isSiteMapAccountLinks'] = SHOW_ACCOUNT_LINKS_ON_SITE_MAP=='Yes';

    // phpBB
    $_ZM_SETTINGS['isEnablePHPBBLinks'] = PHPBB_LINKS_ENABLED == 'true';

    // GV 
    $_ZM_SETTINGS['isEnabledGV'] = MODULE_ORDER_TOTAL_GV_STATUS == 'true';

    // coupons
    $_ZM_SETTINGS['isEnabledCoupons'] = MODULE_ORDER_TOTAL_COUPON_STATUS == 'true';

    // newsletter
    $_ZM_SETTINGS['isEnableUnsubscribeLink'] = SHOW_NEWSLETTER_UNSUBSCRIBE_LINK == 'true';

    // contact us
    $_ZM_SETTINGS['isContactUsStoreAddress'] = CONTACT_US_STORE_NAME_ADDRESS == '1';

    // store
    $_ZM_SETTINGS['storeNameAddress'] = STORE_NAME_ADDRESS;

?>
