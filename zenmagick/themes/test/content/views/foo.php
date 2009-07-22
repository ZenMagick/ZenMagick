<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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

<?php $form->open('foo', '', true, array('id'=>'foo')) ?>
  <fieldset>
    <legend><?php zm_l10n("Foo") ?></legend>
    <div>
      <label for="foo"><?php zm_l10n("Foo") ?></label>
      <input type="text" id="foo" name="foo" value="<?php echo $formData->getFoo() ?>" /> 
    </div>
    <div>
      <label for="bar"><?php zm_l10n("Bar") ?></label>
      <input type="text" id="bar" name="bar" value="<?php echo $formData->getBar() ?>"  /> 
    </div>
    <div>
      <label for="doh"><?php zm_l10n("Bar") ?></label>
      <select id="doh" name="doh[]" size="3" multiple style="width:4em;"> 
        <option value="do">do</option>
        <option value="re">re</option>
        <option value="mi">mi</option>
        <option value="fa">fa</option>
      </select>
    </div>
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Submit") ?>" /></div>
</form>
