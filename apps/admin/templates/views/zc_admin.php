<link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_CATALOG.ZC_ADMIN_FOLDER ?>/includes/stylesheet.css">
<?php 
if (!function_exists('zen_href_link')) {

    /**
     * zen_href_link wrapper that delegates to the Zenmagick implementation.
     *
     * @package zenmagick.store.sf.override
     */
    function zen_href_link($page='', $params='', $transport='NONSSL', $addSessionId=true, $seo=true, $isStatic=false, $useContext=true) {
        if (defined('ZC_INSTALL_PATH')) {
            $request = ZMRequest::instance();
            return $request->url('zc_admin', 'zpid='.$page.'&'.$params);
        }
        return zen_href_link_DISABLED($page, $params, $transport, $addSessionId, $seo, $isStatic, $useContext);
    }

}
$zcAdminFolder = ZC_INSTALL_PATH.ZC_ADMIN_FOLDER.DIRECTORY_SEPARATOR;
$zcPage = $request->getParameter('zpid', 'index').'.php';
$PHP_SELF = $zcAdminFolder.$zcPage;
chdir($zcAdminFolder);

// prepare globals
global $db, $autoLoadConfig, $sniffer;

$code = file_get_contents($zcAdminFolder.$zcPage);
$code = preg_replace("/<!doctype[^>]*>/s", '', $code);
$code = preg_replace("/<html.*<body[^>]*>/s", '', $code);
$code = preg_replace("/require\(\s*DIR_WS_INCLUDES\s*\.\s*'header.php'\s*\);/", '', $code);
$code = preg_replace("/require\(\s*DIR_WS_INCLUDES\s*\.\s*'footer.php'\s*\);/", '', $code);
$code = preg_replace("/<\/body>\s*<\/html>/s", '', $code);
$code = preg_replace("/require\(\s*DIR_WS_INCLUDES\s*\.\s*'application_bottom.php'\s*\);/", '', $code);
eval('?>'.$code);
