<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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

    //TODO: this is default as the password column is restricted to 40 chars
    ZMAuthenticationManager::instance()->addProvider('ZenCartAuthentication', true);


    class ZMViewFixer {
        public function onZMViewStart($args) {
            $request = $args['request'];
            $view = $args['view'];
            $view->setVar('currentLanguage', $request->getSelectedLanguage());
        }
    }

    ZMEvents::instance()->attach(new ZMViewFixer());


    // set up default menu
    ZMAdminMenu::setItem(array('requestId' => 'dashboard', 'title' => _zm('Dashboard')));
    ZMAdminMenu::setItem(array('requestId' => 'catalog', 'title' => _zm('Catalog')));
    ZMAdminMenu::setItem(array('requestId' => 'fulfilment', 'title' => _zm('Fulfilment')));
    ZMAdminMenu::setItem(array('requestId' => 'reports', 'title' => _zm('Reports')));
    ZMAdminMenu::setItem(array('requestId' => 'configuration', 'title' => _zm('Configuration')));
    ZMAdminMenu::setItem(array('requestId' => 'plugins', 'title' => _zm('Plugins')));
    ZMAdminMenu::setItem(array('requestId' => 'tools', 'title' => _zm('Tools')));

    // tools
    ZMAdminMenu::setItem(array('parentId' => 'tools', 'id' => 'tools-cms','title' => _zm('Manage Content')));
    ZMAdminMenu::setItem(array('parentId' => 'tools-cms', 'requestId' => 'static_page_editor', 'title' => _zm('Static Page Editor')));
    ZMAdminMenu::setItem(array('parentId' => 'tools-cms', 'requestId' => 'ezpages', 'title' => _zm('EZPages Editor')));

    ZMAdminMenu::setItem(array('parentId' => 'tools', 'id' => 'tools-admin', 'title' => _zm('Admin')));
    ZMAdminMenu::setItem(array('parentId' => 'tools-admin', 'requestId' => 'admin_users', 'title' => _zm('Manage Users'), 'other' => array('edit_admin_user')));

    ZMAdminMenu::setItem(array('parentId' => 'tools', 'id' => 'tools-dev', 'title' => _zm('Development')));
    ZMAdminMenu::setItem(array('parentId' => 'tools-dev', 'requestId' => 'l10n', 'title' => _zm('Translation Helper')));
    ZMAdminMenu::setItem(array('parentId' => 'tools-dev', 'requestId' => 'console', 'title' => _zm('Console')));

