<?php require '../../external.php'; ?>
<?php
  // set up tools just like a regular view...
  foreach ($request->getToolbox()->getTools() as $name => $tool) {
      $$name = $tool;
  }
?>
<html>
  <head>
    <title>External</title>
  </head>
  <body>
    <h2>Product</h2>
    <p>
      <?php
        $product = $this->container->get('productService')->getProductForId(1, 1);
        echo $product->getName();
      ?>
    </p>
    <h2>Shopping Cart</h2>
      <?php $shoppingCart = $request->getShoppingCart(); ?>
      <?php foreach ($shoppingCart->getItems() as $item) { ?>
        <p>
          <?php echo $item->getQuantity(); ?> x <a href="<?php echo $net->product($item->getId()) ?>"><?php echo $html->encode($item->getProduct()->getName()) ?></a>
          <span class="price"><?php echo $utils->formatMoney($request->getShoppingCart()->getTotal()) ?></span>
        </p>
      <?php } ?>
      <p>Total: <?php echo $shoppingCart->getTotal() ?></p>
    </p>
  </body>
</html>
