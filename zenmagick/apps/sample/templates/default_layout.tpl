<!DOCTYPE html>
<html>
  <head>
    <title><?php _vzm('Sample App') ?></title>
    <?php $resources->cssFile('css/style.css') ?>
    <?php $resources->jsFile('js/validation.js') ?>
  </head>
  <body>
    <!-- load view -->
    <?php echo $this->fetch($viewTemplate) ?>
  </body>
</html
