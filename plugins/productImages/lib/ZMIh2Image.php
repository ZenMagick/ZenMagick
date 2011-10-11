<?php
/**
 * IH2 class for image manipulation
 *
 * @author  Tim Kroeger (original author)
 * @copyright Copyright 2005-2006
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 */
class ZMIh2Image {
    // full path
    private $src = null;
    // cached
    private $local = null;
    // the plugin
    private $plugin_;

    private $filename;
    private $extension;
    private $width;
    private $height;
    private $sizetype;
    var $canvas;
    private $zoom;
    private $watermark;
    private $force_canvas;

    /**
     * Constructor
     *
     * @param string src Image source name (e.g. - images/productimage.jpg)
     * @param string width The image width.
     * @param string height The image height.
     */
    function __construct($src, $width, $height) {
        $this->plugin_ = ZMPlugins::instance()->getPluginForId('productImages');
        $this->src = $src;
        $this->width = $width;
        $this->height = $height;
        $this->zoom = array();

        $this->determine_image_sizetype();

        if (($this->sizetype == 'large' || $this->sizetype == 'medium') && !$this->file_exists()) {
            // large or medium image specified but not found. strip superflous suffix.
            // now we can actually access the default image referenced in the database.
            $this->src = $this->strip_sizetype_suffix($this->src);
        }
        $this->filename = ZC_INSTALL_PATH . $this->src;
        $this->extension = substr($this->src, strrpos($this->src, '.'));

        list($newwidth, $newheight, $resize) = $this->calculate_size($this->width, $this->height);
        // set canvas dimensions
        if ($newwidth > 0 && $newheight > 0) {
            $this->canvas['width'] = $newwidth;
            $this->canvas['height'] = $newheight;
        }

        // initialize overlays (watermark, zoom overlay)
        $this->initialize_overlays($this->sizetype);
	  }

    /**
     * Check if the file exists.
     *
     * @return boolean <code>true</code> if found.
     */
    protected function file_exists() {
        // try to find file by using different file extensions if initial
        // source doesn't succeed
        if (is_file(ZC_INSTALL_PATH . $this->src)) {
            return true;
        } else {
            // do a quick search for files with common extensions
            $extensions = array('.png', '.PNG', '.jpg', '.JPG', '.jpeg', '.JPEG', '.gif', '.GIF');
            $base = substr($this->src, 0, strrpos($this->src, '.'));
            for ($i=0; $i<count($extensions); $i++) {
                if (is_file(ZC_INSTALL_PATH . $base . $extensions[$i])) {
                    $this->src = $base . $extensions[$i];
                    return true;
                }
            }
        }

        return false;
    }

	function determine_image_sizetype() {
		global $ihConf;

		if (strstr($this->src, $ihConf['large']['suffix'])) {
			$this->sizetype = 'large';
		} elseif (strstr($this->src, $ihConf['medium']['suffix'])) {
			$this->sizetype = 'medium';
		} elseif ((intval($this->width) == intval($ihConf['small']['width'])) && (intval($this->height) == intval($ihConf['small']['height']))) {
			$this->sizetype = 'small';
		} else
		$this->sizetype = 'generic';
	}

	function strip_sizetype_suffix($src) {
    global $ihConf;
		$src = preg_replace('/' . $ihConf['large']['suffix'] . '\./', '.', $src);
		$src = preg_replace('/' . $ihConf['medium']['suffix'] . '\./', '.', $src);
		$src = str_replace($ihConf['medium']['prefix'] . '/', '/', $src);
		$src = str_replace($ihConf['large']['prefix'] . '/', '/', $src);
    return $src;
	}

