<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
 * Compress lib/core into a single file 'core.php'.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.admin.utils
 * @version $Id$
 */
class ZMCoreCompressor extends ZMPhpCompressor {
    private $pluginsPreparedFolder_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->pluginsPreparedFolder_ = Runtime::getInstallationPath().'plugins.prepared';

        $this->setRoot(array(Runtime::getInstallationPath().'lib', $this->pluginsPreparedFolder_));
        $this->setOut(Runtime::getInstallationPath().'core.php');
        $this->setTemp(Runtime::getInstallationPath());
        $this->setStripCode(ZMSettings::get('isStripCore'));
    }


    /**
     * Disable / remove the core.php file, effectively disabling the use of it.
     */
    public function disable() {
        @unlink($this->outputFilename_);
    }

    /**
     * check if enabled.
     *
     * @return boolean <code>true</code> if core.php exists, <code>false</code> if not.
     */
    public function isEnabled() {
        return file_exists($this->outputFilename_);
    }

    /**
     * Clean up all temp. files.
     */
    public function clean() {
        parent::clean();
        ZMFileUtils::rmdir($this->pluginsPreparedFolder_);
    }

    /**
     * Compress accoding to the current settings.
     *
     * @return boolean <code>true</code> if successful, <code>false</code> on failure.
     */
    public function compress() {
        // add some levels to make plugins load last
        $this->preparePlugins($this->pluginsPreparedFolder_.DIRECTORY_SEPARATOR.'1'.DIRECTORY_SEPARATOR.'2'.DIRECTORY_SEPARATOR.'3'.DIRECTORY_SEPARATOR.'4');
        return parent::compress();
    }

    /**
     * Prepare plugin files.
     *
     * <p>Prepare those plugin files that can be compressed.</p>
     *
     * @param string out The output directory.
     */
    private function preparePlugins($out) {
        if (!ZMLangUtils::endsWith($out, DIRECTORY_SEPARATOR)) {
            $out .= DIRECTORY_SEPARATOR;
        }

        foreach (ZMPlugins::instance()->getAllPlugins() as $type => $plugins) {
            foreach ($plugins as $plugin) {
                if (!$plugin->isEnabled()) {
                    continue;
                }
                $flag = $plugin->getLoaderPolicy();
                $pluginBase = $out.$type.DIRECTORY_SEPARATOR.$plugin->getId().DIRECTORY_SEPARATOR;
                ZMFileUtils::mkdir($pluginBase);
                if (ZMPlugin::LP_NONE != $flag) {
                    $pluginDir = $plugin->getPluginDirectory();
                    $noDir = false;
                    if (empty($pluginDir)) {
                        $pluginDir = Runtime::getPluginsDirectory() . $type . DIRECTORY_SEPARATOR;
                        $noDir = true;
                    }
                    if ($noDir || ZMPlugin::LP_PLUGIN == $flag) {
                        $files = array($pluginDir.$plugin->getId().'.php');
                    } else {
                        $files = ZMLoader::findIncludes($pluginDir, '.php', ZMPlugin::LP_FOLDER != $flag);
                    }
                    foreach ($files as $file) {
                        $fileBase = str_replace($pluginDir, '', $file);
                        $relDir = dirname($fileBase).DIRECTORY_SEPARATOR;
                        if ('.'.DIRECTORY_SEPARATOR == $relDir) {
                            $relDir = '';
                        }
                        if (false === ($source = file_get_contents($file))) {
                            ZMLogging::instance()->log('unable to read plugin source: '.$file, ZMLogging::WARN);
                            continue;
                        }
                        if (!empty($relDir)) {
                            ZMFileUtils::mkdir($pluginBase . $relDir);
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
                        ZMFileUtils::setFilePerms($outfile);
                    }
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function finaliseFiles($files) {
        // some need to be in order :/
        $loadFirst = array(
            implode(DIRECTORY_SEPARATOR, array('1', 'ZMObject.php')),
            implode(DIRECTORY_SEPARATOR, array('1', 'ZMSettings.php')),
            implode(DIRECTORY_SEPARATOR, array('1', 'defaults.php')),
            implode(DIRECTORY_SEPARATOR, array('1', '2', '3', 'ZMEvents.php')),
            implode(DIRECTORY_SEPARATOR, array('1', '2', 'Events.php')),
        );
        $tmp2 = array();
        foreach ($loadFirst as $first) {
            $tmp2[] = $files[$first];
        }
        foreach ($files as $key => $file) {
            if (!in_array($key, $loadFirst)) {
                $tmp2[] = $file;
            }
        }

        return $tmp2;
    }

}

?>
