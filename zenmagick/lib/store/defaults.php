<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * $Id: defaults.php 2284 2009-06-19 11:09:16Z dermanomann $
 */
?>
<?php

    /**
     * If you are changing ZM_ROOT, make sure to update 
     * ..\zen-cart\includes\init_includes\overrides\init_templates.php
     * and
     * ..\zen-cart\admin\includes\init_includes\overrides\init_templates.php
     *
     * The full order of action is:
     * 1) Uninstall all ZenMagick patches (as some use this value to generate code)
     * 2) Rename directory
     * 3) Update ZM_ROOT
     * 4) Update the files mentioned above
     * 5) Re-install plugins
     */
    define('ZM_ROOT', 'zenmagick/');
    define('ZM_DEFAULT_THEME', 'default');


    //** url page name key **//

    define('ZM_PAGE_KEY', 'main_page');


    //** db **//

    define('ZM_DB_PREFIX', DB_PREFIX);
    define('ZM_TABLE_TOKEN', ZM_DB_PREFIX . 'token');
    

    //** date/time formats used internally by all models **//

    define('ZM_DATE_FORMAT', 'yyyy-mm-dd');
    define('ZM_DATETIME_FORMAT', 'yyyy-mm-dd hh:ii:ss');


    //** others **//

    define('PRODUCTS_OPTIONS_TYPE_SELECT', 0);



    /**
     * Set up default setting.
     *
     * <p>The reason for this being wrapped in a function is to make it possible
     * to include in <code>core.php</code>. Also, this leaves the option of
     * alternative storage to improve loading time.</p>
     *
     * @package org.zenmagick.store
     */
    function zm_get_default_settings() {
        $map = array(
            /**************************************
             * System settings.
             **************************************/

            // version
            'ZenMagickVersion' => '${zenmagick.version}',

            // default database connection; 
            'zenmagick.core.database.connections.default' => array(
                'driver' => 'mysql',
                'host' => DB_SERVER,
                'username' => DB_SERVER_USERNAME,
                'password' => DB_SERVER_PASSWORD,
                'database' => DB_DATABASE,
                'initQuery' => null
            ),

            // use ZenMagick themes
            'isEnableZMThemes' => true,

            // are we in admin or storefront?
            'isAdmin' => defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG,

            // whether to strip code in core.php
            'isStripCore' => true,

            // enable auto patching during installation
            'isEnablePatching' => true,

            // database provider class
            'zenmagick.core.database.provider' => 'ZMZenCartDatabase',

            // path to db mappings, relative to ZM root
            'zenmagick.core.database.mappings.file' => 'lib/store/db_mappings.txt',

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

            // database table prefix
            'zenmagick.core.database.tablePrefix' => ZM_DB_PREFIX,

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

            // enable/disable web stats; this does not include login counts, etc, but product views and such
            'isLogPageStats' => true,

            // download base folder
            'downloadBaseDir' => DIR_FS_DOWNLOAD,

            // cache database mappings
            'zenmagick.core.database.mappings.cache.enabled' => false,

            // default authentication provider
            'defaultAuthenticationProvider' => 'ZMZenCartAuthentication',

            // persistent / transient implementation mappings
            'zenmagick.core.cache.mapping.defaults' => array(),

            // fix file permissions of created files to allow FTP access
            'fs.permissions.fix' => true,
            // default permissions; NOTE the leading 0 is required as this is octal
            'zenmagick.core.fs.permissions.file' => 0666,
            'zenmagick.core.fs.permissions.folder' => 0777,
        
            // enable/disable transaction support in request processing
            'isEnableTransactions' => false,

            // enable db table map building on demand
            'zenmagick.core.database.mappings.autoMap.enabled' => true,

            // enable/disable plugins
            'plugins.enabled' => true,

            // default controller and view class
            'defaultControllerClass' => 'DefaultController',
            'defaultViewClass' => 'PageView',

            'plugins.types' => 'init,admin,request,order_total',
        
            // default product association handler
            'defaultProductAssociationHandler' => 'SimilarOrderProductAssociationHandler',
        

            /**************************************
             * Logging options.
             **************************************/

            // logging provider
            'loggingProvider' => 'Logging',

            // overal log leve
            'logLevel' => 3,

            // enable/disable loggin
            'isLogEnabled' => false,

            // filename for custom logfile
            'zmLogFilename' => null,

            // whether to use ZenMagick error handler for logging (using the filename)
            'isZMErrorHandler' => false,


            /**************************************
             * Stock handling
             **************************************/

            // enable stock tracking
            'isEnableStock' => STOCK_CHECK == 'true',

            // allow checkout of low stock products (low meaning 'out of stock')
            'isAllowLowStockCheckout' => STOCK_ALLOW_CHECKOUT == 'true',


            /**************************************
             * Error pages, other global page settings
             **************************************/

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

            // show cart after product added
            'isShowCartAfterAddProduct' => DISPLAY_CART == 'true',


            /**************************************
             * Guest checkout
             **************************************/

            // guest checkout
            'isGuestCheckout' => false,
            'isLogoffGuestAfterOrder' => false,


            /**************************************
             * Ajax
             **************************************/

            // echo JSON response
            'isJSONEcho' => true,

            // put JSON response in JSON header
            'isJSONHeader' => false,

            // default format; this is taken as method suffix to resolve Ajax methods
            'ajaxFormat' => 'JSON',


            /**************************************
             * formatting and other defaults
             **************************************/

            // decimal places for quantity
            'qtyDecimals' => QUANTITY_DECIMALS,

            // language detection strategy
            'isUseBrowserLanguage' => LANGUAGE_DEFAULT_SELECTOR == 'Browser',

            // default language
            'defaultLanguageCode' => DEFAULT_LANGUAGE,

            // result list 
            'zenmagick.mvc.resultlist.defaultPagination' => 10,
            // comma separated lists
            'resultListProductFilter' => 'CategoryFilter,ManufacturerFilter',
            'resultListProductSorter' => 'ProductSorter',

            // cart form constants
            'textOptionPrefix' => TEXT_PREFIX,
            'uploadOptionPrefix' => UPLOAD_PREFIX,

            // default/store currency
            'defaultCurrency' => DEFAULT_CURRENCY,
            'textCurrencyMapping' => CURRENCIES_TRANSLATIONS,

            // discount decimals
            'discountDecimals' => SHOW_SALE_DISCOUNT_DECIMALS,

            // price calculation decimals for rounding
            'calculationDecimals' => 4,

            // min length for coupon code generation
            'couponCodeLength' => SECURITY_CODE_LENGTH,

            // base attribute price factor on discounted or regular price
            'isDiscountAttributePriceFactor' => '1' == ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL,

            // HTML generation / validation
            'isXHTML' => true,
            'isJSTarget' => true,
            'isAutoJSValidation' => true,
            'isEchoHTML' => true,


            /**************************************
             * Tax settings
             **************************************/

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
             **************************************/

            // cache folder
            'rssCacheDir' => DIR_FS_SQL_CACHE."/zenmagick/rss/",

            // cache TTL
            'rssCacheTimeout' => 1200,


            /**************************************
             * Security and session
             **************************************/

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

            // comma separated list of forms to be secured by a session token
            'zenmagick.mvc.validation.tokenSecuredForms' => 'login',


            /**************************************
             * EMAIL
             **************************************/

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
             **************************************/

            // max 
            'maxBestSellers' => MAX_DISPLAY_BESTSELLERS,
            'maxSpecialProducts' => MAX_RANDOM_SELECT_SPECIALS,
            'maxNewProducts' => SHOW_NEW_PRODUCTS_LIMIT,
            'maxRandomReviews' => MAX_RANDOM_SELECT_REVIEWS,

            // range of enabled order stati to show downloads
            'downloadOrderStatusRange' => DOWNLOADS_CONTROLLER_ORDERS_STATUS.'-'.DOWNLOADS_CONTROLLER_ORDERS_STATUS_END,

            // sort attributes by name rather than the sort order
            'isSortAttributesByName' => PRODUCTS_OPTIONS_SORT_ORDER != '0',
            // sort attribute values by name rather than sort order
            'isSortAttributeValuesByPrice' => PRODUCTS_OPTIONS_SORT_BY_PRICE != '1',

            // show privacy message
            'isPrivacyMessage' => DISPLAY_PRIVACY_CONDITIONS == 'true',

            // allow unsubscribe without logging in
            'isAllowAnonymousUnsubscribe' => true,

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
             **************************************/

            'storeOwner' => STORE_OWNER,
            'storeName' => STORE_NAME,
            'storeNameAddress' => STORE_NAME_ADDRESS,
            'storeCountry' => STORE_COUNTRY,
            'storeZone' => STORE_ZONE,
            'storeEmail' => STORE_OWNER_EMAIL_ADDRESS,
            'storeEmailFrom' => EMAIL_FROM,
            'storeDefaultLanguageId' => 1,


            /**************************************
             * TODO: These are free shipping ot options!
             **************************************/

            'isOrderTotalFreeShipping' => defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true',
            'freeShippingDestination' => MODULE_ORDER_TOTAL_SHIPPING_DESTINATION,
            'freeShippingOrderThreshold' => MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER,
         

            /**************************************
             * Banner
             **************************************/

            'banners.header1' => SHOW_BANNERS_GROUP_SET1,
            'banners.header2' => SHOW_BANNERS_GROUP_SET2,
            'banners.header3' => SHOW_BANNERS_GROUP_SET3,
            'banners.footer1' => SHOW_BANNERS_GROUP_SET4,
            'banners.footer2' => SHOW_BANNERS_GROUP_SET5,
            'banners.footer3' => SHOW_BANNERS_GROUP_SET6,
            'banners.box1' => SHOW_BANNERS_GROUP_SET7,
            'banners.box2' => SHOW_BANNERS_GROUP_SET8,
            'banners.all' => SHOW_BANNERS_GROUP_SET_ALL,


            /**************************************
             * Image settings
             **************************************/

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
             **************************************/

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
        );

        return $map;
    }

    ZMSettings::setAll(zm_get_default_settings());

?>
