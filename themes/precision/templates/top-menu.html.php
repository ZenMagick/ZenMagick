<ul>
  <?php
    $menu = array();
    $menu[] = array($net->url('index'), _zm("Home"));
    if ($session->isAnonymous()) {
        $menu[] = array($net->url('login', '', true), _zm("Login"));
    }
    if ($session->isRegistered()) {
        $menu[] = array($net->url('account', '', true), _zm("Account"));
    }
    if (!$session->isAnonymous()) {
        $menu[] = array($net->url('logoff', '', true), _zm("Logoff"));
    }
    if (!$container->get('shoppingCart')->isEmpty() && !$isCheckout) {
        $menu[] = array($net->url('shopping_cart', '', true), _zm("Cart"));
        $menu[] = array($net->url('checkout_shipping', '', true), _zm("Checkout"));
    }
    /*
     layout does not degrade well if too many items
    foreach ($container->get('ezPageService')->getPagesForHeader($session->getLanguageId()) as $page) {
        $menu[] = array($net->ezPage($page), $page, false);
    }
     */
    foreach ($menu as $ii => $item) {
        $last = $ii == (count($menu) - 1) ? ' class="last"' : '';
        $current = $utils->compareStoreUrl($item[0]) ? ' id="current"' : '';
        if (3 == count($item)) {
          // url, page, false
          $page = $item[1];
          ?><li<?php echo $last ?><?php echo $current ?>><?php echo $html->ezpageLink($page->getId(), '<span>'.$html->encode($page->getTitle()).'</span>') ?></li><?php
        $menu[] = array($html->ezpageLink($page->getId(), '<span>'.$html->encode($page->getTitle()).'</span>', array()));
        } else {
          // url, title
          ?><li<?php echo $last ?><?php echo $current ?>><a href="<?php echo $item[0] ?>"><span><?php echo $item[1] ?></span></a></li><?php
        }
    }
  ?>
</ul>
