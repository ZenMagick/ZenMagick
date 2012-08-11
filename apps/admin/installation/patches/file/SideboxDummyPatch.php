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
namespace zenmagick\apps\admin\installation\patches\file;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\apps\admin\installation\patches\FilePatch;

/**
 * Patch to create dummy sidebox files for zen-cart.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SideboxDummyPatch extends FilePatch {
    protected $sideBoxPath;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('sideboxDummies');
        $this->label_ = 'Create dummy files for all (side)boxes of <strong>all</strong> ZenMagick themes and <strong>installed</strong> plugins';
        $this->sideBoxPath = Runtime::getSettings()->get('zencart.root_dir').'/includes/modules/sideboxes/';
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    public function isOpen() {
        return 0 != count($this->getMissingZCSideboxes());
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    public function isReady() {
        return is_writeable($this->sideBoxPath);
    }

    /**
     * Get the patch group id.
     *
     * @return string The patch group id.
     */
    public function getGroupId() {
        return 'file';
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    public function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . $this->sideBoxPath;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    public function patch($force=false) {
        $missingBoxes = $this->getMissingZCSideboxes();

        if (0 < count($missingBoxes)) {
            foreach ($missingBoxes as $box) {
                if ($this->isReady()) {
                    if (!file_exists($this->sideBoxPath.$box)) {
                        $handle = fopen($this->sideBoxPath.$box, 'ab');
                        fwrite($handle, '<?php /** dummy file created by ZenMagick installation patcher **/ ?>');
                        fclose($handle);
                        $this->setFilePerms($this->sideBoxPath.$box);
                    }
                } else {
                    Runtime::getLogging()->error("** ZenMagick: no permission to create dummy sidebox " . $box);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    public function undo() {
        $dummies = $this->_getDummies();
        foreach ($dummies as $file) {
            @unlink($file);
        }

        return true;
    }

    /**
     * Find all dummies.
     *
     * @return array A list of dummy sidebox files.
     */
    protected function _getDummies() {
        $dummies = array();
        if (file_exists($this->sideBoxPath)) {
            $handle = opendir($this->sideBoxPath);
            while (false !== ($file = readdir($handle))) {
                if (!is_dir($this->sideBoxPath.$file) && 0 !== strpos($file, '.')) {
                    $contents = file_get_contents($this->sideBoxPath.$file);
                    if (false !== strpos($contents, '/** dummy file created by ZenMagick installation patcher **/')) {
                        array_push($dummies, $this->sideBoxPath.$file);
                    }
                }
            }
            closedir($handle);
        }

        return $dummies;
    }

    /**
     * Builds a list of all ZenMagick theme sideboxes that do not have zen-cart sidebox dummies.
     *
     * return array List of sideboxes that need zen-cart dummies.
     */
    protected function getMissingZCSideboxes() {
        // list of boxes dirs to process
        $boxPathList = array();

        $boxPathList[] = Runtime::getInstallationPath().'/apps/storefront/templates/boxes';
        // 1) themes
        foreach ($this->container->get('themeService')->getAvailableThemes() as $theme) {
            $boxPathList[] = $theme->getBoxesDir();
        }

        // 2) plugins
        foreach ($this->container->get('pluginService')->getPluginsForContext() as $plugin) {
            $dir = $plugin->getPluginDirectory().'/templates/boxes';
            $boxPathList[] = $dir;
        }

        $missingBoxes = array();
        foreach ($boxPathList as $boxPath) {
            if (file_exists($boxPath) && is_readable($boxPath)) {
                // make list of all theme boxes
                $handle = opendir($boxPath);
                $zmBoxes = array();
                while (false !== ($file = readdir($handle))) {
                    if (is_file($boxPath.'/'.$file) && false === strpos($file, '.in.')) {
                        $zmBoxes[$file] = $file;
                    }
                }
                closedir($handle);

                $zcBoxes = array();
                $handle = opendir($this->sideBoxPath);
                while (false !== ($file = readdir($handle))) {
                    $zcBoxes[$file] = $file;
                }
                closedir($handle);

                foreach ($zmBoxes as $box) {
                    if (!array_key_exists($box, $zcBoxes) && '.' != $box && '..' != $box && Toolbox::endsWith($box, '.php')) {
                        $missingBoxes[$box] = $box;
                    }
                }
            }
        }

        return $missingBoxes;
    }

}
