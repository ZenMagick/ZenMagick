<ul>
  <?php
    $menu = array();
    $menu[] = array($net->url(FILENAME_DEFAULT), _zm("Home"));
    if ($request->isAnonymous()) {
        $menu[] = array($net->url(FILENAME_LOGIN, '', true), _zm("Login"));
    }
    if ($request->isRegistered()) {
        $menu[] = array($net->url(FILENAME_ACCOUNT, '', true), _zm("Account"));
    }
    if (!$request->isAnonymous()) {
        $menu[] = array($net->url(FILENAME_LOGOFF, '', true), _zm("Logoff"));
    }
    if (!$request->getShoppingCart()->isEmpty() && !$request->isCheckout()) {
        $menu[] = array($net->url(FILENAME_SHOPPING_CART, '', true), _zm("Cart"));
        $menu[] = array($net->url(FILENAME_CHECKOUT_SHIPPING, '', true), _zm("Checkout"));
    }
    /*
     layout does not degrade well if too many items
    foreach (ZMEZPages::instance()->getPagesForHeader($session->getLanguageId()) as $page) {
        $menu[] = array($net->ezPage($page), $page, false);
    }
     */
    foreach ($menu as $ii => $item) {
        $last = $ii == (count($items) - 1) ? ' class="last"' : '';
        if (3 == count($item)) {
          // url, page, false
          $page = $item[1];
          $current = ZMTools::compareStoreUrl($item[0]) ? ' id="current"' : '';
          ?><li<?php echo $last ?><?php echo $current ?>><?php echo $html->ezpageLink($page->getId(), '<span>'.$html->encode($page->getTitle()).'</span>') ?></li><?php 
        $menu[] = array($html->ezpageLink($page->getId(), '<span>'.$html->encode($page->getTitle()).'</span>', array()));
        } else {
          // url, title
          $current = ZMTools::compareStoreUrl($item[0]) ? ' id="current"' : '';
          ?><li<?php echo $last ?><?php echo $current ?>><a href="<?php echo $item[0] ?>"><span><?php echo $item[1] ?></span></a></li><?php
        }
    }
  ?>
</ul>	
