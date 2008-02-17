<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * $Id$
 */
?>
<?php 
/******************************************************************************
 *
 * In order to make this work for your local installation, 
 * please verify/change the following:
 *
 *   - the path to external.php in the top require(..) statement
 *
 *   - username and password for the account to be used (make sure the account
 *     has at least three orders in the order history, or change the code
 *     accordingly)
 *
 ******************************************************************************/

/** CHANGE TO VALID LOCATION **/
// include ZenMagick API
require("../zen-cart/zenmagick/external.php");

    // show messages
    function showMessages() {
    global $zm_messages;

        $zm_messages->_loadMessageStack();
        if ($zm_messages->hasMessages()) {
          echo '<ul id="messages">';
          foreach ($zm_messages->getMessages() as $message) {
              echo '<li class="'.$message->getType().'">'. $message->getText().'</li>';
          }
          echo '</ul>';
        }
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
  <head>
    <title>External ZenMagick Access</title>
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
  </head>
  <body>

    <div>
      <h1>Access Categories and Products</h1>
      <?php $category = $zm_categories->getCategoryForId(9); ?>
      <h2><?php echo $category->getName() ?></h2>
      <ul>
        <?php foreach ($zm_products->getProductsForCategoryId(9) as $product) { ?>
          <li><?php echo $product->getName() ?></li>
        <?php } ?>
      </ul>
    </div>

    <div>
      <h1>Login and query order history</h1>
      <?php 
        /** CHANGE TO VALID ACCOUNT **/
        $email = "some@email.com";
        $pwd = "xxxxxx";

        // fake request
        $_GET['main_page'] = 'login';

        // create login controller
        $controller = $zm_loader->create("LoginController");

        // fake login submit
        global $_POST;
        if (!isset($_POST)) {
            $_POST = array();
        }
        $_POST['email_address'] = $email;
        $_POST['password'] = $pwd;
        // execute
        $view = $controller->processPost();

        showMessages();

        echo "Login result: ".$view->getMappingId()."<br />";

        if ('account' == $view->getMappingId() || 'success' == $view->getMappingId()) {
            $account = $zm_request->getAccount();
            echo "<h3>Account Name: " . $account->getFullName()."</h3>";
            
            // get order history for order #3 using zen-cart bridge
            $_GET['main_page'] = 'account_history_info';
            $_GET['order_id'] = '3';

            zm_call_zc_page('account_history_info', 'order');
            showMessages();

            echo "zen-cart order Date: " . zen_date_long($order->info['date_purchased']) . "<br />";

            // get order history using ZenMagick controller
            $controller = $zm_loader->create("AccountHistoryInfoController");
            // execute; NOTE: reusing $_GET settings
            $view = $controller->processGet();
            $zm_order = $controller->getGlobal("zm_order");
            echo "ZenMagick order date: " . zm_date_short($zm_order->getOrderDate(), false);
        } else {
            echo '<h2>Could not login - did you edit this sample to provide valid login information?</h2>';
        }

        $zen_redirect_url = null;

        // clean cart if not empty
        if (!$zm_cart->isEmpty()) {
            echo "<h3>Cleaning up Shopping Cart ... </h3>";
            $_GET['action'] = 'update_product';
            $_POST['action'] = 'update_product';
            $_POST['main_page'] = 'shopping_cart';
            $_POST['products_id'] = array();
            $_POST['cart_quantity'] = array();
            foreach ($zm_cart->getItems() as $item) {
                $_POST['products_id'][] = $item->getId();
                $_POST['cart_quantity'][] = 0;
            }
            zm_call_zc_page('product_info');
        }

        // fake add to cart
        $_GET['action'] = 'add_product';
        $_POST['action'] = 'add_product';
        $_POST['products_id'] = $zm_request->getParameter('pid', 8);;
        $_POST['cart_quantity'] = 1;
        zm_call_zc_page('product_info');

        echo "<h3>Shopping Cart after add</h3>";
        foreach ($zm_cart->getItems() as $item) {
          ?><?php echo $item->getQty(); ?> x <a href="<?php zm_product_href($item->getId()) ?>"><?php echo $item->getName(); ?></a><br /><?php
        }
        echo $zen_redirect_url;

     ?>
    </div>

  </body>
</html>
