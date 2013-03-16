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
<?php $view['slots']->set('crumbtrail', $crumbtrail->addCrumb(_zm('Account'), $view['router']->generate('account'))->addCrumb(_zm('Edit Account'))); ?>
<?php echo $form->open('account_edit', '', true, array('id'=>'account')) ?>
    <fieldset>
        <legend><?php _vzm("My Account") ?></legend>
        <table cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                   <th id="label"></th>
                   <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($view['settings']->get('isAccountGender')) { ?>
                    <tr>
                        <td><?php _vzm("Title") ?><span>*</span></td>
                        <td>
                            <input type="radio" id="male" name="gender" value="m"<?php $form->checked('m', $account->getGender()) ?> />
                            <label for="male"><?php _vzm("Mr.") ?></label>
                            <input type="radio" id="female" name="gender" value="f"<?php $form->checked('f', $account->getGender()) ?> />
                            <label for="female"><?php _vzm("Ms.") ?></label>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php _vzm("First Name") ?><span>*</span></td>
                    <td><input type="text" name="firstName" value="<?php echo $view->escape($account->getFirstName()) ?>" /></td>
                </tr>
                <tr>
                    <td><?php _vzm("Last Name") ?><span>*</span></td>
                    <td><input type="text" name="lastName" value="<?php echo $view->escape($account->getLastName()) ?>" /></td>
                </tr>
                <?php if ($view['settings']->get('isAccountDOB')) { ?>
                    <tr>
                        <td><?php _vzm("Date of Birth") ?><span>*</span></td>
                        <td><input type="text" name="dob" value="<?php echo $view['date']->short($account->getDob()) ?>" /> <?php echo sprintf(_zm("Format: %s;&nbsp;(e.g: %s)"), $view['date']->getFormat('date', 'short-ui-format'), $view['date']->getFormat('date', 'short-ui-example')) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php _vzm("E-Mail Address") ?><span>*</span></td>
                    <td><input type="text" name="email" value="<?php echo $view->escape($account->getEmail()) ?>" /></td>
                </tr>
                <?php if ($view['settings']->get('isAccountNickname')) { ?>
                    <tr>
                        <td><?php _vzm("Nickname") ?></td>
                        <td><input type="text" name="nickName" value="<?php echo $view->escape($account->getNickName()) ?>" /></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php _vzm("Telephone Number") ?><span>*</span></td>
                    <td><input type="text" name="phone" value="<?php echo $view->escape($account->getPhone()) ?>" /></td>
                </tr>
                <tr>
                    <td><?php _vzm("Fax Number") ?></td>
                    <td><input type="text" name="fax" value="<?php echo $view->escape($account->getFax()) ?>" /></td>
                </tr>
                 <tr>
                    <td><?php _vzm("E-Mail Format") ?><span>*</span></td>
                    <td>
                        <input type="radio" id="html" name="emailFormat" value="HTML"<?php $form->checked('HTML', $account->getEmailFormat(), 'HTML') ?> />
                        <label for="html"><?php _vzm("HTML") ?></label>
                        <input type="radio" id="text" name="emailFormat" value="TEXT"<?php $form->checked('TEXT', $account->getEmailFormat(), 'TEXT', true) ?> />
                        <label for="text"><?php _vzm("Text") ?></label>
                    </td>
                </tr>
                <tr class="legend">
                    <td colspan="2"><?php _vzm("<span>*</span> Mandatory fields") ?></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Update") ?>" /></div>
</form>
