<?php

    // build full zen-cart menu
    ZMAdminMenu::addItem(new ZMAdminMenuItem(null, 'config', zm_l10n_get('Configuration'), null));
    $configGroups = ZMConfig::instance()->getConfigGroups();
    foreach ($configGroups as $group) {
        if ($group->isVisible()) {
            $id = strtolower($group->getName());
            $id = str_replace(' ', '', $id);
            $id = str_replace('/', '-', $id);
            ZMAdminMenu::addItem(new ZMAdminMenuItem('config', $id, zm_l10n_get($group->getName()), 'configuration.php?gID='.$group->getId()));
        }
    }

    //ZMAdminMenu::buildMenu();

?>
