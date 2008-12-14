<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 *
 * $Id$
 */
?>
<?php  

/**
 * Analyze dependencies of a given PHP package (folder tree), resolve and compress.
 *
 * <p>This class is build on top of <code>ZMPhpCompressor</code>. It adds the ability to resolve
 * <em>include</em> and <em>require</em> directives. It also takes care of missing PHP close tags <em>?&gt;</em>.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.admin
 * @version $Id$
 */
class ZMPhpPackagePacker {
    protected $rootFolder;
    protected $outputFilename;
    protected $tempFolder;
    private $debug;
    protected $treeMap;

    
    /**
     * Create new instance.
     *
     * @param string root The root directory to pack.
     * @param string out The [full] output filename.
     * @param string temp A temp folder for transient files and folders; default is <code>null</code>.
     */
    function __construct($root, $out, $temp=null) {
        $this->rootFolder = $root;
        $this->outputFilename = $out;
        $this->setTemp($temp);
        $this->debug = false;
        ZMLoader::resolve('ZMTools');
    }


    /**
     * Set the temp folder.
     *
     * @param string temp A temp folder for transient files and folders; default is <code>null</code>.
     */
    public function setTemp($temp) {
        $this->tempFolder = $temp;
        if (null == $this->tempFolder) {
            $this->tempFolder = $this->outputFilename.'.tmp';
        }
    }

    /**
     * Set the debug flag.
     *
     * @param boolean debug The new debug value.
     */
    public function setDebug($debug) {
        $this->debug = $debug;
    }

    /**
     * Finalize dependencies.
     *
     * <p>Callback to manipulate the computed dependencies.</p>
     *
     * @param array dependencies The computed dependencies.
     * @param array files List of all files as returned by <code>ZMLoader::findIncludes()</code>.
     * @return array The final dependencies.
     */
    public function finalizeDependencies($dependencies, $files) {
        return $dependencies;
    }

    /**
     * Decide whether a class dependencies are resolved or not.
     *
     * <p>Callback to allow custom handling, for example in case of circular references.</p>
     *
     * @param string class The class name.
     * @param int level The current inheritence level (nested folder depth).
     * @param array files List of all files as returned by <code>ZMLoader::findIncludes()</code>.
     * @return boolean <code>true</code>, if the class should be considered cleared of all dependencies.
     */
    public function isResolved($class, $level, $files) {
        return false;
    }

    /**
     * Decide whether a ignore a file completely or not.
     *
     * <p>Callback to allow custom handling, for example exclusion of files or folders.</p>
     *
     * @param string file The file name.
     * @return boolean <code>true</code>, if the file should be ignored.
     */
    public function ignoreFile($file) {
        return false;
    }

    /**
     * Pack all.
     *
     * @param boolean strip If <code>true</code>, stript the files while compressing; default is <code>true</code>.
     */
    public function packFiles($strip=true) {
        $this->clean();
        $this->prepareFiles();
        $this->compressFiles($strip);
        if (!$this->debug) {
            $this->clean();
        }
    }

    /**
     * Clean up temp stuff.
     */
    public function clean() {
        ZMTools::rmdir($this->tempFolder);
        ZMTools::rmdir($this->outputFilename.'.prep/');
    }

    /**
	   * Get next token of a certain type.
     *
	   * @param array tokens List of all token.
	   * @param int key Key to start searching from.
	   * @param int type Type of token to look for.
	   * @return string Found token or <code>null</code>.
	   */
    private function getToken($tokens, $key, $type) {
        ++$key;
        if (!is_array($type)) $type = array($type);
        while (!is_array($tokens[$key]) || !in_array($tokens[$key][0], $type)) {
            ++$key;
            if (!isset($tokens[$key])) {
                return null;
            }
        }
        return $tokens[$key];
    }

    /**
     * Get class info.
     *
     * @param string source The file source.
     * @return array Two element array containing the list of implemented interfaces and parent class.
     */
    protected function getClassInfo($source) {
        $info = array();
        $info['interfaces'] = array();
        $info['parent'] = null;
        $info['class'] = false;
        $tokens = token_get_all($source);
        foreach ($tokens as $key => $token) {
            if (!is_string($token)) {
                // token array
                list($id, $text) = $token;
                switch ($id) {
                case T_INTERFACE:
                    $name = $this->getToken($tokens, $key, T_STRING);
                    $info['interfaces'][] = $name[1];
                    break;
                case T_EXTENDS:
                    $name = $this->getToken($tokens, $key, T_STRING);
                    $info['parent'] = $name[1];
                    break;
                case T_CLASS:
                    $name = $this->getToken($tokens, $key, T_STRING);
                    $info['class'] = $name[1];
                    break;
                }
            }
        }
        return $info;
    }

