<?php

  // app location relative to zenmagick installation (ZM_BASE_PATH)
  define('ZM_APP_PATH', 'apps'.DIRECTORY_SEPARATOR.basename(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR);

  // share code
  define('ZM_SHARED', 'shared');

  // make zc happy
  define('IS_ADMIN_FLAG', true);

  // preload a couple zc files needed
  define('ZC_INSTALL_PATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR);
  require_once ZC_INSTALL_PATH.'admin/includes/configure.php';
  require_once DIR_FS_CATALOG.DIR_WS_INCLUDES.'filenames.php';
  require_once DIR_FS_CATALOG.DIR_WS_INCLUDES.'database_tables.php';

  require '../../../bootstrap.php';
  // more zen-cart config stuff we need
  ZMSettings::set('zenmagick.mvc.request.secure', 'true'==ENABLE_SSL_ADMIN);
  require '../../../mvc.php';
