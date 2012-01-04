<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
<div id="header">
  <div id="logo">
    <a href="<?php echo $admin2->url('index') ?>"><img src="<?php echo $this->asUrl('images/logo.png', $view::RESOURCE) ?>" alt="ZenMagick" title="ZenMagick"></a>
  </div>
  <?php if ($request->getUser()) { ?>
    <div id="header-box">
      <p id="header-state">
        <?php $userLink = '<a href="'.$admin2->url('update_user').'" onclick="ZenMagick.ajaxFormDialog(this.href, {title:\''.sprintf(_zm('User Profile: %s'), $request->getUser()->getName()).'\', formId:\'updateUser\'}); return false;">'.$request->getUser()->getName().'</a>'; ?>
        <?php _vzm('Logged in as %s', $userLink) ?>
        <?php if (!$request->getUser()->isLive()) { ?>
          <span id="demo-note"><?php _vzm('*** DEMO MODE ***') ?></span>
        <?php } ?>
        | <?php echo date('l, F d, Y') ?>
        | <a href="<?php echo $admin2->url('logoff') ?>"><?php _vzm('Log Out') ?></a>
      </p>
      <p id="header-opts">
        <a href="<?php echo $settingsService->get('apps.store.baseUrl') ?>" target="_blank">Storefront</a>
        | <a href="<?php echo  $settingsService->get('apps.store.oldAdminUrl') ?>" target="_blank">OLD Admin</a>
        | <a href="http://forum.zenmagick.org/" target="_blank"><?php _vzm('Get Help') ?></a>
        | <a href="<?php echo $admin2->url('about') ?>" onclick="ZenMagick.ajaxDialog(this.href, {title:'About ZenMagick', width:'85%', height:640}); return false;"><?php _vzm('About') ?></a>
      </p>
    </div>
  <?php } ?>
  <ul id="main-menu">
    <?php if ($request->getUser()) { ?>
      <?php $root = $adminMenu->getRootItemForRequestId($request->getRequestId()); ?>
      <?php foreach ($adminMenu->getRoot()->getChildren() as $item) { ?>
        <li<?php if (null != $root && $root->getId() == $item->getId()) { echo ' class="active"'; } ?>><a href="<?php echo $admin2->url($item->getRequestId()) ?>"><?php echo $item->getName() ?></a></li>
      <?php } ?>
    <?php } ?>
  </ul>
</div>
