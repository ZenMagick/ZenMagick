<?php
/**
 * functions_bmz_image_handler_update.php
 * manage automatic patching of the database for image-handler
 *
 * @author  Tim Kroeger (original author)
 * @copyright Copyright 2005-2006
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 * @version $Id: functions_bmz_image_handler_update.php,v 2.0 Rev 8 2010-05-31 23:46:5 DerManoMann Exp $
 * Last modified by DerManoMann 2010-05-31 23:46:50 
 *
 * C Jones 06-04-2010 - Medium image hover features have been disabled as they do not work 
 * (never did). We left the code in place in case any community member decides to REALLY get these features 
 * working WITHOUT interfering with light box and other product image gallery modifications. Also image hotzones 
 * features have also been disabled as they add no value to the Image Handler mod.
 */

function remove_image_handler() {
	global $db;
	$error = false;
	
	$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'LARGE_IMAGE_MAX_WIDTH' OR " .
				"configuration_key = 'LARGE_IMAGE_MAX_HEIGHT' OR " .
				"configuration_key = 'SMALL_IMAGE_FILETYPE' OR " .
				"configuration_key = 'SMALL_IMAGE_BACKGROUND' OR " .
				"configuration_key = 'WATERMARK_SMALL_IMAGES' OR " .
				"configuration_key = 'ZOOM_SMALL_IMAGES' OR " .
//				"configuration_key = 'SMALL_IMAGE_HOTZONE' OR " .
				"configuration_key = 'SMALL_IMAGE_QUALITY' OR " .
				"configuration_key = 'MEDIUM_IMAGE_FILETYPE' OR " .
				"configuration_key = 'MEDIUM_IMAGE_BACKGROUND' OR " .
				"configuration_key = 'WATERMARK_MEDIUM_IMAGES' OR " .
//				"configuration_key = 'ZOOM_MEDIUM_IMAGES' OR " .
//				"configuration_key = 'MEDIUM_IMAGE_HOTZONE' OR " .
				"configuration_key = 'MEDIUM_IMAGE_QUALITY' OR " .
				"configuration_key = 'LARGE_IMAGE_FILETYPE' OR " .
				"configuration_key = 'LARGE_IMAGE_BACKGROUND' OR " .
				"configuration_key = 'WATERMARK_LARGE_IMAGES' OR " .
				"configuration_key = 'LARGE_IMAGE_QUALITY' OR " .
				"configuration_key = 'WATERMARK_GRAVITY' OR " .
//				"configuration_key = 'ZOOM_GRAVITY' OR " .
				"configuration_key = 'IH_RESIZE' OR " .
				"configuration_key = 'SHOW_UPLOADED_IMAGES';";
	$db->Execute($sql_query);
	$sql_query = "UPDATE " . TABLE_CONFIGURATION . " SET configuration_value='REMOVED' WHERE configuration_key = 'IH_VERSION';";
	$db->Execute($sql_query);
	return $error;
}

