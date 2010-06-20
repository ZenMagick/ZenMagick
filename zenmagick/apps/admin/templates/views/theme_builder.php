<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
            ZMMessages::instance()->msg(sprintf(_zm('Created zen-cart template dummy files for "%s".'), $name));

            // select new theme
            // XXX: TODO: fix lanugageId
            ZMThemes::instance()->updateZCThemeId($name);
            ZMMessages::instance()->msg(sprintf(_zm('New theme "%s" selected as active zen-cart template.'), $name));
        }

    }

?>

<h2><?php _vzm("ZenMagick Theme Builder") ?></h2>

<form action="<?php echo $admin2->url() ?>" method="POST" onsubmit="return zenmagick.confirm('_vzm('Create theme?')', this);">
  <fieldset>
  <legend><?php _vzm("Create new ZenMagick Theme") ?></legend>

  <label for="name"><?php _vzm('Name') ?></label>
      <input type="text" id="name" name="name" value="">
      <?php _vzm('(This is what the folder will be named. <strong>Names are case sensitive!</strong>)') ?>
      <br>

      <input type="checkbox" id="inherit" name="inherit" value="1" checked>
      <label for="inherit"><?php _vzm('Inherit theme defaults') ?></label>
      <?php _vzm('(Recommended, unless <strong>all files are copied</strong>)') ?>
      <br>

      <input type="checkbox" id="switchto" name="switchto" value="1" checked>
      <label for="switchto"><?php _vzm('Switch to the new theme when created') ?></label>
      <br>

      <div class="submit"><input type="submit" value="<?php _vzm("Create") ?>"></div>
  </fieldset>
</form>

<?php $link = '<a href="'.$admin2->url('installation').'">'._zm('installation').'</a>'; ?>
<p><?php echo sprintf(_zm('Once you have created the new theme, make sure to (re-)generate the required dummy theme files for zen-cart
  using the %s screen.', $link)) ?></p>

<p><?php _vzm('Unused directories can safely be deleted.') ?></p>

<p><strong><?php _vzm('It is not recommended to use whitespace in the name. You can always edit the generated files to adjust the description.') ?></strong></p>
