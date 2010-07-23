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

    // top level
    ZMAdminMenu::setItem(array('requestId' => 'index', 'title' => _zm('Dashboard')));
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
    ZMAdminMenu::setItem(array('parentId' => 'tools-admin', 'requestId' => 'manage_roles', 'title' => _zm('Manage Roles')));

    ZMAdminMenu::setItem(array('parentId' => 'tools', 'id' => 'tools-dev', 'title' => _zm('Development')));
    ZMAdminMenu::setItem(array('parentId' => 'tools-dev', 'requestId' => 'l10n', 'title' => _zm('Translation Helper')));
    ZMAdminMenu::setItem(array('parentId' => 'tools-dev', 'requestId' => 'theme_builder', 'title' => _zm('Theme Builder')));
    ZMAdminMenu::setItem(array('parentId' => 'tools-dev', 'requestId' => 'console', 'title' => _zm('Console')));

    // configuration
    ZMAdminMenu::setItem(array('parentId' => 'configuration', 'id' => 'configuration-patches', 'title' => _zm('Patches')));
    ZMAdminMenu::setItem(array('parentId' => 'configuration-patches', 'requestId' => 'installation', 'title' => _zm('Patches')));
    ZMAdminMenu::setItem(array('parentId' => 'configuration', 'id' => 'configuration-cache', 'title' => _zm('Cache')));
    ZMAdminMenu::setItem(array('parentId' => 'configuration-cache', 'requestId' => 'cache_admin', 'title' => _zm('Cache Admin')));

    // plugins (the generic part)
    ZMAdminMenu::setItem(array('parentId' => 'plugins', 'id' => 'plugins-options', 'title' => _zm('Plugin Options')));
    // these are set in Plugin::addMenuItem2

    // fulfilment
    ZMAdminMenu::setItem(array('parentId' => 'fulfilment', 'id' => 'fulfilment-accounts', 'title' => _zm('Accounts')));
    ZMAdminMenu::setItem(array('parentId' => 'fulfilment-accounts', 'requestId' => 'accounts', 'title' => _zm('Overview'), 'other' => array('account')));
    ZMAdminMenu::setItem(array('parentId' => 'fulfilment', 'id' => 'fulfilment-orders', 'title' => _zm('Orders')));
    ZMAdminMenu::setItem(array('parentId' => 'fulfilment-orders', 'requestId' => 'orders', 'title' => _zm('Overview'), 'other' => array('order')));


    // legacy options
    ZMAdminMenu::setItem(array('parentId' => 'configuration', 'id' => 'configuration-legacy', 'title' => _zm('Options')));
    $configGroups = ZMConfig::instance()->getConfigGroups();
    foreach ($configGroups as $group) {
        if ($group->isVisible()) {
            $id = strtolower($group->getName());
            $id = str_replace(' ', '', $id);
            $id = str_replace('/', '-', $id);
            ZMAdminMenu::setItem(array('parentId' => 'configuration-legacy', 'requestId' => 'legacy-config', 'params' => 'groupId='.$group->getId(), 'title' => _zm($group->getName())));
        }
    }
