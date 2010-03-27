<ul>
  <?php
    $menu = array();
    $menu[] = array($request->url(FILENAME_DEFAULT), zm_l10n_get("Home"));
    $menu[] = array($request->url(FILENAME_LOGIN, '', true), zm_l10n_get("Login"));
    $menu[] = array($request->url(FILENAME_SHOPPING_CART, '', true), zm_l10n_get("Cart"));
     if ($request->isRegistered()) {
    $menu[] = array($request->url(FILENAME_ACCOUNT, '', true), zm_l10n_get("Account")); }
    $menu[] = array($request->url(FILENAME_CHECKOUT_SHIPPING, '', true), zm_l10n_get("Checkout"));
   $menu[] = array($request->url(FILENAME_LOGOFF, '', true), zm_l10n_get("Logoff"));
   
   
   

    /*if ($request->isAnonymous()) {
        $menu[] = array($request->url(FILENAME_LOGIN, '', true), zm_l10n_get("Login"));
    }
  /*  if (!$request->isAnonymous()) {
        $menu[] = array($request->url(FILENAME_LOGOFF, '', true), zm_l10n_get("Logoff"));
    }
   /* if (!$request->getShoppingCart()->isEmpty() && !$request->isCheckout()) {
        $menu[] = array($request->url(FILENAME_SHOPPING_CART, '', true), zm_l10n_get("Cart"));
        $menu[] = array($request->url(FILENAME_CHECKOUT_SHIPPING, '', true), zm_l10n_get("Checkout"));
    }

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