	function initialize_overlays($sizetype) {
		global $ihConf;

		switch ($sizetype) {
			case 'large':
				$this->watermark['file'] = ($ihConf['large']['watermark']) ? ZC_INSTALL_PATH . $ihConf['dir']['images'] . 'large/watermark' . $ihConf['large']['suffix'] . '.png' : '';
				$this->zoom['file'] = (isset($ihConf['large']['zoom'])&&$ihConf['large']['zoom']) ? ZC_INSTALL_PATH . $ihConf['dir']['images'] . 'large/zoom' . $ihConf['large']['suffix'] . '.png' : '';
				break;
			case 'medium':
				$this->watermark['file'] = ($ihConf['medium']['watermark']) ? ZC_INSTALL_PATH . $ihConf['dir']['images'] . 'medium/watermark' . $ihConf['medium']['suffix'] . '.png': '';
				$this->zoom['file'] = (isset($ihConf['large']['zoom'])&&$ihConf['medium']['zoom']) ? ZC_INSTALL_PATH . $ihConf['dir']['images'] . 'medium/zoom' . $ihConf['medium']['suffix'] . '.png' : '';
				break;
			case 'small':
				$this->watermark['file'] = ($ihConf['small']['watermark']) ? ZC_INSTALL_PATH . $ihConf['dir']['images'] . 'watermark.png' : '';
				$this->zoom['file'] = (isset($ihConf['large']['zoom'])&&$ihConf['small']['zoom']) ? ZC_INSTALL_PATH . $ihConf['dir']['images'] . 'zoom.png' : '';
				break;
			default:
				$this->watermark['file'] = '';
				$this->zoom['file'] = '';
				break;
		}

		if (($this->watermark['file'] != '') && is_file($this->watermark['file'])) {
		// set watermark parameters
			list($this->watermark['width'], $this->watermark['height']) = @getimagesize($this->watermark['file']);
			list($this->watermark['startx'], $this->watermark['starty']) = $this->calculate_gravity($this->canvas['width'], $this->canvas['height'], $this->watermark['width'], $this->watermark['height'], $ihConf['watermark']['gravity']);
			//echo '(' . $this->watermark['startx'] . ', ' . $this->watermark['starty'] . ') ' . $this->watermark['width'] . 'x' . $this->watermark['height'] . '<br />';
		} else {
			$this->watermark['file'] = '';
		}

		if (($this->zoom['file'] != '') && is_file($this->zoom['file'])) {
		// set zoom parameters
			list($this->zoom['width'], $this->zoom['height']) = @getimagesize($this->zoom['file']);
			list($this->zoom['startx'], $this->zoom['starty']) = $this->calculate_gravity($this->canvas['width'], $this->canvas['height'], $this->zoom['width'], $this->zoom['height'], $ihConf['zoom']['gravity']);
			//echo '(' . $this->zoom['startx'] . ', ' . $this->zoom['starty'] . ') ' . $this->zoom['width'] . 'x' . $this->zoom['height'] . '<br />';
		} else {
			$this->zoom['file'] = '';
		}
	}

	function get_local() {
		if ($this->local) return $this->local;
		// check if image handler is available and if we should resize at all
		if ($this->resizing_allowed()) {
			$this->local = $this->get_resized_image($this->width, $this->height);
		} else {
      $this->local = $this->src;
    }
		return $this->local;
	}

  function resizing_allowed() {
    global $ihConf;
    // only resize if resizing is turned on
    // don't resize template images so test for the configured images directory.
    // if $ihConf['noresize_key'] is found within the string, don't resize either.
    $allowed = false;
    if ($ihConf['resize'] &&
        ((strpos($this->src, $ihConf['dir']['images']) === 0) ||
         ((strpos($this->src, substr(ZMSettings::get('plugins.imageHandler2.cachedir'), strlen(ZC_INSTALL_PATH))) === 0))) &&
        (strpos($this->src, ZMSettings::get('plugins.imageHandler2.noresize_key')) === false)) {
      $allowed = true;
      foreach (ZMSettings::get('plugins.imageHandler2.noresize_dirs') as $dir) {
        $allowed = (strpos($this->src, $ihConf['dir']['images'] . $dir . '/') !== 0);
      }
    }
    return $allowed;
  }

