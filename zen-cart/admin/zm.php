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

  define('IS_ADMIN_FLAG', true);

  require_once 'includes/configure.php';
  require(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'filenames.php');
  require(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'database_tables.php');

  require_once '../zenmagick/init.php';

  $request->getSession()->start();

  // set some admin specific things...
  ZMUrlManager::instance()->clear();
  ZMSacsManager::instance()->reset();
  ZMUrlManager::instance()->load(file_get_contents(ZMFileUtils::mkPath(array(ZMRuntime::getApplicationPath(), 'lib/admin/config', 'url_mappings.yaml'))), false);
  ZMSacsManager::instance()->load(file_get_contents(ZMFileUtils::mkPath(array(ZMRuntime::getApplicationPath(), 'lib/admin/config', 'sacs_mappings.yaml'))), false);
  // make sure we use the appropriate protocol (HTTPS, for example) if required

  //ZMSacsManager::instance()->ensureAccessMethod($request);

  ZMSettings::set('isStoreNameInTitle', false);
  ZMSettings::set('zenmagick.mvc.view.default', 'AdminView');
  ZMSettings::set('zenmagick.mvc.view.defaultLayout', null);

  if (ZMLangUtils::isEmpty($request->getRequestId())) {
      $request->setParameter('main_page', 'index');
  }

  ZMDispatcher::dispatch($request);
  $request->closeSession();
  exit;
