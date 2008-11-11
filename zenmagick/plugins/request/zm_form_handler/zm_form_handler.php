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
 * Generic form handler.
 *
 * @package org.zenmagick.plugins.zm_form_handler
 * @author DerManoMann
 * @version $Id$
 */
class zm_form_handler extends ZMPlugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Form Handler', 'Generic form handler with email notification', '${plugin.version}');
        $this->setLoaderSupport('FOLDER');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Install this plugin.
     */
    function install() {
        parent::install();

        // options: page name(s);form_name?, email address
        $this->addConfigValue('Pages', 'pages', '', 'Comma separated list of page names that should be handled');
        $this->addConfigValue('Notification email address', 'adminEmail', ZMSettings::get('storeEmail'),
            'Email address for admin notifications (use store email if empty)');
        $this->addConfigValue('Notification template', 'emailTemplate', 'form_handler', 'Name of common notification email template (empty will use the page name as template)');
        $this->addConfigValue('Secure', 'secure', 'false', 'Flag *all* form urls as secure','zen_cfg_select_option(array(\'true\',\'false\'),');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();

        // mappings
        $pages = $this->get('pages');
        if (0 < strlen($pages)) {
            $pages = explode(',', $pages);
            $secure = ZMTools::asBoolean($this->get('secure'));
            foreach ($pages as $page) {
                ZMUrlMapper::instance()->setMapping($page, $page, $page, 'PageView', null, 'ZMFormHandlerController');
                ZMUrlMapper::instance()->setMapping($page, 'success', $page, 'RedirectView', ($secure ? 'secure=true' : ''), 'ZMFormHandlerController');
            }

            if ($secure) {
                // mark as secure
                foreach ($pages as $page) {
                    ZMSacsMapper::instance()->setMapping($page, ZMAccounts::ANONYMOUS);
                }
            }

            // XXX: authorization, form id?
        }
    }

}

?>