    /**
     * Prepare the original files to be processed by <code>ZMPhpCompressor</code>.
     */
    protected function prepareFiles() {
        if ($this->debug) {
            echo 'preparing '.$this->rootFolder."<br>\n";
        }
        ZMLoader::instance()->resolve('InstallationPatch');
        $patch = ZMLoader::make('FilePatch', 'patch');

        $fileMap = array();
        $dependsOn = array();
        $classInfo = array();
        $files = ZMLoader::findIncludes($this->rootFolder, '.php', true);
        foreach ($files as $file) {
            if ($this->ignoreFile($file)) {
                continue;
            }
            $lines = $patch->getFileLines($file);
            $patched = false;
            $class = str_replace('.php', '', basename($file));
            $fileMap[$class] = $file;
            $dependsOn[$class] = array();
            $classInfo[$class] = $this->getClassInfo(implode(' ', $lines));
            if ($classInfo[$class]['class']) {
                // only if class in file
                foreach ($lines as $ii => $line) {
                    // this will only match if the filename is a simple string - that's the way PEAR files should be done...
                    if (preg_match('/^\s*\/?\/?\s*(require_once|require|include_once|include){1}\s*\(?\s*[\'"](.*)[\'"]\s*\)?\s*;.*$/', $line, $matches)) {
                        $dependsOn[$class][] = str_replace('.php', '', basename($matches[2]));
                    }
                }
            }
        }

        $dependsOn = $this->finalizeDependencies($dependsOn, $files);

        $resolved = array();

        $levelIndex = 0;
        $this->treeMap = array();
        // while not all resolved
        while (count($resolved) < count($dependsOn)) {
            if ($this->debug && 5 < $levelIndex) {
                echo "<br>\n<br>\n=======".$levelIndex."============<BR>\n";
            }
            $level = array();
            // iterate through all classes
            foreach ($dependsOn as $class => $dependencies) {
                if (isset($resolved[$class])) {
                    // already good
                    continue;
                }

                $clear = true;
                // check if all dependencies are resolved
                foreach ($dependencies as $dclass) {
                    if (!isset($resolved[$dclass])) {
                        $clear = false;
                        if ($this->debug) echo '['.$class."] missing dep: ".$dclass."<BR>\n";
                    }
                }

                if ($clear || $this->isResolved($class, $levelIndex, $files)) {
                    $level[$class] = $class;
                }
            }

            $this->treeMap[$levelIndex] = $level;
            $resolved = array_merge($resolved, $level);

            $levelIndex++;

            if (10 == $levelIndex) { 
                break;
            }
        }

        if ($this->debug) {
            echo count($resolved) . ' of ' . count($dependsOn) . " classes resolved<br>\n";
        }

        $currentDir = $this->outputFilename.'.prep/';
        foreach ($this->treeMap as $level => $classes) {
            if (0 < $level) {
                $currentDir .= $level.'/';
            }

            ZMTools::mkdir($currentDir);

            foreach ($classes as $class) {
                $inFile = $fileMap[$class];
                $lines = $patch->getFileLines($inFile);
                foreach ($lines as $ii => $line) {
                    // match all statements, regardless whether they match the PEAR style expected above or not
                    if (preg_match('/^\s*\s*(require_once|require|include_once|include).*$/', $line, $matches)) {
                        $lines[$ii] = '//'.$line;
                    }
                }
                // fix missing '?'.'>' at end of files
                for ($ii=count($lines); $ii>0; --$ii) {
                    $line = trim($lines[$ii-1]);
                    if (0 < strlen($line)) {
                        if ('>' != substr($line, -1)) {
                            $lines[] = '?'.'>';
                        }
                        break;
                    }
                }
                $extFile = $currentDir.basename($inFile);
                $patch->putFileLines($extFile, $lines);
            }
        }
    }

    /**
     * Compress all prepared files.
     *
     * @param boolean strip If <code>true</code>, stript the files while compressing; default is <code>true</code>.
     */
    protected function compressFiles($strip=true) {
        $compressor = ZMLoader::make('PhpCompressor');
        $compressor->setRoot($this->outputFilename.'.prep/');
        $compressor->setOut($this->outputFilename);
        $compressor->setTemp($this->tempFolder);
        $compressor->setStripCode($strip);
        $compressor->compress();
        if ($this->debug) {
            foreach ($compressor->getErrors() as $error) {
                echo $error."<br>\n";
            }
        } else {
            $compressor->clean();
        }
    }

}

?>
