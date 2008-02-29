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
 * Compress core into a single file 'core.php'.
 *
 * @author mano
 * @package org.zenmagick.admin
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
        $this->pluginsPreparedDirname_ = $zm_runtime->getZMRootPath().'plugins.prepared';
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
        $themes = $zm_runtime->getThemes();
     *
     * @return boolean <code>true</code> if errors exist.
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
     * @return boolean <code>true</code> if core.php exists, <code>false</code> if not.
     */
    function isEnabled() {
        return file_exists($this->coreFilename_);
    }

    /**
     * Clean up all temp. files.
     */
    function clean() {
        zm_rmdir($this->pluginsPreparedDirname_);
        zm_rmdir($this->strippedDirname_);
        zm_rmdir($this->flatDirname_);
    }

    /**
     * Generate 'core.php'
     */
    function generate() {
        $this->clean();
        @unlink($this->coreFilename_);

        // add some levels to make plugins load last
        $this->_preparePlugins($this->pluginsPreparedDirname_.'/1/2/3/4');

        if (zm_setting('isStripCore')) {
            $this->_stripPhpDir($this->coreDirname_, $this->strippedDirname_);
            $this->_stripPhpDir($this->pluginsPreparedDirname_, $this->strippedDirname_);
        }
        if (!$this->hasErrors()) {
            if (zm_setting('isStripCore')) {
                $this->_flattenDirStructure($this->strippedDirname_, $this->flatDirname_);
            } else {
                $this->_flattenDirStructure($this->coreDirname_, $this->flatDirname_);
                $this->_flattenDirStructure($this->pluginsPreparedDirname_, $this->flatDirname_);
            }
            if (!$this->hasErrors()) {
                $this->_createInitBootstrap($this->flatDirname_);
                $this->_compressToSingleFile($this->flatDirname_, $this->coreFilename_);
            }
        }

        if (zm_setting('isStripCore')) {
            $this->clean();
        }
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
     * Prepare plugin files.
     *
     * <p>Prepare those plugin files that can be compressed.</p>
     *
     * @param string out The output directory.
     */
    function _preparePlugins($out) {
    global $zm_runtime;

        if (!zm_ends_with($out, '/')) $out .= '/';

        $zm_plugins = ZMLoader::make("Plugins");
        foreach ($zm_plugins->getAllPlugins() as $type => $plugins) {
            foreach ($plugins as $plugin) {
                if (!$plugin->isEnabled()) {
                    continue;
                }
                $flag = $plugin->getLoaderSupport();
                $pluginBase = $out.$type.'/'.$plugin->getId().'/';
                zm_mkdir($pluginBase, 755);
                if ('NONE' != $flag) {
                    $pluginDir = $plugin->getPluginDir();
                    $noDir = false;
                    if (zm_is_empty($pluginDir)) {
                        $pluginDir = $zm_runtime->getPluginsDir() . $type . '/';
                        $noDir = true;
                    }
                    if ($noDir || 'PLUGIN' == $flag) {
                        $files = array($pluginDir.$plugin->getId().'.php');
                    } else {
                        $files = ZMLoader::findIncludes($pluginDir, 'FOLDER' != $flag);
                    }
                    foreach ($files as $file) {
                        $fileBase = str_replace($pluginDir, '', $file);
                        $relDir = dirname($fileBase).'/';
                        if ('./' == $relDir) {
                            $relDir = '';
                        }
                        $source = file_get_contents($file);
                        if (!zm_is_empty($relDir)) {
                            zm_mkdir($pluginBase . $relDir, 755);
                        }
                        $outfile = $pluginBase . $relDir . basename($file);

                        if (!$handle = fopen($outfile, 'ab')) {
                            array_push($this->errors_, 'could not open file for writing ' . $outfile);
                            return;
                        }

                        if (false === fwrite($handle, $source)) {
                            array_push($this->errors_, 'could not write to file ' . $outfile);
                            return;
                        }
                  
                        fclose($handle);
                    }
                }
            }
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
    function _stripPhpDir($in, $out=null, $recursive=true) {
        //echo "** stripping " . $in . " into " . $out . "\n";
        if (!zm_ends_with($in, '/')) $in .= '/';
        if (!zm_ends_with($out, '/')) $out .= '/';

        $files = ZMLoader::findIncludes($in, $recursive);

        foreach ($files as $name => $infile) {
            $name = basename($infile);
            $dirbase = substr(dirname($infile), strlen($in));
            $outdir = $out.$dirbase;
            if (!zm_ends_with($outdir, '/')) $outdir .= '/';
            $outfile = $outdir.$name;
            //echo $outfile."<BR>";
            if (!file_exists($outdir)) {
                if (!file_exists(dirname($outdir))) {
                    zm_mkdir(dirname($outdir), 755);
                    if (!file_exists(dirname($outdir))) {
                        array_push($this->errors_, 'could not create directory ' . dirname($outdir));
                        return;
                    }
                }
                zm_mkdir($outdir, 755);
                if (!file_exists($outdir)) {
                    array_push($this->errors_, 'could not create directory ' . $outdir);
                    return;
                }
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
        $files = ZMLoader::findIncludes($in.'/', true);

        if (!file_exists($out)) {
            zm_mkdir($out, 755);
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
                    zm_mkdir(dirname($outdir), 755);
                    if (!file_exists(dirname($outdir))) {
                        array_push($this->errors_, 'could not create directory ' . dirname($outdir));
                        return;
                    }
                }
                zm_mkdir($outdir, 755);
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

            if (!zm_setting('isStripCore')) {
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
     * Create init_bootstrap.php
     *
     * @param string out The output directory.
     */
    function _createInitBootstrap($out) {
        $outfile = $out.'/init_bootstrap.php';
        if (!$handle = fopen($outfile, 'ab')) {
            array_push($this->errors_, 'could not open file for writing ' . $outfile);
            return;
        }
        if (false === fwrite($handle, "<?php \n")) {
            array_push($this->errors_, 'could not write to file ' . $outfile);
            return;
        }
        $lines = array(
            '$zm_runtime = new ZMRuntime();',
            '$zm_request = new ZMRequest();',
        );
        foreach ($lines as $line) {
            if (false === fwrite($handle, $line."\n")) {
                array_push($this->errors_, 'could not write to file ' . $outfile);
                return;
            }
        }
        if (false === fwrite($handle, "?>\n")) {
            array_push($this->errors_, 'could not write to file ' . $outfile);
            return;
        }
        fclose($handle);
    }

    /**
     * Compress all files into a single file
     *
     * @param string in The input directory.
     * @param string outfile The output file.
     */
    function _compressToSingleFile($in, $outfile) {
        //echo "** compress " . $in . " into " . $outfile . "\n";
        $files = ZMLoader::findIncludes($in.'/', true);

        $tmp = array();
        // mess around with results to find some files we need to add first...
        foreach ($files as $name => $file) {
            $off = strpos($file, $in);
            $tmp[substr($file, $off+strlen($in)+1)] = $file;
        }
        // some need to be in order :/
        $loadFirst = array(
            '1/zenmagick.php',
            '1/settings.php',
            'ZMObject.php',
            'ZMLoader.php',
            'utils.php',
            'ZMService.php',
            'ZMSession.php',
            'ZMRequest.php',
            'ZMRuntime.php',
            'init_bootstrap.php'
        );
        $tmp2 = array();
        foreach ($loadFirst as $first) {
            array_push($tmp2, $tmp[$first]); unset($tmp[$first]);
        }
        foreach ($tmp as $file) {
            array_push($tmp2, $file);
        }

        $files = $tmp2;

        if (!$handle = fopen($outfile, 'ab')) {
            array_push($this->errors_, 'could not open file for writing ' . $outfile);
            return;
        }
        if (false === fwrite($handle, "<?php define('ZM_SINGLE_CORE', true); ?>\n")) {
            array_push($this->errors_, 'could not write to file ' . $outfile);
            return;
        }

        $inpath = explode('/', $in);
        $currLevel = 0;
        while (0 < count($files)) {
            $processed = 0;
            foreach ($files as $key => $infile) {
                $path = explode('/', $infile);
                $level = count($path)-count($inpath)-1;
                if ($level == $currLevel || $key < 4) {
                    ++$processed;
                    unset($files[$key]);
                    $source = file_get_contents($infile);

                    if (!zm_setting('isStripCore')) {
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
