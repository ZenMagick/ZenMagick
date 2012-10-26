<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace ZenMagick\AdminBundle\Utils;

use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Session\FlashBag;

/**
 * Build the skelton of a new theme.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ThemeBuilder extends ZMObject {
    private $name_;
    private $messages_;
    private $fsLog_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();

        $this->name_ = '';
        $this->messages_ = array();
        $this->fsLog_ = array();
    }


    /**
     * Get messages.
     *
     * @return array List of text messages.
     */
    public function getMessages() {
        return $this->messages_;
    }


    /**
     * Set the name.
     *
     * @param string name The name.
     */
    public function setName($name) { $this->name_ = $name; }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function getName() { return $this->name_; }

    /**
     * Build a theme.
     *
     * @return boolean <code>true</code> if successful, <code>false</code> if not.
     */
    public function build() {
        if (empty($this->name_)) {
            $this->messages_[] = array(FlashBag::T_WARN, 'Invalid theme name "' . $this->name_ . '".');
            return false;
        }

        if (!$this->_createFolder()) {
            return false;
        }

        if (!$this->_createThemeConfig()) {
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

        $this->messages_[] = array(FlashBag::T_SUCCESS, 'Successfully created new theme "' . $this->name_ . '".');
        return true;
    }

    /**
     * Get the themes basedir.
     *
     * @return string The theme base directory.
     */
    public function getBaseDir() {
        return $this->container->get('themeService')->getThemesDir() . '/' . $this->name_ . '/';
    }

    /**
     * Create all required folder.
     *
     * @return boolean <code>true</code> if successful, <code>false</code> if not.
     */
    function _createFolder() {
        $themeDir = $this->getBaseDir();
        if (file_exists($themeDir)) {
            $this->messages_[] = array(FlashBag::T_WARN, 'Theme "' . $this->name_ . '" already exists!');
            return false;
        }

        $filesystem = $this->container->get('filesystem');

        // try base dir
        $filesystem->mkdir($themeDir, 0755);
        $this->fsLog_[] = $themeDir;
        if (!file_exists($themeDir)) {
            $this->messages_[] = array(FlashBag::T_WARN, 'Could not create theme dir "' . $themeDir . '".');
            return false;
        }

        // do the common ones
        $dirs = array($themeDir.'/content/boxes', $themeDir.'/lib', $themeDir.'/content/views', $themeDir.'/locale');
        $filesystem->mkdir($dirs, 0755);
        $this->fsLog_[] = array_merge($this->fsLog_, $dirs);

        return true;
    }

    /**
     * Create theme config.
     *
     * @return boolean <code>true</code> if successful, <code>false</code> if not.
     */
    function _createThemeConfig() {
        $configFile = $this->getBaseDir() . 'theme.yaml';

        if (!$handle = fopen($configFile, 'ab')) {
            $this->messages[] = array(FlashBag::T_WARN, 'could not open theme config for writing ' . $configFile);
            return false;
        }

        $contents = "
name: '".$this->name_."'
version: '0.1'
author: 'zenmagick.org'
description: '".$this->name_." theme; generated by ThemeBuilder'";

        if (false === fwrite($handle, $contents)) {
            $this->errors_[] = array(FlashBag::T_WARN, 'could not write to file ' . $configFile);
            return;
        }

        fclose($handle);

        $this->fsLog_[] = $configFile;

        return true;
    }

}