<?php
/*
 * ZenMagick - Another PHP framework.
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
<?php

  // app location relative to zenmagick installation (ZM_BASE_PATH)
  define('ZM_APP_PATH', 'apps'.DIRECTORY_SEPARATOR.basename(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR);

  // pre-load a couple zen-cart files needed
  define('ZC_INSTALL_PATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))).DIRECTORY_SEPARATOR);
  // make zen-cart happy
  define('IS_ADMIN_FLAG', true);
  define('GZIP_LEVEL', false);

  // name of Zen Cart admin folder
  if (!defined('ZC_ADMIN_FOLDER')) {
     define('ZC_ADMIN_FOLDER', 'admin');
  }

  require realpath(dirname(__FILE__).'/../../../bootstrap.php');
  require realpath(dirname(__FILE__).'/../../../mvc.php');
