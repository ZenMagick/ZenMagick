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
     * Default c'tor.
     */
    function __construct() {
        parent::__construct('Language', 'Set the session language');
        $this->setScope(ZM_SCOPE_STORE);
        $this->setPreferredSortOrder(15);
    }

    /**
     * Default c'tor.
     */
    function zm_init_language() {
        $this->__construct();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Init this plugin.
     */
    function init() {
    global $zm_request, $zm_languages;

        parent::init();

        $session = $zm_request->getSession();
        if (null == ($language = $session->getLanguage()) || 0 != ($languageCode = $zm_request->getLanguageCode())) {
            if (0 != $languageCode) {
                // URL parameter takes precedence
                $language = $zm_languages->getLanguageForCode($languageCode);
            } else {
                if (zm_setting('isUseBrowserLanguage')) {
                    $language = zm_get_browser_language();
                } else {
                    $language = $zm_languages->getLanguageForCode(zm_setting('defaultLanguageCode'));
                }
            }
            if (null == $language) {
                $language = $this->create("Language");
                $language->setId(1);
                $language->setDirectory('english');
                $language->setCode('en');
                zm_log('invalid language - defaulting to en', ZM_LOG_WARN);
            }

            $session->setLanguage($language);
        }
    }

}

?>
