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
 */ $admin2->title() ?>

<?php /*
	customers_default_address_id 	int(11) 			No 	0 		Browse distinct values 	Change 	Drop 	Primary 	Unique 	Index 	Fulltext
	customers_password 	varchar(40) 	utf8_general_ci 		No 			Browse distinct values 	Change 	Drop 	Primary 	Unique 	Index 	Fulltext
	customers_authorization 	int(1) 			No 	0 		Browse distinct values 	Change 	Drop 	Primary 	Unique 	Index 	Fulltext

  TODO:
  - above
  - edit address
  - custom customer, customer_info fields
*/?>

<form action="" method="POST">
    <fieldset>
        <legend><?php _vzm("Account: %s", $account->getFullName()) ?></legend>
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td><?php _vzm("Title") ?></td>
                <td>
                    <input type="radio" id="male" name="gender" value="m"<?php $form->checked('m', $account->getGender()) ?>>
                    <label for="male"><?php _vzm("Mr.") ?></label>
                    <input type="radio" id="female" name="gender" value="f"<?php $form->checked('f', $account->getGender()) ?>>
                    <label for="female"><?php _vzm("Ms.") ?></label>
                </td>
            </tr>
            <tr>
                <td><?php _vzm("First Name") ?></td>
                <td><input type="text" name="firstName" value="<?php echo $html->encode($account->getFirstName()) ?>"></td>
            </tr>
            <tr>
                <td><?php _vzm("Last Name") ?></td>
                <td><input type="text" name="lastName" value="<?php echo $html->encode($account->getLastName()) ?>"></td>
            </tr>
            <tr>
                <td><?php _vzm("Date of Birth") ?></td>
                <td><input type="text" name="dob" value="<?php echo $locale->shortDate($account->getDob()) ?>" /> <?php echo sprintf(_zm("Format: %s;&nbsp;(e.g: %s)"), $locale->getFormat('date', 'short-ui-format'), $locale->getFormat('date', 'short-ui-example')) ?></td>
            </tr>
            <tr>
                <td><?php _vzm("E-Mail Address") ?></td>
                <td><input type="text" name="email" value="<?php echo $html->encode($account->getEmail()) ?>"></td>
            </tr>
            <tr>
                <td><?php _vzm("Nickname") ?></td>
                <td><input type="text" name="nickName" value="<?php echo $html->encode($account->getNickName()) ?>"></td>
            </tr>
            <tr>
                <td><?php _vzm("Telephone Number") ?></td>
                <td><input type="text" name="phone" value="<?php echo $html->encode($account->getPhone()) ?>"></td>
            </tr>
            <tr>
                <td><?php _vzm("Fax Number") ?></td>
                <td><input type="text" name="fax" value="<?php echo $html->encode($account->getFax()) ?>"></td>
            </tr>
             <tr>
                <td><?php _vzm("E-Mail Format") ?></td>
                <td>
                    <input type="radio" id="html" name="emailFormat" value="HTML"<?php $form->checked('HTML', $account->getEmailFormat(), 'HTML') ?>>
                    <label for="html"><?php _vzm("HTML") ?></label>
                    <input type="radio" id="text" name="emailFormat" value="TEXT"<?php $form->checked('TEXT', $account->getEmailFormat(), 'TEXT', true) ?>>
                    <label for="text"><?php _vzm("Text") ?></label>
                </td>
            </tr>

            <tr>
                <td><?php _vzm("Newsletter") ?></td>
                <td><input type="checkbox" name="newsletterSubscriber" value="true"<?php $form->checked(true, $account->isNewsletterSubscriber()) ?>/></td>
            </tr>

            <tr>
                <td><?php _vzm("Price Group") ?></td>
                <td><?php echo $form->idpSelect('priceGroupId', $priceGroups, $account->getPriceGroupId(), array('size'=>1)) ?></td>
            </tr>

            <tr>
                <td><?php _vzm("Referral") ?></td>
                <td><input type="text" name="fax" value="<?php echo $html->encode($account->getReferral()) ?>"></td>
            </tr>

            <tr>
                <td><?php _vzm("PayPal Payer Id") ?></td>
                <td><input type="text" name="payPalPayerId" value="<?php echo $html->encode($account->getPayPalPayerId()) ?>"></td>
            </tr>
            <tr>
                <td><?php _vzm("PayPal Express Checkout") ?></td>
                <td><input type="checkbox" name="payPalEc" value="true"<?php $form->checked(true, $account->isPayPalEc()) ?>/></td>
            </tr>
        </table>
    </fieldset>
</form>
