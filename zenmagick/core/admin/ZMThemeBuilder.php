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
 * Build the skelton of a new theme.
 *
 * @author DerManoMann
 * @package org.zenmagick.admin
 * @version $Id$
 */
class ZMThemeBuilder extends ZMObject {
    var $name_;
    var $inheritDefaults_;
    var $messages_;
    var $fsLog_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        $this->name_ = '';
        $this->inheritDefaults_ = true;
        $this->messages_ = array();
        $this->fsLog_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get messages.
     *
     * @return array List of text messages.
     */
    function getMessages() {
        return $this->messages_;
    }


    /**
     * Set the name.
     *
     * @param string name The name.
     */
    function setName($name) { $this->name_ = $name; }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    function getName() { return $this->name_; }

    /**
     * Set the inherit defaults flag.
     *
     * @param boolean inheritDefaults The value.
     */
    function setInheritDefaults($inheritDefaults) { $this->inheritDefaults_ = $inheritDefaults; }

    /**
     * Get the inherit defaults flag.
     *
     * @return boolean The value.
     */
    function getInheritDefaults() { return $this->inheritDefaults_; }

    /**
     * Build a theme.
     *
     * @return boolean <code>true</code> if successful, <code>false</code> if not.
     */
    function build() {
        if (empty($this->name_)) {
            $this->messages_[] = array(ZMMessages::T_WARN, 'Invalid theme name "' . $this->name_ . '".');
            return false;
        }

        if (!$this->_createFolder()) {
            return false;
        }

        if (!$this->_createThemeInfoClass()) {
            return false;
        }

        if (!$this->_createInheritDefaultsSetting()) {
            return false;
        }

        // try to set group/owner
        // XXX TODO: use FileUtils
        clearstatcache();
        $owner = fileowner(__FILE__);
        $group = filegroup(__FILE__);
        foreach (array_reverse($this->fsLog_) as $file) {
            @chgrp($file, $group);
            @chown($file, $owner);
        }

        $this->messages_[] = array(ZMMessages::T_SUCCESS, 'Successfully created new theme "' . $this->name_ . '".');
        return true;
    }

    /**
     * Get the themes basedir.
     *
     * @return string The theme base directory.
     */
    function getBaseDir() {
        return ZMRuntime::getThemesDir() . $this->name_ . '/';
    }

    /**
     * Create all required folder.
     *
     * @return boolean <code>true</code> if successful, <code>false</code> if not.
     */
    function _createFolder() {
        $themeDir = $this->getBaseDir();
        if (file_exists($themeDir)) {
            $this->messages_[] = array(ZMMessages::T_WARN, 'Theme "' . $this->name_ . '" already exists!');
            return false;
        }

        // try base dir
        ZMFileUtils::mkdir($themeDir);
        $this->fsLog_[] = $themeDir;
        if (!file_exists($themeDir)) {
            $this->messages_[] = array(ZMMessages::T_WARN, 'Could not create theme dir "' . $themeDir . '".');
            return false;
        }
        
        // do the common ones
        ZMFileUtils::mkdir($themeDir.'content/');
        $this->fsLog_[] = $themeDir.'content/';
        ZMFileUtils::mkdir($themeDir.'extra/');
        $this->fsLog_[] = $themeDir.'extra/';
        ZMFileUtils::mkdir($themeDir.'content/'.'views/');
        $this->fsLog_[] = $themeDir.'content/'.'views/';
        ZMFileUtils::mkdir($themeDir.'content/boxes/');
        $this->fsLog_[] = $themeDir.'content/boxes/';
        ZMFileUtils::mkdir($themeDir.'lang/');
        $this->fsLog_[] = $themeDir.'lang/';

        return true;
    }

    /**
     * Create theme info class.
     *
     * @return boolean <code>true</code> if successful, <code>false</code> if not.
     */
    function _createThemeInfoClass() {
        $infoName = $this->name_ . ' ThemeInfo';
        $infoClass = ZMLoader::makeClassname($infoName);

        $infoClassFile = $this->getBaseDir() . $infoClass .  '.php';

        if (!$handle = fopen($infoClassFile, 'ab')) {
            $this->messages[] = array(ZMMessages::T_WARN, 'could not open theme info class for writing ' . $infoClassFile);
            return false;
        }

        $contents = '<?php

// theme info class generated by ZMThemeBuilder; edit at own risk
class '.$infoClass.' extends ZMThemeInfo {

    // c\'tor
    function __construct() {
        parent::__construct();

        $this->setName("'.$this->name_.'");
        $this->setVersion("0.1");
        $this->setAuthor("ZenMagick");
        $this->setDescription("'.$this->name_.' theme; generated by ZMThemeBuilder.");
    }
    
}

?>
';

        if (false === fwrite($handle, $contents)) {
            $this->errors_[] = array(ZMMessages::T_WARN, 'could not write to file ' . $infoClassFile);
            return;
        }
  
        fclose($handle);

        $this->fsLog_[] = $infoClassFile;

        return true;
    }

    /**
     * Handle inherit defaults setting.
     *
     * @return boolean <code>true</code> if successful, <code>false</code> if not.
     */
    function _createInheritDefaultsSetting() {
        if ($this->inheritDefaults_) {
            // nothing to do
            return true;
        }

        $themeDir = $this->getBaseDir();
        $localFile = $themeDir.ZM_THEME_EXTRA_DIR . 'local.php';

        if (!$handle = fopen($localFile, 'ab')) {
            $this->messages_[] = array(ZMMessages::T_WARN, 'could not open theme local.php file for writing ' . $localFile);
            return false;
        }

        $contents = '<?php

    ZMSettings::set(\'isEnableThemeDefaults\', false);

?>
';

        if (false === fwrite($handle, $contents)) {
            $this->messages_[] = array(ZMMessages::T_WARN, 'could not write to file ' . $localFileinfoClassFile);
            return;
        }
  
        fclose($handle);

        $this->fsLog_[] = $localFile;

        return true;
    }

}

?>
