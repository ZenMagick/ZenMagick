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
<?php $view->extend('::base.html.php'); ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="<?php echo $settingsService->get('zenmagick.http.html.charset') ?>" />
    <title><?php $view['slots']->output('title', _zm('ZenMagick Admin')) ?></title>
    <link rel="shortcut icon" href="<?php echo $this->asUrl('resource:favicon.ico') ?>">
    <?php $resources->cssFile('style/zenmagick.css') ?>
    <?php $resources->cssFile('style/jquery-ui/jquery-ui-1.8.15.custom.css') ?>
    <?php $resources->cssFile('style/jquery.cluetip.css') ?>
    <?php $resources->cssFile('style/views/'.$request->getRequestId().'.css') ?>
    <?php $resources->jsFile('js/jquery-1.6.2.min.js') ?>
    <?php $resources->jsFile('style/jquery-ui/jquery-ui-1.8.15.custom.min.js') ?>
    <?php $resources->jsFile('js/jquery.form.js') ?>
    <?php $resources->jsFile('js/jquery.cluetip.min.js') ?>
    <?php $resources->jsFile('js/zenmagick.js') ?>
  </head>
  <body id="p-<?php echo $request->getRequestId() ?>">
    <div id="main">
      <?php echo $this->render('AdminBundle::header.html.php'); ?>
      <div id="content">
          <?php echo $this->render('AdminBundle::messages.html.php'); ?>
        <?php $view['slots']->output('_content'); ?>
        </div><!-- view-container -->
      </div><!-- content -->
      <?php echo $this->render('AdminBundle::footer.html.php'); ?>
    </div>
    <script type="text/javascript">
      $('.tt[title]').cluetip({clickThrough: true, splitTitle: '|', arrows: true });
      ZenMagick.datepicker();
    </script>
  </body>
</html>
