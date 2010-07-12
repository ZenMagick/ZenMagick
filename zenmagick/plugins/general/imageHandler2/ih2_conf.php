<?php
/**
 * bmz_image_handler_conf.php
 * additional configuration entries for image handler
 *
 * @author  Tim Kroeger (original author)
 * @copyright Copyright 2005-2006
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 * @version $Id: bmz_image_handler_conf.php,v 2.0 Rev 8 2010-05-31 23:46:5 DerManoMann Exp $
 * Last modified by DerManoMann 2010-05-31 23:46:50 
 */
global $ihConf;
global $bmzConf;

$ihConf['noresize_key']         = 'noresize';         //files which contain this string will not be resized
$ihConf['noresize_dirs']        = array('noresize', 'banners'); //images in directories with these names within the images directory will not be resized.
$ihConf['trans_threshold']      = '90%';              //this is where semitransparent pixels blend to transparent when rendering gifs with ImageMagick
$ihConf['im_convert']           = '';                 //if you want to use ImageMagick, you must specify the convert binary here (e.g. '/usr/bin/convert')
$ihConf['gdlib']                = 2;                  //the GDlib version (0, 1 or 2) 2 tries to autodetect
$ihConf['allow_mixed_case_ext'] = false;              //allow files with mixed case extensions like 'Jpeg'. This costs some time for every displayed image. It's better to just use lower case extensions
$ihConf['default']['bg']        = 'transparent 255:255:255';
$ihConf['default']['quality']   = 85;


/**
 * bmz_io_conf.php
 * filesystem access configuration
 *
 * @author  Tim Kroeger (original author)
 * @copyright Copyright 2005-2006
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 * @version $Id: bmz_io_conf.php,v 2.0 Rev 8 2010-05-31 23:46:5 DerManoMann Exp $
 * Last modified by DerManoMann 2010-05-31 23:46:50 
 */
 
$bmzConf = array();
$bmzConf['umask']       = 0111;              //set the umask for new files
$bmzConf['dmask']       = 0000;              //directory mask accordingly
//$bmzConf['cachetime']   = 60*60*24;         //maximum age for cachefile in seconds (defaults to a day)
$bmzConf['cachetime']   = 0;         //maximum age for cachefile in seconds (defaults to a day)
$bmzConf['cachedir']    = DIR_FS_CATALOG . 'bmz_cache';
$bmzConf['lockdir']     = DIR_FS_CATALOG . 'bmz_lock';

/* Safemode Hack */
$bmzConf['safemodehack'] = 0;               //read http://wiki.breakmyzencart.com/zen-cart:safemodehack !
$bmzConf['ftp']['host'] = 'localhost';
$bmzConf['ftp']['port'] = '21';
$bmzConf['ftp']['user'] = 'user';
$bmzConf['ftp']['pass'] = 'password';
$bmzConf['ftp']['root'] = DIR_FS_CATALOG;




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

$ihConf['version']              = '2.0';

$ihConf['dir']['docroot']       = DIR_FS_CATALOG;
$ihConf['dir']['images']        = DIR_WS_IMAGES;

$ihConf['resize']               = defined('IH_RESIZE') ? (IH_RESIZE == 'yes') : false;

$ihConf['small']['width']       = SMALL_IMAGE_WIDTH;
$ihConf['small']['height']      = SMALL_IMAGE_HEIGHT;
$ihConf['small']['filetype']     = defined('SMALL_IMAGE_FILETYPE') ? SMALL_IMAGE_FILETYPE : 'no_change';
$ihConf['small']['bg']          = defined('SMALL_IMAGE_BACKGROUND') ? SMALL_IMAGE_BACKGROUND : 'transparent 255:255:255';
$ihConf['small']['quality']     = defined('SMALL_IMAGE_QUALITY') ? intval(SMALL_IMAGE_QUALITY) : 85;
$ihConf['small']['watermark']   = defined('WATERMARK_SMALL_IMAGES') ? (WATERMARK_SMALL_IMAGES == 'yes') : false;
$ihConf['small']['zoom']        = defined('ZOOM_SMALL_IMAGES') ? (ZOOM_SMALL_IMAGES == 'yes') : true;
//$ihConf['small']['hotzone']     = defined('SMALL_IMAGE_HOTZONE') ? (SMALL_IMAGE_HOTZONE == 'yes') : false;

$ihConf['medium']['prefix']      = '/medium';
$ihConf['medium']['suffix']      = IMAGE_SUFFIX_MEDIUM;
$ihConf['medium']['width']      = MEDIUM_IMAGE_WIDTH;
$ihConf['medium']['height']     = MEDIUM_IMAGE_HEIGHT;
$ihConf['medium']['filetype']    = defined('MEDIUM_IMAGE_FILETYPE') ? MEDIUM_IMAGE_FILETYPE : 'no_change';
$ihConf['medium']['bg']         = defined('MEDIUM_IMAGE_BACKGROUND') ? MEDIUM_IMAGE_BACKGROUND : 'transparent 255:255:255';
$ihConf['medium']['quality']    = defined('MEDIUM_IMAGE_QUALITY') ? intval(MEDIUM_IMAGE_QUALITY) : 85;
$ihConf['medium']['watermark']  = defined('WATERMARK_MEDIUM_IMAGES') ? (WATERMARK_MEDIUM_IMAGES == 'yes') : false;
//$ihConf['medium']['zoom']       = defined('ZOOM_MEDIUM_IMAGES') ? (ZOOM_MEDIUM_IMAGES == 'yes') : false;
//$ihConf['medium']['hotzone']    = defined('MEDIUM_IMAGE_HOTZONE') ? (MEDIUM_IMAGE_HOTZONE == 'yes') : false;

$ihConf['large']['prefix']      = '/large';
$ihConf['large']['suffix']       = IMAGE_SUFFIX_LARGE;
$ihConf['large']['width']       = defined('LARGE_IMAGE_MAX_WIDTH') ? LARGE_IMAGE_MAX_WIDTH : '750';
$ihConf['large']['height']      = defined('LARGE_IMAGE_MAX_HEIGHT') ? LARGE_IMAGE_MAX_HEIGHT : '550';
$ihConf['large']['filetype']     = defined('LARGE_IMAGE_FILETYPE') ? LARGE_IMAGE_FILETYPE : 'no_change';
$ihConf['large']['bg']          = defined('LARGE_IMAGE_BACKGROUND') ? LARGE_IMAGE_BACKGROUND : 'transparent 255:255:255';
$ihConf['large']['quality']     = defined('LARGE_IMAGE_QUALITY') ? intval(LARGE_IMAGE_QUALITY) : 85;
$ihConf['large']['watermark']   = defined('WATERMARK_LARGE_IMAGES') ? (WATERMARK_LARGE_IMAGES == 'yes') : false;

$ihConf['watermark']['gravity'] = defined('WATERMARK_GRAVITY') ? WATERMARK_GRAVITY : 'Center';
$ihConf['zoom']['gravity']      = defined('ZOOM_GRAVITY') ? ZOOM_GRAVITY : 'SouthEast';
