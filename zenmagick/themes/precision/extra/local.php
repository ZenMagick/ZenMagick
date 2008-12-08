<?php

  ZMLayout::instance()->setLeftColBoxes(array('categories.php', 'featured.php', 'information.php'));
  ZMLayout::instance()->setRightColBoxes(array('search.php', 'manufacturers.php', 'ezpages.php'));

  ZMSettings::set('isUseCategoryPage', false);
  ZMSettings::set('resultListProductFilter', '');

?>
