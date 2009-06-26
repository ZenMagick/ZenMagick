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
 *
 * $Id: misc.php 2308 2009-06-24 11:03:11Z dermanomann $
 */
?>
<?php  

    // check for existance of all obsolete files
    function zm_get_obsolete_files() {
        $all_obsolete_files = array();
        $all_obsolete_files[] = DIR_FS_ADMIN . "zmFeatures.php";
        $all_obsolete_files[] = DIR_FS_ADMIN . "zmCleanup.php";
        $all_obsolete_files[] = DIR_FS_ADMIN . "includes/boxes/extra_boxes/zmFeatures_catalog_dhtml.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "themes/default/controller/DefaultController.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "themes/default/controller";
        $all_obsolete_files[] = ZM_BASE_DIR . "themes/extra/categories.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "core/phpBB.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "themes/default/content/views/popup/popup_cvv_help.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "themes/default/content/views/popup/popup_search_help.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "themes/lang/english/other.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "admin_init.php";
        // Ultimate SEO
        $all_obsolete_files[] = ZM_BASE_DIR . "core/ext/reset_seo_cache.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "core/ext/seo.install.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "core/ext/seo.url.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "core/ext/seo.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "core/admin/installation/patches/file/ZMUltimateSeoSupportPatch.php";
        $all_obsolete_files[] = DIR_FS_CATALOG . "includes/functions/extra_functions/zenmagick_ultimate_seo.php";
        $all_obsolete_files[] = DIR_FS_CATALOG . "admin/includes/functions/extra_functions/seo.php";
        //
        $all_obsolete_files[] = ZM_BASE_DIR . "themes/default/extra/url_mapping.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "core/rp/uip/controller/ZMCheckoutAnonymousController.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "themes/demo/controller/AjaxCountryController.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "themes/demo/content/yui";
        $all_obsolete_files[] = ZM_BASE_DIR . "themes/demo/content/prototype15.js";
        $all_obsolete_files[] = ZM_BASE_DIR . "core/admin/installation/patches/file/ZMDynamicAdminMenuPatch.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "zenmagick/themes/default/content/category.js";
        $all_obsolete_files[] = ZM_BASE_DIR . "core/misc/phpBB.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "core/validation/rules/custom/ZMNickNameRule.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "core/bootstrap.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "core/cache/defaults.php";
        $all_obsolete_files[] = ZM_BASE_DIR . "core/cache/ZMPageCache.php";
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
            $l10nMap = zm_build_theme_l10n_map(dirname($root).'/'.ZM_DEFAULT_THEME, false, false);
        }

        if ($merge) {
            // load existing mappings
            Runtime::setThemeId($themeId);
            ZMThemes::instance()->resolveTheme(ZMSettings::get('isEnableThemeDefaults') ? ZM_DEFAULT_THEME : $themeId);
            if (0 < count($GLOBALS['_zm_i18n_text'])) {
                $l10nMap['inherited_mappings'] = $GLOBALS['_zm_l10n_text'];
            }
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

?>
