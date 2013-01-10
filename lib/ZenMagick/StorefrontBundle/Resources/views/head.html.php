<title><?php echo $this->fragment('page.title') ?></title>
<?php $this->fragment('page.title', $metaTags->getTitle()) ?>
<base href="/" />
<meta charset="<?php echo $settingsService->get('zenmagick.http.html.charset') ?>" />
<meta name="generator" content="ZenMagick <?php echo AppKernel::APP_VERSION ?>" />
<meta name="keywords" content="<?php echo $metaTags->getKeywords()?>" />
<meta name="description" content="<?php echo $metaTags->getDescription()?>" />
