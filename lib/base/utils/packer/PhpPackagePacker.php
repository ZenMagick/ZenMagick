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

use zenmagick\base\Beans;
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;

/**
 * Analyze dependencies of a given PHP package (folder tree), resolve and compress.
 *
 * <p>This class is build on top of <code>PhpCompressor</code>. It adds the ability to resolve
 * <em>include</em> and <em>require</em> directives. It also takes care of missing PHP close tags <em>?&gt;</em>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class PhpPackagePacker extends ZMObject {
    protected $rootFolder_;
    protected $outputFilename_;
    protected $tempFolder_;
    private $debug_;
    protected $treeMap_;
    private $resolveInheritance_;


    /**
     * Create new instance.
     *
     * @param string root The root directory to pack; default is <code>null</code>.
     * @param string out The [full] output filename; default is <code>null</code>.
     * @param string temp A temp folder for transient files and folders; default is <code>null</code>.
     */
    public function __construct($root=null, $out=null, $temp=null) {
        parent::__construct();
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
     * Decide whether a ignore a file completely or not.
     *
     * <p>Callback to allow custom handling, for example exclusion of files or folders.</p>
     *
     * @param string file The file name.
     * @return boolean <code>true</code>, if the file should be ignored.
     */
    public function ignoreFile($filename) {
        return false;
    }

    /**
     * Allow custom file patching.
     *
     * @param string filename The file name.
     * @return array The (patched) lines or <code>null</code>.
     */
    public function patchFile($filename, $lines) {
        return null;
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
     * @param boolean strip If <code>true</code>, stript the files while compressing.
     * @param boolean stripRef If <code>true</code>, strip code that uses references.
     */
    public function packFiles($strip, $stripRef) {
        $this->clean();
        $this->prepareFiles();
        $this->compressFiles($strip, $stripRef);
        if (!$this->debug_) {
            $this->clean();
        }
    }

    /**
     * Clean up temp stuff.
     */
    public function clean() {
        $this->container->get('filesystem')->remove(array(
            $this->tempFolder_,
            $this->outputFilename_.'.prep'
        ));
    }

    /**
     * Get a list of all files to process.
     *
     * @return array A list of file names.
     */
    protected function getFileList() {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->rootFolder_));
        $it = new \RegexIterator($it, '/\.php$/', \RegexIterator::MATCH);
        return array_keys(iterator_to_array($it));
    }

    /**
     * Get a list of class/interface names to be assumed resolved.
     *
     * <p>The default implementation will return a list of predefined PHP standard and SPL exceptions and interfaces.</p>
     * @return array A list of class/interface names.
     */
    public function getPreResolved() {
        return array(
            // interfaces
            'Traversable', 'Iterator', 'IteratorAggregate', 'ArrayAccess', 'Serializable',
            // SPL interfaces
            'Countable', 'OuterIterator', 'RecursiveIterator', 'SeekableIterator', 'SplObserver', 'SplSubject',
            // exceptions
            'Exception', 'ErrorException',
            // SPL exceptions
            'BadFunctionCallException', 'BadMethodCallException', 'DomainException', 'InvalidArgumentException', 'LengthException',
            'LogicException', 'OutOfBoundsException', 'OutOfRangeException', 'OverflowException', 'RangeException', 'RuntimeException',
            'UnderflowException', 'UnexpectedValueException'
        );
    }

    /**
     * Prepare the original files to be processed by <code>PhpCompressor</code>.
     */
    protected function prepareFiles() {
        $files = $this->getFileList();
        $tree = PhpSourceAnalyzer::buildDepdencyTree($files, $this->getPreResolved());

        $currentDir = $this->outputFilename_.'.prep'.DIRECTORY_SEPARATOR;
        foreach ($tree as $level => $files) {
            if (0 < $level) {
                $currentDir .= $level.DIRECTORY_SEPARATOR;
            }

            $this->container->get('filesystem')->mkdir($currentDir, 0755);

            foreach ($files as $filename => $details) {
                if ($this->ignoreFile($filename)) {
                    continue;
                }
                $lines = file($filename);
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
                $extFile = $currentDir.basename($filename);
                if (null != ($patchedLines = $this->patchFile($filename, $lines))) {
                    $lines = $patchedLines;
                }
                self::putFileLines($extFile, $lines);
            }
        }
    }

    /**
     * Write the given lines to file.
     *
     * @param string file The filename.
     * @param array lines The  lines to write.
     * @return boolean <code>true</code> if successful, <code>false</code> if not.
     */
    public static function putFileLines($file, $lines) {
        $fileExists = file_exists($file);
        $handle = fopen($file, 'wb');
        if ($handle) {
            $lineCount = count($lines) - 1;
            foreach ($lines as $ii => $line) {
                $eol = $ii < $lineCount ? "\n" : '';
                fwrite($handle, $line.$eol);
            }
            fclose($handle);
            if (!$fileExists) {
                \ZMFileUtils::setFilePerms($file);
            }
            return true;
        }

        return false;
    }

    /**
     * Compress all prepared files.
     *
     * @param boolean stripCode If <code>true</code>, strip the files while compressing.
     * @param boolean stripRef If <code>true</code>, strip code that uses references.
     */
    protected function compressFiles($stripCode, $stripRef) {
        $compressor = $this->container->get('phpCompressor');
        $compressor->setRoot($this->outputFilename_.'.prep'.DIRECTORY_SEPARATOR);
        $compressor->setOut($this->outputFilename_);
        $compressor->setTemp($this->tempFolder_);
        $compressor->setStripCode($stripCode);
        $compressor->setStripRef($stripRef);
        $compressor->compress();
        if (!$this->debug_) {
            $compressor->clean();
        }
        foreach ($compressor->getErrors() as $error) {
            Runtime::getLogging()->debug($error);
        }
    }

}
