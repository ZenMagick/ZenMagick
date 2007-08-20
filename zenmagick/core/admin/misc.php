<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
 * $Id$
 */
?>
<?php  

    $_zm_obsolete_files = array();
    array_push($_zm_obsolete_files, DIR_FS_ADMIN . "zmFeatures.php");
    array_push($_zm_obsolete_files, DIR_FS_ADMIN . "zmCleanup.php");
    array_push($_zm_obsolete_files, DIR_FS_ADMIN . "includes/boxes/extra_boxes/zmFeatures_catalog_dhtml.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "themes/default/controller/DefaultController.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "themes/default/controller");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "themes/extra/categories.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "core/phpBB.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "themes/default/content/views/popup/popup_cvv_help.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "themes/default/content/views/popup/popup_search_help.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "themes/lang/english/other.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "admin_init.php");

    // Ultimate SEO
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "core/ext/reset_seo_cache.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "core/ext/seo.install.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "core/ext/seo.url.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "core/ext/seo.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "admin/installation/patches/file/ZMUltimateSeoSupportPatch.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . "includes/functions/extra_functions/zenmagick_ultimate_seo.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . "admin/includes/functions/extra_functions/seo.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "themes/default/extra/url_mapping.php");
    array_push($_zm_obsolete_files, DIR_FS_CATALOG . ZM_ROOT . "core/rp/uip/controller/ZMCheckoutAnonymousController.php");


    // check for existance of all obsolete files
    function zm_get_obsolete_files() {
    global $_zm_obsolete_files;
        $obsolete = array();
        foreach ($_zm_obsolete_files as $file) {
            if (file_exists($file)) {
                array_push($obsolete, $file);
            }
        }
        return $obsolete;
    }

    // make zen-cart relative
    function zm_mk_relative($file) {
      return zm_starts_with($file, DIR_FS_CATALOG) ? substr($file, strlen(DIR_FS_CATALOG)) : file;
    }

    // find all l10n strings
    function zm_build_theme_l10n_map($root, $defaults=false, $merge=false) {
    global $zm_runtime;

        $themeId = basename($root);
        $l10nMap = array();

        if ($defaults) {
            // do default theme first
            $l10nMap = zm_build_theme_l10n_map(dirname($root).'/'.ZM_DEFAULT_THEME, false, false);
        }

        if ($merge) {
            // load existing mappings
            $zm_runtime->setThemeId($themeId);
            zm_resolve_theme(zm_setting('isEnableThemeDefaults') ? ZM_DEFAULT_THEME : $themeId);
            if (0 < count($GLOBALS['_zm_i18n_text'])) {
                $l10nMap['inherited_mappings'] = $GLOBALS['_zm_l10n_text'];
            }
        }

        $includes = zm_find_includes($root.'/', true);
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


    function zm_adm_cmp_redirect($view, $params) {
        ob_end_clean();
        zen_redirect(zen_href_link(ZM_ADMINFN_CATALOG_MANAGER . "?view=".$view.$params));
    }

?>
