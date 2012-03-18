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

$resourceManager->cssFile('zc_admin.css');
$resourceManager->jsFile('zc_admin.js');

function split_slash($s) {
  $s = preg_replace('#(\S)/#', '$1 /', $s);
  return preg_replace('#/(\S)#', '/ $1', $s);
}

$zcAdminFolder = ZC_INSTALL_PATH.ZENCART_ADMIN_FOLDER.DIRECTORY_SEPARATOR;
$zpid = str_replace('.php', '', $request->getParameter('zpid', 'index'));
$zcPage = $zpid.'.php';
chdir($zcAdminFolder);

// prepare globals
global $PHP_SELF, $db, $autoLoadConfig, $sniffer, $currencies, $template, $current_page_base, $zco_notifier, $zc_products, $session_started;
$session_started = true;
define('TEXT_EDITOR_INFO', ''); // hide text editor box
$PHP_SELF = $zcAdminFolder.$zcPage;
$code = file_get_contents($zcAdminFolder.$zcPage);
$code = preg_replace("/<!doctype[^>]*>/s", '', $code);
$code = preg_replace("/require\(.*application_top.php'\s*\);/", "require('".zenmagick\base\Runtime::getInstallationPath().'/shared/store/bundles/ZenCartBundle/bridge/includes/application_top.php'."');", $code);
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
$content = str_replace('src="includes', 'src="/'.ZENCART_ADMIN_FOLDER.'/includes', $content);
$content = str_replace('src="images', 'src="/'.ZENCART_ADMIN_FOLDER.'/images', $content);
$content = str_replace(array('onmouseover="rowOverEffect(this)"', 'onmouseout="rowOutEffect(this)"'), '', $content);
//action="/zmdev/zenmagick/apps/admin/web/index.php?rid=zc_admin&zpid=categories&" method="get">
$content = preg_replace('|<select([^>]*)name="reset_editor"(.*?)>(.*?)</select>|sm', '', $content);
$content = preg_replace('/(action="[^"]*index.php\?rid=zc_admin&zpid=)([^&"]*)([^>]*>)/', '$1$2$3<input type="hidden" name="rid" value="zc_admin"><input type="hidden" name="zpid" value="$2">', $content);
//echo $content;return;

// printing view
$skipMenu = in_array($zpid, $settings->get('apps.store.zencart.skipLayout', array()));

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
          require('includes/boxes/' . $zm_menu . '_dhtml.php');
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
  <?php echo $currentEditor->apply($request, $view, null); ?>
  <?php if (isset($scripts)) { ?>
    <div id="navbar"></div>
    <div id="hoverJS"></div>
    <script type="text/javascript"> function cssjsmenu(foo) {}; init(); </script>
  <?php } ?>
