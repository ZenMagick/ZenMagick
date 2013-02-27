<ul>
  <?php
    $menu = array();
    $menu[] = array($net->generate('index'), _zm("Home"));
    if ($session->isAnonymous()) {
        $menu[] = array($net->generate('login'), _zm("Login"));
    }
    if ($session->isRegistered()) {
        $menu[] = array($net->generate('account'), _zm("Account"));
    }
    if (!$session->isAnonymous()) {
        $menu[] = array($net->generate('logoff'), _zm("Logoff"));
    }
    if (!$container->get('shoppingCart')->isEmpty() && !$isCheckout) {
        $menu[] = array($net->generate('shopping_cart'), _zm("Cart"));
        $menu[] = array($net->generate('checkout_shipping'), _zm("Checkout"));
    }
    foreach ($container->get('ezPageService')->getPagesForHeader($session->getLanguageId()) as $page) {
        $menu[] = array($net->ezPage($page), $page, false);
    }
    foreach ($menu as $item) {

        $current = $utils->compareStoreUrl($item[0]) ? ' id="current"' : '';
        if (3 == count($item)) {
          // url, page, false
          $page = $item[1];
          ?><li<?php echo $current ?>><?php echo $html->ezpageLink($page->getId(), '<span>'.$html->encode($page->getTitle()).'</span>') ?></li><?php
        $menu[] = array($html->ezpageLink($page->getId(), '<span>'.$html->encode($page->getTitle()).'</span>', array()));
        } else {
          // url, title
          ?><li<?php echo $current ?>><a href="<?php echo $item[0] ?>"><span><?php echo $item[1] ?></span></a></li><?php
        }
    }
  ?>
</ul>