	function get_resized_image($width, $height, $override_sizetype = '', $filetype = '') {
		global $ihConf;

		$sizetype = ($override_sizetype == '') ? $this->sizetype : $override_sizetype;
		switch ($sizetype) {
      case 'large':
        $file_extension = (($ihConf['large']['filetype'] == 'no_change') ? $this->extension : '.' . $ihConf['large']['filetype']);
        $background = $ihConf['large']['bg'];
        $quality = $ihConf['large']['quality'];
        $width = $ihConf['large']['width'];
        $height = $ihConf['large']['height'];
        break;
      case 'medium':
        $file_extension = (($ihConf['medium']['filetype'] == 'no_change') ? $this->extension : '.' . $ihConf['medium']['filetype']);
        $background = $ihConf['medium']['bg'];
        $quality = $ihConf['medium']['quality'];
        break;
      case 'small':
        $file_extension = (($ihConf['small']['filetype'] == 'no_change') ? $this->extension : '.' . $ihConf['small']['filetype']);
        $background = $ihConf['small']['bg'];
        $quality = $ihConf['small']['quality'];
        break;
      default:
        $file_extension = $this->extension;
        $background = ZMSettings::get('plugins.imageHandler2.defaults.bg');
        $quality = ZMSettings::get('plugins.imageHandler2.defaults.quality');
        break;
		}
		list($newwidth, $newheight, $resize) = $this->calculate_size($width, $height);
		// set canvas dimensions
		if (($newwidth > 0) && ($newheight > 0)) {
			$this->canvas['width'] = $newwidth;
			$this->canvas['height'] = $newheight;
		}

		$this->initialize_overlays($sizetype);

		// override filetype?
		$file_extension = ($filetype == '') ? $file_extension : $filetype;

		// Do we need to resize, watermark, zoom or convert to another filetype?
		if ($resize || ($this->watermark['file'] != '') || ($this->zoom['file'] != '') || ($file_extension != $this->extension)){
			$local = ZMProductImagesPlugin::getCacheName($this->src . $this->watermark['file'] . $this->zoom['file'] . $quality . $background . $ihConf['watermark']['gravity'] . $ihConf['zoom']['gravity'], '.image.' . $newwidth . 'x' . $newheight . $file_extension);
			//echo $local . '<br />';
			$mtime = @filemtime($local); // 0 if not exists
			if ( (($mtime > @filemtime($this->filename)) && ($mtime > @filemtime($this->watermark['file'])) && ($mtime > @filemtime($this->zoom['file'])) ) ||
				$this->resize_imageIM($file_extension, $local, $background, $quality) ||
				$this->resize_imageGD($file_extension, $local, $background, $quality) ) {
				return str_replace(ZC_INSTALL_PATH, '', $local);
			}
			//still here? resizing failed
		}
		return $this->src;
	}

	/**
	 * Calculate desired image size as set in admin->configuration->images.
	 */
	function calculate_size($pref_width, $pref_height = '') {
		list($width, $height) = @getimagesize($this->filename);
		// default: nothing happens (preferred dimension = actual dimension)
		$newwidth = $width;
		$newheight = $height;
		if (($width > 0) && ($height > 0)) {
			if ((strrpos($pref_width . $pref_height, '%') !== false)) {
				// possible scaling to % of original size
				// calculate new dimension in pixels
				if (($pref_width !== '') && ($pref_height != '')) {
					// different factors for width and height
					$hscale = intval($pref_width) / 100;
					$vscale = intval($pref_height) / 100;
				} else {
					// one of the the preferred values has the scaling factor
					$hscale = intval($pref_width . $pref_height) / 100;
					$vscale = $hscale;
				}
				$newwidth = floor($width * $hscale);
				$newheight = floor($height * $vscale);
			} else {
				$this->force_canvas = (strrpos($pref_width . $pref_height, '!') !== false);
				// failsafe for old zen-cart configuration one image dimension set to 0
				$pref_width = ($pref_width == '' || intval($pref_width) == 0) ? 0 : intval($pref_width);
				$pref_height = ($pref_height == '' || intval($pref_height) == 0) ? 0 : intval($pref_height);
				if ((!$this->force_canvas) && ($pref_width != 0) && ($pref_height != 0)) {
					// if no '!' is appended to dimensions we don't force the canvas size to
					// match the preferred size. the image will not have the exact specified size.
					// (we're in fact forcing the old 0-dimension zen-magic trick)
					$oldratio = $width / $height;
					$pref_ratio = $pref_width / $pref_height;
					if ($pref_ratio > $oldratio) {
						$pref_width = 0;
					} else {
						$pref_height = 0;
					}
				}

				// now deal with the calculated preferred sizes
				if (($pref_width == 0) && ($pref_height > 0)) {
					// image dimensions are calculated to fit the preferred height
					$pref_width = floor($width * ($pref_height / $height));
				} elseif (($pref_width > 0) && ($pref_height == 0)) {
					// image dimensions are calculated to fit the preferred width
					$pref_height = floor($height * ($pref_width / $width));
				}
        if ((($pref_width > 0) && ($pref_height > 0))
          && (($pref_width < $width ) || ($pref_height < $height))) {
					// only calculate new dimensions if we have sane values
					$newwidth = $pref_width;
					$newheight = $pref_height;
				}
			}
		}
		$resize = (($newwidth != $width) || ($newheight != $height));
		return array($newwidth, $newheight, $resize);
	}

