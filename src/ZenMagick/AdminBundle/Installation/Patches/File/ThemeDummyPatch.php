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
namespace ZenMagick\AdminBundle\Installation\Patches\File;

use ZenMagick\AdminBundle\Installation\Patches\FilePatch;

/**
 * Patch to create zen-cart theme dummy files for all ZenMagick themes.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ThemeDummyPatch extends FilePatch
{
    protected $catalogTemplatePath;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct('themeDummies');
        $this->label = 'Create admin dummy files for all installed ZenMagick themes';
        $this->catalogTemplatePath = $this->container->getParameter('zencart.root_dir').'/includes/templates/';
    }

    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    public function isOpen()
    {
        foreach ($this->container->get('themeService')->getAvailableThemes() as $theme) {
            if (!file_exists($this->catalogTemplatePath.$theme->getId())) {
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
    public function isReady()
    {
        return is_writeable($this->catalogTemplatePath);
    }

    /**
     * Get the patch group id.
     *
     * @return string The patch group id.
     */
    public function getGroupId()
    {
        return 'file';
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    public function getPreconditionsMessage()
    {
        return $this->isReady() ? "" : "Need permission to write " . $this->catalogTemplatePath;
    }

    /**
     * Execute this patch.
     *
     * @param boolean force If set to <code>true</code> it will force patching even if
     *  disabled as per settings.
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    public function patch($force=false)
    {
        foreach ($this->container->get('themeService')->getAvailableThemes() as $theme) {
            $filesystem = $this->container->get('filesystem');
            $themeId = $theme->getId();
            if (!file_exists($this->catalogTemplatePath.$themeId)) {
                if (is_writeable($this->catalogTemplatePath)) {
                    $templateDir = $this->catalogTemplatePath.$themeId.'/';
                    $themeConfig = $theme->getConfig();
                    $filesystem->mkdir(array($templateDir, $templateDir.'images'), 0755);
                    if (!array_key_exists('preview', $themeConfig)) {
                        $imageName = 'preview.jpg';
                    }
                    $theme = $this->container->get('themeService')->getThemeForId($themeId);
                    if (file_exists($theme->getBaseDir().'preview.jpg')) {
                        copy($theme->getBaseDir().'/preview.jpg', $templateDir.'/images/'.$imageName);
                    } else {
                        $kernel = $this->container->get('kernel');
                        $notFoundImage = $kernel->locateResource('@ZenCartBundle/public/images/preview_not_found.jpg');
                        copy($notFoundImage, $templateDir.'/images/'.$imageName);
                    }
                    $handle = fopen($this->catalogTemplatePath.$themeId."/template_info.php", 'ab');
                    fwrite($handle, '<?php /** dummy file created by ZenMagick installation patcher **/'."\n");
                    fwrite($handle, '  $template_version = ' . "'" . addslashes($themeConfig['version']) . "';\n");
                    fwrite($handle, '  $template_name = ' . "'" . addslashes($themeConfig['name']) . "';\n");
                    fwrite($handle, '  $template_author = ' . "'" . addslashes($themeConfig['author']) . "';\n");
                    fwrite($handle, '  $template_description = ' . "'" . addslashes($themeConfig['description']) . "';\n");
                    fwrite($handle, '  $template_screenshot = ' . "'" . $imageName . "';\n");
                    fwrite($handle, '?>');
                    fclose($handle);
                    $this->setFilePerms($templateDir."template_info.php");
                    $this->setFilePerms($templateDir."images");
                    $this->setFilePerms($templateDir."images/".$imageName);
                } else {
                    $this->container->get('logger')->err("** ZenMagick: no permission to create theme dummy ".$themeId);

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
    public function undo()
    {
        $dummies = $this->getDummies();
        foreach ($dummies as $file) {
            $this->container->get('filesystem')->remove($file);
        }

        return true;
    }

    /**
     * Find all dummies.
     *
     * @return array A list of dummy templates.
     */
    public function getDummies()
    {
        $dummies = array();
        if (file_exists($this->catalogTemplatePath)) {
            $handle = opendir($this->catalogTemplatePath);
            while (false !== ($file = readdir($handle))) {
                if (is_dir($this->catalogTemplatePath.$file) && 0 !== strpos($file, '.')) {
                    if (file_exists($this->catalogTemplatePath.$file."/template_info.php")) {
                        $contents = file_get_contents($this->catalogTemplatePath.$file."/template_info.php");
                        if (false !== strpos($contents, 'created by ZenMagick')) {
                            array_push($dummies, $this->catalogTemplatePath.$file);
                        }
                    }
                }
            }
            closedir($handle);
        }

        return $dummies;
    }

}
