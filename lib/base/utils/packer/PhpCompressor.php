<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\base\utils\packer;

use zenmagick\base\ZMObject;

/*
 * T_ML_COMMENT does not exist in PHP 5.
 * The following three lines define it in order to
 * preserve backwards compatibility.
 *
 * The next two lines define the PHP 5 only T_DOC_COMMENT,
 * which we will mask as T_ML_COMMENT for PHP 4.
 */
if (!defined('T_ML_COMMENT')) {
    /** @access private */
    define('T_ML_COMMENT', T_COMMENT);
} else {
    /** @access private */
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
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class PhpCompressor extends ZMObject {
    protected $rootFolders_;
    protected $outputFilename_;
    protected $tempFolder_;
    protected $errors_;

    //these two will be set once processing starts
    protected $strippedFolder_;
    protected $flatFolder_;

    // options
    protected $stripCode_;
    protected $stripRef_;


    /**
     * Create new instance.
     *
     * @param mixed root The root directory to compress (or array of directory names); default is <code>null</code>.
     * @param string out The [full] output filename; default is <code>null</code>.
     * @param string temp A temp folder for transient files and folders; default is <code>null</code>.
     */
    public function __construct($root=null, $out=null, $temp=null) {
        parent::__construct();
        $this->setRoot($root);
        $this->setOut($out);
        $this->setTemp($temp);

        $this->stripCode_ = true;
        $this->stripRef_ = true;

        $this->errors_ = array();
    }

    /**
     * Get errors.
     *
     * @return array List of text messages.
     */
    public function getErrors() {
        return $this->errors_;
    }

    /**
     * Check for errors.
     *
     * @return boolean <code>true</code> if errors exist.
     */
    public function hasErrors() {
        return 0 != count($this->errors_);
    }

    /**
     * Set the root folder(s).
     *
     * @param mixed root The root directory to compress (or array of directory names); default is <code>null</code>.
     */
    public function setRoot($root) {
        if (!is_array($root)) {
            $this->rootFolders_ = array($root);
        } else {
            $this->rootFolders_ = $root;
        }
    }

    /**
     * Set the output filename.
     *
     * @param string out The [full] output filename.
     */
    public function setOut($out) {
        $this->outputFilename_ = $out;
    }

    /**
     * Set the temp folder.
     *
     * @param string temp A temp folder for transient files and folders (unless stripping is off).
     */
    public function setTemp($temp) {
        $this->tempFolder_ = $temp;
        if (null == $this->tempFolder_) {
            if (is_array($this->rootFolders_) && 0 < count($this->rootFolders_) && is_dir($this->rootFolders_[0])) {
                $this->tempFolder_ = dirname(dirname($this->rootFolders_[0]));
            } else {
                $this->tempFolder_ = dirname(__FILE__);
            }
        }
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
        $this->stripCode_ = $strip;
    }

    /**
     * Configure whether or not to strip the use of references; eg. <code>&$</code> or <code>= &</code>.
     *
     * @param boolean strip The new value.
     */
    public function setStripRef($strip) {
        $this->stripRef_ = $strip;
    }

    /**
     * Clean up all temp. files.
     */
    public function clean() {
        $this->container->get('filesystem')->remove(array(
            $this->strippedFolder_,
            $this->flatFolder_
        ));
    }

    /**
     * Compress accoding to the current settings.
     *
     * @return boolean <code>true</code> if successful, <code>false</code> on failure.
     */
    public function compress() {
        $this->strippedFolder_ = $this->tempFolder_.DIRECTORY_SEPARATOR.'stripped';
        $this->flatFolder_ = $this->tempFolder_.DIRECTORY_SEPARATOR.'flat';

        $this->clean();
        if (file_exists($this->outputFilename_)) {
            @unlink($this->outputFilename_);
        }

        if ($this->stripCode_) {
            foreach ($this->rootFolders_ as $folder) {
                $this->stripPhpDir($folder, $this->strippedFolder_);
            }
        }
        if (!$this->hasErrors()) {
            if ($this->stripCode_) {
                $this->flattenDirStructure($this->strippedFolder_, $this->flatFolder_);
            } else {
                foreach ($this->rootFolders_ as $folder) {
                    $this->flattenDirStructure($folder, $this->flatFolder_);
                }
            }
            if (!$this->hasErrors()) {
                $this->compressToSingleFile($this->flatFolder_, $this->outputFilename_);
            }
        }

        if ($this->stripCode_) {
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
        $src = ob_get_clean();
        if ($this->stripRef_) {
            $src = str_replace(array(',&$', ', &$', '( &$', '(&$', '=&', '&new', '& new', 'function &', ' = &'),
                array(',$', ', $', '( $', '($', '=', 'new', 'new', 'function ', ' = '),
                $src);
        }
        return $src;
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
            \ZMFileUtils::setFilePerms($out);
        } else {
            echo $source;
        }
    }

    /**
     * Scan (recursively) for <code>.php</code> files.
     *
     * <p>It is worth mentioning that directories will always be processed only after
     * all plain files in a directory are done.</p>
     *
     * @param string dir The name of the root directory to scan.
     * @param string ext Optional file suffix/extension; default is <em>.php</em>.
     * @param boolean recursive If <code>true</code>, scan recursively.
     * @return array List of full filenames of <code>.php</code> files.
     */
    protected function findIncludes($dir, $ext='.php', $recursive=false) {
       $includes = array();
       if (!is_dir($dir) || false !== strpos($dir, '.svn')) {
           return $includes;
       }

       // save directories for later
       $dirs = array();

       $handle = @opendir($dir);
       while (false !== ($file = readdir($handle))) {
           if ("." == $file || ".." == $file) {
               continue;
           }
           $file = $dir.$file;
           if (is_dir($file)) {
               $dirs[] = $file;
           } else if ($ext == substr($file, -strlen($ext))) {
               $includes[] = $file;
           }
       }
       @closedir($handle);

       // process folders last
       if ($recursive) {
           foreach ($dirs as $dir) {
               $includes = array_merge($includes, $this->findIncludes($dir.DIRECTORY_SEPARATOR, $ext, $recursive));
           }
       }

       return $includes;
    }

    /**
     * Recursivley strip a directory.
     *
     * <p>Uses <code>findIncludes()</code> to find files to process.</p>
     *
     * @param string in The input directory.
     * @param string out The output directory.
     * @param boolean recursive If true, strip recursivley.
     */
    protected function stripPhpDir($in, $out=null, $recursive=true) {
        //echo "** stripping " . $in . " into " . $out . "\n";
        if (!\ZMLangUtils::endsWith($in, DIRECTORY_SEPARATOR)) $in .= DIRECTORY_SEPARATOR;
        if (!\ZMLangUtils::endsWith($out, DIRECTORY_SEPARATOR)) $out .= DIRECTORY_SEPARATOR;

        $files = $this->findIncludes($in, '.php', $recursive);

        $filesystem = $this->container->get('filesystem');

        foreach ($files as $name => $infile) {
            $name = basename($infile);
            $dirbase = substr(dirname($infile), strlen($in));
            $outdir = $out.$dirbase;
            if (!\ZMLangUtils::endsWith($outdir, DIRECTORY_SEPARATOR)) $outdir .= DIRECTORY_SEPARATOR;
            $outfile = $outdir.$name;
            //echo $outfile."<BR>";
            if (!file_exists($outdir)) {
                if (!file_exists(dirname($outdir))) {
                    $filesystem->mkdir(dirname($outdir), 0755);
                    if (!file_exists(dirname($outdir))) {
                        array_push($this->errors_, 'could not create directory ' . dirname($outdir));
                        return;
                    }
                }
                $filesystem->mkdir($outdir, 0755);
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
        $files = $this->findIncludes($in.DIRECTORY_SEPARATOR, '.php', true);

        if (!file_exists($out)) {
            $this->container->get('filesystem')->mkdir($out, 0755);
        }

        $filesystem = $this->container->get('filesystem');

        $inpath = explode(DIRECTORY_SEPARATOR, $in);
        foreach ($files as $name => $infile) {
            $path = explode(DIRECTORY_SEPARATOR, $infile);
            $level = count($path)-count($inpath)-1;
            $outdir = $out;
            for ($ii=1;$ii<=$level;++$ii) {
                $outdir .= DIRECTORY_SEPARATOR.$ii;
            }
            if (!file_exists($outdir)) {
                if (!file_exists(dirname($outdir))) {
                    $filesystem->mkdir(dirname($outdir), 0755);
                    if (!file_exists(dirname($outdir))) {
                        array_push($this->errors_, 'could not create directory ' . dirname($outdir));
                        return;
                    }
                }
                $filesystem->mkdir($outdir, 0755);
                if (!file_exists($outdir)) {
                    array_push($this->errors_, 'could not create directory ' . $outdir);
                    return;
                }
            }
            $outfile = $outdir.DIRECTORY_SEPARATOR.basename($infile);
            $sub = 1;
            while (file_exists($outfile)) {
                $outfile = $outdir.DIRECTORY_SEPARATOR.$sub.'-'.basename($infile);
                ++$sub;
            }
            //echo $infile . " >> " . $outfile . "\n";
            $source = file_get_contents($infile);

            if (!$handle = fopen($outfile, 'ab')) {
                array_push($this->errors_, 'could not open file for writing ' . $outfile);
                return;
            }

            if (!$this->stripCode_) {
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
            \ZMFileUtils::setFilePerms($outfile);
        }
    }

    /**
     * Empty callback to make final adjustments to the file list before compressing to a single file.
     *
     * @param array files List of files.
     * @return array The final list.
     */
    protected function finaliseFiles($files) {
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
        $files = $this->findIncludes($in.DIRECTORY_SEPARATOR, '.php', true);

        $tmp = array();
        // mess around with results ...
        foreach ($files as $name => $file) {
            $off = strpos($file, $in);
            $tmp[substr($file, $off+strlen($in)+1)] = $file;
        }
        $files = $this->finaliseFiles($tmp);

        // use ob to collect content
        ob_start();
        $inpath = explode(DIRECTORY_SEPARATOR, $in);
        $currLevel = 0;
        while (0 < count($files)) {
            $processed = 0;
            foreach ($files as $key => $infile) {
                // leave here just in case...
                if (empty($infile) || !file_exists($file)) {
                    unset($files[$key]);
                    continue;
                }
                $path = explode(DIRECTORY_SEPARATOR, $infile);
                $level = count($path)-count($inpath)-1;
                if ($level == $currLevel || $key < 4) {
                    ++$processed;
                    unset($files[$key]);
                    $source = file_get_contents($infile);

                    if ($this->stripRef_) {
                        $source = str_replace(array(',&$', ', &$', '( &$', '(&$', '=&', '&new', '& new', 'function &', ' = &'),
                            array(',$', ', $', '( $', '($', '=', 'new', 'new', 'function ', ' = '),
                            $source);
                    }

                    if (!$this->stripCode_) {
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
        \ZMFileUtils::setFilePerms($outfile);
    }

}
