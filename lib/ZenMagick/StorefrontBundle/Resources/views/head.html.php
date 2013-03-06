<title><?php $view['slots']->output('title', $view->escape($metaTags->getTitle())) ?></title>
<base href="/" />
<meta charset="<?php echo $view->getCharset(); ?>" />
<meta name="generator" content="ZenMagick <?php echo AppKernel::APP_VERSION ?>" />
<meta name="keywords" content="<?php echo $view->escape($metaTags->getKeywords()) ?>" />
<meta name="description" content="<?php echo $view->escape($metaTags->getDescription()) ?>" />
