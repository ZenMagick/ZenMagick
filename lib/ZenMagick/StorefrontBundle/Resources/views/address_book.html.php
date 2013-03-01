<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
<?php $view->extend('StorefrontBundle::default_layout.html.php'); ?>

<?php $crumbtrail->addCrumb(_zm('Account'), $view['router']->generate('account'))->addCrumb(_zm('Addresses')) ?>
<?php foreach ($addressList as $address) { ?>
    <fieldset>
        <legend><?php echo $view->escape($address->getFirstName() . ' ' . $address->getLastName()) ?>
        <?php echo ($address->isPrimary() ? _vzm("(primary)") : ''); ?></legend>
        <div class="btn">
            <?php if (!$address->isPrimary()) { ?>
                <a class="btn" href="<?php echo $view['router']->generate('address_book_delete', array('id' => $address->getId())) ?>"><?php _vzm("Delete") ?></a>
            <?php } ?>
            <a class="btn" href="<?php echo $view['router']->generate('address_book_edit', array('id' => $address->getId())) ?>"><?php _vzm("Edit") ?></a>
        </div>
        <?php echo $macro->formatAddress($address) ?>
    </fieldset>
<?php } ?>
<div class="btn"><a href="<?php echo $view['router']->generate('address_book_add') ?>" class="btn"><?php _vzm("Add Address") ?></a></div>
