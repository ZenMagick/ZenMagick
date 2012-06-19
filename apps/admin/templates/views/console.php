<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 */

    $code = stripslashes($request->request->get('code', '', false));

    $zm_result = null;
    if ('' != $code) {
        $code = '$container = zenmagick\base\Runtime::getContainer();'."\n".$code;
        ob_start();
        eval($code);
        $zm_result = ob_get_contents();
        ob_end_clean();
    }
    if ('' == $code) {
        $code = "\$product = \$container->get('productService')->getProductForId(8, 1);\n"
               ."if (null != \$product) {\n"
               ."   echo \$product->getName().\":\\n\".\$product->getDescription();\n"
               ."   \n"
               ."} else {\n"
               ."   echo 'not found';\n"
               ."}\n";
    }

?>

<?php $admin->title() ?>
<div id="b_console">
  <form action="<?php echo $admin->url() ?>" method="POST">
      <fieldset>
          <legend><?php _vzm("<code>PHP</code> Console") ?></legend>
          <label for="code"><?php _vzm("Code:") ?></label>
          <textarea id="name" name="code" rows="10" cols="80"><?php echo $html->encode($code) ?></textarea><br>
          <input class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm("Execute") ?>">
          <?php if (null != $zm_result) { ?>
              <div id="console">
                  <?php echo str_replace("\n", "<br>", $html->encode($zm_result)); ?>
              </div>
          <?php } ?>
      </fieldset>
  </form>
</div>
