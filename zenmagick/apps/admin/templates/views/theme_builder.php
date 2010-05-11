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
 * $Id: zmThemeBuilder.php 2647 2009-11-27 00:30:20Z dermanomann $
 */
?>
<?php

    if ('POST' == $request->getMethod()) {
        $name = $request->getParameter('name');
        $inherit = $request->getParameter('inherit', false);
        $switchto = $request->getParameter('switchto', false);

        $themeBuilder = new ZMThemeBuilder();
        $themeBuilder->setName($request->getParameter('name'));
        $themeBuilder->setInheritDefaults($request->getParameter('inherit', false));
        $buildOK = $themeBuilder->build();
        foreach ($themeBuilder->getMessages() as $msgInfo) {
            ZMMessages::instance()->add($msgInfo[1], $msgInfo[0]);
        }

        if ($switchto && $buildOK) {
            // create dummy files
            $dummyPatch = new ZMThemeDummyPatch();
            $dummyPatch->patch(true);
            ZMMessages::instance()->msg(zm_l10n_get('Created zen-cart template dummy files for "%s".', $name));

            // select new theme
            // XXX: TODO: fix lanugageId
            ZMThemes::instance()->updateZCThemeId($name);
            ZMMessages::instance()->msg(zm_l10n_get('New theme "%s" selected as active zen-cart template.', $name));
        }

    }

?>

<h2><?php zm_l10n("ZenMagick Theme Builder") ?></h2>

<form action="<?php echo $toolbox->admin->url() ?>" method="POST" onsubmit="return zenmagick.confirm('Create theme?', this);">
  <fieldset>
  <legend><?php zm_l10n("Create new ZenMagick Theme") ?></legend>

      <label for="name">Name</label>
      <input type="text" id="name" name="name" value="">
      (This is what the folder will be named. <strong>Names are case sensitive!</strong>)
      <br>

      <input type="checkbox" id="inherit" name="inherit" value="1" checked>
      <label for="inherit">Inherit theme defaults</label>
      (Recommended, unless <strong>all files are copied</strong>)
      <br>

      <input type="checkbox" id="switchto" name="switchto" value="1" checked>
      <label for="switchto">Switch to the new theme when created</label>
      <br>

      <div class="submit"><input type="submit" value="<?php zm_l10n("Create") ?>"></div>
  </fieldset>
</form>

<p>Once you have created the new theme, make sure to (re-)generate the required dummy theme files for zen-cart
using the <a href="<?php echo $admin->url('installation') ?>">installation</a> screen.</p>

<p>Unused directories can safely be deleted</p>

<p><strong>It is not recommended to use whitespace in the name. You can always edit the generated files to adjust the description.</strong></p>
