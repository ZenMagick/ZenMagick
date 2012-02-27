<!DOCTYPE html>
<html>
  <head>
    <title><?php _vzm('Sample App') ?></title>
    <meta charset="<?php echo $settingsService->get('zenmagick.http.html.charset') ?>" />
    <?php $resources->cssFile('css/style.css') ?>
    <?php $resources->jsFile('js/validation.js') ?>
  </head>
  <body>
    <!-- load view -->
    <?php echo $this->fetch($viewTemplate) ?>
  </body>
</html>
