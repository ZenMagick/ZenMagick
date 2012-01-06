<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\apps\admin\installation\patches\file;

if (!defined('DIR_FS_CATALOG_TEMPLATES')) {
    define('DIR_FS_CATALOG_TEMPLATES', ZC_INSTALL_PATH . 'includes/templates');
}

use zenmagick\base\Runtime;
use zenmagick\apps\admin\installation\patches\FilePatch;


/**
 * Patch to create zen-cart theme dummy files for all ZenMagick themes.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ThemeDummyPatch extends FilePatch {
    private $includeDefault_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('themeDummies');
        $this->includeDefault_ = true;
    }


    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    function isOpen() {
        foreach ($this->container->get('themeService')->getAvailableThemes() as $theme) {
            if (Runtime::getSettings()->get('apps.store.themes.default') == $theme->getThemeId() && !$this->includeDefault_) {
                continue;
            }
            if (!file_exists(DIR_FS_CATALOG_TEMPLATES.$theme->getThemeId())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if this patch is ready to be applied.
     *
     * @return boolean <code>true</code> if this patch is ready and all preconditions are met.
     */
    function isReady() {
        return is_writeable(DIR_FS_CATALOG_TEMPLATES);
    }

    /**
     * Get the patch group id.
     *
     * @return string The patch group id.
     */
    function getGroupId() {
        return 'file';
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return $this->isReady() ? "" : "Need permission to write " . DIR_FS_CATALOG_TEMPLATES;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function patch($force=false) {
        foreach ($this->container->get('themeService')->getAvailableThemes() as $theme) {
            if (Runtime::getSettings()->get('apps.store.themes.default') == $theme->getThemeId() && !$this->includeDefault_) {
                continue;
            }
            $themeId = $theme->getThemeId();
            if (!file_exists(DIR_FS_CATALOG_TEMPLATES.$themeId)) {
                if (is_writeable(DIR_FS_CATALOG_TEMPLATES)) {
                    $templateDir = DIR_FS_CATALOG_TEMPLATES.$themeId.DIRECTORY_SEPARATOR;
                    $themeConfig = $theme->getConfig();
                    \ZMFileUtils::mkdir($templateDir);
                    \ZMFileUtils::mkdir($templateDir.'images');
                    if (!array_key_exists('preview', $themeConfig)) {
                        $imageName = 'preview.jpg';
                    }
                    $theme = $this->container->get('themeService')->getThemeForId($themeId);
                    if (file_exists($theme->getBaseDir().'preview.jpg')) {
                        copy($theme->getBaseDir().'preview.jpg', $templateDir.'images'.DIRECTORY_SEPARATOR.$imageName);
                    } else {
                        copy(Runtime::getInstallationPath().'lib/store/etc/images/preview_not_found.jpg', $templateDir.'images'.DIRECTORY_SEPARATOR.$imageName);
                    }
                    $handle = fopen(DIR_FS_CATALOG_TEMPLATES.$themeId."/template_info.php", 'ab');
                    fwrite($handle, '<?php /** dummy file created by ZenMagick installation patcher **/'."\n");
                    fwrite($handle, '  $template_version = ' . "'" . addslashes($themeConfig['version']) . "';\n");
                    fwrite($handle, '  $template_name = ' . "'" . addslashes($themeConfig['name']) . "';\n");
                    fwrite($handle, '  $template_author = ' . "'" . addslashes($themeConfig['author']) . "';\n");
                    fwrite($handle, '  $template_description = ' . "'" . addslashes($themeConfig['description']) . "';\n");
                    fwrite($handle, '  $template_screenshot = ' . "'" . $imageName . "';\n");
                    fwrite($handle, '?>');
                    fclose($handle);
                    \ZMFileUtils::setFilePerms($templateDir."template_info.php");
                    \ZMFileUtils::setFilePerms($templateDir."images");
                    \ZMFileUtils::setFilePerms($templateDir."images".DIRECTORY_SEPARATOR.$imageName);
                } else {
                    Runtime::getLogging()->error("** ZenMagick: no permission to create theme dummy ".$themeId);
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
    function undo() {
        $dummies = $this->_getDummies();
        foreach ($dummies as $file) {
            // avoid recursive delete, just in case
            @unlink($file."/template_info.php");
            \ZMFileUtils::rmdir($file.'/images', true);
            \ZMFileUtils::rmdir($file, false);
        }

        return true;
    }


    /**
     * Find all dummies.
     *
     * @return array A list of dummy templates.
     */
    function _getDummies() {
        $dummies = array();
        if (file_exists(DIR_FS_CATALOG_TEMPLATES)) {
            $handle = opendir(DIR_FS_CATALOG_TEMPLATES);
            while (false !== ($file = readdir($handle))) {
                if (is_dir(DIR_FS_CATALOG_TEMPLATES.$file) && !\ZMLangUtils::startsWith($file, '.')) {
                    if (file_exists(DIR_FS_CATALOG_TEMPLATES.$file."/template_info.php")) {
                        $contents = file_get_contents(DIR_FS_CATALOG_TEMPLATES.$file."/template_info.php");
                        if (false !== strpos($contents, 'created by ZenMagick')) {
                            array_push($dummies, DIR_FS_CATALOG_TEMPLATES.$file);
                        }
                    }
                }
            }
            closedir($handle);
        }

        return $dummies;
    }

}
