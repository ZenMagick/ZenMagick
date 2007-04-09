<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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

    $_ZM_SETTINGS = array();
    // these must be the first five entries
    $_ZM_SETTINGS['isEnableZenMagick'] = true;
    $_ZM_SETTINGS['ZenMagickVersion'] = '${zenmagick.version}';
    $_ZM_SETTINGS['logLevel'] = 2; // 1=error; 2=warn; 3=info
    $_ZM_SETTINGS['isLogEnabled'] = false; //0 != $_ZM_SETTINGS['logLevel'];
    $_ZM_SETTINGS['isDieOnError'] = false;
    // whether to strip code in core.php
    $_ZM_SETTINGS['isStripCore'] = true;

    // patch flags
    $_ZM_SETTINGS['isEnablePatching'] = true;
    $_ZM_SETTINGS['isAdminPatchThemeSupport'] = true;
    $_ZM_SETTINGS['isAdminAutoRebuild'] = true;
    $_ZM_SETTINGS['isAutoCreateZCSideboxes'] = true;
    $_ZM_SETTINGS['isAutoCreateZCThemeDummies'] = true;
    $_ZM_SETTINGS['isI18nSupport'] = false;
    $_ZM_SETTINGS['isPatchRewriteBase'] = true;
    $_ZM_SETTINGS['isUltimateSeoPatchSupport'] = false;
    $_ZM_SETTINGS['ultimateSeoPatchSupportUseZMCode'] = true;
    $_ZM_SETTINGS['isEmailPatchSupport'] = true;

    // list of pages for SEO; null means all
    $_ZM_SETTINGS['seoEnabledPagesList'] = array("index", "product_info", "product_reviews_info");

    $_ZM_SETTINGS['isDisplayTimerStats'] = DISPLAY_PAGE_PARSE_TIME == 'true';

    // page cache
    $_ZM_SETTINGS['isPageCacheEnabled'] = true;
    $_ZM_SETTINGS['pageCacheDir'] = DIR_FS_SQL_CACHE."/zenmagick/pages/";
    $_ZM_SETTINGS['pageCacheTTL'] = 300; // in sec.
    // method to determine if page is cacheable or not
    $_ZM_SETTINGS['pageCacheStrategyCallback'] = 'zm_is_page_cacheable';

    // rss config
    $_ZM_SETTINGS['rssCacheDir'] = DIR_FS_SQL_CACHE."/zenmagick/rss/";
    $_ZM_SETTINGS['rssCacheTimeout'] = 1200;

    // HTML generation / validation
    //$_ZM_SETTINGS['isXHTML'] = true;
    $_ZM_SETTINGS['isJSTarget'] = true;

    // system
    $_ZM_SETTINGS['isAdmin'] = defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG;
    $_ZM_SETTINGS['isEnableOB'] = GZIP_LEVEL != '1';
    $_ZM_SETTINGS['isEnableSSL'] = ENABLE_SSL == 'true';
    $_ZM_SETTINGS['isForceCookieUse'] = SESSION_FORCE_COOKIE_USE == 'True';

    // email
    $_ZM_SETTING['isEmailEnabled'] = SEND_EMAILS == 'true';
    $_ZM_SETTING['emailSkipList'] = defined('EMAIL_MODULES_TO_SKIP') ? explode(",", constant('EMAIL_MODULES_TO_SKIP')) : array();
    $_ZM_SETTING['emailTestReceiver'] = (defined('DEVELOPER_OVERRIDE_EMAIL_ADDRESS') && DEVELOPER_OVERRIDE_EMAIL_ADDRESS != '') ? DEVELOPER_OVERRIDE_EMAIL_ADDRESS : null;
    $_ZM_SETTING['isEmailAdminExtraHtml'] = ADMIN_EXTRA_EMAIL_FORMAT != 'TEXT';

    // Ajax
    $_ZM_SETTINGS['isEchoJSON'] = false;
    $_ZM_SETTINGS['ajaxFormat'] = 'JSON';

    // ZM pretty URLs
    $_ZM_SETTINGS['isZMPrettyLinks'] = false;
    // default to default in ZM themes
    $_ZM_SETTINGS['isEnableThemeDefaults'] = true;

    // main layout
    $_ZM_SETTINGS['isEnableLeftColumn'] = COLUMN_LEFT_STATUS != 0;
    $_ZM_SETTINGS['isEnableRightColumn'] = COLUMN_RIGHT_STATUS != 0;

    $_ZM_SETTINGS['isShowCrumbtrail'] = DEFINE_BREADCRUMB_STATUS == '1';
    $_ZM_SETTINGS['isShowEZHeaderNav'] = EZPAGES_STATUS_HEADER == '1';
    $_ZM_SETTINGS['isShowEZBoxesNav'] = EZPAGES_STATUS_SIDEBOX == '1';
    $_ZM_SETTINGS['isShowEZFooterNav'] = EZPAGES_STATUS_FOOTER == '1';

    // use category page
    $_ZM_SETTINGS['isUseCategoryPage'] = false;
    $_ZM_SETTINGS['isShowCategoryProductCount'] = 'true' == SHOW_COUNTS;

    // language support for ez-pages
    $_ZM_SETTINGS['isEZPagesLangSupport'] = false;

    // sort default
    $_ZM_SETTINGS['defaultProductSortOrder'] = 'price';

    $_ZM_SETTINGS['isSortAttributesByName'] = PRODUCTS_OPTIONS_SORT_ORDER != '0';
    // sort attribute values
    $_ZM_SETTINGS['isSortAttributeValuesByPrice'] = PRODUCTS_OPTIONS_SORT_BY_PRICE != '1';

    // default to random on pages that have products_id set
    $_ZM_SETTINGS['isReviewsDefaultToRandom'] = true;

    // max orders in account overview
    $_ZM_SETTINGS['accountOrderHistoryLimit'] = 3;

    // page not found
    $_ZM_SETTINGS['isPageNotFoundDefinePage'] = DEFINE_PAGE_NOT_FOUND_STATUS == '1';

    // max result list
    $_ZM_SETTINGS['maxProductResultList'] = MAX_DISPLAY_PRODUCTS_LISTING;
    $_ZM_SETTINGS['maxBestSellers'] = MAX_DISPLAY_BESTSELLERS;
    $_ZM_SETTINGS['maxSpecialProducts'] = MAX_RANDOM_SELECT_SPECIALS;
    $_ZM_SETTINGS['maxRandomReviews'] = MAX_RANDOM_SELECT_REVIEWS;

    // this is a general limit
    $_ZM_SETTINGS['globalNewProductsLimit'] = SHOW_NEW_PRODUCTS_LIMIT;
    // this is a display limit
    $_ZM_SETTINGS['maxNewProducts'] = MAX_DISPLAY_NEW_PRODUCTS;

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

    // default/store currency
    $_ZM_SETTINGS['defaultCurrency'] = DEFAULT_CURRENCY;
    $_ZM_SETTINGS['textCurrencyMapping'] = CURRENCIES_TRANSLATIONS;

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
    $_ZM_SETTINGS['storeName'] = STORE_NAME;
    $_ZM_SETTINGS['storeNameAddress'] = STORE_NAME_ADDRESS;
    $_ZM_SETTINGS['storeCountry'] = STORE_COUNTRY;
    $_ZM_SETTINGS['storeEmail'] = STORE_OWNER_EMAIL_ADDRESS;
    $_ZM_SETTINGS['storeEmailFrom'] = EMAIL_FROM;

    // modules/shipping
    $_ZM_SETTINGS['isOrderTotalFreeShipping'] = defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true';
    $_ZM_SETTINGS['freeShippingDestination'] = MODULE_ORDER_TOTAL_SHIPPING_DESTINATION;
    $_ZM_SETTINGS['freeShippingOrderThreshold'] = MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER;

    // banner
    $_ZM_SETTINGS['bannerGroup1'] = SHOW_BANNERS_GROUP_SET1;
    $_ZM_SETTINGS['bannerGroup2'] = SHOW_BANNERS_GROUP_SET2;
    $_ZM_SETTINGS['bannerGroup3'] = SHOW_BANNERS_GROUP_SET3;
    $_ZM_SETTINGS['bannerGroup4'] = SHOW_BANNERS_GROUP_SET4;
    $_ZM_SETTINGS['bannerGroup5'] = SHOW_BANNERS_GROUP_SET5;
    $_ZM_SETTINGS['bannerGroup6'] = SHOW_BANNERS_GROUP_SET6;
    $_ZM_SETTINGS['bannerGroup7'] = SHOW_BANNERS_GROUP_SET7;
    $_ZM_SETTINGS['bannerGroup8'] = SHOW_BANNERS_GROUP_SET8;
    $_ZM_SETTINGS['bannerGroupAll'] = SHOW_BANNERS_GROUP_SET_ALL;

    // images
    $_ZM_SETTINGS['imgSuffixMedium'] = IMAGE_SUFFIX_MEDIUM;
    $_ZM_SETTINGS['imgSuffixLarge'] = IMAGE_SUFFIX_LARGE;

    // meta
    $_ZM_SETTINGS['metaTagKeywordDelimiter'] = ', ';
    $_ZM_SETTINGS['metaTagCrumbtrailDelimiter'] = ' - ';
    $_ZM_SETTINGS['metaTitleDelimiter'] = ' :: ';
    $_ZM_SETTINGS['metaTitlePrefix'] = 'title_';

    // flags :)
    $_ZM_SETTINGS['flagMaxColumns'] = MAX_LANGUAGE_FLAGS_COLUMNS;

?>
