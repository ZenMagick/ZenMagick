<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
?><?php
use zenmagick\base\ClassLoader;
use zenmagick\base\Runtime;
use zenmagick\base\events\Event;
use zenmagick\http\sacs\SacsManager;

  // name of Zen Cart admin folder
  if (!defined('ZC_ADMIN_FOLDER')) {
     define('ZC_ADMIN_FOLDER', basename(dirname(__FILE__)));
  }

  $view = null;
  require_once 'includes/application_top.php';

  $container = Runtime::getContainer();

  $classLoader = new ClassLoader();
  $classLoader->addPath(Runtime::getInstallationPath().'/apps/admin/lib');
  $classLoader->register();

  // set some admin specific things...
  ZMUrlManager::instance()->clear();
  SacsManager::instance()->reset();
  SacsManager::instance()->load(file_get_contents(ZMFileUtils::mkPath(array(Runtime::getInstallationPath(), 'apps/admin/config', 'sacs_mappings.yaml'))), false);

  if (ZMLangUtils::isEmpty($request->getRequestId())) {
      $request->setParameter('main_page', 'index');
  }

  $viewFile = 'content/views/'.$request->getRequestId().'.php';

  //XXX: buffer code, so redirects should still work...
  ob_start();

  if (!in_array($request->getRequestId(), array('login', 'logoff', 'password_forgotten'))) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title>ZenMagick Admin :: Installation</title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="content/zenmagick.css">
    <link rel="stylesheet" type="text/css" href="content/jquery/jquery.treeview.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script type="text/javascript" src="includes/menu.js"></script>
    <script type="text/javascript" src="includes/general.js"></script>
    <script type="text/javascript" src="content/zenmagick.js"></script>
    <script type="text/javascript" src="content/jquery/jquery-1.3.2.min.js"></script>
  </head>
  <body id="b_admin">

    <?php require DIR_WS_INCLUDES . 'header.php'; ?>

    <?php if ($container->get('messageService')->hasMessages()) { ?>
        <ul id="messages">
        <?php foreach ($container->get('messageService')->getMessages() as $message) { ?>
            <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
        <?php } ?>
        </ul>
    <?php } ?>

    <div id="main">
      <div id="content">
        <?php if (file_exists($viewFile)) {
            include $viewFile;
        } else { ?>
            <h2>Invalid request</h2>
        <?php } ?>
      </div>
    </div>

  </body>
</html>
<?php
  } else {
    if (null != $view) {
        echo $view->generate($request);
    } else {
        echo '<h2>Invalid request</h2>';
    }
  }

  // clear messages
  //$session = Runtime::getContainer()->get('session');
  //$session->setValue('messages', null, 'zenmagick.mvc');
  //$session->setValue('messageToStack', '');
  unset($_SESSION['messageToSTack']);
  unset($_SESSION['__ZM_NSP__zenmagick.mvc']);

  // allow plugins and event subscribers to filter/modify the final contents; corresponds with ob_start() in init.php
  $event = new Event(null, array('request' => $request, 'view' => $view, 'contents' => ob_get_clean()));
  Runtime::getEventDispatcher()->dispatch('finalise_contents', $event);
  echo $event->get('contents');

  require DIR_WS_INCLUDES . 'application_bottom.php';
