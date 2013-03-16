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
<?php $view['slots']->set('crumbtrail', $crumbtrail->addCrumb(_zm('Account'), $view['router']->generate('account'))->addCrumb(_zm('Create Account'))); ?>
<script type="text/javascript">
    var all_zones = new Array();
    <?php
        foreach ($view->container->get('countryService')->getCountries() as $country) {
            $zones = $view->container->get('countryService')->getZonesForCountryId($country->getId());
            if (0 < count($zones)) {
                echo 'all_zones['.$country->getId() . '] = new Array();';
                foreach ($zones as $zone) {
                    echo "all_zones[".$country->getId()."][".$zone->getId()."] = '" . $zone->getName() ."';";
                }
            }
        }
    ?>
</script>
<?php /*=== include to allow PHP execution in ZM context ==*/ ?>
<script type="text/javascript"><?php echo $this->render('StorefrontBundle::dynamicState.js.php') ?></script>

<?php echo $form->open('create_account', '', true, array('id'=>'registration')) ?>
    <?php if ($view['settings']->get('isPrivacyMessage')) { ?>
        <fieldset>
            <legend><?php _vzm("About Privacy") ?></legend>
            <p>
                <?php _vzm("Please acknowledge you agree with our privacy statement by ticking the following box.") ?></br>
                <?php $href = '<a href="' . $net->staticPage('privacy') . '">' . _zm("here") . '</a>'; ?>
                <?php _vzm("The privacy statement can be read %s.", $href) ?></p>
            <p><input type="checkbox" id="privacy" name="privacy" value="1" /><label for="privacy"><?php _vzm("I have read and agreed to your privacy statement.") ?></label></p>
        </fieldset>
    <?php } ?>
    <fieldset>
        <legend><?php _vzm("Create Account") ?></legend>
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
                            <input type="radio" id="male" name="gender" value="m"<?php $form->checked('m', $registration->getGender()) ?> />
                            <label for="male"><?php _vzm("Mr.") ?></label>
                            <input type="radio" id="female" name="gender" value="f"<?php $form->checked('f', $registration->getGender()) ?> />
                            <label for="female"><?php _vzm("Ms.") ?></label>
                        </td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php _vzm("First Name") ?><span>*</span></td>
                    <td><input type="text" name="firstName" value="<?php echo $view->escape($registration->getFirstName()) ?>" /></td>
                </tr>
                <tr>
                    <td><?php _vzm("Last Name") ?><span>*</span></td>
                    <td><input type="text" name="lastName" value="<?php echo $view->escape($registration->getLastName()) ?>" /></td>
                </tr>
                <?php if ($view['settings']->get('isAccountDOB')) { ?>
                    <tr>
                        <td><?php _vzm("Date of Birth") ?><span>*</span></td>
                        <td><input type="text" name="dob" value="<?php echo $view->escape($registration->getDob()) ?>" /> <?php echo sprintf(_zm("Format: %s;&nbsp;(e.g: %s)"), $view['date']->getFormat('date', 'short-ui-format'), $view['date']->getFormat('date', 'short-ui-example')) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php _vzm("E-Mail Address") ?><span>*</span></td>
                    <td><input type="text" name="email" value="<?php echo $view->escape($registration->getEmail()) ?>" /></td>
                </tr>
                <?php if ($view['settings']->get('isAccountNickname')) { ?>
                    <tr>
                        <td><?php _vzm("Nickname") ?></td>
                        <td><input type="text" name="nickName" value="<?php echo $view->escape($registration->getNickName()) ?>" /></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td><?php _vzm("Password") ?><span>*</span></td>
                    <td><input type="password" name="password" value="" /></td>
                </tr>
                <tr>
                    <td><?php _vzm("Confirm Password") ?><span>*</span></td>
                    <td><input type="password" name="confirmation" value="" /></td>
                </tr>
                <?php if ($view['settings']->get('isAccountCompany')) { ?>
                    <tr>
                        <td><?php _vzm("Company Name") ?></td>
                        <td><input type="text" name="companyName" value="<?php echo $view->escape($registration->getCompanyName()) ?>" /></td>
                    </tr>
                <?php } ?>

                <tr>
                    <td><?php _vzm("Street Address") ?><span>*</span></td>
                    <td><input type="text" name="addressLine1" value="<?php echo $view->escape($registration->getAddressLine1()) ?>" <?php echo $form->fieldLength('address_book', 'entry_street_address') ?> /></td>
                </tr>
                <tr>
                    <td><?php _vzm("Suburb") ?></td>
                    <td><input type="text" name="suburb" value="<?php echo $view->escape($registration->getSuburb()) ?>" <?php echo $form->fieldLength('address_book', 'entry_suburb') ?> /></td>
                </tr>
                <tr>
                    <td><?php _vzm("City") ?><span>*</span></td>
                    <td><input type="text" name="city" value="<?php echo $view->escape($registration->getCity()) ?>" <?php echo $form->fieldLength('address_book', 'entry_city') ?> /></td>
                </tr>
                <?php
                    $countryId = $registration->getCountryId();
                    $countryId = 0 != $countryId ? $countryId : $view['settings']->get('storeCountry');
                ?>
                <tr>
                    <td><?php _vzm("Post Code") ?><span>*</span></td>
                    <td><input type="text" name="postcode" value="<?php echo $view->escape($registration->getPostcode()) ?>" <?php echo $form->fieldLength('address_book', 'entry_postcode') ?> /></td>
                </tr>
                 <tr>
                    <td><?php _vzm("Country") ?><span>*</span></td>
                    <td><?php echo $form->idpSelect('countryId', $view->container->get('countryService')->getCountries(), $countryId) ?></td>
                </tr>
                <?php if ($view['settings']->get('isAccountState')) { ?>
                    <?php $zones = $view->container->get('countryService')->getZonesForCountryId($countryId); ?>
                    <tr>
                        <td><?php _vzm("State/Province") ?><span>*</span></td>
                        <td>
                            <?php if (0 < count($zones)) { ?>
                                <?php echo $form->idpSelect('zoneId', $zones, $registration->getZoneId()) ?>
                            <?php } else { ?>
                                <input type="text" name="state" value="<?php echo $view->escape($registration->getState()) ?>" />
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>

                <tr>
                    <td><?php _vzm("Telephone Number") ?><span>*</span></td>
                    <td><input type="text" name="phone" value="<?php echo $view->escape($registration->getPhone()) ?>" /></td>
                </tr>
                <tr>
                    <td><?php _vzm("Fax Number") ?></td>
                    <td><input type="text" name="fax" value="<?php echo $view->escape($registration->getFax()) ?>" /></td>
                </tr>

                 <tr>
                    <td><?php _vzm("E-Mail Format") ?><span>*</span></td>
                    <td>
                        <input type="radio" id="html" name="emailFormat" value="HTML"<?php $form->checked('HTML', $registration->getEmailFormat(), 'HTML') ?> />
                        <label for="html"><?php _vzm("HTML") ?></label>
                        <input type="radio" id="text" name="emailFormat" value="TEXT"<?php $form->checked('TEXT', $registration->getEmailFormat(), 'TEXT', true) ?> />
                        <label for="text"><?php _vzm("Text") ?></label>
                    </td>
                </tr>
                <?php if ($view['settings']->get('isAccountNewsletter')) { ?>
                    <tr>
                        <td></td>
                        <td><input type="checkbox" id="newsletterSubscriber" name="newsletterSubscriber" value="1"<?php $form->checked($registration->isNewsletterSubscriber()) ?> /><label for="newsletterSubscriber"><?php _vzm("Receive Store Newsletter") ?></label></td>
                    </tr>
                <?php } ?>

                <?php if ($view['settings']->get('isAccountReferral')) { ?>
                    <tr>
                        <td><?php _vzm("Referral Code") ?><span>*</span></td>
                        <td><input type="text" name="referral" value="" /></td>
                    </tr>
                <?php } ?>

                <tr class="legend">
                    <td colspan="2"><?php _vzm("<span>*</span> Mandatory fields") ?></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Create Account") ?>" /></div>
</form>
