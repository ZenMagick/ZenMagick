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

use ZenMagick\Base\Runtime;

// NOTE: this isn't actually set anywhere in zencart, it's only used in functions_prices. Must require extra_configures?
if (!defined('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL')) define('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL', 0);

if (!function_exists('zm_split_email_addresses')) {
    /**
     * Split email addresses as per zc convention.
     */
    function zm_split_email_addresses($s) {
        $recipients = array();
        foreach (explode(',', $s) as $address) {
            preg_match('/([^<]*)<(.*@.*)>/', $address, $token);
            if ($token) {
                $token[1] = trim($token[1]);
                $recipients[trim($token[2])] = empty($token[1]) ? null : $token[1];
            } else {
                $recipients[trim($address)] = null;
            }
        }
        return $recipients;
    }

    /**
     * Split browser languages like http://zencart-solutions.palek.cz/en/multilanguage-zencart/default-language-by-browser.html
     */
    function zm_split_language_substitutions($s) {
        $s = explode(',', $s);
        $l = array();
        foreach ($s as $alias) {
            $subst = explode(':', $alias);
            $l[trim($subst[0])] = trim($subst[1]);
        }
        return $l;
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
    function zm_get_default_settings($settingsService) {
        $map = array(
            /*** security ***/
            'zenmagick.base.authentication.minPasswordLength' => ENTRY_PASSWORD_MIN_LENGTH < 6 ? 6 : ENTRY_PASSWORD_MIN_LENGTH,

            // enable gift vouchers
            'isEnabledGV' => defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS == 'true',

            // enable coupons
            'isEnabledCoupons' => defined('MODULE_ORDER_TOTAL_COUPON_STATUS') && MODULE_ORDER_TOTAL_COUPON_STATUS == 'true',

            // allow anonymous tell a friend; good for spamming ;)
            'isTellAFriendAnonymousAllow' => defined('ALLOW_GUEST_TO_TELL_A_FRIEND') && ALLOW_GUEST_TO_TELL_A_FRIEND == 'true',

            // do reviews need to be approved
            'isApproveReviews' => REVIEWS_APPROVAL == '1',

            // customer approval default value
            'defaultCustomerApproval' => CUSTOMERS_APPROVAL_AUTHORIZATION,

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

            // show category listing for single products or show product page instead?
            'isSkipSingleProductCategory' => SKIP_SINGLE_PRODUCT_CATEGORIES == 'True',

            // show cart after product added
            'isShowCartAfterAddProduct' => DISPLAY_CART == 'true',

            // @todo store the path in the database (supporting both absolute and relative paths)
            'downloadBaseDir' => $settingsService->exists('downloadBaseDir') ? $settingsService->get('downloadBaseDir') : realpath(Runtime::getInstallationPath().'/../download'),
            // @todo downloadPubDir should really just exist in a publically accessible cache directory (like minified css/js)
            'downloadPubDir' => $settingsService->exists('downloadPubDir') ? $settingsService->get('downloadPubDir') : realpath(Runtime::getInstallationPath().'/../pub'),
            'downloadByRedirect' => DOWNLOAD_BY_REDIRECT == 'true',
            'downloadInChunks' => DOWNLOAD_IN_CHUNKS == 'true',

            /**************************************
             * formatting and other defaults
             **************************************/

            // decimal places for quantity
            'qtyDecimals' => QUANTITY_DECIMALS,

            // language detection strategy
            'isUseBrowserLanguage' => LANGUAGE_DEFAULT_SELECTOR == 'Browser',

            'apps.store.browserLanguageSubstitutions' => defined('BROWSER_LANGUAGE_SUBSTITUTIONS') ?
                zm_split_language_substitutions(BROWSER_LANGUAGE_SUBSTITUTIONS) : array(),

            // default language
            'defaultLanguageCode' => DEFAULT_LANGUAGE,

            // cart form constants
            'textOptionPrefix' => defined('TEXT_PREFIX') ? TEXT_PREFIX : 'txt_',
            'uploadOptionPrefix' => defined('UPLOAD_PREFIX') ? UPLOAD_PREFIX : 'upload_',

            'apps.store.cart.uploads' => $settingsService->exists('uploadBaseDir') ? $settingsService->get('uploadBaseDir'): realpath(Runtime::getInstallationPath().'/../images/uploads'),

            // default/store currency
            'defaultCurrency' => DEFAULT_CURRENCY,
            'textCurrencyMapping' => defined('CURRENCIES_TRANSLATIONS') ? CURRENCIES_TRANSLATIONS : '',

            // discount decimals
            'discountDecimals' => SHOW_SALE_DISCOUNT_DECIMALS,

            // min length for coupon code generation
            'couponCodeLength' => SECURITY_CODE_LENGTH,

            // base attribute price factor on discounted or regular price
            'isDiscountAttributePriceFactor' => '1' == (defined('ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL') ? ATTRIBUTES_PRICE_FACTOR_FROM_SPECIAL : '0'),

            'apps.store.pricing.text.ignoreWS' => '1' == TEXT_SPACES_FREE,

            /**************************************
             * Tax settings
             **************************************/

            // tax decimal places
            'taxDecimalPlaces' => (int)TAX_DECIMAL_PLACES,

            // tax inclusive/exclusive
            'showPricesTaxIncluded' => DISPLAY_PRICE_WITH_TAX == 'true',

            // product tax base
            'productTaxBase' => STORE_PRODUCT_TAX_BASIS, //shipping,billing,store

            // shipping tax base
            'shippingTaxBase' => STORE_SHIPPING_TAX_BASIS, //shipping,billing,store


            /**************************************
             * Security and session
             **************************************/

            'apps.store.warnBeforeMaintenance' => WARN_BEFORE_DOWN_FOR_MAINTENANCE == 'true',
            'apps.store.downForMaintenance' => DOWN_FOR_MAINTENANCE == 'true',
            'apps.store.downForMaintenanceRoute' => DOWN_FOR_MAINTENANCE_FILENAME,
            'apps.store.downForMaintenancePages' => array('logoff', 'privacy', 'contact_us', 'conditions', 'shippinginfo'),
            // @todo migrate to some better method to let admin view restricted storefront content
            'apps.store.adminOverrideIPs' => explode(',', str_replace(' ', '', EXCLUDE_ADMIN_IP_FOR_MAINTENANCE)),

            // cookies only?
            'isForceCookieUse' => SESSION_FORCE_COOKIE_USE == 'True',
            /**
             * @todo this won't work and/or doesn't matter until we support shared certificates.
             *       It's likely that we would want want to split this into 2 seperate parameters.
             * 'zenmagick.http.session.domain' => ($this->container->get('request')->isSecure()
             *                                   && defined('HTTP_COOKIE_DOMAIN') ?
             *                                      HTTP_COOKIE_DOMAIN :
             *                                      (!$this->container->get('request')->isSecure() &&
             *                                      defined('HTTPS_COOKIE_DOMAIN') ? HTTPS_COOKIE_DOMAIN : null)),
             */
            'zenmagick.http.session.domain' => null,

            'isResolveClientIP' => SESSION_IP_TO_HOST_ADDRESS == 'true',

            'zenmagick.http.session.validator.userAgent' => SESSION_CHECK_USER_AGENT == 'True',
            'zenmagick.http.session.validator.ip' => SESSION_CHECK_IP_ADDRESS == 'True',
            'zenmagick.http.session.validator.sslSessionId' => SESSION_CHECK_SSL_SESSION_ID == 'True',

            /**************************************
             * EMAIL
             **************************************/
            'emailSkipList' => defined('EMAIL_MODULES_TO_SKIP') ? explode(",", constant('EMAIL_MODULES_TO_SKIP')) : array(),
            'isEmailAdminExtraHtml' => ADMIN_EXTRA_EMAIL_FORMAT != 'TEXT',
            'isEmailAdminCreateAccount' => SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO_STATUS == '1' && SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO != '',
            'emailAdminCreateAccount' => zm_split_email_addresses(SEND_EXTRA_CREATE_ACCOUNT_EMAILS_TO),
            'isEmailAdminTellAFriend' => defined('SEND_EXTRA_TELL_A_FRIEND_EMAILS_TO_STATUS') && SEND_EXTRA_TELL_A_FRIEND_EMAILS_TO_STATUS == '1' and SEND_EXTRA_TELL_A_FRIEND_EMAILS_TO != '',
            'emailAdminTellAFriend' => zm_split_email_addresses(defined('SEND_EXTRA_TELL_A_FRIEND_EMAILS_TO') ? SEND_EXTRA_TELL_A_FRIEND_EMAILS_TO : ''),
            'isEmailAdminReview' => SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO_STATUS == '1' && SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO != '',
            'emailAdminReview' => zm_split_email_addresses(SEND_EXTRA_REVIEW_NOTIFICATION_EMAILS_TO),
            'isEmailAdminGvSend' => SEND_EXTRA_GV_CUSTOMER_EMAILS_TO_STATUS == '1' && SEND_EXTRA_GV_CUSTOMER_EMAILS_TO != '',
            'emailAdminGvSend' => zm_split_email_addresses(SEND_EXTRA_GV_CUSTOMER_EMAILS_TO),


            /**************************************
             * Layout/API behaviour
             **************************************/

            // max
            'maxBestSellers' => (int)MAX_DISPLAY_BESTSELLERS,
            'maxSpecialProducts' => (int)MAX_RANDOM_SELECT_SPECIALS,
            'maxNewProducts' => (int)SHOW_NEW_PRODUCTS_LIMIT,
            'maxRandomReviews' => (int)MAX_RANDOM_SELECT_REVIEWS,

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

            'apps.store.newAccountDiscountCouponId' => ((NEW_SIGNUP_DISCOUNT_COUPON != '' && NEW_SIGNUP_DISCOUNT_COUPON != '0') ? NEW_SIGNUP_DISCOUNT_COUPON : null),
            'apps.store.newAccountGVAmount' => NEW_SIGNUP_GIFT_VOUCHER_AMOUNT,

            // optional account data
            'isAccountGender' => ACCOUNT_GENDER == 'true',
            'isAccountDOB' => ACCOUNT_DOB == 'true',
            'isAccountCompany' => ACCOUNT_COMPANY == 'true',
            'isAccountState' => ACCOUNT_STATE == 'true',
            'isAccountNewsletter' => ACCOUNT_NEWSLETTER_STATUS != 0,
            'isAccountReferral' => CUSTOMERS_REFERRAL_STATUS == 2,

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
        );

        return $map;
    }
}

    $replace = true;
    foreach (zm_get_default_settings($settingsService) as $name => $value) {
        if ($replace || !$settingsService->exists($name)) {
            $settingsService->set($name, $value);
        }
    }
