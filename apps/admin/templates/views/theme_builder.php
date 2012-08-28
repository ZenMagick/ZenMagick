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

    if ('POST' == $request->getMethod()) {
        $name = $request->request->get('name');
        $themeBuilder = $container->get('themeBuilder');
        $themeBuilder->setName($request->request->get('name'));
        $buildOK = $themeBuilder->build();
        foreach ($themeBuilder->getMessages() as $msgInfo) {
            $messageService->add($msgInfo[1], $msgInfo[0]);
        }
    }

?>

<?php $admin->title(_zm('Theme Builder')) ?></h1>

<form action="<?php echo $net->url() ?>" method="POST" onsubmit="return ZenMagick.confirm('_vzm('Create theme?')', this);">
  <fieldset>
  <legend><?php _vzm("Create new ZenMagick Theme") ?></legend>

  <label for="name"><?php _vzm('Name') ?></label>
      <input type="text" id="name" name="name" value="">
      <?php _vzm('(This is what the folder will be named. <strong>Names are case sensitive!</strong>)') ?>
      <br>

      <div class="submit"><input class="<?php echo $buttonClasses ?>" type="submit" value="<?php _vzm("Create") ?>"></div>
  </fieldset>
</form>

<?php $link = '<a href="'.$net->url('installation').'">'._zm('installation').'</a>'; ?>
<p><?php echo sprintf(_zm('Once you have created the new theme, make sure to (re-)generate the required dummy theme files for zen-cart
  using the %s screen.'), $link) ?></p>

<p><?php _vzm('Unused directories can safely be deleted.') ?></p>

<p><strong><?php _vzm('It is not recommended to use whitespace in the name. You can always edit the generated files to adjust the description.') ?></strong></p>
