<h1><?php _vzm('Zen Cart Admin') ?></h1>
<link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_CATALOG.ZC_ADMIN_FOLDER ?>/includes/stylesheet.css">
<script>
function check_form() {
  return true;
}
</script>
<?php
if (!function_exists('zen_href_link')) {
    function zen_href_link($page='', $params='', $transport='NONSSL', $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
        if (defined('ZC_INSTALL_PATH')) {
            $request = ZMRequest::instance();
            return $request->url('zc_admin', 'zpid='.str_replace('.php', '', $page).'&'.$params);
        }
        return zen_href_link_DISABLED($page, $params, $transport, $addSessionId, $seo, $isStatic, $useContext);
    }
}

// load ZM email wrapper and replacement functions
require_once ZMRuntime::getInstallationPath().'apps/store/lib/email.php';
require_once ZMRuntime::getInstallationPath().'apps/store/lib/zencart_overrides.php';
// map emails view; here we want a store view; merge
ZMUrlManager::instance()->setMapping(null, array('emails' => array('view' => 'SavantView')), false);

function split_slash($s) {
  $s = preg_replace('#(\S)/#', '$1 /', $s);
  return preg_replace('#/(\S)#', '/ $1', $s);
}

$zcAdminFolder = ZC_INSTALL_PATH.ZC_ADMIN_FOLDER.DIRECTORY_SEPARATOR;
$zcPage = str_replace('.php', '', $request->getParameter('zpid', 'index')).'.php';
chdir($zcAdminFolder);

// prepare globals
global $PHP_SELF, $db, $autoLoadConfig, $sniffer, $currencies, $template, $current_page_base, $zco_notifier;
$PHP_SELF = $zcAdminFolder.$zcPage;
$code = file_get_contents($zcAdminFolder.$zcPage);
$code = preg_replace("/<!doctype[^>]*>/s", '', $code);
$code = preg_replace("/<html.*<body[^>]*>/s", '', $code);
$code = preg_replace("/require\(.*header.php'\s*\);/", '', $code);
$code = preg_replace("/require\(.*footer.php'\s*\);/", '', $code);
$code = preg_replace("/<\/body>\s*<\/html>/s", '', $code);
$code = preg_replace("/require\(.*application_bottom.php'\s*\);/", '', $code);
//echo '<pre>'; echo htmlentities($code); echo '</pre>';
ob_start();
eval('?>'.$code);
$content = ob_get_clean();
$content = str_replace('id="main"', '', $content);
$content = str_replace('src="includes', 'src="'.DIR_WS_ADMIN.'includes', $content);
$content = str_replace('src="images', 'src="'.DIR_WS_ADMIN.'images', $content);
$content = str_replace(array('onmouseover="rowOverEffect(this)"', 'onmouseout="rowOutEffect(this)"'), '', $content);
?>
<div id="sub-menu">
  <div id="sub-common">
    <?php
      ob_start();
      $zc_menus = array('catalog', 'modules', 'customers', 'taxes', 'localization', 'reports', 'tools', 'gv_admin', 'extras');
      $menu = array();
      foreach ($zc_menus as $zm_menu) {
          require(DIR_WS_BOXES . $zm_menu . '_dhtml.php');
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
<div id="view-container">
  <?php echo $content; ?>
