<?php

  // app location relative to zenmagick installation (ZM_BASE_PATH)
  define('ZM_APP_PATH', 'apps'.DIRECTORY_SEPARATOR.basename(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR);

  require '../../../bootstrap.php';
  require '../../../mvc.php';
