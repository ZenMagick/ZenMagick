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
 */
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=<?php echo ZMSettings::get('zenmagick.mvc.html.charset') ?>">
    <title><?php _vzm('ZenMagick Admin') ?></title>
    <?php $resources->cssFile('style/zenmagick.css') ?>
    <?php $resources->cssFile('style/jquery-ui-lightness/jquery-ui-1.8.1.custom.css') ?>
    <?php $resources->jsFile('js/jquery-1.4.2.min.js') ?>
    <?php $resources->jsFile('js/jquery-ui-1.8.1.custom.min.js') ?>
    <?php $resources->jsFile('js/jquery.form.js') ?>
    <?php $resources->jsFile('js/zenmagick.js', ZMViewUtils::FOOTER) ?>
  </head>
  <body id="p-<?php echo $request->getRequestId() ?>">
    <div id="main">
      <?php echo $this->fetch('header.php'); ?>
      <?php if (ZMMessages::instance()->hasMessages()) { ?>
          <ul id="messages">
          <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
              <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
          <?php } ?>
          </ul>
      <?php } ?>
      <div id="content">
        <?php echo $this->fetch($viewTemplate); ?>
      </div>
      <?php echo $this->fetch('footer.php'); ?>
    </div>
  </body>
</html>
