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
    <meta charset="<?php echo $view->getCharset() ?>" />
    <title><?php $view['slots']->output('title', _zm('ZenMagick Admin')) ?></title>
    <link rel="shortcut icon" href="<?php echo $view['assets']->getUrl('favicon.ico') ?>">
        <?php
        // @todo move to asset groups to a configuration file
        foreach ($view['assetic']->stylesheets(
                array('bundles/admin/style/*',
                      'bundles/admin/style/jquery-ui/jquery-ui-1.8.15.custom.css',
                   'bundles/admin/style/views/*'
                ),
                array('cssrewrite')) as $url) {
                echo '<link rel="stylesheet" href="'.$view->escape($url).'" />';
        }
        // @todo move some bits to footer
        foreach ($view['assetic']->javascripts(
            array('@AdminBundle/Resources/public/js/jquery-1.6.2.min.js',
                  '@AdminBundle/Resources/public/js/jquery.cluetip.min.js',
                  '@AdminBundle/Resources/public/js/jquery.form.js',
                  '@AdminBundle/Resources/public/jquery-ui/jquery-ui-1.8.15.custom.min.js',
                  '@AdminBundle/Resources/public/js/jquery.form.js',
                  '@AdminBundle/Resources/public/js/dashboard.js',
                  '@AdminBundle/Resources/public/js/zenmagick.js',
            ),
            array()) as $url) {
                echo '<script src="'.$view->escape($url).'"></script>';
        }
        ?>
  </head>
  <body id="p-<?php echo $view['request']->getRouteId() ?>">
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
