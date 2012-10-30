<?php
$adminDir = $container->getParameter('zencart.admin_dir');
$adminWeb = basename($adminDir);
$zcPage = str_replace('zc_admin_', '',$request->getRequestId()).'.php';
chdir($adminDir);

//$autoLoader->setErrorLevel();
// needed in a local context
global $currencies;
// Might want to actually load it completely local instead.
$autoLoader->setErrorLevel();
$autoLoader->includeFiles('includes/languages/%language%.php');
$autoLoader->includeFiles('includes/languages/%language%/'.$zcPage);
$autoLoader->includeFiles('includes/languages/%language%/extra_definitions/*.php');

$PHP_SELF = $adminDir.'/'.$zcPage;
$code = file_get_contents($adminDir.'/'.$zcPage);

$code = preg_replace("/require\(.*application_top.php'\s*\);/", '', $code);
$code = preg_replace("/require\(.*footer.php'\s*\);/", '', $code);
$code = preg_replace("/require\(.*application_bottom.php'\s*\);/", '', $code);
ob_start();
eval('?>'.$code);
$content = ob_get_clean();
$spiffyCalKill = array( // this often gets included outside of <head> so we "fix" the entire document
    '<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">',
    '<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js">');
$content = str_replace($spiffyCalKill, '', $content);
$content = str_replace('src="includes', 'src="/'.$adminWeb.'/includes', $content);
$content = str_replace('href="includes', 'href="/'.$adminWeb.'/includes', $content);
$content = str_replace('src="images', 'src="/'.$adminWeb.'/images', $content);
//$content = preg_replace('|<select([^>]*)name="reset_editor"(.*?)>(.*?)</select>|sm', '', $content);

echo $content;
?>
<?php echo $currentEditor->apply($request, $view, null); ?>
<?php $autoLoader->restoreErrorLevel(); ?>
