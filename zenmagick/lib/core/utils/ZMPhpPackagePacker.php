<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * @package org.zenmagick.core.utils
 * @version $Id$
 */
class ZMPhpPackagePacker {
    protected $rootFolder_;
    protected $outputFilename_;
    protected $tempFolder_;
    private $debug_;
    protected $treeMap_;
    private $resolveInheritance_;


    /**
     * Create new instance.
     *
     * @param string root The root directory to pack.
     * @param string out The [full] output filename.
     * @param string temp A temp folder for transient files and folders; default is <code>null</code>.
     */
    function __construct($root, $out, $temp=null) {
        $this->rootFolder_ = $root;
        $this->outputFilename_ = $out;
        $this->setTemp($temp);
        $this->debug_ = false;
        $this->resolveInheritance_ = false;
    }


    /**
     * Set the temp folder.
     *
     * @param string temp A temp folder for transient files and folders; default is <code>null</code>.
     */
    public function setTemp($temp) {
        $this->tempFolder_ = $temp;
        if (null == $this->tempFolder_) {
            $this->tempFolder_ = $this->outputFilename_.'.tmp';
        }
    }

    /**
     * Set the resolveInheritance flag.
     *
     * <p>Default is false - that should be enough for PEAR compatible packages.</p>
     *
     * @param boolean resolveInheritance if <code>true</code> try to resolve inheritance as determined by analysing the PHP source.
     */
    public function setResolveInheritance($resolveInheritance) {
        $this->resolveInheritance_ = $resolveInheritance;
    }

    /**
     * Set the debug flag.
     *
     * @param boolean debug The new debug value.
     */
    public function setDebug($debug) {
        $this->debug_ = $debug;
    }

    /**
     * Finalise dependencies.
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
     * Decide whether a drop an include/require line or not.
     *
     * @param string line The line in question.
     * @return boolean <code>true</code>, if the include/require should be dropped.
     */
    public function dropInclude($line) {
        return true;
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
        if (!$this->debug_) {
            $this->clean();
        }
    }

    /**
     * Clean up temp stuff.
     */
    public function clean() {
        ZMFileUtils::rmdir($this->tempFolder_);
        ZMFileUtils::rmdir($this->outputFilename_.'.prep'.DIRECTORY_SEPARATOR);
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
     * Get a list of all files to process.
     *
     * @return array A list of file names.
     */
    protected function getFileList() {
        return ZMLoader::findIncludes($this->rootFolder_, '.php', true);
    }

    /**
     * Prepare the original files to be processed by <code>ZMPhpCompressor</code>.
     */
    protected function prepareFiles() {
        if ($this->debug_) {
            echo 'preparing files... '."<br>\n";
        }

        $fileMap = array();
        $dependsOn = array();
        $classInfo = array();
        $files = $this->getFileList();
        foreach ($files as $file) {
            if ($this->ignoreFile($file)) {
                continue;
            }
            $lines = ZMFileUtils::getFileLines($file);
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
                // or requested
                if ($this->resolveInheritance_) {
                    $dependsOn[$class] = array_merge($dependsOn[$class],$classInfo[$class]['interfaces']);
                    if (!empty($classInfo[$class]['parent'])) {
                        $dependsOn[$class][] = $classInfo[$class]['parent'];
                    }
                }
            }
        }

        $dependsOn = $this->finalizeDependencies($dependsOn, $files);

        if ($this->debug_) {
            echo "* processing ".count($files)." files<BR>\n";
            echo "* dependencies:<pre>";
            var_dump($dependsOn);
            echo "</pre>";
        }

        $resolved = array();

        $levelIndex = 0;
        $this->treeMap_ = array();
        // while not all resolved
        while (count($resolved) < count($dependsOn)) {
            if ($this->debug_) {
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
                        if ($this->debug_) echo '['.$class."] missing dep: ".$dclass."<BR>\n";
                    }
                }

                if ($clear || $this->isResolved($class, $levelIndex, $files)) {
                    $level[$class] = $class;
                }
            }

            $this->treeMap_[$levelIndex] = $level;
            $resolved = array_merge($resolved, $level);

            $levelIndex++;

            if (10 == $levelIndex) {
                ZMLogging::instance()->log('max nesting level - aborting');
                break;
            }
        }

        if ($this->debug_) {
            echo count($resolved) . ' of ' . count($dependsOn) . " classes resolved<br>\n";
        }

        $currentDir = $this->outputFilename_.'.prep'.DIRECTORY_SEPARATOR;
        foreach ($this->treeMap_ as $level => $classes) {
            if (0 < $level) {
                $currentDir .= $level.DIRECTORY_SEPARATOR;
            }

            ZMFileUtils::mkdir($currentDir);

            foreach ($classes as $class) {
                $inFile = $fileMap[$class];
                $lines = ZMFileUtils::getFileLines($inFile);
                foreach ($lines as $ii => $line) {
                    // match all statements, regardless whether they match the PEAR style expected above or not
                    if (preg_match('/^\s*\s*(require_once|require|include_once|include).*$/', $line, $matches)) {
                        if ($this->dropInclude($line)) {
                            $lines[$ii] = '//'.$line;
                        }
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
                ZMFileUtils::putFileLines($extFile, $lines);
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
        $compressor->setRoot($this->outputFilename_.'.prep'.DIRECTORY_SEPARATOR);
        $compressor->setOut($this->outputFilename_);
        $compressor->setTemp($this->tempFolder_);
        $compressor->setStripCode($strip);
        $compressor->compress();
        if ($this->debug_) {
            foreach ($compressor->getErrors() as $error) {
                echo $error."<br>\n";
            }
        } else {
            $compressor->clean();
        }
    }

}
