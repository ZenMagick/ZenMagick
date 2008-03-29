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

    /**************************************
     * System options.
     */

        // version
        'ZenMagickVersion' => '${zenmagick.version}',

        // use ZenMagick templating
        'isEnableZenMagick' => true,

        // are we in admin or storefront?
        'isAdmin' => defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG,

        // legacy API is initializing all zm_ globals
        'isLegacyAPI' => false,

        // whether to strip code in core.php
        'isStripCore' => true,

        // enable auto patching during installation
        'isEnablePatching' => true,

        // legacy API is initializing all zm_ globals
        'isLegacyAPI' => false,

        // database provider class
        'dbProvider' => 'ZMZenCartDatabase',

        // default access level; same as anonymous
        'defaultAccessLevel' => null,

        // language support for ez-pages; this is API only, zen-cart does not use this
        'isEZPagesLangSupport' => false,

        // show products in Catalog Manager tree or not
        'admin.isShowCatalogTreeProducts' => true,

        // sanitize attributes when handling product submissions (add to cart, etc)
        'isSanitizeAttributes' => true,

        // look for define pages in theme folder
        'isZMDefinePages' => true,

        // default to default in ZM themes
        'isEnableThemeDefaults' => true,

        // template suffix/extension
        'templateSuffix' => '.php',

        // enable gift vouchers 
        'isEnabledGV' => MODULE_ORDER_TOTAL_GV_STATUS == 'true',

        // enable coupons
        'isEnabledCoupons' => MODULE_ORDER_TOTAL_COUPON_STATUS == 'true',

        // allow anonymous tell a friend; good for spamming ;)
        'isTellAFriendAnonymousAllow' => ALLOW_GUEST_TO_TELL_A_FRIEND == 'true',

        // do reviews need to be approved
        'isApproveReviews' => REVIEWS_APPROVAL == '1',

        // customer approval default value
        'defaultCustomerApproval' => CUSTOMERS_APPROVAL_AUTHORIZATION,



    /**************************************
     * Logging options.
     */

        // overal log leve
        'logLevel' => ZM_LOG_INFO,

        // enable/disable loggin
        'isLogEnabled' => false,

        // filename for custom logfile
        'zmLogFilename' => null,

        // whether to use ZenMagick error handler for logging (using the filename)
        'isZMErrorHandler' => false,

        // whether to log missing settings
        'isLogMissingSettings' => false,


    /**************************************
     * Stock handling
     */

        // enable stock tracking
        'isEnableStock' => STOCK_CHECK == 'true',

        // allow checkout of low stock products (low meaning 'out of stock')
        'isAllowLowStockCheckout' => STOCK_ALLOW_CHECKOUT == 'true',



    /**************************************
     * Error pages, other global page settings
     */

        // will be used if the original view is not valid/does not exist
        'missingPageId' => 'error',

        // redirect page for invalid sessions
        'invalidSessionPage' => FILENAME_COOKIE_USAGE,

        // static homepage
        'staticHome' => null,

        // use category page
        'isUseCategoryPage' => false,

        // show category listing for single products or show product page instead?
        'isSkipSingleProductCategory' => SKIP_SINGLE_PRODUCT_CATEGORIES == 'True',



    /**************************************
     * Guest checkout
     */

        // guest checkout
        'isGuestCheckout' => false,
        'isLogoffGuestAfterOrder' => false,



    /**************************************
     * Ajax checkout
     */

        // echo JSON response
        'isJSONEcho' => true,

        // put JSON response in JSON header
        'isJSONHeader' => false,

        // default format; this is taken as method suffix to resolve Ajax methods
        'ajaxFormat' => 'JSON',



    /**************************************
     * formatting and other defaults
     */

        // decimal places for quantity
        'qtyDecimals' => QUANTITY_DECIMALS,

        // language detection strategy
        'isUseBrowserLanguage' => LANGUAGE_DEFAULT_SELECTOR == 'Browser',

        // default language
        'defaultLanguageCode' => DEFAULT_LANGUAGE,

        // result list 
        'defaultResultListPagination' => 10,

        // cart form constants
        'textOptionPrefix' => TEXT_PREFIX,
        'uploadOptionPrefix' => UPLOAD_PREFIX,

        // default/store currency
        'defaultCurrency' => DEFAULT_CURRENCY,
        'textCurrencyMapping' => CURRENCIES_TRANSLATIONS,

        // discount decimals
        'discountDecimals' => SHOW_SALE_DISCOUNT_DECIMALS,

        // price calucaltion decimals for rounding
        'calculationDecimals' => 4,

        // min length for coupon code generation
        'couponCodeLength' => SECURITY_CODE_LENGTH,



    /**************************************
     * Tax settings
     */

        // tax decimal places
        'taxDecimalPlaces' => TAX_DECIMAL_PLACES,

        // tax inclusive/exclusive
        'isTaxInclusive' => DISPLAY_PRICE_WITH_TAX == 'true',

        // product tax base
        'productTaxBase' => STORE_PRODUCT_TAX_BASIS, //shipping,billing,store

        // shipping tax base
        'shippingTaxBase' => STORE_SHIPPING_TAX_BASIS, //shipping,billing,store



    /**************************************
     * RSS settings
     */

        // cache folder
        'rssCacheDir' => DIR_FS_SQL_CACHE."/zenmagick/rss/",

        // cache TTL
        'rssCacheTimeout' => 1200,




        // HTML generation / validation
        //'isXHTML' => true,
        'isJSTarget' => true,
        'isAutoJSValidation' => true,
        'isEchoHTML' => true,



    /**************************************
     * Security and session
     */

        // use SSL
        'isEnableSSL' => ENABLE_SSL == 'true',

        // force use of SSL
        'isEnforceSSL' => true,

        // cookies only?
        'isForceCookieUse' => SESSION_FORCE_COOKIE_USE == 'True',

        // recreate sessions?
        'isSessionRecreate' => SESSION_RECREATE == 'True',

        // minimum length of passwords
        'minPasswordLength' => ENTRY_PASSWORD_MIN_LENGTH < 6 ? 6 : ENTRY_PASSWORD_MIN_LENGTH,

        'isResolveClientIP' => SESSION_IP_TO_HOST_ADDRESS == 'true',



    /**************************************
     * EMAIL
     */

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



    /**************************************
     * Layout/API behaviour
     */

        // max 
        'maxBestSellers' => MAX_DISPLAY_BESTSELLERS,
        'maxSpecialProducts' => MAX_RANDOM_SELECT_SPECIALS,
        'maxNewProducts' => SHOW_NEW_PRODUCTS_LIMIT,
        'maxRandomReviews' => MAX_RANDOM_SELECT_REVIEWS,

        // TODO: (depends on sorter/filter?) sort default
        'defaultProductSortOrder' => 'price',
        // sort attributes by name rather than the sort order
        'isSortAttributesByName' => PRODUCTS_OPTIONS_SORT_ORDER != '0',
        // sort attribute values by name rather than sort order
        'isSortAttributeValuesByPrice' => PRODUCTS_OPTIONS_SORT_BY_PRICE != '1',

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



    /**************************************
     * Store info
     */

        'storeOwner' => STORE_OWNER,
        'storeName' => STORE_NAME,
        'storeNameAddress' => STORE_NAME_ADDRESS,
        'storeCountry' => STORE_COUNTRY,
        'storeZone' => STORE_ZONE,
        'storeEmail' => STORE_OWNER_EMAIL_ADDRESS,
        'storeEmailFrom' => EMAIL_FROM,



    /**************************************
     * TODO: These are free shipping ot options!
     */

        'isOrderTotalFreeShipping' => defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true',
        'freeShippingDestination' => MODULE_ORDER_TOTAL_SHIPPING_DESTINATION,
        'freeShippingOrderThreshold' => MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER,

 

    /**************************************
     * Banner
     */

        'bannerGroup1' => SHOW_BANNERS_GROUP_SET1,
        'bannerGroup2' => SHOW_BANNERS_GROUP_SET2,
        'bannerGroup3' => SHOW_BANNERS_GROUP_SET3,
        'bannerGroup4' => SHOW_BANNERS_GROUP_SET4,
        'bannerGroup5' => SHOW_BANNERS_GROUP_SET5,
        'bannerGroup6' => SHOW_BANNERS_GROUP_SET6,
        'bannerGroup7' => SHOW_BANNERS_GROUP_SET7,
        'bannerGroup8' => SHOW_BANNERS_GROUP_SET8,
        'bannerGroupAll' => SHOW_BANNERS_GROUP_SET_ALL,



    /**************************************
     * Image settings
     */

        // show 'no image found' image
        'isShowNoPicture' => PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1',

        // the 'no image found' image
        'imgNotFound' => PRODUCTS_IMAGE_NO_IMAGE,

        // suffix for medium size images
        'imgSuffixMedium' => IMAGE_SUFFIX_MEDIUM,

        // suffix for large size images
        'imgSuffixLarge' => IMAGE_SUFFIX_LARGE,



    /**************************************
     * Meta tag options
     */

        // keyword delimiter
        'metaTagKeywordDelimiter' => ', ',

        // delimiter for meta tag crumbtrail content
        'metaTagCrumbtrailDelimiter' => ' - ',

        // meta tag title delimiter
        'metaTitleDelimiter' => ' :: ',

        // setting prefix to lookup custom meta tag data; example 'title_index'
        'metaTitlePrefix' => 'title_',

        // add store name to title
        'isStoreNameInTitle' => true

   ));

?>
