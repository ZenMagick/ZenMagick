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
?>
<div id="header">
  <div id="logo">
    <a href="<?php echo $view['router']->generate('admin_index') ?>"><img src="<?php echo $this->asUrl('resource:images/logo-small.png') ?>" alt="ZenMagick" title="ZenMagick"></a>
  </div>
  <?php if ($app->getUser()) { ?>
    <div id="header-box">
      <p id="header-state">
        <?php $userLink = '<a href="'.$view['router']->generate('update_user').'" onclick="ZenMagick.ajaxFormDialog(this.href, {title:\''.sprintf(_zm('User Profile: %s'), $app->getUser()->getUsername()).'\', formId:\'updateUser\'}); return false;">'.$app->getUser()->getUsername().'</a>'; ?>
        <?php _vzm('Logged in as %s', $userLink) ?>
        | <a href="<?php echo $app->getRequest()->getSchemeAndHttpHost() ?>" target="_blank">Storefront</a>
        | <?php echo date('l, F d, Y') ?>
        | <a href="<?php echo $view['router']->generate('admin_logout') ?>"><?php _vzm('Log Out') ?></a>
      </p>
      <!-- <a href="http://forum.zenmagick.org/" target="_blank"><?php _vzm('Get Help') ?></a> -->
    </div>
    <?php echo $view->container->get('knp_menu.templating.helper')->render('admin_main', array('depth' => 1)); ?>
  <?php } ?>
</div>
