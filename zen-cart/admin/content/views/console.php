<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * $Id: zmConsole.php 2647 2009-11-27 00:30:20Z dermanomann $
 */
?>
<?php

    if ('true' == $request->getParameter('remove', 'false')) {
        // destry myself
        unlink(DIR_FS_ADMIN.'zmConsole.php');
        zen_redirect(zen_href_link(FILENAME_DEFAULT));
    }

    $code = stripslashes($request->getParameter('code', '', false));
    $zm_result = null;
    if ('' != $code) {
        ob_start();
        eval($code);
        $zm_result = ob_get_contents();                                                                                       
        ob_end_clean();                                                                                                   
    }
    if ('' == $code) {
        $code = "\$product = ZMProducts::instance()->getProductForId(8, 1);\n"
               ."if (null != \$product) {\n"
               ."   echo \$product->getName().\":\\n\".\$product->getDescription();\n"
               ."   \n"
               ."} else {\n"
               ."   echo 'not found';\n"
               ."}\n";
    }

?>

<div id="b_console">
  <form action="<?php echo $toolbox->admin->url() ?>" method="POST">
      <fieldset>
          <legend><?php zm_l10n("<code>PHP</code> Console") ?></legend>
          <label for="code"><?php zm_l10n("Code:") ?></label>
          <textarea id="name" name="code" rows="10" cols="80"><?php echo $request->getToolbox()->html->encode($code) ?></textarea><br>
          <input type="submit" value="<?php zm_l10n("Execute") ?>">
          <?php if (null != $zm_result) { ?>
              <div id="console">
                  <?php echo str_replace("\n", "<br>", $request->getToolbox()->html->encode($zm_result)); ?>
              </div>
          <?php } ?>
      </fieldset>
  </form>
  <a href="<?php echo $toolbox->admin->url(null, 'remove=true') ?>" onclick="return zm_user_confirm('Remove console ?');"><?php zm_l10n("Remove Console from admin menu") ?></a>
</div>