function install_image_handler() {
	global $db;
    global $ihConf;
  $sort_order_offset = 100;
	$i = 0;
	
	if (defined('IH_VERSION')) {
		$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'IH_VERSION';";
		$db->Execute($sql_query);
	}

	//------------------------------
	// IH_RESIZE configuration entry
	//------------------------------
	$ih_resize = 'yes';
	if (defined('IMAGE_MANAGER_HANDLER')) {
		// ok, some image handler has been installed
		$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'IMAGE_MANAGER_HANDLER';";
		if (IMAGE_MANAGER_HANDLER == 'none') $ih_resize = 'no';
		$db->Execute($sql_query);
	}
	if (!defined('IH_RESIZE')) {
		$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
					"('IH resize images', 'IH_RESIZE', '$ih_resize', 'Select either ''no'' which is old Zen-Cart behaviour or ''yes'' to activate automatic resizing and caching of images. If you want to use ImageMagick you have to specify the location of the <strong>convert</strong> binary in <em>includes/extra_configures/bmz_image_handler_conf.php</em>.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_option(array(''yes'', ''no''),', now());";
		$db->Execute($sql_query);
		define(IH_RESIZE, $ih_resize);
	}
	

	//-----------------------------------------
	// SMALL_IMAGE_FILETYPE configuration entry
	//-----------------------------------------
	$sql_query = '';
	if (!defined('SMALL_IMAGE_FILETYPE')) {
		$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
					"('IH small image filetype', 'SMALL_IMAGE_FILETYPE', 'no_change', 'Select one of ''jpg'', ''gif'' or ''png''. Internet Explorer has still issues displaying png-images with transparent areas. You better stick to ''gif'' for transparency or ''jpg'' for larger images. ''no_change'' is old zen-cart behavior, use the same file extension for small images as uploaded image''s.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_option(array(''gif'', ''jpg'', ''png'', ''no_change''),', now());";
		$db->Execute($sql_query);
		define(SMALL_IMAGE_FILETYPE, 'no_change');
	}

	//-------------------------------------------
	// SMALL_IMAGE_BACKGROUND configuration entry
	//-------------------------------------------
	$sql_query = '';
	if (!defined('SMALL_IMAGE_BACKGROUND')) {
		$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
					"('IH small image background', 'SMALL_IMAGE_BACKGROUND', '255:255:255', 'If converted from an uploaded image with transparent areas, these areas become the specified color. Set to ''transparent'' to keep transparency.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_textarea_small(', now());";
		$db->Execute($sql_query);
		define(SMALL_IMAGE_BACKGROUND, '255:255:255');
	}

	//-------------------------------------------
	// WATERMARK_SMALL_IMAGES configuration entry
	//-------------------------------------------
	$watermark_small_images = 'no';
	$sql_query = '';
	if (defined('WATERMARK_SMALL_IMAGES')) {
		$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'WATERMARK_SMALL_IMAGES';";
		if (WATERMARK_SMALL_IMAGES == 'True') $watermark_small_images = 'yes';
		$db->Execute($sql_query);
	}
	$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
				"('IH watermark small images', 'WATERMARK_SMALL_IMAGES', '$watermark_small_images', 'Set to ''yes'', if you want to show watermarked small images instead of unmarked small images.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_option(array(''no'', ''yes''),', now());";
	$db->Execute($sql_query);

	//--------------------------------------
	// ZOOM_SMALL_IMAGES configuration entry
	//--------------------------------------
	$zoom_small_images = 'yes';
	$sql_query = '';
	if (defined('ZOOM_SMALL_IMAGES')) {
		$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'ZOOM_SMALL_IMAGES';";
		if (ZOOM_SMALL_IMAGES == 'yes') $zoom_small_images = 'yes';
		$db->Execute($sql_query);
	}
	$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
				"('IH zoom small images', 'ZOOM_SMALL_IMAGES', '$zoom_small_images', 'Set to ''yes'', if you want to enable a nice zoom overlay while hovering the mouse pointer over small images.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_option(array(''no'', ''yes''),', now());";
	$db->Execute($sql_query);

  //--------------------------------------
  // SMALL_IMAGE_HOTZONE configuration entry
  //--------------------------------------
//  $small_image_hotzone = 'no';
//  $sql_query = '';
//  if (defined('SMALL_IMAGE_HOTZONE')) {
//    $sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'SMALL_IMAGE_HOTZONE';";
//    if (SMALL_IMAGE_HOTZONE == 'yes') $small_image_hotzone = 'yes';
//    $db->Execute($sql_query);
//  }
//  $sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
//        "('IH small image hotzone', 'SMALL_IMAGE_HOTZONE', '$small_image_hotzone', 'Set to ''yes'', if you want the nice zoom overlay to appear while hovering the mouse pointer over the small images'' hotzone only instead of the whole image. The hotzone will be defined by the uploaded zoom overlay image and it''s position relative to the image (gravity).', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_option(array(''no'', ''yes''),', now());";
//  $db->Execute($sql_query);

	//----------------------------------------
	// SMALL_IMAGE_QUALITY configuration entry
	//----------------------------------------
	$small_image_quality = '85';
	$sql_query = '';
	if (defined('SMALL_IMAGE_QUALITY')) {
		$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'SMALL_IMAGE_QUALITY';";
		$small_image_quality = SMALL_IMAGE_QUALITY;
		$db->Execute($sql_query);
	}
	$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
				"('IH small image compression quality', 'SMALL_IMAGE_QUALITY', '$small_image_quality', 'Specify the desired image quality for small jpg images, decimal values ranging from 0 to 100. Higher is better quality and takes more space. Default is 85 which is ok unless you have very specific needs.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_textarea_small(', now());";
	$db->Execute($sql_query);


	//------------------------------------------
	// MEDIUM_IMAGE_FILETYPE configuration entry
	//------------------------------------------
	$sql_query = '';
	if (!defined('MEDIUM_IMAGE_FILETYPE')) {
		$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
					"('IH medium image filetype', 'MEDIUM_IMAGE_FILETYPE', 'no_change', 'Select one of ''jpg'', ''gif'' or ''png''. Internet Explorer has still issues displaying png-images with transparent areas. You better stick to ''gif'' for transparency or ''jpg'' for larger images. ''no_change'' is old zen-cart behavior, use the same file extension for medium images as uploaded image''s.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_option(array(''gif'', ''jpg'', ''png'', ''no_change''),', now());";
		$db->Execute($sql_query);
		define(MEDIUM_IMAGE_FILETYPE, 'no_change');
	}

	//--------------------------------------------
	// MEDIUM_IMAGE_BACKGROUND configuration entry
	//--------------------------------------------
	$sql_query = '';
	if (!defined('MEDIUM_IMAGE_BACKGROUND')) {
		$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
					"('IH medium image background', 'MEDIUM_IMAGE_BACKGROUND', '255:255:255', 'If converted from an uploaded image with transparent areas, these areas become the specified color. Set to ''transparent'' to keep transparency.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_textarea_small(', now());";
		$db->Execute($sql_query);
		define(MEDIUM_IMAGE_BACKGROUND, '255:255:255');
	}

	//--------------------------------------------
	// WATERMARK_MEDIUM_IMAGES configuration entry
	//--------------------------------------------
	$watermark_medium_images = 'no';
	$sql_query = '';
	if (defined('WATERMARK_MEDIUM_IMAGES')) {
		$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'WATERMARK_MEDIUM_IMAGES';";
		if (WATERMARK_MEDIUM_IMAGES == 'True') $watermark_medium_images = 'yes';
		$db->Execute($sql_query);
	}
	$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
				"('IH watermark medium images', 'WATERMARK_MEDIUM_IMAGES', '$watermark_medium_images', 'Set to ''yes'', if you want to show watermarked medium images instead of unmarked medium images.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_option(array(''no'', ''yes''),', now());";
	$db->Execute($sql_query);

	//---------------------------------------
	// ZOOM_MEDIUM_IMAGES configuration entry
	//---------------------------------------
//	$zoom_medium_images = 'no';
//	$sql_query = '';
//	if (defined('ZOOM_MEDIUM_IMAGES')) {
//		$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'ZOOM_MEDIUM_IMAGES';";
//		if (ZOOM_MEDIUM_IMAGES == 'yes') $zoom_medium_images = 'yes';
//		$db->Execute($sql_query);
//	}
//	$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
//				"('IH zoom medium images', 'ZOOM_MEDIUM_IMAGES', '$zoom_medium_images', 'Set to ''yes'', if you want to enable a nice zoom overlay while hovering the mouse pointer over medium images.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_option(array(''no'', ''yes''),', now());";
//	$db->Execute($sql_query);

  //-----------------------------------------
  // MEDIUM_IMAGE_HOTZONE configuration entry
  //-----------------------------------------
//  $medium_image_hotzone = 'no';
//  $sql_query = '';
//  if (defined('MEDIUM_IMAGE_HOTZONE')) {
//    $sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MEDIUM_IMAGE_HOTZONE';";
//    if (MEDIUM_IMAGE_HOTZONE == 'yes') $medium_image_hotzone = 'yes';
//    $db->Execute($sql_query);
//  }
//  $sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
//        "('IH medium image hotzone', 'MEDIUM_IMAGE_HOTZONE', '$medium_image_hotzone', 'Set to ''yes'', if you want the nice zoom overlay to appear while hovering the mouse pointer over the medium images'' hotzone only instead of the whole image. The hotzone will be defined by the uploaded zoom overlay image and it''s position relative to the image (gravity).', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_option(array(''no'', ''yes''),', now());";
//  $db->Execute($sql_query);

	//-----------------------------------------
	// MEDIUM_IMAGE_QUALITY configuration entry
	//-----------------------------------------
	$medium_image_quality = '85';
	$sql_query = '';
	if (defined('MEDIUM_IMAGE_QUALITY')) {
		$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MEDIUM_IMAGE_QUALITY';";
		$medium_image_quality = MEDIUM_IMAGE_QUALITY;
		$db->Execute($sql_query);
	}
	$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
				"('IH medium image compression quality', 'MEDIUM_IMAGE_QUALITY', '$medium_image_quality', 'Specify the desired image quality for medium jpg images, decimal values ranging from 0 to 100. Higher is better quality and takes more space. Default is 85 which is ok unless you have very specific needs.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_textarea_small(', now());";
	$db->Execute($sql_query);


	//-----------------------------------------
	// LARGE_IMAGE_FILETYPE configuration entry
	//-----------------------------------------
	$sql_query = '';
	if (!defined('LARGE_IMAGE_FILETYPE')) {
		$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
					"('IH large image filetype', 'LARGE_IMAGE_FILETYPE', 'no_change', 'Select one of ''jpg'', ''gif'' or ''png''. Internet Explorer has still issues displaying png-images with transparent areas. You better stick to ''gif'' for transparency or ''jpg'' for larger images. ''no_change'' is old zen-cart behavior, use the same file extension for large images as uploaded image''s.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_option(array(''gif'', ''jpg'', ''png'', ''no_change''),', now());";
		$db->Execute($sql_query);
		define(LARGE_IMAGE_FILETYPE, 'no_change');
	}

	//-------------------------------------------
	// LARGE_IMAGE_BACKGROUND configuration entry
	//-------------------------------------------
	$sql_query = '';
	if (!defined('LARGE_IMAGE_BACKGROUND')) {
		$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
					"('IH large image background', 'LARGE_IMAGE_BACKGROUND', '255:255:255', 'If converted from an uploaded image with transparent areas, these areas become the specified color. Set to ''transparent'' to keep transparency.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_textarea_small(', now());";
		$db->Execute($sql_query);
		define(LARGE_IMAGE_BACKGROUND, '255:255:255');
	}

	//-------------------------------------------
	// WATERMARK_LARGE_IMAGES configuration entry
	//-------------------------------------------
	$watermark_large_images = 'no';
	$sql_query = '';
	if (defined('WATERMARK_LARGE_IMAGES')) {
		$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'WATERMARK_LARGE_IMAGES';";
		if (WATERMARK_LARGE_IMAGES == 'True') $watermark_large_images = 'yes';
		$db->Execute($sql_query);
	}
	$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
				"('IH watermark large images', 'WATERMARK_LARGE_IMAGES', '$watermark_large_images', 'Set to ''yes'', if you want to show watermarked large images instead of unmarked large images.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_option(array(''no'', ''yes''),', now());";
	$db->Execute($sql_query);

	//----------------------------------------
	// LARGE_IMAGE_QUALITY configuration entry
	//----------------------------------------
	$large_image_quality = '85';
	$sql_query = '';
	if (defined('LARGE_IMAGE_QUALITY')) {
		$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'LARGE_IMAGE_QUALITY';";
		$large_image_quality = LARGE_IMAGE_QUALITY;
		$db->Execute($sql_query);
	}
	$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
				"('IH large image compression quality', 'LARGE_IMAGE_QUALITY', '$large_image_quality', 'Specify the desired image quality for large jpg images, decimal values ranging from 0 to 100. Higher is better quality and takes more space. Default is 85 which is ok unless you have very specific needs.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_textarea_small(', now());";
	$db->Execute($sql_query);


	//------------------------------------------
	// LARGE_IMAGE_MAX_WIDTH configuration entry
	//------------------------------------------
	$sql_query = '';
	$large_image_max_width = '750';
	if (!defined('LARGE_IMAGE_MAX_WIDTH')) {
		$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
					"('IH large image maximum width', 'LARGE_IMAGE_MAX_WIDTH', '" . $large_image_max_width . "', 'Specify a maximum width for your large images. If width and height are empty or set to 0, no resizing of large images is done.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_textarea_small(', now())";
		$db->Execute($sql_query);
		define(LARGE_IMAGE_MAX_WIDTH, $large_image_max_width);
	}
	
	//-------------------------------------------
	// LARGE_IMAGE_MAX_HEIGHT configuration entry
	//-------------------------------------------
	$sql_query = '';
	$large_image_max_height = '550';
	if (!defined('LARGE_IMAGE_MAX_HEIGHT')) {
		$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
					"('IH large image maximum height', 'LARGE_IMAGE_MAX_HEIGHT', '" . $large_image_max_height . "', 'Specify a maximum height for your large images. If width and height are empty or set to 0, no resizing of large images is done.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_textarea_small(', now())";
		$db->Execute($sql_query);
		define(LARGE_IMAGE_MAX_HEIGHT, $large_image_max_height);
	}
	

	//--------------------------------------
	// WATERMARK_GRAVITY configuration entry
	//--------------------------------------
	$sql_query = '';
	if (!defined('WATERMARK_GRAVITY')) {
		$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
					"('IH watermark gravity', 'WATERMARK_GRAVITY', 'Center', 'Select the position for the watermark relative to the image''s canvas. Default is <strong>Center</Strong>.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_drop_down(array(array(''id''=>''NorthWest'', ''text''=>''NorthWest''), array(''id''=>''North'', ''text''=>''North''), array(''id''=>''NorthEast'', ''text''=>''NorthEast''), array(''id''=>''West'', ''text''=>''West''), array(''id''=>''Center'', ''text''=>''Center''), array(''id''=>''East'', ''text''=>''East''), array(''id''=>''SouthWest'', ''text''=>''SouthWest''), array(''id''=>''South'', ''text''=>''South''), array(''id''=>''SouthEast'', ''text''=>''SouthEast'')),', now());";
		$db->Execute($sql_query);
		define(WATERMARK_GRAVITY, 'Center');
	}


	//---------------------------------
	// ZOOM_GRAVITY configuration entry
	//---------------------------------
//	$sql_query = '';
//	if (!defined('ZOOM_GRAVITY')) {
//		$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
//					"('IH zoom gravity', 'ZOOM_GRAVITY', 'SouthEast', 'Select the position for the zoom-hint overlay relative to the image''s canvas. Default is <strong>SouthEast</Strong>.', 4, " . ($sort_order_offset + $i++) . ", 'zen_cfg_select_drop_down(array(array(''id''=>''NorthWest'', ''text''=>''NorthWest''), array(''id''=>''North'', ''text''=>''North''), array(''id''=>''NorthEast'', ''text''=>''NorthEast''), array(''id''=>''West'', ''text''=>''West''), array(''id''=>''Center'', ''text''=>''Center''), array(''id''=>''East'', ''text''=>''East''), array(''id''=>''SouthWest'', ''text''=>''SouthWest''), array(''id''=>''South'', ''text''=>''South''), array(''id''=>''SouthEast'', ''text''=>''SouthEast'')),', now());";
//		$db->Execute($sql_query);
//		define(ZOOM_GRAVITY, 'SouthEast');
//	}


	//----------------------------------------------
	// ADDITIONAL_IMAGE_FILETYPE configuration entry
	//----------------------------------------------
	$sql_query = '';
	if (defined('ADDITIONAL_IMAGE_FILETYPE')) {
		$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'ADDITIONAL_IMAGE_FILETYPE';";
		$db->Execute($sql_query);
	}


	//------------------------------------------------
	// ADDITIONAL_IMAGE_BACKGROUND configuration entry
	//------------------------------------------------
	$sql_query = '';
	if (defined('ADDITIONAL_IMAGE_BACKGROUND')) {
		$sql_query = "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'ADDITIONAL_IMAGE_BACKGROUND';";
		$db->Execute($sql_query);
	}

	// set to first image-handler version which supported automatic updates
	// and update database	
	$sql_query = "INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) VALUES " .  
				"('Image Handler Version', 'IH_VERSION', '" . $ihConf['version'] . "', 'This is used by image handler to check if the database is up to date with uploaded image handler files.', 0, 100, 'zen_cfg_textarea_small(', now());";
	$db->Execute($sql_query);
  if (!defined('IH_VERSION')) define(IH_VERSION, $ihConf['version']);

}


