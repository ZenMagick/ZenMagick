<title><?php echo $this->fragment('page.title') ?></title>
<?php $this->fragment('page.title', $metaTags->getTitle()) ?>
<base href="<?php echo $net->absoluteUrl('', true) ?>" />
<meta charset="<?php echo $settingsService->get('zenmagick.http.html.charset') ?>" />
<meta name="generator" content="ZenMagick <?php echo $settingsService->get('zenmagick.version') ?>" />
<meta name="keywords" content="<?php echo $metaTags->getKeywords()?>" />
<meta name="description" content="<?php echo $metaTags->getDescription()?>" />
