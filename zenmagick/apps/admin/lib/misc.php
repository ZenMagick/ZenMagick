<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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

    // check for existance of all obsolete files
    function zm_get_obsolete_files() {
        $all_obsolete_files = array();
        $all_obsolete_files[] = DIR_FS_ADMIN . "zmFeatures.php";
        $all_obsolete_files[] = DIR_FS_ADMIN . "zmCleanup.php";
        $all_obsolete_files[] = DIR_FS_ADMIN . "includes/boxes/extra_boxes/zmFeatures_catalog_dhtml.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "themes/default/controller/DefaultController.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "themes/default/controller";
        $all_obsolete_files[] = ZM_BASE_PATH . "themes/extra/categories.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "core/phpBB.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "themes/default/content/views/popup/popup_cvv_help.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "themes/default/content/views/popup/popup_search_help.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "themes/lang/english/other.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "admin_init.php";
        // Ultimate SEO
        $all_obsolete_files[] = ZM_BASE_PATH . "core/ext/reset_seo_cache.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "core/ext/seo.install.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "core/ext/seo.url.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "core/ext/seo.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "core/admin/installation/patches/file/ZMUltimateSeoSupportPatch.php";
        $all_obsolete_files[] = DIR_FS_CATALOG . "includes/functions/extra_functions/zenmagick_ultimate_seo.php";
        $all_obsolete_files[] = DIR_FS_CATALOG . "admin/includes/functions/extra_functions/seo.php";
        //
        $all_obsolete_files[] = ZM_BASE_PATH . "themes/default/extra/url_mapping.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "core/rp/uip/controller/ZMCheckoutAnonymousController.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "themes/demo/controller/AjaxCountryController.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "themes/demo/content/yui";
        $all_obsolete_files[] = ZM_BASE_PATH . "themes/demo/content/prototype15.js";
        $all_obsolete_files[] = ZM_BASE_PATH . "core/admin/installation/patches/file/ZMDynamicAdminMenuPatch.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "zenmagick/themes/default/content/category.js";
        $all_obsolete_files[] = ZM_BASE_PATH . "core/misc/phpBB.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "core/validation/rules/custom/ZMNickNameRule.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "core/bootstrap.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "core/cache/defaults.php";
        $all_obsolete_files[] = ZM_BASE_PATH . "core/cache/ZMPageCache.php";
        //
        $all_obsolete_files[] = DIR_FS_ADMIN . "zmCacheManager.php";
        //
        $all_obsolete_files[] = DIR_FS_CATALOG . "admin/includes/functions/extra_functions/zenmagick.php";
        //
        $all_obsolete_files[] = DIR_FS_ADMIN . "zmCMPattributes.php";
        $all_obsolete_files[] = DIR_FS_ADMIN . "zmCMPproduct.php";
        $all_obsolete_files[] = DIR_FS_ADMIN . "zmCMPcategory.php";
        $all_obsolete_files[] = DIR_FS_ADMIN . "zmCMPfeatures.php";
        $all_obsolete_files[] = DIR_FS_ADMIN . "includes/dtree";
        $all_obsolete_files[] = DIR_FS_ADMIN . "includes/zmCatalogDtree.php";

        $obsolete = array();
        foreach ($all_obsolete_files as $file) {
            if (file_exists($file)) {
                array_push($obsolete, $file);
            }
        }
        return $obsolete;
    }

    // find all l10n strings
    function zm_build_theme_l10n_map($root, $defaults=false, $merge=false) {
        $themeId = basename($root);
        $l10nMap = array();

        if ($defaults) {
            // do default theme first
            $l10nMap = zm_build_theme_l10n_map(dirname($root).'/'.ZMSettings::get('defaultThemeId'), false, false);
        }

        if ($merge) {
            // load existing mappings
            Runtime::setThemeId($themeId);
            $defaultThemeId = (ZMSettings::get('isEnableThemeDefaults') ? ZMSettings::get('defaultThemeId') : $themeId);
            ZMThemes::instance()->resolveTheme($defaultThemeId, ZMRequest::instance()->getSession()->getLanguage());
        }

        $includes = ZMLoader::findIncludes($root.'/', '.php', true);
        foreach ($includes as $include) {
            $strings = array();
            $contents = file_get_contents($include);
            $pos = 0;
            while (-1 < $pos) {
                $pos = strpos($contents, "zm_l10n", $pos);
                if ($pos === false)
                    break;
                $ob = strpos($contents, "(", $pos+1);
                if ($pos < $ob) {
                    // found something
                    // examine first non whitespace char to figure out which quote to look for
                    $quote = '';
                    $qi = $ob+1;
                    while (true) {
                        $quote = trim(substr($contents, $qi, 1));
                        if ("'" == $quote || '"' == $quote) {
                            break;
                        }
                        if ('' != $quote) {
                            // not a string
                            $quote = null;
                            break;
                        }

                        ++$qi;
                        // sanity check
                        if ($qi-$ob > 10)
                          break;
                    }
                    $pos += $qi-$ob+1;
                    $text = '';
                    if ('' != $quote) {
                        // have a quote
                        $lastChar = '';
                        $start = $qi+1;
                        $len = 0;
                        $char = '';
                        while (true) {
                            $char = substr($contents, $start+$len, 1);
                            $len++;
                            if ($char == $quote && $lastChar != '\\') {
                                break;
                            }
                            $lastChar = $char;
                            $text .= $char;
                            if ($len > 1000)
                                break;
                        }
                        $strings[$text] = $text;
                    } else {
                        // found something, but not a string
                        // echo "<span style='color:red;'>Found something: '".substr($contents, $qi-10, 20)."...'</span><br>";
                    }
                } else {
                    break;
                }
            }
            if (0 < count($strings)) {
                $l10nMap[zm_mk_relative($include)] = $strings;
            }
        }

        return $l10nMap;
    }

    // make zen-cart relative
    function zm_mk_relative($file) {
      return ZMLangUtils::startsWith($file, DIR_FS_CATALOG) ? substr($file, strlen(DIR_FS_CATALOG)) : file;
    }
