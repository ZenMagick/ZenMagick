<?php
/**
 * additional configuration entries for image handler
 *
 * @author  Tim Kroeger (original author)
 * @copyright Copyright 2005-2006
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 * @version $Id: bmz_image_handler_conf.php,v 2.0 Rev 8 2010-05-31 23:46:5 DerManoMann Exp $
 * Last modified by DerManoMann 2010-05-31 23:46:50
 */

ZMSettings::set('plugins.imageHandler2.cachedir', ZMSettings::get('apps.store.zencart.path').'/bmz_cache');
// files which contain this string will not be resized
ZMSettings::set('plugins.imageHandler2.noresize_key', 'noresize');
// images in directories with these names within the images directory will not be resized.
ZMSettings::set('plugins.imageHandler2.noresize_dirs', array('noresize', 'banners'));
// this is where semitransparent pixels blend to transparent when rendering gifs with ImageMagick
ZMSettings::set('plugins.imageHandler2.trans_threshold', '90%');
$ihConf['im_convert']           = '';
// if you want to use ImageMagick, you must specify the convert binary here (e.g. '/usr/bin/convert')
ZMSettings::set('plugins.imageHandler2.im_convert', '');
// the GDlib version (0, 1 or 2) 2 tries to autodetect
ZMSettings::set('plugins.imageHandler2.gdlib', 2);
// some defaults
ZMSettings::set('plugins.imageHandler2.defaults.bg', 'transparent 255:255:255');
ZMSettings::set('plugins.imageHandler2.defaults.quality', 85);


global $ihConf;

$ihConf['dir']['docroot']       = ZMSettings::get('apps.store.zencart.path').'/';
$ihConf['dir']['images']        = 'images/';

$ihConf['resize']               = defined('IH_RESIZE') ? (IH_RESIZE == 'yes') : false;

$ihConf['small']['width']       = SMALL_IMAGE_WIDTH;
$ihConf['small']['height']      = SMALL_IMAGE_HEIGHT;
$ihConf['small']['filetype']     = defined('SMALL_IMAGE_FILETYPE') ? SMALL_IMAGE_FILETYPE : 'no_change';
$ihConf['small']['bg']          = defined('SMALL_IMAGE_BACKGROUND') ? SMALL_IMAGE_BACKGROUND : 'transparent 255:255:255';
$ihConf['small']['quality']     = defined('SMALL_IMAGE_QUALITY') ? intval(SMALL_IMAGE_QUALITY) : 85;
$ihConf['small']['watermark']   = defined('WATERMARK_SMALL_IMAGES') ? (WATERMARK_SMALL_IMAGES == 'yes') : false;
$ihConf['small']['zoom']        = defined('ZOOM_SMALL_IMAGES') ? (ZOOM_SMALL_IMAGES == 'yes') : true;

$ihConf['medium']['prefix']      = '/medium';
$ihConf['medium']['suffix']      = IMAGE_SUFFIX_MEDIUM;
$ihConf['medium']['width']      = MEDIUM_IMAGE_WIDTH;
$ihConf['medium']['height']     = MEDIUM_IMAGE_HEIGHT;
$ihConf['medium']['filetype']    = defined('MEDIUM_IMAGE_FILETYPE') ? MEDIUM_IMAGE_FILETYPE : 'no_change';
$ihConf['medium']['bg']         = defined('MEDIUM_IMAGE_BACKGROUND') ? MEDIUM_IMAGE_BACKGROUND : 'transparent 255:255:255';
$ihConf['medium']['quality']    = defined('MEDIUM_IMAGE_QUALITY') ? intval(MEDIUM_IMAGE_QUALITY) : 85;
$ihConf['medium']['watermark']  = defined('WATERMARK_MEDIUM_IMAGES') ? (WATERMARK_MEDIUM_IMAGES == 'yes') : false;

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
