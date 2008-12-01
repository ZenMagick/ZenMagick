<?php

  ZMLayout::instance()->setLeftColBoxes(array('categories.php', 'information.php'));
  ZMLayout::instance()->setRightColBoxes(array('search.php', 'manufacturers.php', 'banner_box.php'));

  ZMSettings::set('isUseCategoryPage', false);
  ZMSettings::set('resultListProductFilter', '');

?>