	function resize_imageIM($file_ext, $dest_name, $bg, $quality = 85) {
    global $ihConf;
    global $messageStack;
    //echo 'im_convert: ' . $ihConf['im_convert'] . '<br />';
    // check if convert is configured
    if(!ZMSettings::get('plugins.imageHandler2.im_convert')) return false;
    //echo 'Trying to use ImageMagick.<br />';
    $size = $this->canvas['width'] . 'x' . $this->canvas['height'];
    //echo $size . '<br />';
    $bg = trim($bg);
    $bg = ($bg == '') ? ZMSettings::get('plugins.imageHandler2.defaults.bg') : $bg;
    $transparent = (strpos($bg, 'transparent') !== false);
    $transparent &= preg_match('/(\.gif)|(\.png)/i', $file_ext);
    $color = $this->get_background_rgb($bg);
    if ($color) {
      $bg = 'rgb(' . $color['r'] . ',' .  $color['g'] . ',' . $color['b'] . ')';
      $bg .= $transparent ? ' transparent' : '';
    }
    $gif_treatment = false;
    if ($transparent && ($file_ext == ".gif")) {
      // Special treatment for gif files
      $bg = trim(str_replace('transparent', '', $bg));
      $bg = ($bg != '') ? $bg : 'rgb(255,255,255)';
      $temp_name = substr($dest_name, 0, strrpos($dest_name, '.')) . '-gif_treatment.png';
      $gif_treatment = true;
    } else {
      $bg = (strpos($bg, 'transparent') === false) ? $bg : 'transparent';
    }

    // still no background? default to transparent
		$bg = ($bg != '') ? $bg : 'transparent';
    $command  = ZMSettings::get('plugins.imageHandler2.im_convert') . " -size $size ";
    $command .= "xc:none -fill " . ($gif_treatment ? "transparent" : "\"$bg\"") . " -draw 'color 0,0 reset'";
    $size .= $this->force_canvas ? '' : '!';
    $command .= ' "' . $this->filename . '" -compose Over -gravity Center -geometry ' . $size . ' -composite';
    $command .= ($this->watermark['file'] != '') ? ' "' . $this->watermark['file'] . '" -compose Over -gravity ' . $ihConf['watermark']['gravity'] . " -composite" : '';
    $command .= ($this->zoom['file'] != '') ? ' "' . $this->zoom['file'] . '" -compose Over -gravity ' . $ihConf['zoom']['gravity'] . " -composite " : ' ';
    $command .= $gif_treatment ? $temp_name : (preg_match("/\.jp(e)?g/i", $file_ext) ? "-quality $quality " : '') . "\"$dest_name\"";
    @exec($command . ' 2>&1', $message, $retval);
    if ($gif_treatment) {
      if ($retval != 0) return false;
      $command  = ZMSettings::get('plugins.imageHandler2.im_convert') . " -size $size ";
      $command .= "xc:none -fill \"$bg\" -draw 'color 0,0 reset'";
      $command .= " \"$temp_name\" -compose Over -gravity Center -geometry $size -composite";
      $command .= " \"$temp_name\" -channel Alpha -threshold " . ZMSettings::get('plugins.imageHandler2.trans_threshold') . " -compose CopyOpacity -gravity Center -geometry $size -composite";
      $command .= " \"$dest_name\"";
      @exec($command . ' 2>&1', $message, $retval);
    }
    if ($retval == 0) return true;

		return false;
	}

