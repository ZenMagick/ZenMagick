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

  $za_contents = array();
  $za_heading = array();
  $za_heading = array('text' => BOX_HEADING_EXTRAS, 'link' => zen_href_link(FILENAME_ALT_NAV, '', 'NONSSL'));
if ($za_dir = @dir(DIR_WS_BOXES . 'extra_boxes')) {
  while ($zv_file = $za_dir->read()) {
    if (preg_match('/extras_dhtml.php$/', $zv_file)) {
      require(DIR_WS_BOXES . 'extra_boxes/' . $zv_file);
    }
  }
}
?>
<!-- extras_dhtml //-->
<?php
if (sizeof($za_contents)) echo zen_draw_admin_box($za_heading, $za_contents);

  require(DIR_WS_BOXES . 'zenmagick_dhtml.php');

?>
<!-- extras_dhtml_eof //-->
