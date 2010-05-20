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

<div style="float:left;padding:3px 12px;">
  <img src="<?php echo $this->asUrl('images/logo-235x64.png', ZMView::RESOURCE) ?>" alt="logo">
</div>
<?php if ($request->getUser()) { ?>
  <div style="float:left;">
    <p><a href="<?php echo DIR_WS_CATALOG ?>" target="_blank">Storefront</a> | <a href="<?php echo DIR_WS_ADMIN ?>index.php">OLD Admin</a></p>
    <p>
      <a href="<?php echo $admin2->url('index') ?>">Home</a> |
      <a href="<?php echo $admin2->url('installation') ?>">Installation</a> |
      <a href="<?php echo $admin2->url('plugins') ?>">Pugins</a> |
      <a href="<?php echo $admin2->url('catalog_manager') ?>">Catalog Manager</a> |
      <a href="<?php echo $admin2->url('cache_admin') ?>">Cache Admin</a> |
      <a href="<?php echo $admin2->url('ezpages') ?>">EZPages Editor</a> |
      <a href="<?php echo $admin2->url('static_page_editor') ?>">Static Page Editor</a> |
      <a href="<?php echo $admin2->url('edit_user') ?>">Change User Details</a> |
      <a href="<?php echo $admin2->url('about') ?>">About</a> |
      <a href="<?php echo $admin2->url('logoff') ?>">Logoff</a>
    </p>
  </div>
<?php } ?>
<hr style="clear:left;">
