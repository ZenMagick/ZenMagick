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


/**
 * Compress core into a single file 'core.php'.
 *
 * @author DerManoMann
 * @package org.zenmagick.admin
 * @version $Id$
 */
ZMLoader::resolve('PhpCompressor');
class ZMCoreCompressor extends ZMPhpCompressor {
    private $pluginsPreparedFolder;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct(ZMRuntime::getZMRootPath().'core', ZMRuntime::getZMRootPath().'core.php', ZMRuntime::getZMRootPath());
        $this->pluginsPreparedFolder = ZMRuntime::getZMRootPath().'plugins.prepared';
        $this->stripCode = ZMSettings::get('isStripCore');
    }


    /**
     * Disable / remove the core.php file, effectively disabling the use of it.
     */
    public function disable() {
        @unlink($this->outputFilename);
    }

    /**
     * check if enabled.
     *
     * @return boolean <code>true</code> if core.php exists, <code>false</code> if not.
     */
    public function isEnabled() {
        return file_exists($this->outputFilename);
    }

    /**
     * Clean up all temp. files.
     */
    public function clean() {
        parent::clean();
        ZMTools::rmdir($this->pluginsPreparedFolder);
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

        // add some levels to make plugins load last
        $this->preparePlugins($this->pluginsPreparedFolder.'/1/2/3/4');

        if ($this->stripCode) {
            $this->stripPhpDir($this->rootFolder, $this->strippedFolder);
            $this->stripPhpDir($this->pluginsPreparedFolder, $this->strippedFolder);
        }
        if (!$this->hasErrors()) {
            if ($this->stripCode) {
                $this->flattenDirStructure($this->strippedFolder, $this->flatFolder);
            } else {
                $this->flattenDirStructure($this->rootFolder, $this->flatFolder);
                $this->flattenDirStructure($this->pluginsPreparedFolder, $this->flatFolder);
            }
            if (!$this->hasErrors()) {
                $this->createInitBootstrap($this->flatFolder);
                $this->compressToSingleFile($this->flatFolder, $this->outputFilename);
            }
        }

        if ($this->stripCode) {
            $this->clean();
        }

        return !$this->hasErrors();
    }

    /**
     * Prepare plugin files.
     *
     * <p>Prepare those plugin files that can be compressed.</p>
     *
     * @param string out The output directory.
     */
    private function preparePlugins($out) {
        if (!ZMTools::endsWith($out, '/')) $out .= '/';

        foreach (ZMPlugins::instance()->getAllPlugins() as $type => $plugins) {
            foreach ($plugins as $plugin) {
                if (!$plugin->isEnabled()) {
                    continue;
                }
                $flag = $plugin->getLoaderSupport();
                $pluginBase = $out.$type.'/'.$plugin->getId().'/';
                ZMTools::mkdir($pluginBase, 755);
                if ('NONE' != $flag) {
                    $pluginDir = $plugin->getPluginDir();
                    $noDir = false;
                    if (empty($pluginDir)) {
                        $pluginDir = ZMRuntime::getPluginsDir() . $type . '/';
                        $noDir = true;
                    }
                    if ($noDir || 'PLUGIN' == $flag) {
                        $files = array($pluginDir.$plugin->getId().'.php');
                    } else {
                        $files = ZMLoader::findIncludes($pluginDir, '.php', 'FOLDER' != $flag);
                    }
                    foreach ($files as $file) {
                        $fileBase = str_replace($pluginDir, '', $file);
                        $relDir = dirname($fileBase).'/';
                        if ('./' == $relDir) {
                            $relDir = '';
                        }
                        $source = file_get_contents($file);
                        if (!empty($relDir)) {
                            ZMTools::mkdir($pluginBase . $relDir, 755);
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
                        ZMTools::setFilePerms($outfile);
                    }
                }
            }
        }
    }

    /**
     * Create init_bootstrap.php
     *
     * @param string out The output directory.
     */
    private function createInitBootstrap($out) {
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
            "define('ZM_SINGLE_CORE', true);",
            'if (ZMSettings::get("isLegacyAPI")) {',
            '  $zm_loader = ZMLoader::instance();',
            '  $zm_runtime = ZMLoader::make("Runtime");',
            '  $zm_request = new ZMRequest();',
            '}'
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
        ZMTools::setFilePerms($outfile);
    }

    /**
     * Empty callback to make final adjustments to the file list before compressing to a single file.
     *
     * @param array files List of files.
     * @return array The final list.
     */
    protected function finalizeFiles($files) {
        // some need to be in order :/
        $loadFirst = array(
            'ZMSettings.php',
            '1/constants.php',
            '1/defaults.php',
            'ZMObject.php',
            'ZMLoader.php',
            'ZMRuntime.php',
            '1/ZMRequest.php',
            'init_bootstrap.php'
        );
        $tmp2 = array();
        foreach ($loadFirst as $first) {
            $tmp2[] = $files[$first];
        }
        $firstLookup = array_flip($loadFirst);
        foreach ($files as $key => $file) {
            if (!isset($firstLookup[$key])) {
                $tmp2[] = $file;
            }
        }

        return $tmp2;
    }

}

?>
