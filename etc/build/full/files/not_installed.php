<?php
$relPath = (file_exists('zenmagick/init.php')) ? '' : '../';
$instPath = (file_exists('zc_install/index.php')) ? 'zc_install/index.php' : (file_exists('../zc_install/index.php') ? '../zc_install/index.php' : '');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en">
<head>
<title>Installation Required</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="keywords" content="" />
<meta name="description" content="" />
<meta http-equiv="imagetoolbar" content="no" />
<meta name="authors" content="zenmagick.org" />
<meta name="generator" content="shopping cart program by ZenMagick, http://www.zenmagick.org/" />
<meta name="robots" content="noindex, nofollow" />
<style type="text/css">
<!--
.systemError {color: #FFFFFF}
-->
</style>


</head>

<body style="margin: 20px">
<?php echo $relPath ?>
<div style="width: 730px; background-color: #ffffff; margin: auto; padding: 10px; border: 1px solid #cacaca;">
<div style="border-bottom:1px solid black;">
<img src="<?php echo $relPath; ?>zc_install/includes/templates/template_default/images/logo.png" alt="ZenMagick" title=" ZenMagick " border="0" />
</div>
<h1>Hello. Thank you for using ZenMagick.</h1>
<h2>You are seeing this page for one or more reasons:</h2>
<ol>
<li>This is your <strong>first time using ZenMagick</strong> and you haven't yet completed the normal Installation procedure.<br />
If this is the case for you,
<?php if ($instPath) { ?>
<a href="<?php echo $instPath; ?>">Click here</a> to begin installation.
<?php } else { ?>
you will need to upload the "zc_install" folder using your FTP program, and then run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser (or reload this page to see a link to it).
<?php } ?>
<br /><br />
</li>
<li>Your <tt><strong>/includes/configure.php</strong></tt> and/or <tt><strong>/admin/includes/configure.php</strong></tt> file contains invalid <em>path information</em> and/or invalid <em>database-connection information</em>.<br />
If you recently edited your configure.php files for any reason, or maybe moved your site to a different folder or different server, then you'll need to review and update all your settings to the correct values for your server.<br />
See the <a href="http://forum.zenmagick.org/" target="_blank">forum</a> and/or <a href="http://wiki.zenmagick.org/" target="_blank">documentation</a> on the ZenMagick website for assistance.</li>
</ol>
<br />
<h2>To begin installation ...</h2>
<ol>
<?php if ($instPath) { ?>
<li>Run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser.</li>
<?php } else { ?>
<li>You will need to upload the "zc_install" folder using your FTP program, and then run <a href="<?php echo $instPath; ?>">zc_install/index.php</a> via your browser (or reload this page to see a link to it).</li>
<?php } ?>
<li>The <a href="http://forum.zenmagick.org/" target="_blank">forum</a> and<a href="http://wiki.zenmagick.org/" target="_blank">documentation</a>  area on the ZenMagick website will also be of value if you run into difficulties.</li>
</ol>

</div>
    <p style="text-align: center; font-size: small;">Copyright &copy; 2006-<?php echo date('Y'); ?> <a href="http://www.zenmagick.org/" target="_blank">ZenMagick</a></p>
</body></html>
