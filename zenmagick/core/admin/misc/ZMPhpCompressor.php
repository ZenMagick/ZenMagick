<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * Generic PHP code compressor.
 *
 * <p>Can be used to compress all PHP files of a given folder or folder tree into a single PHP file.</p>
 *
 * <p>Supports some archaic form of inheritance/dependencies in that levels are processed one after the other.
 * That means that a class in a second level folder may extend any class that resides in the root folder or
 * any first level folder.</p>
 *
 * <p>If no temp folder is configured, the location of this file will be used to store temporary files and folders.</p>
 *
 * <p>Depends on <code>ZMLoader</code> and <code>ZMTools</code>.</p>
 *
 * @author mano
 * @package org.zenmagick.admin
 * @version $Id$
 */
class ZMPhpCompressor {
    protected $rootFolder;
    protected $outputFilename;
    protected $tempFolder;
    protected $errors;

    //these two will be set once processing starts
    protected $strippedFolder;
    protected $flatFolder;

    // some optiones
    protected $stripCode;


    /**
     * Create new instance.
     *
     * @param string root The root directory to compress; default is <code>null</code>.
     * @param string out The [full] output filename; default is <code>null</code>.
     * @param string temp A temp folder for transient files and folders; default is <code>null</code>.
     */
    function __construct($root=null, $out=null, $temp=null) {
        $this->setRoot($root);
        $this->setOut($out);
        $this->setTemp($temp);

        $this->stripCode = true;

        $this->errors_ = array();
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
     * @return boolean <code>true</code> if errors exist.
     */
    function hasErrors() {
        return 0 != count($this->errors_);
    }

    /**
     * Set the root folder.
     *
     * @param string root The root directory to compress; default is <code>null</code>.
     */
    public function setRoot($root) {
        $this->rootFolder = $root;
    }

    /**
     * Set the output filename.
     *
     * @param string out The [full] output filename; default is <code>null</code>.
     */
    public function setOut($out) {
        $this->outputFilename = $out;
    }

    /**
     * Set the temp folder.
     *
     * @param string temp A temp folder for transient files and folders; default is <code>null</code>.
     */
    public function setTemp($temp) {
        $this->tempFolder = $temp;
        if (null == $this->tempFolder) {
            if (null != $this->rootFolder && is_dir($this->rootFolder)) {
                $this->tempFolder = dirname(dirname($this->rootFolder));
            } else {
                $this->tempFolder = dirname(__FILE__);
            }
        }
        echo $this->tempFolder;
    }

    /**
     * Configure whether or not to strip the compressed code.
     *
     * <p>Disabling stripping will have the following effects:</p>
     * <ul>
     *  <li>The code will be stored in a single file, but not compressed</li>
     *  <li>Temp files and folder will not be removed afterwoods to analysis</li>
     * </ul>
     *
     * @param boolean strip The new value.
     */
    public function setStripCode($strip) {
        $this->stripCode = $strip;
    }

    /**
     * Clean up all temp. files.
     */
    function clean() {
        ZMTools::rmdir($this->strippedFolder);
        ZMTools::rmdir($this->flatFolder);
    }

    /**
     * Compress accoding to the current settings.
     *
     * @return boolean <code>true</code> if successful, <code>false</code> on failure.
     */
    public function compress() {
        $this->strippedFolder = $this->tempFolder.'/stripped';
        $this->flatFolder = $this->tempFolder.'/flat';

        $this->clean();
        @unlink($this->outputFilename);

        if ($this->stripCode) {
            $this->stripPhpDir($this->rootFolder, $this->strippedFolder);
        }
        if (!$this->hasErrors()) {
            if ($this->stripCode) {
                $this->flattenDirStructure($this->strippedFolder, $this->flatFolder);
            } else {
                $this->flattenDirStructure($this->rootFolder, $this->flatFolder);
            }
            if (!$this->hasErrors()) {
                $this->compressToSingleFile($this->flatFolder, $this->outputFilename);
            }
        }

        if ($this->stripCode) {
            $this->clean();
        }

        return !$this->hasErrors();
    }

    /**
     * Strip the given PHP source text.
     *
     * @param string source The PHP source code.
     * @return string The stripped code.
     */
    protected function stripPhpSource($source) {
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
                   case T_END_HEREDOC:
                       echo $text."\n";
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
     * @param string out The output filename; if <code>null</code> just echo; default is <code>null</code>.
     */
    protected function stripPhpFile($in, $out=null) {
        $source = file_get_contents($in);
        $source = $this->stripPhpSource($source);
        // strip empty PHP open/close tags
        $source = $this->stripPhpSource($source);
        if (null !== $out) {
            // write to file
            if (!$handle = fopen($out, 'ab')) {
                array_push($this->errors_, 'could not open file for writing ' . $out);
                return;
            }

            if (false === fwrite($handle, $source)) {
                array_push($this->errors_, 'could not write to file ' . $out);
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
     * <p>Uses <code>ZMLoader::findIncludes()</code> to find files to process.</p>
     *
     * @param string in The input directory.
     * @param string out The output directory.
     * @param boolean recursive If true, strip recursivley.
     */
    protected function stripPhpDir($in, $out=null, $recursive=true) {
        //echo "** stripping " . $in . " into " . $out . "\n";
        if (!ZMTools::endsWith($in, '/')) $in .= '/';
        if (!ZMTools::endsWith($out, '/')) $out .= '/';

        $files = ZMLoader::findIncludes($in, $recursive);

        foreach ($files as $name => $infile) {
            $name = basename($infile);
            $dirbase = substr(dirname($infile), strlen($in));
            $outdir = $out.$dirbase;
            if (!ZMTools::endsWith($outdir, '/')) $outdir .= '/';
            $outfile = $outdir.$name;
            //echo $outfile."<BR>";
            if (!file_exists($outdir)) {
                if (!file_exists(dirname($outdir))) {
                    ZMTools::mkdir(dirname($outdir), 755);
                    if (!file_exists(dirname($outdir))) {
                        array_push($this->errors_, 'could not create directory ' . dirname($outdir));
                        return;
                    }
                }
                ZMTools::mkdir($outdir, 755);
                if (!file_exists($outdir)) {
                    array_push($this->errors_, 'could not create directory ' . $outdir);
                    return;
                }
            }
            //echo $infile . " >> " . $outfile ."\n";
            $this->stripPhpFile($infile, $outfile);
        }
    }

    /**
     * Flatten the directory structure.
     *
     * @param string in The input directory.
     * @param string out The output directory.
     */
    protected function flattenDirStructure($in, $out) {
        //echo "** flatten " . $in . " into " . $out . "\n";
        $files = ZMLoader::findIncludes($in.'/', true);

        if (!file_exists($out)) {
            ZMTools::mkdir($out, 755);
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
                    ZMTools::mkdir(dirname($outdir), 755);
                    if (!file_exists(dirname($outdir))) {
                        array_push($this->errors_, 'could not create directory ' . dirname($outdir));
                        return;
                    }
                }
                ZMTools::mkdir($outdir, 755);
                if (!file_exists($outdir)) {
                    array_push($this->errors_, 'could not create directory ' . $outdir);
                    return;
                }
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
                rray_push($this->errors_, 'could not open file for writing ' . $outfile);
                return;
            }

            if (!$this->stripCode) {
                if (false === fwrite($handle, "<?php /* ".$infile." */ ?>\n")) {
                    array_push($this->errors_, 'could not write to file ' . $outfile);
                    return;
                }
            }

            if (false === fwrite($handle, $source)) {
                array_push($this->errors_, 'could not write to file ' . $outfile);
                return;
            }
      
            fclose($handle);
        }
    }

    /**
     * Empty callback to make final adjustments to the file list before compressing to a single file.
     *
     * @param array files List of files.
     * @return array The final list.
     */
    protected function finalizeFiles($files) {
        return $files;
    }

    /**
     * Compress all files into a single file
     *
     * @param string in The input directory.
     * @param string outfile The output file.
     */
    protected function compressToSingleFile($in, $outfile) {
        //echo "** compress " . $in . " into " . $outfile . "\n";
        $files = ZMLoader::findIncludes($in.'/', true);

        $tmp = array();
        // mess around with results ...
        foreach ($files as $name => $file) {
            $off = strpos($file, $in);
            $tmp[substr($file, $off+strlen($in)+1)] = $file;
        }
        $files = $this->finalizeFiles($tmp);

        // use ob to collect content
        ob_start();
        $inpath = explode('/', $in);
        $currLevel = 0;
        while (0 < count($files)) {
            $processed = 0;
            foreach ($files as $key => $infile) {
                // leave here just in case...
                if (empty($infile) || !file_exists($file)) {
                    unset($files[$key]);
                    continue;
                }
                $path = explode('/', $infile);
                $level = count($path)-count($inpath)-1;
                if ($level == $currLevel || $key < 4) {
                    ++$processed;
                    unset($files[$key]);
                    $source = file_get_contents($infile);

                    if (!$this->stripCode) {
                        echo "<?php /* ".$infile." */ ?>\n";
                    }
                    echo $source;
                }
            }
            if (0 == $processed) {
                ++$currLevel;
            }
        }

        $content = ob_get_clean();

        $content = str_replace("?><?php", '', $content);

        if (!$handle = fopen($outfile, 'ab')) {
            array_push($this->errors_, 'could not open file for writing ' . $outfile);
            return;
        }

        if (false === fwrite($handle, $content)) {
            array_push($this->errors_, 'could not write to file ' . $outfile);
            return;
        }

        fclose($handle);
    }

}

?>
