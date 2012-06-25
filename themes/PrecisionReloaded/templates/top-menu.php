<ul>
  <?php
    $menu = array();
    $menu[] = array($net->url('index'), _zm("Home"));
    $menu[] = array($net->url('login', '', true), _zm("Login"));
    $menu[] = array($net->url('shopping_cart', '', true), _zm("Cart"));
     if ($request->isRegistered()) {
    $menu[] = array($net->url('account', '', true), _zm("Account")); }
    $menu[] = array($net->url('checkout_shipping', '', true), _zm("Checkout"));
   $menu[] = array($net->url('logoff', '', true), _zm("Logoff"));




    /*if ($request->isAnonymous()) {
        $menu[] = array($net->url('login', '', true), _zm("Login"));
    }
  /*  if (!$request->isAnonymous()) {
        $menu[] = array($net->url('logoff', '', true), _zm("Logoff"));
    }
   /* if (!$request->getShoppingCart()->isEmpty() && !$isCheckout) {
        $menu[] = array($net->url('shopping_cart', '', true), _zm("Cart"));
        $menu[] = array($net->url('checkout_shipping', '', true), _zm("Checkout"));
    }

     layout does not degrade well if too many items
    foreach ($container->get('ezPageService')->getPagesForHeader($session->getLanguageId()) as $page) {
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
