<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
/*
 * T_ML_COMMENT does not exist in PHP 5.
 * The following three lines define it in order to
 * preserve backwards compatibility.
 *
 * The next two lines define the PHP 5 only T_DOC_COMMENT,
 * which we will mask as T_ML_COMMENT for PHP 4.
 */
if (!defined('T_ML_COMMENT')) {
   define('T_ML_COMMENT', T_COMMENT);
} else {
   define('T_DOC_COMMENT', T_ML_COMMENT);
}


/**
 * Compress core into a single file 'core.php'.
 *
 * @author mano
 * @package net.radebatz.zenmagick.admin
 * @version $Id$
 */
class ZMCoreCompressor extends ZMObject {
    var $coreDirname_;
    var $strippedDirname_;
    var $flatDirname_;
    var $coreFilename_;
    var $errors_;


    /**
     * Default c'tor.
     */
    function ZMCoreCompressor() {
    global $zm_runtime;

        parent::__construct();

        $this->coreDirname_ = $zm_runtime->getZMRootPath().'core';
        $this->strippedDirname_ = $zm_runtime->getZMRootPath().'core.stripped';
        $this->flatDirname_ = $zm_runtime->getZMRootPath().'core.flat';
        $this->coreFilename_ = $zm_runtime->getZMRootPath().'core.php';
        $this->errors_ = array();
    }

    /**
     * Default c'tor.
     */
    function __construct() {
        $this->ZMCoreCompressor();
    }

    /**
     * Default d'tor.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get errors.
     *
     * @return array List of text messages.
     */
    function getErrors() {
        return $this->errors_;
    }

    /**
     * Check for errors.
     *
     * @return bool <code>true</code> if errors exist.
     */
    function hasErrors() {
        return 0 != count($this->errors_);
    }

    /**
     * Disable / remove the core.php file, effectively disabling the use of it.
     */
    function disable() {
        @unlink($this->coreFilename_);
    }

    /**
     * check if enabled.
     *
     * @return bool <code>true</code> if core.php exists, <code>false</code> if not.
     */
    function isEnabled() {
        return file_exists($this->coreFilename_);
    }

    /**
     * Clean up all temp. files.
     */
    function clean() {
        zm_rmdir($this->strippedDirname_);
        zm_rmdir($this->flatDirname_);
    }

    /**
     * Generate 'core.php'
     */
    function generate() {
        $this->clean();
        @unlink($this->coreFilename_);

        $this->_stripPhpDir($this->coreDirname_, $this->strippedDirname_);
        if (!$this->hasErrors()) {
            $this->_flattenDirStructure($this->strippedDirname_, $this->flatDirname_);
            if (!$this->hasErrors()) {
                $this->_compressToSingleFile($this->flatDirname_, $this->coreFilename_);
            }
        }

        $this->clean();
    }

    /**
     * Strip the given PHP source text.
     *
     * @param string source The PHP source code.
     * @return string The stripped code.
     */
    function _stripPhpSource($source) {
        $source = str_replace("<?php  ?>", '', $source);
        $source = trim($source);
        $tokens = token_get_all($source);

        ob_start();
        foreach ($tokens as $token) {
           if (is_string($token)) {
               // simple 1-character token
               echo $token;
           } else {
               // token array
               list($id, $text) = $token;
         
               switch ($id) {
                   case T_COMMENT:
                   case T_ML_COMMENT: // we've defined this
                   case T_DOC_COMMENT: // and this
                       // no action on comments
                       break;
                   case T_WHITESPACE:
                       echo ' ';
                       break;
                   case T_OPEN_TAG:
                       echo '<?php ';
                       break;
                   case T_CLOSE_TAG:
                       echo '?>';
                       break;

                   default:
                       // anything else -> output "as is"
                       echo $text;
                       break;
               }
           }
        }
        return ob_get_clean();
    }

    /**
     * Strip a PHP file.
     *
     * @param string in The input filename.
     * @param string out The output filename; if <code>null</code> just echo.
     */
    function _stripPhpFile($in, $out=null) {
        $source = file_get_contents($in);
        $source = $this->_stripPhpSource($source);
        // strip empty PHP open/close tags
        $source = $this->_stripPhpSource($source);
        if (null !== $out) {
            // write to file
            if (!$handle = fopen($out, 'ab')) {
                array_push($this->errors_, 'could not open file for writing ' . $file);
                return;
            }

            if (false === fwrite($handle, $source)) {
                array_push($this->errors_, 'could not write to file ' . $file);
                return;
            }
      
            fclose($handle);
        } else {
            echo $source;
        }
    }

