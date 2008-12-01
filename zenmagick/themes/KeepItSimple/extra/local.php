<?php

  ZMLayout::instance()->setRightColBoxes(array('categories.php', 'manufacturers.php', 'information.php', 'banner_box.php'));
  if ('index' == ZMRequest::getPageName()) {
      ZMLayout::instance()->setLeftColBoxes(array('featured.php', 'reviews.php'));
  } else {
      ZMLayout::instance()->setLeftColEnabled(false);
      if (ZMRequest::isCheckout(false)) {
          ZMLayout::instance()->setRightColBoxes(array('information.php'));
      }
  }

  ZMSettings::set('isUseCategoryPage', false);
  ZMSettings::set('resultListProductFilter', '');
  ZMSettings::set('defaultResultListPagination', 6);

?>
