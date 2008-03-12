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
 */
?>
<?php

/**
 * Init plugin to set up the language.
 *
 * @package org.zenmagick.plugins.init
 * @author DerManoMann
 * @version $Id$
 */
class zm_init_language extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Language', 'Set the session language');
        $this->setScope(ZM_SCOPE_STORE);
        $this->setPreferredSortOrder(15);
    }

    /**
     * Create new instance.
     */
    function zm_init_language() {
        $this->__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Init this plugin.
     */
    function init() {
        parent::init();

        $session = ZMRequest::getSession();
        if (null == ($language = $session->getLanguage()) || 0 != ($languageCode = ZMRequest::getLanguageCode())) {
            if (0 != $languageCode) {
                // URL parameter takes precedence
                $language = ZMLanguages::instance()->getLanguageForCode($languageCode);
            } else {
                if (zm_setting('isUseBrowserLanguage')) {
                    $language = $this->getClientLanguage();
                } else {
                    $language = ZMLanguages::instance()->getLanguageForCode(zm_setting('defaultLanguageCode'));
                }
            }
            if (null == $language) {
                $language = $this->create("Language");
                $language->setId(1);
                $language->setDirectory('english');
                $language->setCode('en');
                ZMObject::log('invalid language - defaulting to en', ZM_LOG_WARN);
            }

            $session->setLanguage($language);
        }
    }


    /**
     * Determine the browser language.
     *
     * <p>As found at <a href="http://zencart-solutions.palek.cz/en/multilanguage-zencart/default-language-by-browser.html">http://zencart-solutions.palek.cz/en/multilanguage-zencart/default-language-by-browser.html</a>.</p>
     *
     * @return ZMLanguage The preferred language based on request headers or <code>null</code>.
     */
    function getClientLanguage() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // build list of language identifiers
            $browser_languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            
            // build list of language substitutions
            if (defined('BROWSER_LANGUAGE_SUBSTITUTIONS') && BROWSER_LANGUAGE_SUBSTITUTIONS != '') {
                $substitutions = explode(',', BROWSER_LANGUAGE_SUBSTITUTIONS);
                $language_substitutions = array();
                for ($i = 0; $i < count($substitutions); $i++) {
                    $subst = explode(':', $substitutions[$i]);
                    $language_substitutions[trim($subst[0])] = trim($subst[1]);
                }
            }

            for ($i=0, $n=sizeof($browser_languages); $i<$n; $i++) {
                // separate the clear language identifier from possible language quality (q param)
                $lang = explode(';', $browser_languages[$i]);
                
                if (strlen($lang[0]) == 2) {
                    // 2 letter only language code (code without subtags)
                    $code = $lang[0];
                
                } elseif (strpos($lang[0], '-') == 2 || strpos($lang[0], '_') == 2) {
                    // 2 letter language code with subtags
                    // use only language code and throw out all possible subtags
                    // the underscore is not RFC3036 and RFC4646 valid, but sometimes used and acceptable in this case
                    $code = substr($lang[0], 0, 2);
                } else {
                    // ignore all other language identifiers
                    $code = '';
                }

                if (null != ($language = (ZMLanguages::instance()->getLanguageForCode($code)))) {
                    // found!
                    return $language;
                } elseif (isset($language_substitutions[$code])) {
                    // try fallback to substitue
                    $code = $language_substitutions[$code];
                    if (null != ($language = (ZMLanguages::instance()->getLanguageForCode($code)))) {
                        // found!
                        return $language;
                    }
                }
            }
        }

        return null;
    }

}

?>
