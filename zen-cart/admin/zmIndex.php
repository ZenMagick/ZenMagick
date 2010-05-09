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
?><?php
  require_once 'includes/application_top.php';

  // set some admin specific things...
  ZMUrlManager::instance()->clear();
  ZMSacsManager::instance()->reset();
  ZMSacsManager::instance()->load(file_get_contents(ZMFileUtils::mkPath(array(ZMRuntime::getInstallationPath(), 'apps/admin/config', 'sacs_mappings.yaml'))), false);
  // make sure we use the appropriate protocol (HTTPS, for example) if required
  //TODO: redirect uses net, not admin... ZMSacsManager::instance()->ensureAccessMethod($request);

  ZMSettings::set('isStoreNameInTitle', false);
  ZMSettings::set('zenmagick.mvc.view.default', 'AdminView');
  if (ZMLangUtils::isEmpty($request->getRequestId())) {
      $request->setParameter('main_page', 'index');
  }

  /* TODO: use once a new admin UI is done
  ZMDispatcher::dispatch($request);
  exit;
  */

  // use default mappings only, taken from ZMDispatcher...
  $controller = $request->getController();
  $view = null;

  try {
      // execute controller
      $view = $controller->process($request);
  } catch (Exception $e) {
      ZMLogging::instance()->dump($e, null, ZMLogging::ERROR);
      $controller = ZMLoader::make(ZMSettings::get('zenmagick.mvc.controller.defaultClass', 'DefaultController'));
      $view = $controller->findView('error', array('exception' => $e));
      $request->setController($controller);
  }

  //XXX: buffer code, so redirects should still work...
  ob_start();

  if (!in_array($request->getRequestId(), array('login', 'logoff', 'password_forgotten'))) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title>ZenMagick Admin :: <?php echo $request->getToolbox()->metaTags->getTitle() ?></title>
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

    <?php if (ZMMessages::instance()->hasMessages()) { ?>
        <ul id="messages">
        <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
            <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
        <?php } ?>
        </ul>
    <?php } ?>

    <div id="main">
      <div id="content">
        <?php if (null != $view) {
            echo $view->generate($request);
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

  // allow plugins and event subscribers to filter/modify the final contents; corresponds with ob_start() in init.php
  $args = ZMEvents::instance()->fireEvent(null, ZMMVCConstants::FINALISE_CONTENTS, 
          array('request' => $request, 'view' => $view, 'contents' => ob_get_clean()));
  echo $args['contents'];

  require DIR_WS_INCLUDES . 'application_bottom.php';
?>
