<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

    //** db **//
    define('ZM_TABLE_TOKEN', DB_PREFIX . 'token');
    define('ZM_TABLE_ADMIN_ROLES', DB_PREFIX . 'admin_roles');
    define('ZM_TABLE_ADMINS_TO_ROLES', DB_PREFIX . 'admins_to_roles');
    define('ZM_TABLE_ADMIN_PREFS', DB_PREFIX . 'admin_prefs');
    define('ZM_TABLE_SACS_PERMISSIONS', DB_PREFIX . 'sacs_permissions');

    //** others **//
    if (!defined('PRODUCTS_OPTIONS_TYPE_SELECT')) {
        define('PRODUCTS_OPTIONS_TYPE_SELECT', 0);
    }
    if (!defined('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL')) {
        define('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL', 0);
    }


    /**
     * Set up default setting.
     *
     * <p>The reason for this being wrapped in a function is to make it possible
     * to include in <code>core.php</code>. Also, this leaves the option of
     * alternative storage to improve loading time.</p>
     *
     * @package zenmagick.store.shared
     */
    function zm_get_default_settings() {
        $map = array(
            /*** cache ***/
            'zenmagick.core.cache.provider.file.baseDir' => dirname(zenmagick\base\Runtime::getInstallationPath()).'/cache/zenmagick/',

            /*** security ***/
            'zenmagick.core.authentication.minPasswordLength' => ENTRY_PASSWORD_MIN_LENGTH < 6 ? 6 : ENTRY_PASSWORD_MIN_LENGTH,

            // are we in admin or storefront?
            'isAdmin' => defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG,

            // default access level; same as anonymous
            'defaultAccessLevel' => null,

            // language support for ez-pages; this is API only, zen-cart does not use this
            'isEZPagesLangSupport' => true,

            // show products in Catalog Manager tree or not
            'admin.isShowCatalogTreeProducts' => true,

            // sanitize attributes when handling product submissions (add to cart, etc)
            'isSanitizeAttributes' => true,

            // verify category path
            'verifyCategoryPath' => false,

            // template suffix/extension
            'zenmagick.mvc.templates.ext' => '.php',

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


            // enable/disable transaction support in request processing
            'zenmagick.mvc.transactions.enabled' => false,


            // default controller and view class
            'zenmagick.mvc.controller.default' => 'ZMDefaultController',
            'zenmagick.mvc.view.defaultLayout' => 'default_layout',


            // default product association handler
            'defaultProductAssociationHandler' => 'ZMSimilarOrderProductAssociationHandler',

            // sidebox block provider
            'zenmagick.mvc.blocks.blockProviders' => 'ZMStoreBlockProvider',


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
            'zenmagick.mvc.json.echo' => true,

            // default format; this is taken as method suffix to resolve Ajax methods
            'zenmagick.mvc.ajax.format' => 'JSON',

            /**************************************
             * formatting and other defaults
             **************************************/

            // decimal places for quantity
            'qtyDecimals' => QUANTITY_DECIMALS,

            // language detection strategy
            'isUseBrowserLanguage' => LANGUAGE_DEFAULT_SELECTOR == 'Browser',

            // default language
            'defaultLanguageCode' => DEFAULT_LANGUAGE,

            // comma separated lists
            'resultListProductFilter' => 'ZMCategoryFilter,ZMManufacturerFilter',
            'resultListProductSorter' => 'ZMProductSorter',

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
            'isJSTarget' => true,
            'isAutoJSValidation' => true,


            /**************************************
             * Tax settings
             **************************************/

            // tax decimal places
            'taxDecimalPlaces' => TAX_DECIMAL_PLACES,

            // tax inclusive/exclusive
            'showPricesTaxIncluded' => DISPLAY_PRICE_WITH_TAX == 'true',

            // product tax base
            'productTaxBase' => STORE_PRODUCT_TAX_BASIS, //shipping,billing,store

            // shipping tax base
            'shippingTaxBase' => STORE_SHIPPING_TAX_BASIS, //shipping,billing,store


            /**************************************
             * RSS settings
             **************************************/

            // cache folder
            'rssCacheDir' => dirname(zenmagick\base\Runtime::getInstallationPath())."/cache/zenmagick/rss/",

            // cache TTL
            'rssCacheTimeout' => 1200,

            'zenmagick.mvc.rss.sources' => 'ZMDefaultRssFeedSource,ZMCatalogRssFeedSource',


            /**************************************
             * Security and session
             **************************************/

            // use SSL
            'zenmagick.mvc.request.secure' => ENABLE_SSL == 'true' || (defined('ENABLE_SSL_ADMIN') && ENABLE_SSL_ADMIN == 'true'),

            // force use of SSL
            'zenmagick.mvc.request.enforceSecure' => 'true',

            // cookies only?
            'isForceCookieUse' => SESSION_FORCE_COOKIE_USE == 'True',

            // recreate sessions?
            'isSessionRecreate' => SESSION_RECREATE == 'True',

            'sessionPersistence' => STORE_SESSIONS,


            'isResolveClientIP' => SESSION_IP_TO_HOST_ADDRESS == 'true',



            /**************************************
             * EMAIL
             **************************************/

            // transport
            'zenmagick.core.email.transport' => EMAIL_TRANSPORT,
            'zenmagick.core.email.smtp.host' => EMAIL_SMTPAUTH_MAIL_SERVER,
            'zenmagick.core.email.smtp.port' => EMAIL_SMTPAUTH_MAIL_SERVER_PORT,
            'zenmagick.core.email.smtp.user' => EMAIL_SMTPAUTH_MAILBOX,
            'zenmagick.core.email.smtp.password' => EMAIL_SMTPAUTH_PASSWORD,


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
            // t&c message during checkout
            'isConditionsMessage' => DISPLAY_CONDITIONS_ON_CHECKOUT == 'true',

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

    ZMSettings::addAll(zm_get_default_settings());
