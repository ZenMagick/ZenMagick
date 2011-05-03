<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 */
?>

<?php $manufacturers = ZMManufacturers::instance()->getManufacturers($session->getLanguageId()); ?>
<?php if (0 < count($manufacturers)) { ?>
    <h3><?php _vzm("Manufacturers") ?></h3>
    <div id="sb_manufacturers" class="box">
        <?php echo $form->open('manufacturer', '', $request->isSecure(), array('method' => 'get', 'onsubmit'=>null)) ?>
            <div>
                <?php echo $form->idpSelect('manufacturers_id', array_merge(array(ZMLoader::make("ZMIdNamePair", "", _zm("Please Select"))), $manufacturers), $request->getManufacturerId(), array('size'=>1, 'onchange'=>'this.form.submit()')) ?>
                <noscript>
                    <div><input type="submit" class="btn" value="<?php _vzm('Go') ?>" /></div>
                </noscript>
            </div>
        </form>
    </div>
<?php } ?>