  function alphablend($background, $overlay, $threshold = -1) {
    /* -------------------------------------------------------------------- */
    /*      Simple cases we want to handle fast.                            */
    /* -------------------------------------------------------------------- */
    if ($overlay['alpha'] == 0) return $overlay;
    if ($overlay['alpha'] == 127) return $background;
    if (($background['alpha'] == 127) && ($threshold == -1)) return $overlay;

    /* -------------------------------------------------------------------- */
    /*      What will the overlay and background alphas be?  Note that      */
    /*      the background weighting is substantially reduced as the        */
    /*      overlay becomes quite opaque.                                   */
    /* -------------------------------------------------------------------- */
    $alpha =  $overlay['alpha'] * $background['alpha'] / 127;
    if (($threshold > -1) && ($alpha <= $threshold)) {
      $background['alpha'] = 0;
      $alpha = 0;
    }

    $overlay_weight = 127 - $overlay['alpha'];
    $background_weight = (127 - $background['alpha']) * $overlay['alpha'] / 127;
    $total_weight = $overlay_weight + $background_weight;

    $red = (($overlay['red'] * $overlay_weight) + ($background['red'] * $background_weight)) / $total_weight;
    $green = (($overlay['green'] * $overlay_weight) + ($background['green'] * $background_weight)) / $total_weight;
    $blue = (($overlay['blue'] * $overlay_weight) + ($background['blue'] * $background_weight)) / $total_weight;

    return array('alpha'=>$alpha, 'red'=>$red, 'green'=>$green, 'blue'=>$blue);
  }

  function imagemergealpha($background, $overlay, $startwidth, $startheight, $newwidth, $newheight, $threshold = '', $background_override = '', $debug = false) {
    //restore the transparency
    if (ZMSettings::get('plugins.imageHandler2.gdlib')>1){
      imagealphablending($background, false);
    }

    $threshold = ($threshold != '') ? intval(127 * intval($threshold) / 100) : -1;

    for($x=0; $x<$newwidth; $x++) {
      for($y=0; $y<$newheight; $y++) {
        $c = imagecolorat($background, $x + $startwidth, $y + $startheight);
        $background_color = imagecolorsforindex($background, $c);
        //if ($debug) echo "($x/$y): " . $background_color['alpha'] . ':' . $background_color['red'] . ':' . $background_color['green'] . ':' . $background_color['blue'] . ' ++ ';
        $c = imagecolorat($overlay, $x, $y);
        $overlay_color = imagecolorsforindex($overlay, $c);
        //if ($debug) echo $overlay_color['alpha'] . ':' . $overlay_color['red'] . ':' . $overlay_color['green'] . ':' . $overlay_color['blue'] . ' ==&gt; ';
        $color = $this->alphablend($background_color, $overlay_color, $threshold);
        //if ($debug) echo $color['alpha'] . ':' . $color['red'] . ':' . $color['green'] . ':' . $color['blue'] . '<br />';
        //if ($threshold > -1) $color['alpha'] = ($color['alpha'] > $threshold) ? 127 : 0;


        if (($threshold > -1) && ($color['alpha'] > $threshold)) {
          $color = $background_override;
        } else {
          $color = imagecolorallocatealpha($background, $color['red'], $color['green'], $color['blue'], $color['alpha']);
        }
        imagesetpixel($background, $x + $startwidth, $y + $startheight, $color);
      }
    }
    return $background;
  }


