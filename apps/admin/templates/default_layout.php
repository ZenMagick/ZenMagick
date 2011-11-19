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
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=<?php echo $settings->get('zenmagick.mvc.html.charset') ?>">
    <title><?php _vzm('ZenMagick Admin') ?></title>
    <link rel="shortcut icon" href="<?php echo $this->asUrl('favicon.ico', ZMView::RESOURCE) ?>">
    <?php $resources->cssFile('style/zenmagick.css') ?>
    <?php $resources->cssFile('style/jquery-ui/jquery-ui-1.8.15.custom.css') ?>
    <?php $resources->cssFile('style/jquery.cluetip.css') ?>
    <?php $resources->jsFile('js/jquery-1.6.2.min.js') ?>
    <?php $resources->jsFile('style/jquery-ui/jquery-ui-1.8.15.custom.min.js') ?>
    <?php $resources->jsFile('js/jquery.form.js') ?>
    <?php $resources->jsFile('js/jquery.cluetip.min.js') ?>
    <?php $resources->jsFile('js/zenmagick.js') ?>
  </head>
  <body id="p-<?php echo $request->getRequestId() ?>">
    <div id="main">
      <?php echo $this->fetch('header.php'); ?>
      <div id="content">
          <?php echo $this->fetch('messages.php'); ?>
          <?php echo $this->fetch($viewTemplate); ?>
          <br clear="left">
        </div><!-- view-container -->
      </div><!-- content -->
      <?php echo $this->fetch('footer.php'); ?>
    </div>
  </body>
    <script>
      $('.tt[title]').cluetip({clickThrough: true, splitTitle: '|', arrows: true });
      ZenMagick.datepicker();
    </script>
</html>
