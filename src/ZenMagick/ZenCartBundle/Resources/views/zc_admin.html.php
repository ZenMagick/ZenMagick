<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2013 zenmagick.org
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

$adminDir = $this->container->getParameter('zencart.admin_dir');
$adminWeb = basename($adminDir);
$zcPage = str_replace('zc_admin_', '',$request->getRequestId()).'.php';
chdir($adminDir);

//$autoLoader->setErrorLevel();
// needed in a local context
define('TEXT_EDITOR_INFO', ''); // hide text editor box
global $currencies, $products_options_types_list, $current_category_id;
// needed for developers_tool_kit page
global $check_directory, $configuration_key_lookup, $directory_array, $found, $sub_dir_files;

// Might want to actually load it completely local instead.
$autoLoader->setErrorLevel();
$autoLoader->includeFiles('includes/languages/%language%.php');
$autoLoader->includeFiles('includes/languages/%language%/'.$zcPage);
$autoLoader->includeFiles('includes/languages/%language%/extra_definitions/*.php');

$PHP_SELF = $adminDir.'/'.$zcPage;
$code = file_get_contents($adminDir.'/'.$zcPage);
$code = preg_replace("/<!doctype[^>]*>/s", '', $code);
$code = preg_replace("/require\(.*application_top.php'\s*\);/", '', $code);
$code = preg_replace("/require\(.*header.php'\s*\);/", '', $code);
$code = preg_replace("/require\(.*footer.php'\s*\);/", '', $code);
$code = preg_replace("/<\/body>\s*<\/html>/s", '', $code);
$code = preg_replace("/require\(.*application_bottom.php'\s*\);/", '', $code);
//echo '<pre>'; echo htmlentities($code); echo '</pre>';
//file_put_contents(dirname(__FILE__).'/foo.php', $code);
//include dirname(__FILE__).'/foo.php';die();
//die();
ob_start();
eval('?>'.$code);
$content = ob_get_clean();
$spiffyCalKill = array( // this often gets included outside of <head> so we "fix" the entire document
    '<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">',
    '<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js">');
$content = str_replace($spiffyCalKill, '', $content);
// get all head content and find all script code
preg_match('|<head\>(.*?)</head\>|is', $content, $head);
if (1 == count($head)) {
    preg_match_all("/<script?.+<\/script\>/Uis", $head[0], $scripts);
    foreach ($scripts as $match) {
        foreach ($match as $script) {
            $skip = false;
            foreach (array('includes/menu.js', 'includes/general.js') as $exclude) {
                if (false !== strpos($script, $exclude)) {
                    $skip = true;
                    break;
                }
            }

            if (!$skip) {
                echo $script."\n";
            }
        }
    }
}
$content = preg_replace("/<html.*?<body[^>]*>/s", '', $content);
$content = str_replace('id="main"', '', $content);
$content = str_replace('src="includes', 'src="/'.$adminWeb.'/includes', $content);

$content = str_replace('src="images', 'src="/'.$adminWeb.'/images', $content);
$content = str_replace(array('onmouseover="rowOverEffect(this)"', 'onmouseout="rowOutEffect(this)"'), '', $content);
$content = preg_replace('|<select([^>]*)name="reset_editor"(.*?)>(.*?)</select>|sm', '', $content);
//echo $content;return;
?>
<script type="text/javascript">
function check_form()
{
  return true;
}
</script>
<?php echo $content; ?>
<?php if (isset($scripts)) { ?>
    <div id="navbar"></div>
    <div id="hoverJS"></div>
    <script type="text/javascript"> function cssjsmenu(foo) {}; init(); </script>
<?php } ?>
<?php $autoLoader->restoreErrorLevel();
