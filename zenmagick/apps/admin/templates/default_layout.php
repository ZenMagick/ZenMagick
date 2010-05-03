<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 *
 * $Id$
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=<?php echo ZMSettings::get('zenmagick.mvc.html.charset') ?>">
    <title>ZenMagick Admin</title>
    <!--TODO:::-->
    <base href="<?php echo ($request->isSecure() ?'https':'http') ?>://lasse.net<?php echo dirname($request->getContext()).'/zenmagick/apps/admin/web/' ?>">
    <link  rel="stylesheet" type="text/css" href="style/zenmagick.css">
    <link  rel="stylesheet" type="text/css" href="js/jquery/jquery.treeview.css">
    <script src="js/zenmagick.js"></script>
    <script src="js/jquery/jquery-1.3.2.min.js"></script>
    <?php $utils->cssFile('js/jquery/jquery.treeview.css') ?>
    <?php $utils->jsNow('js/zenmagick.js') ?>
    <?php $utils->jsNow('js/jquery/jquery-1.3.2.min.js') ?>
  </head>
  <body>
    <?php if (ZMMessages::instance()->hasMessages()) { ?>
        <ul id="messages">
        <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
            <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
        <?php } ?>
        </ul>
    <?php } ?>

    <div id="main">
      <p>
        <a href="/zenmagick/admin/index.php">ZCAdmin</a> |
        <a href="<?php echo $admin2->url('index') ?>">Home</a> |
        <a href="<?php echo $admin2->url('installation') ?>">Installation</a> |
        <a href="<?php echo $admin2->url('plugins') ?>">Pugins</a> |
        <a href="<?php echo $admin2->url('catalog_manager') ?>">Catalog Manager</a> |
        <a href="<?php echo $admin2->url('cache_admin') ?>">Cache Admin</a> |
        <a href="<?php echo $admin2->url('static_page_editor') ?>">Static Page Editor</a> |
        <a href="<?php echo $admin2->url('about') ?>">About</a> |
        <a href="<?php echo $admin2->url('logoff') ?>">Logoff</a>
      </p>
      <div id="content">
        <?php echo $this->fetch($viewTemplate); ?>
      </div>
    </div>

  </body>
</html>