  function resize_imageGD($file_ext, $dest_name, $bg, $quality = 85) {
    global $messageStack;

    if(ZMSettings::get('plugins.imageHandler2.gdlib') < 1) return false; //no GDlib available or wanted
    $srcimage = $this->load_imageGD($this->filename);
    if (!$srcimage) return false; // couldn't load image
    $src_ext = substr($this->filename, strrpos($this->filename, '.'));
    $srcwidth = imagesx($srcimage);
    $srcheight = imagesy($srcimage);
    if ($this->force_canvas) {
      if (($srcwidth / $this->canvas['width']) > ($srcheight / $this->canvas['height'])) {
        $newwidth = $this->canvas['width'];
        $newheight = floor(($newwidth / $srcwidth) * $srcheight);
       } else {
        $newheight = $this->canvas['height'];
        $newwidth = floor(($newheight / $srcheight) * $srcwidth);
       }
    } else {
      $newwidth = $this->canvas['width'];
      $newheight = $this->canvas['height'];
    }
    $startwidth = (($this->canvas['width'] - $newwidth)/2);
    $startheight = (($this->canvas['height'] - $newheight)/2);

    if((ZMSettings::get('plugins.imageHandler2.gdlib')>1) && function_exists("imagecreatetruecolor")){
      $tmpimg = @imagecreatetruecolor ($newwidth, $newheight);
    }
    if(!$tmpimg) $tmpimg = @imagecreate($newwidth, $newheight);
    if(!$tmpimg) return false;

    //keep alpha channel if possible
    if (ZMSettings::get('plugins.imageHandler2.gdlib')>1 && function_exists('imagesavealpha')){
      imagealphablending($tmpimg, false);
    }
    //try resampling first
    if(function_exists("imagecopyresampled")){
      if(!@imagecopyresampled($tmpimg, $srcimage, 0, 0, 0, 0, $newwidth, $newheight, $srcwidth, $srcheight)) {
        imagecopyresized($tmpimg, $srcimage, 0, 0, 0, 0, $newheight, $newwidth, $srcwidth, $srcheight);
      }
    } else {
      imagecopyresized($tmpimg, $srcimage, 0, 0, 0, 0, $newwidth, $newheight, $srcwidth, $srcheight);
    }

    imagedestroy($srcimage);

    // initialize FIRST background image (transparent canvas)
    if((ZMSettings::get('plugins.imageHandler2.gdlib')>1) && function_exists("imagecreatetruecolor")){
      $newimg = @imagecreatetruecolor ($this->canvas['width'], $this->canvas['height']);
    }
    if(!$newimg) $newimg = @imagecreate($this->canvas['width'], $this->canvas['height']);
    if(!$newimg) return false;

    if (ZMSettings::get('plugins.imageHandler2.gdlib')>1 && function_exists('imagesavealpha')){
      imagealphablending($newimg, false);
    }
    $background_color = imagecolorallocatealpha($newimg, 255, 255, 255, 127);
    imagefilledrectangle($newimg, 0, 0, $this->canvas['width'] - 1, $this->canvas['height'] - 1, $background_color);

    //$newimg = $this->imagemergealpha($newimg, $tmpimg, $startwidth, $startheight, $newwidth, $newheight);
    imagecopy($newimg, $tmpimg, $startwidth, $startheight, 0, 0, $newwidth, $newheight);
    imagedestroy($tmpimg);
    $tmpimg = $newimg;


    if (ZMSettings::get('plugins.imageHandler2.gdlib')>1 && function_exists('imagesavealpha')){
      imagealphablending($tmpimg, true);
    }
    // we need to watermark our images
    if ($this->watermark['file'] != '') {
      $this->watermark['image'] = $this->load_imageGD($this->watermark['file']);
      imagecopy($tmpimg, $this->watermark['image'], $this->watermark['startx'], $this->watermark['starty'], 0, 0, $this->watermark['width'], $this->watermark['height']);
      //$tmpimg = $this->imagemergealpha($tmpimg, $this->watermark['image'], $this->watermark['startx'], $this->watermark['starty'], $this->watermark['width'], $this->watermark['height']);
      imagedestroy($this->watermark['image']);
    }

    // we need to zoom our images
    if ($this->zoom['file'] != '') {
      $this->zoom['image'] = $this->load_imageGD($this->zoom['file']);
      //imagecopy($tmpimg, $this->zoom['image'], $this->zoom['startx'], $this->zoom['starty'], 0, 0, $this->zoom['width'], $this->zoom['height']);
      $tmpimg = $this->imagemergealpha($tmpimg, $this->zoom['image'], $this->zoom['startx'], $this->zoom['starty'], $this->zoom['width'], $this->zoom['height']);
      imagedestroy($this->zoom['image']);
    }

    // initialize REAL background image (filled canvas)
    if((ZMSettings::get('plugins.imageHandler2.gdlib')>1) && function_exists("imagecreatetruecolor")){
      $newimg = @imagecreatetruecolor ($this->canvas['width'], $this->canvas['height']);
    }
    if(!$newimg) $newimg = @imagecreate($this->canvas['width'], $this->canvas['height']);
    if(!$newimg) return false;

    if (ZMSettings::get('plugins.imageHandler2.gdlib')>1 && function_exists('imagesavealpha')){
      imagealphablending($newimg, false);
    }

    // determine background
    // default to white as "background" -> better rendering on bright pages
    // when downsampling to gif with just boolean transparency
    $color = $this->get_background_rgb($bg);
    if (!$color) {
      $color = $this->get_background_rgb(ZMSettings::get('plugins.imageHandler2.defaults.bg'));
      $transparent = (strpos(ZMSettings::get('plugins.imageHandler2.defaults.bg'), 'transparent') !== false);
    } else {
      $transparent = (strpos($bg, 'transparent') !== false);
    }
    $transparent &= preg_match('/(\.gif)|(\.png)/i', $file_ext);

    $alpha = $transparent ? 127 : 0;
    if ($color) {
      $background_color = imagecolorallocatealpha($newimg, intval($color['r']), intval($color['g']), intval($color['b']), $alpha);
    } else {
      $background_color = imagecolorallocatealpha($newimg, 255, 255, 255, $alpha);
    }
    imagefilledrectangle($newimg, 0, 0, $this->canvas['width'] - 1, $this->canvas['height'] - 1, $background_color);

    if (ZMSettings::get('plugins.imageHandler2.gdlib')>1 && function_exists('imagesavealpha')){
      imagealphablending($newimg, true);
    }

    if (preg_match('/\.gif/i', $file_ext)) {
      if ($transparent) {
        $newimg = $this->imagemergealpha($newimg, $tmpimg, 0, 0, $this->canvas['width'], $this->canvas['height'], ZMSettings::get('plugins.imageHandler2.trans_threshold'), $background_color);
        imagecolortransparent($newimg, $background_color);
      } else {
        imagecopy($newimg, $tmpimg, 0, 0, 0, 0, $this->canvas['width'], $this->canvas['height']);
      }
    } else {
      if ($transparent) {
        $newimg = $this->imagemergealpha($newimg, $tmpimg, 0, 0, $this->canvas['width'], $this->canvas['height']);
      } else {
        imagecopy($newimg, $tmpimg, 0, 0, 0, 0, $this->canvas['width'], $this->canvas['height']);
      }
    }
    imagedestroy($tmpimg);

    if (ZMSettings::get('plugins.imageHandler2.gdlib')>1 && function_exists('imagesavealpha')){
      imagesavealpha($newimg, true);
    }

    if (preg_match('/\.gif/i', $file_ext)) {
      if (ZMSettings::get('plugins.imageHandler2.gdlib')>1 && function_exists('imagetruecolortopalette')) {
        imagetruecolortopalette($newimg, true, 256);
      }
    }

    return $this->save_imageGD($file_ext, $newimg, $dest_name, $quality);
  }

