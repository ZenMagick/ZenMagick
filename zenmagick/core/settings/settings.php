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
    $_ZM_SETTINGS['logLevel'] = ZM_LOG_INFO; // 1=error; 2=warn; 3=info
    $_ZM_SETTINGS['isLogEnabled'] = false; //0 != $_ZM_SETTINGS['logLevel'];
    $_ZM_SETTINGS['zmLogFilename'] = null; // custom logfile
    $_ZM_SETTINGS['isZMErrorHandler'] = false; // custom error handler
    $_ZM_SETTINGS['isDieOnError'] = false;
    $_ZM_SETTINGS['missingPageId'] = 'error';
    // whether to strip code in core.php
    $_ZM_SETTINGS['isStripCore'] = true;

    // patch flags
    $_ZM_SETTINGS['isEnablePatching'] = true;

    // guest account behaviour
    $_ZM_SETTINGS['isLogoffGuestAfterOrder'] = true;

    $_ZM_SETTINGS['isDisplayTimerStats'] = DISPLAY_PAGE_PARSE_TIME == 'true';

    // enable POST request processing for listed pages
    $_ZM_SETTINGS['postRequestEnabledList'] = "login,password_forgotten,account_password,account_edit,contact_us,address_book_process,address_book_delete,create_account,tell_a_friend,product_reviews_write,account_newsletters,account_notifications,checkout_shipping_address,gv_send,gv_send_confirm";

    // sanitize attributes when handling product submissions (add to cart, etc)
    $_ZM_SETTINGS['isSanitizeAttributes'] = true;

    // decimal places for quantity
    $_ZM_SETTINGS['qtyDecimals'] = QUANTITY_DECIMALS;

    // page cache
    $_ZM_SETTINGS['isPageCacheEnabled'] = true;
    $_ZM_SETTINGS['pageCacheDir'] = DIR_FS_SQL_CACHE."/zenmagick/pages/";
    $_ZM_SETTINGS['pageCacheTTL'] = 300; // in sec.
    // method to determine if page is cacheable or not
    $_ZM_SETTINGS['pageCacheStrategyCallback'] = 'zm_is_page_cacheable';

    // look for define pages in theme folder
    $_ZM_SETTINGS['isZMDefinePages'] = false;

    // language strategy
    $_ZM_SETTINGS['isUseBrowserLanguage'] = LANGUAGE_DEFAULT_SELECTOR == 'Browser';
    $_ZM_SETTINGS['defaultLanguageCode'] = DEFAULT_LANGUAGE;

    // static homepage
    $_ZM_SETTINGS['staticHome'] = null;

    // rss config
    $_ZM_SETTINGS['rssCacheDir'] = DIR_FS_SQL_CACHE."/zenmagick/rss/";
    $_ZM_SETTINGS['rssCacheTimeout'] = 1200;

    // stock options
    $_ZM_SETTINGS['isEnableStock'] = STOCK_CHECK == 'true';
    $_ZM_SETTINGS['isAllowLowStockCheckout'] = STOCK_ALLOW_CHECKOUT == 'true';

    // HTML generation / validation
    //$_ZM_SETTINGS['isXHTML'] = true;
    $_ZM_SETTINGS['isJSTarget'] = true;
    $_ZM_SETTINGS['isAutoJSValidation'] = true;

    // system
    $_ZM_SETTINGS['isAdmin'] = defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG;
    $_ZM_SETTINGS['isEnableSSL'] = ENABLE_SSL == 'true';
    $_ZM_SETTINGS['isEnforceSSL'] = true;
    $_ZM_SETTINGS['isForceCookieUse'] = SESSION_FORCE_COOKIE_USE == 'True';
    $_ZM_SETTINGS['isSessionRecreate'] = SESSION_RECREATE == 'True';
    $_ZM_SETTINGS['minPasswordLength'] = ENTRY_PASSWORD_MIN_LENGTH < 6 ? 6 : ENTRY_PASSWORD_MIN_LENGTH;
    $_ZM_SETTINGS['isResolveClientIP'] = SESSION_IP_TO_HOST_ADDRESS == 'true';
    $_ZM_SETTINGS['couponCodeLength'] = SECURITY_CODE_LENGTH;

    // email
    $_ZM_SETTINGS['isEmailEnabled'] = SEND_EMAILS == 'true';
    $_ZM_SETTINGS['emailSkipList'] = defined('EMAIL_MODULES_TO_SKIP') ? explode(",", constant('EMAIL_MODULES_TO_SKIP')) : array();
    $_ZM_SETTINGS['emailTestReceiver'] = (defined('DEVELOPER_OVERRIDE_EMAIL_ADDRESS') && DEVELOPER_OVERRIDE_EMAIL_ADDRESS != '') ? DEVELOPER_OVERRIDE_EMAIL_ADDRESS : null;
    $_ZM_SETTINGS['isEmailAdminExtraHtml'] = ADMIN_EXTRA_EMAIL_FORMAT != 'TEXT';
    $_ZM_SETTINGS['isEmailAdminCreateAccount'] = SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_STATUS == '1' && SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO != '';
    $_ZM_SETTINGS['emailAdminCreateAccount'] = SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO;
    $_ZM_SETTINGS['isEmailAdminTellAFriend'] = SEND_EXTRA_TELL_A_FRIEND_EMAILS_TO_STATUS == '1' and SEND_EXTRA_TELL_A_FRIEND_EMAILS_TO != '';
    $_ZM_SETTINGS['emailAdminTellAFriend'] = SEND_EXTRA_TELL_A_FRIEND_EMAILS_TO;
    $_ZM_SETTINGS['isEmailAdminReview'] = SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO_STATUS == '1' && SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO != '';
    $_ZM_SETTINGS['emailAdminReview'] = SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO;
    $_ZM_SETTINGS['isEmailAdminGvSend'] = SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_STATUS == '1' && SEND_EXTRA_GV_CUSTOMER_EMAILS_TO != '';
    $_ZM_SETTINGS['emailAdminGvSend'] = SEND_EXTRA_GV_CUSTOMER_EMAILS_TO;

    // Ajax
    $_ZM_SETTINGS['isJSONEcho'] = true;
    $_ZM_SETTINGS['isJSONHeader'] = false;
    $_ZM_SETTINGS['ajaxFormat'] = 'JSON';

    // default to default in ZM themes
    $_ZM_SETTINGS['isEnableThemeDefaults'] = true;
    $_ZM_SETTINGS['templateSuffix'] = '.php';

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
    $_ZM_SETTINGS['isSkipSingleProductCategory'] = SKIP_SINGLE_PRODUCT_CATEGORIES == 'True';

    // reviews
    $_ZM_SETTINGS['isApproveReviews'] = REVIEWS_APPROVAL == '1';

    // guest checkout
    $_ZM_SETTINGS['isGuestCheckout'] = false;

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

    // tell a friend
    $_ZM_SETTINGS['isTellAFriendAnonymousAllow'] = ALLOW_GUEST_TO_TELL_A_FRIEND == 'true';

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

    // optional account data
    $_ZM_SETTINGS['isAccountGender'] = ACCOUNT_GENDER == 'true';
    $_ZM_SETTINGS['isAccountDOB'] = ACCOUNT_DOB == 'true';
    $_ZM_SETTINGS['isAccountCompany'] = ACCOUNT_COMPANY == 'true';
    $_ZM_SETTINGS['isAccountState'] = ACCOUNT_STATE == 'true';
    $_ZM_SETTINGS['isAccountNewsletter'] = ACCOUNT_NEWSLETTER_STATUS != 0;
    $_ZM_SETTINGS['isAccountReferral'] = CUSTOMERS_REFERRAL_STATUS == 2;
    $_ZM_SETTINGS['isAccountNickname'] = false;

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

    // tax
    $_ZM_SETTINGS['taxDecimalPlaces'] = TAX_DECIMAL_PLACES;
    $_ZM_SETTINGS['isTaxInclusive'] = DISPLAY_PRICE_WITH_TAX == 'true';
    $_ZM_SETTINGS['productTaxBase'] = STORE_PRODUCT_TAX_BASIS; //shipping,billing,store
    $_ZM_SETTINGS['shippingTaxBase'] = STORE_SHIPPING_TAX_BASIS; //shipping,billing,store

    $_ZM_SETTINGS['discountDecimals'] = SHOW_SALE_DISCOUNT_DECIMALS;
    $_ZM_SETTINGS['calculationDecimals'] = 4;

    // site map
    $_ZM_SETTINGS['isSiteMapAccountLinks'] = SHOW_ACCOUNT_LINKS_ON_SITE_MAP=='Yes';

    // GV 
    $_ZM_SETTINGS['isEnabledGV'] = MODULE_ORDER_TOTAL_GV_STATUS == 'true';

    // coupons
    $_ZM_SETTINGS['isEnabledCoupons'] = MODULE_ORDER_TOTAL_COUPON_STATUS == 'true';

    // newsletter
    $_ZM_SETTINGS['isEnableUnsubscribeLink'] = SHOW_NEWSLETTER_UNSUBSCRIBE_LINK == 'true';

    // contact us
    $_ZM_SETTINGS['isContactUsStoreAddress'] = CONTACT_US_STORE_NAME_ADDRESS == '1';

    // store
    $_ZM_SETTINGS['storeOwner'] = STORE_OWNER;
    $_ZM_SETTINGS['storeName'] = STORE_NAME;
    $_ZM_SETTINGS['storeNameAddress'] = STORE_NAME_ADDRESS;
    $_ZM_SETTINGS['storeCountry'] = STORE_COUNTRY;
    $_ZM_SETTINGS['storeZone'] = STORE_ZONE;
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
    $_ZM_SETTINGS['imgNotFound'] = PRODUCTS_IMAGE_NO_IMAGE;
    $_ZM_SETTINGS['imgSuffixMedium'] = IMAGE_SUFFIX_MEDIUM;
    $_ZM_SETTINGS['imgSuffixLarge'] = IMAGE_SUFFIX_LARGE;

    // meta
    $_ZM_SETTINGS['metaTagKeywordDelimiter'] = ', ';
    $_ZM_SETTINGS['metaTagCrumbtrailDelimiter'] = ' - ';
    $_ZM_SETTINGS['metaTitleDelimiter'] = ' :: ';
    $_ZM_SETTINGS['metaTitlePrefix'] = 'title_';
    $_ZM_SETTINGS['isStoreNameInTitle'] = true;

    // flags :)
    $_ZM_SETTINGS['flagMaxColumns'] = MAX_LANGUAGE_FLAGS_COLUMNS;

    // others
    $_ZM_SETTINGS['textOptionPrefix'] = TEXT_PREFIX;
    $_ZM_SETTINGS['uploadOptionPrefix'] = UPLOAD_PREFIX;

?>
