<?php
/**
 * captcha.php CAPTCHA class
 *
 * @package captcha
 * @copyright Copyright 2004-2007 AndrewBerezin eCommerce-Service.com
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */

define('CAPCHA_USE_OB', 'false');
define('CAPCHA_NOISE', '30');

class PCaptcha {
  var $captchaCode = '';
  var $captchaCode_length = 4;
  var $img_width = 240;
  var $img_height = 50;
  var $img_type = 'jpeg';
  var $chars_min_size = 0.6;
  var $chars_max_size = 0.8;
  var $chars_shadow = true;
  var $chars_rotation = 23;
  var $noise = 0;
  var $backgroundColor = array(array(220, 220, 220), array(255, 255, 255));
  var $linesColor = array(array(150, 150, 150), array(185, 185, 185));
  var $textColor = array(array(30, 30, 30), array(199, 199, 199));
  var $dir_fs_fonts = '/fonts/';
  var $ttf_list = array();

  var $max_try = 5;
  var $failure_proc = '';

  var $quality = 60;
  var $chars = array('1', '2', '3', '4', '5', '6', '7', '8', '9',
                     'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',
                     'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
                     'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
  var $_background;
  var $img;
  var $_gd_version;
  var $debug = false;

  function __construct($request) {
    global $session_started;

    $plugin = ZMPlugins::instance()->getPluginForId('captcha');

    if(defined('CAPTCHA_CODE_LENGTH')) $this->captchaCode_length = CAPTCHA_CODE_LENGTH;
    if(defined('CAPTCHA_IMG_WIDTH')) $this->img_width = CAPTCHA_IMG_WIDTH;
    if(defined('CAPTCHA_IMG_HEIGHT')) $this->img_height = CAPTCHA_IMG_HEIGHT;
    if(defined('CAPTCHA_IMG_TYPE')) $this->img_type = CAPTCHA_IMG_TYPE;
    if(defined('CAPTCHA_CHARS_SHADOW')) $this->chars_shadow = (CAPTCHA_CHARS_SHADOW == 'true' ? true : false);
    if(defined('CAPTCHA_CHARS_MIN_SIZE')) $this->chars_min_size = CAPTCHA_CHARS_MIN_SIZE;
    if(defined('CAPTCHA_CHARS_MAX_SIZE')) $this->chars_max_size = CAPTCHA_CHARS_MAX_SIZE;
    if(defined('CAPTCHA_CHARS_ROTATION')) $this->chars_rotation = CAPTCHA_CHARS_ROTATION;
    if(defined('CAPCHA_NOISE')) $this->noise = (int)CAPCHA_NOISE;
		$this->dir_fs_fonts = $plugin->getConfigPath('fonts/');
    if(defined('CAPTCHA_MAX_TRY')) $this->max_try = CAPTCHA_MAX_TRY;
    if(defined('CAPTCHA_FAILURE_PROC')) $this->failure_proc = CAPTCHA_FAILURE_PROC;

    if(!$this->_gd_version = $this->_get_gd_version()) die(ERROR_CAPTCHA_GD);
    if($this->img_type == 'gif' && !$this->_gd_version['GIF Create Support']) die(ERROR_CAPTCHA_GIF);
    if($this->img_type == 'png' && !$this->_gd_version['PNG Support']) die(ERROR_CAPTCHA_PNG);
    if($this->img_type == 'jpeg' && !$this->_gd_version['JPG Support']) die(ERROR_CAPTCHA_JPG);
    if(!$session_started) die(ERROR_CAPTCHA_SESSION);

    $this->ttf_list = $this->_getFileList($this->dir_fs_fonts, 'ttf');
		$this->img_href = $request->url('captcha_img', zen_session_name() . '=' . zen_session_id(), true);
  }

/**
 * Generate the HTML output code for the Captcha image field
 */
  function img($alt = '', $width = '', $height = '', $parameters = '') {
    if(trim($width) == '') $width = $this->img_width;
    if(trim($height) == '') $height = $this->img_height;
    $alt = htmlspecialchars(trim($alt));
    if($alt == '') $alt = IMAGE_CAPTCHA_ALT;
    $image = '<img id="captcha-img" src="' . $this->img_href . '&amp;rand=' . str_replace(' ', '', microtime()) . '" alt="' . $alt . '"';
    if ($alt != '') {
      $image .= ' title=" ' . $alt . ' "';
    }
    $image .= ' width="' . $width . '" height="' . $height . '"';
    if (trim($parameters) != '') $image .= ' ' . $parameters;
    $image .= ' />';
    return $image;
  }

/**
 * Generate the HTML output code for the Redraw Captcha image button
 */
  function redraw_button($image, $alt = '', $parameters = '', $sec_class = '') {
    $redraw = ' onclick="document.getElementById(\'captcha-img\').src=\'' . $this->img_href . '&rand=\'+Math.random();return false;"';
    $redraw_button = zen_image_button($image, $alt, $redraw . ' ' . $parameters, $sec_class );
    if (strtolower(IMAGE_USE_CSS_BUTTONS) == 'yes' && strpos($redraw_button, $redraw) === false) {
    	$redraw_button = str_replace('<span ', '<span ' . $redraw, $redraw_button);
    }
    return $redraw_button;
  }

/**
 * Generate the HTML output code for the Captcha input field
 */
  function input_field($name = 'captcha_code', $parameters = '') {
    $name = htmlspecialchars(trim($name));
    $field = '<input type="text" name="' . $name . '"';
    if (zen_not_null($parameters)) $field .= ' ' . $parameters;
    $field .= ' />';
    $_SESSION['captcha_field'] = $name;
    return $field;
  }

/**
 * Validate the Captcha Code
 */
  function validateCaptchaCode() {
    if(!isset($_POST[$_SESSION['captcha_field']])) return false;
    $captcha_code = strtoupper($_POST[$_SESSION['captcha_field']]);
    $captcha_code = str_replace("0", "O", $captcha_code);
    $valid = ($_SESSION['captcha_code'] == md5($captcha_code));
    if(!$valid) {
      if(!isset($_SESSION['captcha_validations'])) $_SESSION['captcha_validations'] = array();
      $_SESSION['captcha_validations'][] = time();
      if(sizeof($_SESSION['captcha_validations']) > $this->max_try) {
        if(trim($this->failure_proc) != "") {
          if(function_exists($this->failure_proc)) {

          } else {

          }
        }
//    var_dump(method_exists($this, 'validateCaptchaCodeExt'));
      }
    }
    unset($_SESSION['captcha_validations']);
    return $valid;
  }

/**
 * Generate Captcha Code and Create the Captcha Image
 */
  function generateCaptcha() {
    $chars_count = sizeof($this->chars)-1;
    $this->captchaCode = '';
    for ($i = 1; $i <= $this->captchaCode_length; $i++) {
      mt_srand((double)microtime()*1000000);
      $j = intval(mt_rand(0, $chars_count));
      $this->captchaCode .= $this->chars[$j];
    }
    $_SESSION['captcha_code'] = md5($this->captchaCode);

    if($this->_gd_version['version'] >= 2) {
      $this->img = imagecreatetruecolor($this->img_width, $this->img_height);
      $this->imageColorFunc = 'imagecolorallocate';
    } else {
      $this->img = imageCreate($this->img_width, $this->img_height);
      $this->imageColorFunc = 'imagecolorclosest';
    }

    $backgroundRGB = array('r' => mt_rand($this->backgroundColor[0][0], $this->backgroundColor[1][0]),
                           'g' => mt_rand($this->backgroundColor[0][1], $this->backgroundColor[1][1]),
                           'b' => mt_rand($this->backgroundColor[0][2], $this->backgroundColor[1][2]));
    $backgroundColor = $this->_imageColor($backgroundRGB);
    imagefilledrectangle($this->img, 0, 0, $this->img_width, $this->img_height, $backgroundColor);

    $linesRGB = array('r' => mt_rand($this->linesColor[0][0], $this->linesColor[1][0]),
                      'g' => mt_rand($this->linesColor[0][1], $this->linesColor[1][1]),
                      'b' => mt_rand($this->linesColor[0][2], $this->linesColor[1][2]));
    $linesColor = $this->_imageColor($linesRGB);

/*
    $y1 = 0;
    $y2 = $this->img_height;
    foreach(array(array(0,20,20,40), array(20,40,0,20), array(10,60,0,50)) as $k => $v) {
      for ($x1 = mt_rand($v[0],$v[1]), $x2 = mt_rand($v[2],$v[3]); $x1 < $this->img_width && $x2 < $this->img_width;) {
        imageLine($this->img, $x1, $y1, $x2, $y2, $linesColor);
        $x1 += mt_rand($v[0], $v[1]);
        $x2 += mt_rand($v[2], $v[3]);
      }
    }

    $x1 = 0;
    $x2 = $this->img_width;
    foreach(array(array(0,10,10,20), array(10,20,0,10), array(10,60,40,50)) as $k => $v) {
      for ($y1 = mt_rand($v[0],$v[1]), $y2 = mt_rand($v[2],$v[3]); $y1 < $this->img_height && $y2 < $this->img_height;) {
        imageLine($this->img, $x1, $y1, $x2, $y2, $linesColor);
        $y1 += mt_rand($v[0], $v[1]);
        $y2 += mt_rand($v[2], $v[3]);
      }
    }
*/
    $y1 = 0;
    $y2 = $this->img_height;
    for ($i = 0, $n = $this->img_width/8; $i < $n; $i++) {
      mt_srand((double)microtime()*1000000);
      $x1 = mt_rand(0, $this->img_width);
      mt_srand((double)microtime()*1000000);
      $x2 = mt_rand(0, $this->img_width);
      imageLine($this->img, $x1, $y1, $x2, $y2, $linesColor);
    }

    $x1 = 0;
    $x2 = $this->img_width;
    for ($i = 0, $n = $this->img_height/8; $i < $n; $i++) {
      mt_srand((double)microtime()*1000000);
      $y1 = mt_rand(0, $this->img_height);
      mt_srand((double)microtime()*1000000);
      $y2 = mt_rand(0, $this->img_height);
      imageLine($this->img, $x1, $y1, $x2, $y2, $linesColor);
    }

    $padding_left = 15;
    $x2 = $this->img_width/($this->captchaCode_length + 1);
    for ($i = 0; $i < $this->captchaCode_length; $i++) {
      if (sizeof($this->ttf_list) > 0) {
        $font = $this->ttf_list[(int)mt_rand(0, count($this->ttf_list) - 1)];
      }
      $size_max = $this->img_height * $this->chars_max_size;
      $size = (int)mt_rand($size_max*$this->chars_min_size, $size_max);
      $rotation = mt_rand($this->chars_rotation*(-1), $this->chars_rotation);
      $x = $padding_left + $x2*$i;
      $y = mt_rand($size + 3, $this->img_height-5);
      $charForeColor = array();
      $charBackColor = array();
      do {
        $charForeColor['r'] = mt_rand($this->textColor[0][0], $this->textColor[1][0]);
      } while ($charForeColor['r'] == $backgroundRGB['r']);
      do {
        $charForeColor['g'] = mt_rand($this->textColor[0][0], $this->textColor[1][0]);
      } while ($charForeColor['g'] == $backgroundRGB['g']);
      do {
        $charForeColor['b'] = mt_rand($this->textColor[0][1], $this->textColor[1][1]);
      } while ($charForeColor['b'] == $backgroundRGB['b']);
      do {
        $charBackColor['r'] = ($charForeColor['r'] < 100 ? $charForeColor['r'] * 2 : mt_rand($this->textColor[0][0], $this->textColor[1][0]));
      } while (($charBackColor['r'] == $backgroundRGB['r']) && ($charBackColor['r'] == $charForeColor['r']));
      do {
        $charBackColor['g'] = ($charForeColor['g'] < 100 ? $charForeColor['g'] * 2 : mt_rand($this->textColor[0][1], $this->textColor[1][1]));
      } while (($charBackColor['g'] == $backgroundRGB['g']) && ($charBackColor['g'] == $charForeColor['g']));
      do {
        $charBackColor['b'] = ($charForeColor['b'] < 100 ? $charForeColor['b'] * 2 : mt_rand($this->textColor[0][2], $this->textColor[1][2]));
      } while (($charBackColor['b'] == $backgroundRGB['b']) && ($charBackColor['b'] == $charForeColor['b']));
      $charForeColor = $this->_imageColor($charForeColor);
      $charBackColor = $this->_imageColor($charBackColor);
      // Add the letter
      if (function_exists('imagettftext') && (sizeof($this->ttf_list) > 0)) {
        if($this->chars_shadow) {
//        imagettftext($this->img, $size, $rotation, $x+2,   $y, $charBackColor, $font, $this->captchaCode[$i]);
          imagettftext($this->img, $size, $rotation, $x+1, $y+1, $charBackColor, $font, $this->captchaCode[$i]);
        }
        imagettftext($this->img, $size, $rotation,   $x,   $y, $charForeColor, $font, $this->captchaCode[$i]);
      } else {
        $size = 5;
        $charBackColor = $this->_imageColor(array(0, 0, 255));
        $x = 26;
        $y = 20;
        $s = 36;
        if($this->chars_shadow) {
          imagestring($this->img, $size, $x + ($s * $i) + 1, $y+1, $this->captchaCode[$i], $charBackColor);
        }
        imagestring($this->img, $size, $x + ($s * $i), $y, $this->captchaCode[$i], $charForeColor);
      }
    }

    if($this->noise > 0) {
      for ($x = 0; $x < $this->img_width; $x++) {
          for ($y = 0; $y < $this->img_height; $y++) {

              $random = mt_rand(-$this->noise, $this->noise);
              $rgb = imagecolorat($this->img, $x, $y);

              $pixelRGB = array('r' => (($rgb >> 16) & 0xFF)+$random,
                                'g' => (($rgb >> 8) & 0xFF)+$random,
                                'b' => ($rgb & 0xFF)+$random);

              $pixelColor = $this->_imageColor($pixelRGB);
              imagesetpixel($this->img, $x, $y, $pixelColor);
          }

      }
    }

    @header('HTTP/1.0 200 OK');
//ensure no caching by browser - START
    @header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    @header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
    @header('Cache-Control: no-store, no-cache, must-revalidate'); // no cache for HTTP/1.1
    @header('Cache-Control: post-check=0, pre-check=0', false); // no cache for HTTP/1.1
    @header('Pragma: no-cache'); // no cache for HTTP/1.0
//ensure no caching by browser - END
    if($this->debug) {
      header('Content-Type: text/html');
    } else {
      @header('Content-Transfer-Encoding: binary');
      @header('Content-Disposition:attachment; filename=captcha_img.' . $this->img_type);
      @header('Content-Type: image/' . $this->img_type);
    }
    if(CAPCHA_USE_OB == 'true') {
      ob_start();
    }
    if($this->img_type == 'png') {
//      imagepng($this->img, NULL, (int)$this->quality);
      imagepng($this->img);
    } elseif($this->img_type == 'gif') {
      imagegif($this->img);
    } else {
//      imagejpeg($this->img, NULL, (int)$this->quality);
      imagejpeg($this->img);
    }
    imagedestroy($this->img);
    if(CAPCHA_USE_OB == 'true') {
      $img = ob_get_contents();
      ob_end_clean();
      @header('Content-Length: ' . strlen($img));
      echo $img;
    }

    return true;

  }

  function _imageColor($rgb) {
    $r = ($rgb['r'] > 255) ? 255 : (($rgb['r'] < 0) ? 0 : (int)($rgb['r']));
    $g = ($rgb['g'] > 255) ? 255 : (($rgb['g'] < 0) ? 0 : (int)($rgb['g']));
    $b = ($rgb['b'] > 255) ? 255 : (($rgb['b'] < 0) ? 0 : (int)($rgb['b']));
    $imageColorFunc = $this->imageColorFunc;
    $color = $imageColorFunc($this->img, $r, $g, $b);
    return $color;
  }

  function _getFileList($directory, $file_ext) {
    $file_list = array();
    if ($za_dir = @dir(rtrim($directory, '/'))) {
      while ($filename = $za_dir->read()) {
        if (preg_match('/\.' . $file_ext . '$/i', $filename) > 0) {
          $file_list[] = $directory . $filename;
        }
      }
      sort($file_list);
    }
    $za_dir->close();
    return $file_list;
  }

  function _get_gd_version() {
    if(!extension_loaded('gd')) return false;
    if(!function_exists('gd_info')) return false;
    $gd_info = gd_info();
    preg_match('/\d/', $gd_info['GD Version'], $m);
    $gd_info['version'] = $m[0];
    return $gd_info;
  }
}
?>