	function calculate_gravity($canvaswidth, $canvasheight, $overlaywidth, $overlayheight, $gravity) {
	      // Calculate overlay position from gravity setting. Center as default.
	      $startheight = (($canvasheight - $overlayheight)/2);
	      $startwidth = (($canvaswidth - $overlaywidth)/2);
	      if (strpos($gravity, 'North') !== false) {
	        $startheight = 0;
	      } elseif (strpos($gravity, 'South') !== false) {
	        $startheight = $canvasheight - $overlayheight;
	      }
	      if (strpos($gravity, 'West') !== false) {
	        $startwidth = 0;
	      } elseif (strpos($gravity, 'East') !== false) {
	        $startwidth = $canvaswidth - $overlaywidth;
	      }
	      return array($startwidth, $startheight);
	}

	function load_imageGD($src_name) {
		// create an image of the given filetype
		$file_ext = substr($src_name, strrpos($src_name, '.'));
		switch (strtolower($file_ext)) {
			case '.gif':
			    if(!function_exists("imagecreatefromgif")) return false;
			    	$image = @imagecreatefromgif($src_name);
				break;
			case '.png':
			    if(!function_exists("imagecreatefrompng")) return false;
				$image = @imagecreatefrompng($src_name);
				break;
			case '.jpg':
			case '.jpeg':
			    if(!function_exists("imagecreatefromjpeg")) return false;
				$image = @imagecreatefromjpeg($src_name);
				break;
		}
		return $image;
	}

