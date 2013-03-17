<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" dir="ltr">
  <head>
    <?php echo $this->render('StorefrontBundle::head.html.php') ?>
    <?php
    // @todo move to asset groups to a configuration file
    foreach ($view['assetic']->stylesheets(
            array('bundles/storefront/css/site.css',
            ),
            array('cssrewrite', '?cssmin')) as $url) {
            echo '<link rel="stylesheet" href="'.$view->escape($url).'" />';
    }
    // @todo move some bits to footer
    foreach ($view['assetic']->javascripts(
        array(
            '@StorefrontBundle/Resources/public/js/jquery.js',
            '@StorefrontBundle/Resources/public/js/jquery.form.js',
            '@StorefrontBundle/Resources/public/js/common.js',
            '@StorefrontBundle/Resources/public/js/validation.js',
        ),
        array('?jsminplus')) as $url) {
            echo '<script src="'.$view->escape($url).'"></script>';
    }
    ?>
    <?php if (!$templateManager->isLeftColEnabled() || !$templateManager->isRightColEnabled()) { ?>
      <style type="text/css" media="screen,projection">
        <?php if (!$templateManager->isLeftColEnabled()) { ?>
          #content {margin-left:10px;}
        <?php } ?>
        <?php if (!$templateManager->isRightColEnabled()) { ?>
          body div#content {margin-right:20px;}
        <?php } ?>
      </style>
    <?php } ?>
  </head>

  <body id="b_<?php echo $view['request']->getRouteId() ?>">
    <?php define('KEYWORD_DEFAULT', _zm("search ...")); ?>
    <div id="bannerOne"><?php echo $this->fetchBlockGroup('banners.header1') ?></div>

    <div id="container">
      <?php echo $this->render('StorefrontBundle::header.html.php') ?>
      <?php echo $this->render('StorefrontBundle::menu.html.php') ?>

      <div id="leftcol">
        <?php echo $this->fetchBlockGroup('leftColumn') ?>
      </div>

      <div id="rightcol">
        <?php echo $this->fetchBlockGroup('rightColumn') ?>
      </div>

      <div id="content">
        <?php //@todo refactor buildCrumbtrail so we can pass the arguments to it. ?>
        <div id="crumbtrail"><?php echo $macro->buildCrumbtrail(null, ' &gt; ') ?></div>
        <div id="bannerThree"><?php echo $this->fetchBlockGroup('banners.header3') ?></div>

        <?php if ($view['session']->hasMessages()) { ?>
            <ul id="messages">
            <?php foreach ($view['session']->getMessages() as $message) { ?>
                <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
            <?php } ?>
            </ul>
        <?php } ?>

        <?php $view['slots']->output('_content'); ?>

        <div id="bannerFour"><?php echo $this->fetchBlockGroup('banners.footer1') ?></div>
      </div>

      <?php echo $this->render('StorefrontBundle::footer.html.php') ?>
    </div>

    <div id="bannerSix"><?php echo $this->fetchBlockGroup('banners.footer3') ?></div>

  </body>
</html>
