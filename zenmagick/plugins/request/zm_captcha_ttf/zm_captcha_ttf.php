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
 */
?>
<?php

define('ZM_CAPTCHA_TTF_FIELD', 'captcha');


/**
 * Plugin to enable support for CAPTCHA TTF in ZenMagick.
 *
 * @package net.radebatz.zenmagick.plugins.zm_patcha_ttf
 * @author mano
 * @version $Id$
 */
class zm_captcha_ttf extends ZMPlugin {
    var $captcha_;
    // page => (status, form_name)
    var $pageConfig_ = array(
        'create_account' => array(CAPTCHA_CREATE_ACCOUNT, 'create_account'),
        'contact_us' => array(CAPTCHA_CONTACT_US, 'contact_us'),
        'tell_a_friend' => array(CAPTCHA_TELL_A_FRIEND, 'tell_a_friend'),
        'links_submit' => array(CAPTCHA_LINKS_SUBMIT, 'links_submit'),
        'product_reviews_write' => array(CAPTCHA_REVIEWS_WRITE, 'products_reviews_write')
    );
    var $captchaEnabled_;


    /**
     * Default c'tor.
     */
    function zm_captcha_ttf() {
        parent::__construct('ZenMagick CAPTCHA TTF Plugin', 'CAPTCHA TTF support for ZenMagick');
        $this->setLoaderSupport('ALL');
        $this->captchaEnabled_ = false;
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->zm_captcha_ttf();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Install this plugin.
     */
    function install() {
        parent::install();
        zm_sql_patch(file($this->getPluginDir()."install.sql"), $this->messages_);
    }

    /**
     * Remove this plugin.
     *
     * @param bool keepSettings If set to <code>true</code>, the settings will not be removed; default is <code>false</code>.
     */
    function remove($keepSettings=false) {
        parent::remove($keepSettings);
        zm_sql_patch(file($this->getPluginDir()."uninstall.sql"), $this->messages_);
    }


    /**
     * Init this plugin.
     */
    function init() {
    global $zm_request, $zm_validator;

        parent::init();

        // check if we need to do anything for this request...
        $page = $zm_request->getPageName();
        if (array_key_exists($page, $this->pageConfig_)) {
            $this->captcha_ = new pcaptcha();
            $session = new ZMSession();
            $session->setValue('captcha_field', ZM_CAPTCHA_TTF_FIELD);
            $config = $this->pageConfig_[$page];
            if ('false' != $config[0]) {
                // active for this page
                $this->captchaEnabled_ = true;
                $zm_validator->addRule($config[1], new ZMRequiredRule(ZM_CAPTCHA_TTF_FIELD, 'Please enter the captcha.'));
                $captchaRule = $this->create("WrapperRule", ZM_CAPTCHA_TTF_FIELD, 'The entered captcha is not correct.');
                $captchaRule->setFunction('zm_captcha_ttf_validate');
                $zm_validator->addRule($config[1], $captchaRule);
            }
        }
    }


    /**
     * Get the captcha instance.
     *
     * @return captcha The captcha.
     */
    function &getCaptcha() {
        return $this->captcha_;
    }

    /**
     * Check if captcha is enabled for this request.
     *
     * @return bool <code>true</code> if the captcha is enabled, <code>false</code> if not.
     */
    function isCaptchaEnabled() {
        return $this->captchaEnabled_;
    }

    /**
     * Create the captcha image.
     *
     * <p>This is a copnvenience method around <code>$captcha->img()</code>.</p>
     */
    function showImage() {
        echo $this->captcha_->img();
    }

}


/**
 * Validate the captcha value.
 *
 * @package net.radebatz.zenmagick.plugins.zm_patcha_ttf
 * @param string page The page name.
 */
function zm_captcha_ttf_validate($req) {
global $zm_request, $zm_captcha_ttf;

    if (zm_is_empty($zm_request->getParameter(ZM_CAPTCHA_TTF_FIELD))) {
        // we have a required rule, so no need for additional checks
        return true;
    }
    $captcha = $zm_captcha_ttf->getCaptcha();
    return $captcha->validateCaptchaCode();
}

?>