	function save_imageGD($file_ext, $image, $dest_name, $quality = 75) {
		switch (strtolower($file_ext)) {
			case '.gif':
			    if(!function_exists("imagegif")) return false;
				$ok = imagegif($image, $dest_name);
				break;
			case '.png':
			    if(!function_exists("imagepng")) return false;
				$quality = (int)$quality/100;
				$ok = imagepng($image, $dest_name, $quality);
				break;
			case '.jpg':
			case '.jpeg':
			    if(!function_exists("imagejpeg")) return false;
				$ok = imagejpeg($image, $dest_name, $quality);
				break;
			default: $ok = false;
		}
		imagedestroy($image);

		return $ok;
	}

	function get_background_rgb($bg) {
		$bg = trim(str_replace('transparent', '', $bg));
		list($red, $green, $blue)= preg_split('/[, :]/', $bg);
		if (preg_match('/[0-9]+/', $red.$green.$blue)) {
			$red = min(intval($red), 255);
			$green = min(intval($green), 255);
			$blue = min(intval($blue), 255);
			$color = array('r'=>$red, 'g'=>$green, 'b'=>$blue);
			return $color;
		} else {
			return false;
		}
	}

	function get_additional_parameters($alt, $width, $height, $parameters) {
		global $ihConf;
    if ($this->sizetype == 'small') {
      if ($ihConf[$this->sizetype]['zoom']) {
        if ($this->zoom['file'] == '' || !$ihConf[$this->sizetype]['hotzone']) {
          // if no zoom image, the whole image triggers the popup
          $this->zoom['startx'] = 0;
          $this->zoom['starty'] = 0;
          $this->zoom['width'] = $width;
          $this->zoom['height'] = $height;
        }
        //escape possible quotes if they're not already escapped
        $alt = preg_replace("/([^\\\\])'/", '$1\\\'', $alt);
        $alt = str_replace('"', '&quot;', $alt);
        // strip potential suffixes just to be sure
        $src = $this->strip_sizetype_suffix($this->src);
        // define zoom sizetype
        $zoom_sizetype = ($this->sizetype=='small')?'medium':'large';
        // additional zoom functionality
        $products_image_directory = substr($src, strlen($ihConf['dir']['images']), strrpos($src, '/') - strlen($ihConf['dir']['images']) + 1);
        $products_image_filename = substr($src, strrpos($src, '/'), strlen ($src) - strrpos ($src, '/') - strlen ($this->extension));
        $products_image_zoom = $ihConf['dir']['images'] . $zoom_sizetype . '/' . $products_image_directory . $products_image_filename . $ihConf[$zoom_sizetype]['suffix'] . $this->extension;
        $ih_zoom_image = new ZMIh2Image($products_image_zoom, $ihConf[$zoom_sizetype]['width'], $ihConf[$zoom_sizetype]['height']);
        $products_image_zoom = $ih_zoom_image->get_local();
        list($zoomwidth, $zoomheight) = @getimagesize(ZC_INSTALL_PATH . $products_image_zoom);
        // we should parse old parameters here and possibly merge some inc case they're duplicate
        $parameters .= ($parameters != '') ? ' ' : '';
        return $parameters . 'style="position:relative" onmouseover="showtrail(' . "'$products_image_zoom','$alt',$width,$height,$zoomwidth,$zoomheight,this," . $this->zoom['startx'].','.$this->zoom['starty'].','.$this->zoom['width'].','.$this->zoom['height'].');" onmouseout="hidetrail();" ';
      }
       return $parameters;
    }
    return $parameters;
    }
}
