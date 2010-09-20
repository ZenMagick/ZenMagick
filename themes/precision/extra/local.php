<?php

  ZMTemplateManager::instance()->setLeftColBoxes(array('categories.php', 'featured.php', 'information.php'));
  ZMTemplateManager::instance()->setRightColBoxes(array('search.php', 'manufacturers.php', 'ezpages.php'));

  ZMSettings::set('isUseCategoryPage', false);
  ZMSettings::set('resultListProductFilter', '');

?>
