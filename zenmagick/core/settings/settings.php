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
 *
 * $Id$
 */
?>
<?php

    ZMSettings::setAll(array(
        'isEnableZenMagick' => true,
        'ZenMagickVersion' => '${zenmagick.version}',
        'logLevel' => ZM_LOG_INFO,
        'isLogEnabled' => false,
        'zmLogFilename' => null,
        'isZMErrorHandler' => false,
        'missingPageId' => 'error',
        // whether to strip code in core.php
        'isStripCore' => true,
        // legacy API is initializing all zm_ globals
        'isLegacyAPI' => false,

        // database provider class
        'dbProvider' => 'ZMZenCartDatabase',

        // redirect page for invalid sessions
        'invalidSessionPage' => FILENAME_COOKIE_USAGE,

        // admin settings
        'admin.isShowCatalogTreeProducts' => true,

        // default access level
        'defaultAccessLevel' => null,

        // patch flags
        'isEnablePatching' => true,

        // guest account behaviour
        'isLogoffGuestAfterOrder' => true,

        'isDisplayTimerStats' => DISPLAY_PAGE_PARSE_TIME == 'true',

        // enable POST request processing for listed pages
        'postRequestEnabledList' => "login,password_forgotten,account_password,account_edit,contact_us,address_book_process,address_book_delete,create_account,tell_a_friend,product_reviews_write,account_newsletters,account_notifications,checkout_shipping_address,gv_send,gv_send_confirm",

        // sanitize attributes when handling product submissions (add to cart, etc)
        'isSanitizeAttributes' => true,

        // decimal places for quantity
        'qtyDecimals' => QUANTITY_DECIMALS,

        // look for define pages in theme folder
        'isZMDefinePages' => true,

        // language strategy
        'isUseBrowserLanguage' => LANGUAGE_DEFAULT_SELECTOR == 'Browser',
        'defaultLanguageCode' => DEFAULT_LANGUAGE,

        // static homepage
        'staticHome' => null,

        // rss config
        'rssCacheDir' => DIR_FS_SQL_CACHE."/zenmagick/rss/",
        'rssCacheTimeout' => 1200,

        // stock options
        'isEnableStock' => STOCK_CHECK == 'true',
        'isAllowLowStockCheckout' => STOCK_ALLOW_CHECKOUT == 'true',

        // HTML generation / validation
        //'isXHTML' => true,
        'isJSTarget' => true,
        'isAutoJSValidation' => true,
        'isEchoHTML' => true,

        // system
        'isAdmin' => defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG,
        'isEnableSSL' => ENABLE_SSL == 'true',
        'isEnforceSSL' => true,
        'isForceCookieUse' => SESSION_FORCE_COOKIE_USE == 'True',
        'isSessionRecreate' => SESSION_RECREATE == 'True',
        'minPasswordLength' => ENTRY_PASSWORD_MIN_LENGTH < 6 ? 6 : ENTRY_PASSWORD_MIN_LENGTH,
        'isResolveClientIP' => SESSION_IP_TO_HOST_ADDRESS == 'true',
        'couponCodeLength' => SECURITY_CODE_LENGTH,

        // email
        'isEmailEnabled' => SEND_EMAILS == 'true',
        'emailSkipList' => defined('EMAIL_MODULES_TO_SKIP') ? explode(",", constant('EMAIL_MODULES_TO_SKIP')) : array(),
        'emailTestReceiver' => (defined('DEVELOPER_OVERRIDE_EMAIL_ADDRESS') && DEVELOPER_OVERRIDE_EMAIL_ADDRESS != '') ? DEVELOPER_OVERRIDE_EMAIL_ADDRESS : null,
        'isEmailAdminExtraHtml' => ADMIN_EXTRA_EMAIL_FORMAT != 'TEXT',
        'isEmailAdminCreateAccount' => SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_STATUS == '1' && SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO != '',
        'emailAdminCreateAccount' => SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO,
        'isEmailAdminTellAFriend' => SEND_EXTRA_TELL_A_FRIEND_EMAILS_TO_STATUS == '1' and SEND_EXTRA_TELL_A_FRIEND_EMAILS_TO != '',
        'emailAdminTellAFriend' => SEND_EXTRA_TELL_A_FRIEND_EMAILS_TO,
        'isEmailAdminReview' => SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO_STATUS == '1' && SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO != '',
        'emailAdminReview' => SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO,
        'isEmailAdminGvSend' => SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_STATUS == '1' && SEND_EXTRA_GV_CUSTOMER_EMAILS_TO != '',
        'emailAdminGvSend' => SEND_EXTRA_GV_CUSTOMER_EMAILS_TO,

        // Ajax
        'isJSONEcho' => true,
        'isJSONHeader' => false,
        'ajaxFormat' => 'JSON',

        // default to default in ZM themes
        'isEnableThemeDefaults' => true,
        'templateSuffix' => '.php',

        // main layout
        'isEnableLeftColumn' => COLUMN_LEFT_STATUS != 0,
        'isEnableRightColumn' => COLUMN_RIGHT_STATUS != 0,

        'isShowCrumbtrail' => DEFINE_BREADCRUMB_STATUS == '1',
        'isShowEZHeaderNav' => EZPAGES_STATUS_HEADER == '1',
        'isShowEZBoxesNav' => EZPAGES_STATUS_SIDEBOX == '1',
        'isShowEZFooterNav' => EZPAGES_STATUS_FOOTER == '1',

        // use category page
        'isUseCategoryPage' => false,
        'isShowCategoryProductCount' => 'true' == SHOW_COUNTS,
        'isSkipSingleProductCategory' => SKIP_SINGLE_PRODUCT_CATEGORIES == 'True',

        // reviews
        'isApproveReviews' => REVIEWS_APPROVAL == '1',

        // guest checkout
        'isGuestCheckout' => false,

        // language support for ez-pages
        'isEZPagesLangSupport' => false,

        // sort default
        'defaultProductSortOrder' => 'price',

        'isSortAttributesByName' => PRODUCTS_OPTIONS_SORT_ORDER != '0',
        // sort attribute values
        'isSortAttributeValuesByPrice' => PRODUCTS_OPTIONS_SORT_BY_PRICE != '1',

        // default to random on pages that have products_id set
        'isReviewsDefaultToRandom' => true,

        // result list 
        'defaultResultListPagination' => 10,

        // page not found
        'isPageNotFoundDefinePage' => DEFINE_PAGE_NOT_FOUND_STATUS == '1',

        // tell a friend
        'isTellAFriendAnonymousAllow' => ALLOW_GUEST_TO_TELL_A_FRIEND == 'true',

        // max 
        'maxBestSellers' => MAX_DISPLAY_BESTSELLERS,
        'maxSpecialProducts' => MAX_RANDOM_SELECT_SPECIALS,
        'maxRandomReviews' => MAX_RANDOM_SELECT_REVIEWS,
        'globalNewProductsLimit' => SHOW_NEW_PRODUCTS_LIMIT,
        'maxNewProducts' => MAX_DISPLAY_NEW_PRODUCTS,

        // show privacy message
        'isPrivacyMessage' => DISPLAY_PRIVACY_CONDITIONS == 'true',

        // optional account data
        'isAccountGender' => ACCOUNT_GENDER == 'true',
        'isAccountDOB' => ACCOUNT_DOB == 'true',
        'isAccountCompany' => ACCOUNT_COMPANY == 'true',
        'isAccountState' => ACCOUNT_STATE == 'true',
        'isAccountNewsletter' => ACCOUNT_NEWSLETTER_STATUS != 0,
        'isAccountReferral' => CUSTOMERS_REFERRAL_STATUS == 2,
        'isAccountNickname' => false,

        // account
        'firstNameMinLength' => ENTRY_FIRST_NAME_MIN_LENGTH,
        'lastNameMinLength' => ENTRY_LAST_NAME_MIN_LENGTH,
        'phoneMinLength' => ENTRY_TELEPHONE_MIN_LENGTH,
        'addressMinLength' => ENTRY_STREET_ADDRESS_MIN_LENGTH,
        'postcodeMinLength' => ENTRY_POSTCODE_MIN_LENGTH,
        'cityMinLength' => ENTRY_CITY_MIN_LENGTH,
        'stateMinLength' => ENTRY_STATE_MIN_LENGTH,
        'passwordMinLength' => ENTRY_PASSWORD_MIN_LENGTH,

        // default customer approval setting
        'defaultCustomerApproval' => CUSTOMERS_APPROVAL_AUTHORIZATION,

        // product notification
        'isCustomerProductNotifications' => CUSTOMERS_PRODUCTS_NOTIFICATION_STATUS == '1',

        // downloads
        'isDownloadsEnabled' => DOWNLOAD_ENABLED == 'true',

        // default/store currency
        'defaultCurrency' => DEFAULT_CURRENCY,
        'textCurrencyMapping' => CURRENCIES_TRANSLATIONS,

        // tax
        'taxDecimalPlaces' => TAX_DECIMAL_PLACES,
        'isTaxInclusive' => DISPLAY_PRICE_WITH_TAX == 'true',
        'productTaxBase' => STORE_PRODUCT_TAX_BASIS, //shipping,billing,store
        'shippingTaxBase' => STORE_SHIPPING_TAX_BASIS, //shipping,billing,store

        'discountDecimals' => SHOW_SALE_DISCOUNT_DECIMALS,
        'calculationDecimals' => 4,

        // site map
        'isSiteMapAccountLinks' => SHOW_ACCOUNT_LINKS_ON_SITE_MAP=='Yes',

        // GV 
        'isEnabledGV' => MODULE_ORDER_TOTAL_GV_STATUS == 'true',

        // coupons
        'isEnabledCoupons' => MODULE_ORDER_TOTAL_COUPON_STATUS == 'true',

        // newsletter
        'isEnableUnsubscribeLink' => SHOW_NEWSLETTER_UNSUBSCRIBE_LINK == 'true',

        // contact us
        'isContactUsStoreAddress' => CONTACT_US_STORE_NAME_ADDRESS == '1',

        // store
        'storeOwner' => STORE_OWNER,
        'storeName' => STORE_NAME,
        'storeNameAddress' => STORE_NAME_ADDRESS,
        'storeCountry' => STORE_COUNTRY,
        'storeZone' => STORE_ZONE,
        'storeEmail' => STORE_OWNER_EMAIL_ADDRESS,
        'storeEmailFrom' => EMAIL_FROM,

        // modules/shipping
        'isOrderTotalFreeShipping' => defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true',
        'freeShippingDestination' => MODULE_ORDER_TOTAL_SHIPPING_DESTINATION,
        'freeShippingOrderThreshold' => MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER,

        // banner
        'bannerGroup1' => SHOW_BANNERS_GROUP_SET1,
        'bannerGroup2' => SHOW_BANNERS_GROUP_SET2,
        'bannerGroup3' => SHOW_BANNERS_GROUP_SET3,
        'bannerGroup4' => SHOW_BANNERS_GROUP_SET4,
        'bannerGroup5' => SHOW_BANNERS_GROUP_SET5,
        'bannerGroup6' => SHOW_BANNERS_GROUP_SET6,
        'bannerGroup7' => SHOW_BANNERS_GROUP_SET7,
        'bannerGroup8' => SHOW_BANNERS_GROUP_SET8,
        'bannerGroupAll' => SHOW_BANNERS_GROUP_SET_ALL,

        // images
        'isShowNoPicture' => PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1',
        'imgNotFound' => PRODUCTS_IMAGE_NO_IMAGE,
        'imgSuffixMedium' => IMAGE_SUFFIX_MEDIUM,
        'imgSuffixLarge' => IMAGE_SUFFIX_LARGE,

        // meta
        'metaTagKeywordDelimiter' => ', ',
        'metaTagCrumbtrailDelimiter' => ' - ',
        'metaTitleDelimiter' => ' :: ',
        'metaTitlePrefix' => 'title_',
        'isStoreNameInTitle' => true,

        // others
        'textOptionPrefix' => TEXT_PREFIX,
        'uploadOptionPrefix' => UPLOAD_PREFIX
    ));

?>
