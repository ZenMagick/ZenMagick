<?php
/**
 * @package admin
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}


require_once('../zenmagick/admin_init.php');

  $za_contents = array();
  $za_heading = array();
  $za_heading = array('text' => "ZenMagick", 'link' => zen_href_link(FILENAME_ALT_NAV, '', 'NONSSL'));
  $za_contents[] = array('text' => "Features", 'link' => zen_href_link(ZM_ADMINFN_FEATURES, '', 'NONSSL'));
  $za_contents[] = array('text' => "Language Tool", 'link' => zen_href_link(ZM_ADMINFN_L10N, '', 'NONSSL'));
  $za_contents[] = array('text' => "Installation Cleanup", 'link' => zen_href_link(ZM_ADMINFN_CLEANUP, '', 'NONSSL'));

?>
<!-- tools //-->
<?php
echo zen_draw_admin_box($za_heading, $za_contents);
?>
<!-- tools_eof //-->