    /**
     * Recursivley strip a directory.
     *
     * <p>uses <code>zm_find_includes()</code> to find files to process.</p>
     *
     * @param string in The input directory.
     * @param string out The output directory.
     * @param bool recursive If true, strip recursivley.
     */
    function _stripPhpDir($in, $out=null, $recursive=true) {
        //echo "** stripping " . $in . " into " . $out . "\n";
        if (!zm_ends_with($in, '/')) $in .= '/';
        if (!zm_ends_with($out, '/')) $out .= '/';

        $files = zm_find_includes($in, $recursive);

        foreach ($files as $name => $infile) {
            $name = basename($infile);
            $dirbase = substr(dirname($infile), strlen($in));
            $outdir = $out.$dirbase.'/';
            $outfile = $outdir.$name;
            if (!file_exists($outdir)) {
                if (!file_exists(dirname($outdir))) {
                    mkdir(dirname($outdir), 0755);
                }
                mkdir($outdir, 0755);
            }
            //echo $infile . " >> " . $outfile ."\n";
            $this->_stripPhpFile($infile, $outfile);
        }
    }

    /**
     * Flatten the directory structure.
     *
     * @param string in The input directory.
     * @param string out The output directory.
     */
    function _flattenDirStructure($in, $out) {
        //echo "** flatten " . $in . " into " . $out . "\n";
        $files = zm_find_includes($in.'/', true);

        if (!file_exists($out)) {
            mkdir($out, 0755);
        }

        $inpath = explode('/', $in);
        foreach ($files as $name => $infile) {
            $path = explode('/', $infile);
            $level = count($path)-count($inpath)-1;
            $outdir = $out;
            for ($ii=1;$ii<=$level;++$ii) {
                $outdir .= '/'.$ii;
            }
            if (!file_exists($outdir)) {
                if (!file_exists(dirname($outdir))) {
                    mkdir(dirname($outdir), 0755);
                }
                mkdir($outdir, 0755);
            }
            $outfile = $outdir.'/'.basename($infile);
            $sub = 1;
            while (file_exists($outfile)) {
                $outfile = $outdir.'/'.$sub.'-'.basename($infile);
                ++$sub;
            }
            //echo $infile . " >> " . $outfile . "\n";
            $source = file_get_contents($infile);

            if (!$handle = fopen($outfile, 'ab')) {
                array_push($this->errors_, 'could not open file for writing ' . $file);
                return;
            }

            if (false === fwrite($handle, $source)) {
                array_push($this->errors_, 'could not write to file ' . $file);
                return;
            }
      
            fclose($handle);
        }
    }

    /**
     * Compress all files into a single file
     *
     * @param string in The input directory.
     * @param string outfile The output file.
     */
    function _compressToSingleFile($in, $outfile) {
        //echo "** compress " . $in . " into " . $outfile . "\n";
        $files = zm_find_includes($in.'/', true);

        $tmp = array();
        // mess around with results to find some files we need to add first...
        foreach ($files as $name => $file) {
            $off = strpos($file, $in);
            $tmp[substr($file, $off+strlen($in)+1)] = $file;
        }
        $tmp2 = array();
        array_push($tmp2, $tmp['bootstrap.php']); unset($tmp['bootstrap.php']);
        array_push($tmp2, $tmp['1/settings.php']); unset($tmp['1/settings.php']);
        array_push($tmp2, $tmp['1/zenmagick.php']); unset($tmp['1/zenmagick.php']);
        array_push($tmp2, $tmp['ZMLoader.php']); unset($tmp['ZMLoader.php']);
        foreach ($tmp as $file) {
            array_push($tmp2, $file);
        }

        $files = $tmp2;

        if (!$handle = fopen($outfile, 'ab')) {
            array_push($this->errors_, 'could not open file for writing ' . $outfile);
            return;
        }
        if (false === fwrite($handle, "<?php define(ZM_SINGLE_CORE, true); ?>\n")) {
            array_push($this->errors_, 'could not write to file ' . $outfile);
            return;
        }

        $inpath = explode('/', $in);
        $currLevel = 0;
        while (0 < count($files)) {
            $processed = 0;
            foreach ($files as $key => $infile) {
                //echo $key.' '.$infile."<br>";
                $path = explode('/', $infile);
                $level = count($path)-count($inpath)-1;
                if ($level == $currLevel || $key < 4) {
                    ++$processed;
                    //echo $infile . " >> " . $outfile . "\n";
                    unset($files[$key]);
                    $source = file_get_contents($infile);

                    if (false) {
                        if (false === fwrite($handle, "<?php /* ".$infile." */ ?>\n")) {
                            array_push($this->errors_, 'could not write to file ' . $outfile);
                            return;
                        }
                    }
                    if (false === fwrite($handle, $source)) {
                        array_push($this->errors_, 'could not write to file ' . $outfile);
                        return;
                    }
                }
            }
            if (0 == $processed) {
                ++$currLevel;
            }
        }

        fclose($handle);
    }

}

?>
