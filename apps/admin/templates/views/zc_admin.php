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

<link rel="stylesheet" type="text/css" href="<?php echo ZM_DIR_WS_CATALOG.ZC_ADMIN_FOLDER ?>/includes/stylesheet.css">

<?php

use zenmagick\base\Runtime;

if (!function_exists('zen_href_link')) {
    function zen_href_link($page='', $params='', $transport='NONSSL', $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
        if (defined('ZC_INSTALL_PATH')) {
            $request = Runtime::getContainer()->get('request');
            // strip rid,zpid frm params
            parse_str($params, $tmp);
            unset($tmp['rid']);
            unset($tmp['zpid']);
            $params = http_build_query($tmp);
            return $request->url('zc_admin', 'zpid='.str_replace('.php', '', $page).'&'.$params);
        }
        return zen_href_link_DISABLED($page, $params, $transport, $addSessionId, $seo, $isStatic, $useContext);
    }
}

// load ZM email wrapper and replacement functions
require_once Runtime::getInstallationPath().'apps/storefront/lib/zencart_overrides.php';
// map emails view; here we want a store view; merge
ZMUrlManager::instance()->setMapping(null, array('emails' => array('view' => 'SavantView')), false);

function split_slash($s) {
  $s = preg_replace('#(\S)/#', '$1 /', $s);
  return preg_replace('#/(\S)#', '/ $1', $s);
}

$zcAdminFolder = ZC_INSTALL_PATH.ZC_ADMIN_FOLDER.DIRECTORY_SEPARATOR;
$zpid = str_replace('.php', '', $request->getParameter('zpid', 'index'));
$zcPage = $zpid.'.php';
chdir($zcAdminFolder);

// prepare globals
global $PHP_SELF, $db, $autoLoadConfig, $sniffer, $currencies, $template, $current_page_base, $zco_notifier, $zc_products;
$PHP_SELF = $zcAdminFolder.$zcPage;
$code = file_get_contents($zcAdminFolder.$zcPage);
$code = preg_replace("/<!doctype[^>]*>/s", '', $code);
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
// get all head content and find all script code
preg_match("/<head\>.*<\/head\>/is", $content, $head);
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
$content = preg_replace("/<html.*<body[^>]*>/s", '', $content);
$content = str_replace('id="main"', '', $content);
$content = str_replace('src="includes', 'src="'.ZM_DIR_WS_ADMIN.'includes', $content);
$content = str_replace('src="images', 'src="'.ZM_DIR_WS_ADMIN.'images', $content);
$content = str_replace(array('onmouseover="rowOverEffect(this)"', 'onmouseout="rowOutEffect(this)"'), '', $content);
//action="/zmdev/zenmagick/apps/admin/web/index.php?rid=zc_admin&zpid=categories&" method="get">
$content = preg_replace('/(action="[^"]*index.php\?rid=zc_admin&zpid=)([^&"]*)([^>]*>)/', '$1$2$3<input type="hidden" name="rid" value="zc_admin"><input type="hidden" name="zpid" value="$2">', $content);
//$content = preg_replace('/(action="[^"]*index.php)\?rid=zc_admin&zpid=[^&"]*([^>]*>)/', '$1$2', $content);
//echo $content;return;

// printing view
$skipMenu = in_array($zpid, zenmagick\base\Runtime::getSettings()->get('apps.store.zencart.skipLayout', array()));

if (!$skipMenu) {
?>
<h1><?php _vzm('Zen Cart Admin') ?></h1>
<script>
function check_form() {
  return true;
}
</script>
<div id="sub-menu">
  <div id="sub-common">
    <?php
      ob_start();
      $zc_menus = array('catalog', 'modules', 'customers', 'taxes', 'localization', 'reports', 'tools', 'gv_admin', 'extras');
      $menu = array();
      foreach ($zc_menus as $zm_menu) {
          require(ZM_DIR_WS_BOXES . $zm_menu . '_dhtml.php');
          $header = split_slash($za_heading['text']);
          $menu[$header] = array();
          $skipList = array('zmIndex', 'template_select', 'server_info', 'sqlpatch', 'zpid=admin','ezpages', 'define_page_editor',
          'record_artists', 'record_company', 'music_genre', 'media_manager', 'media_types');
          foreach ($za_contents as $item) {
              $skip = false;
              foreach ($skipList as $s) {
                  if (-1 < strpos($item['link'], $s)) {
                      $skip = true;
                      break;
                  }
              }
              if (!$skip) {
                  $menu[$header][strip_tags(split_slash($item['text']))] = $item['link'];
              }
          }
      }
      ob_end_clean();
    ?>
    <?php foreach ($menu as $header => $items) { ?>
      <h3><a href="#"><?php echo $header ?></a></h3>
      <div>
        <ul>
          <?php foreach ($items as $text => $link) { ?>
            <li><a href="<?php echo $link ?>"><?php echo $text ?></a></li>
          <?php } ?>
        </ul>
      </div>
    <?php } ?>
  </div>
</div>
<script type="text/javascript">
  $(function() {
    $("#sub-common").accordion({
      active: false,
      autoHeight: false,
      collapsible: true,
      navigation: true,
      navigationFilter: function() {
        return this.href == location.href;
      }
    });
  });
</script>
<?php } ?>
<div id="view-container">
  <?php echo $content; ?>
  <?php if (isset($scripts)) { ?>
    <div id="navbar"></div>
    <div id="hoverJS"></div>
    <script> function cssjsmenu(foo) {}; init(); </script>
  <?php } ?>
