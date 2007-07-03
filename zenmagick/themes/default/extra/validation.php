<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
?><?php

    /*
     * The central place for all form validation. The validations configured here
     * will be used by both the client (JavaScript) and the server (controller).
     *
     * Themes are free to replace single or all validations.
     *
     * The name passed into the ruleset must match the id attribute of the HTML form.
     */


    /* edit account */
    $zm_validator->addRuleSet(new ZMRuleSet('edit_account', array(
        new ZMRequiredRule('firstname', 'Please enter your first name.'),
        new ZMRequiredRule('lastname', 'Please enter your last name.'),
        new ZMRequiredRule('email_address', 'Please enter your email address.'),
        new ZMEmailRule('email_address', 'Please enter a valid email address.'),
        new ZMRequiredRule('telephone', "Please enter your telephone details."),
        new ZMMinRule('telephone', ENTRY_TELEPHONE_MIN_LENGTH, 'Your Telephone Number must contain a minimum of %2$s characters.')
    )));
    if (zm_setting('isAccountGender')) {
        $zm_validator->addRule('edit_account', new ZMRequiredRule('gender', 'Please choose a gender.'));
    }
    if (zm_setting('isAccountDOB')) {
        $zm_validator->addRule('edit_account', new ZMRequiredRule('dob', 'Please enter your date of birth.'));
        $zm_validator->addRule('edit_account', new ZMDateRule('dob', null, 'Please enter a valid date of birth.'));
    }


    /* account password */
    $zm_validator->addRuleSet(new ZMRuleSet('account_password', array(
        new ZMRequiredRule('password_current', 'Please enter you current password.'),
        new ZMRequiredRule('password_new', 'Please enter the new password.'),
        new ZMMinRule('password_new', zm_setting('minPasswordLength'), 'Your password must contain a minimum of %2$s characters.'),
        new ZMRequiredRule('password_confirmation', 'Please confirm the new password.'),
        new ZMFieldMatchRule('password_new', 'password_confirmation', 'The new password and confirm password must match.')
    )));


    /* address */
    $zm_validator->addRuleSet(new ZMRuleSet('address', array(
        new ZMRequiredRule('firstname', 'Please enter your first name.'),
        new ZMRequiredRule('lastname', 'Please enter your last name.'),
        new ZMRequiredRule('street_address', 'Please enter your address.'),
        new ZMRequiredRule('city', 'Please enter a city.'),
        new ZMRequiredRule('postcode', 'Please enter a postcode.'),
        new ZMRequiredRule('zone_country_id', 'Please select a country.')
    )));
    if (zm_setting('isAccountGender')) {
        $zm_validator->addRule('address', new ZMRequiredRule('gender', 'Please choose a gender.'));
    }
    if (zm_setting('isAccountState')) {
        $zm_validator->addRule('address', new ZMRequiredRule('state', 'Please enter a state.'));
    }


    /* advanced search */
    $zm_validator->addRuleSet(new ZMRuleSet('advanced_search', array(
        new ZMRequiredRule('keyword', 'Search cannot be empty.')
    )));


    /* contact us */
    $zm_validator->addRuleSet(new ZMRuleSet('contact_us', array(
        new ZMRequiredRule('contactname', 'Please enter your name.'),
        new ZMRequiredRule('email', 'Please enter your email address.'),
        new ZMEmailRule('email', 'Please enter a valid email address.'),
        new ZMRequiredRule('enquiry', 'Please enter your message.')
    )));


    /* create account */
    $zm_validator->addRuleSet(new ZMRuleSet('create_account', array(
        new ZMRequiredRule('firstname', 'Please enter your first name.'),
        new ZMRequiredRule('lastname', 'Please enter your last name.'),
        new ZMRequiredRule('email_address', 'Please enter your email address.'),
        new ZMEmailRule('email_address', 'Please enter a valid email address.'),
        new ZMRequiredRule('street_address', 'Please enter your address.'),
        new ZMRequiredRule('city', 'Please enter a city.'),
        new ZMRequiredRule('postcode', 'Please enter a postcode.'),
        new ZMRequiredRule('zone_country_id', 'Please select a country.'),
        new ZMRequiredRule('telephone', "Please enter your telephone details.")
    )));
    if (zm_setting('isPrivacyMessage')) {
        $zm_validator->addRule('create_account', new ZMRequiredRule('privacy_conditions', 'You must agree to the privacy policy.'));
    }
    if (zm_setting('isAccountGender')) {
        $zm_validator->addRule('create_account', new ZMRequiredRule('gender', 'Please choose a gender.'));
    }
    if (zm_setting('isAccountDOB')) {
        $zm_validator->addRule('create_account', new ZMRequiredRule('dob', 'Please enter your date of birth.'));
        $zm_validator->addRule('create_account', new ZMDateRule('dob', null, 'Please enter a valid date of birth.'));
    }
    if (zm_setting('isAccountState')) {
        $zm_validator->addRule('create_account', new ZMRequiredRule('state', 'Please enter a state.'));
    }


    /* login */
    $zm_validator->addRuleSet(new ZMRuleSet('login', array(
        new ZMRequiredRule('email_address', 'Please enter your email address.'),
        new ZMEmailRule('email_address', 'Please enter a valid email address.'),
        new ZMRequiredRule('password', "Please enter your password.")
    )));


    /* review */
    $zm_validator->addRuleSet(new ZMRuleSet('review', array(
        new ZMRequiredRule('rating', 'Please choose a rating.'),
        new ZMMinRule('review_text', REVIEW_TEXT_MIN_LENGTH, 'A review needs to have at least %2$s characters.')
    )));


    /* tell a friend */
    $zm_validator->addRuleSet(new ZMRuleSet('tell_a_friend', array(
        new ZMRequiredRule('from_name', 'Please enter your name.'),
        new ZMRequiredRule('from_email_address', 'Please enter your email address.'),
        new ZMEmailRule('from_email_address', "Please enter a valid email address."),
        new ZMRequiredRule('to_name', "Please enter your friend's name."),
        new ZMRequiredRule('to_email_address', "Please enter your friend's email address."),
        new ZMEmailRule('to_email_address', "Please enter a valid friend's email address.")
    )));

?>
