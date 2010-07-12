<?php
/**
 * functions_bmz_image_handler.php
 * html_output hook function and additional image referencing functions for
 * backwards compatibility, parsing of configuration settings
 *
 * @author  Tim Kroeger (original author)
 * @copyright Copyright 2005-2006
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 * @version $Id: functions_bmz_image_handler.php,v 2.0 Rev 8 2010-05-31 23:46:5 DerManoMann Exp $
 * Last modified by DerManoMann 2010-05-31 23:46:50 
 */

function handle_image($src, $alt, $width, $height, $parameters) {
	global $ihConf;
	
	if ($ihConf['resize']) {
    	$ih_image = new ZMIh2Image($src, $width, $height);
    // override image path, get local image from cache
    if ($ih_image) { 
      $src = $ih_image->get_local();
      $parameters = $ih_image->get_additional_parameters($alt, $ih_image->canvas['width'], $ih_image->canvas['height'], $parameters);
    }
  } else {
    // default to standard Zen-Cart fallback behavior for large -> medium -> small images
    $image_ext = substr($src, strrpos($src, '.'));
    $image_base = substr($src, strlen(DIR_WS_IMAGES), -strlen($image_ext));
    if (strrpos($src, IMAGE_SUFFIX_LARGE) && !is_file(DIR_FS_CATALOG . $src)) {
      //large image wanted but not found
      $image_base = $ihConf['medium']['prefix'] . substr($image_base, strlen($ihConf['large']['prefix']), -strlen($ihConf['large']['suffix'])) . $ihConf['medium']['suffix'];
      $src = DIR_WS_IMAGES . $image_base . $image_ext;
    }
    if (strrpos($src, IMAGE_SUFFIX_MEDIUM) && !is_file(DIR_FS_CATALOG . $src)) {
      //medium image wanted but not found
      $image_base = substr($image_base, strlen($ihConf['medium']['prefix']), -strlen($ihConf['medium']['suffix'])); 
      $src = DIR_WS_IMAGES . $image_base . $image_ext;
    }
  }
  return array($src, $alt, intval($width), intval($height), $parameters);
}
