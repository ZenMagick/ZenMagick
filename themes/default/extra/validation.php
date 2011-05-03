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
?><?php

    /*
     * The central place for all form validation. The validations configured here
     * will be used by both the client (JavaScript) and the server (controller).
     *
     * Themes are free to replace single or all validations.
     *
     * The ruleset id must match the id attribute of the HTML form.
     */


    $validator = ZMValidator::instance();
    /* edit account */
    $validator->addRules('account', array(
        array('RequiredRule' ,'firstName', 'Please enter your first name.'),
        array('MaxFieldLengthRule' ,'firstName', TABLE_CUSTOMERS, 'customers_firstname'),
        array('RequiredRule' ,'lastName', 'Please enter your last name.'),
        array('MaxFieldLengthRule' ,'lastName', TABLE_CUSTOMERS, 'customers_lastname'),
        array('RequiredRule' ,'email', 'Please enter your email address.'),
        array('MaxFieldLengthRule' ,'email', TABLE_CUSTOMERS, 'customers_email_address'),
        array('EmailRule' ,'email', 'Please enter a valid email address.'),
        array('RequiredRule' ,'phone', "Please enter your telephone details."),
        array('MaxFieldLengthRule' ,'phone', TABLE_CUSTOMERS, 'customers_telephone'),
        array('MinRule' ,'phone', ENTRY_TELEPHONE_MIN_LENGTH, 'Your Telephone Number must contain a minimum of %2$s characters.')
    ));
    if (ZMSettings::get('isAccountGender')) {
        $validator->addRule('account', array('RequiredRule' ,'gender', 'Please choose a gender.'));
    }
    if (ZMSettings::get('isAccountDOB')) {
        $validator->addRule('account', array('RequiredRule' ,'dob', 'Please enter your date of birth.'));
        $validator->addRule('account', array('DateRule' ,'dob', null, 'Please enter a valid date of birth.'));
    }


    /* account password */
    $validator->addRules('account_password', array(
        array('RequiredRule' ,'password_current', 'Please enter you current password.'),
        array('RequiredRule' ,'password_new', 'Please enter the new password.'),
        array('MinRule' ,'password_new', ZMSettings::get('zenmagick.core.authentication.minPasswordLength'), 'Your password must contain a minimum of %2$s characters.'),
        array('RequiredRule' ,'password_confirmation', 'Please confirm the new password.'),
        array('FieldMatchRule' ,'password_new', 'password_confirmation', 'The new password and confirm password must match.')
    ));


    /* address */
    $validator->addRules('address', array(
        array('RequiredRule' ,'firstName', 'Please enter your first name.'),
        array('MaxFieldLengthRule' ,'firstName', TABLE_ADDRESS_BOOK, 'entry_firstname'),
        array('RequiredRule' ,'lastName', 'Please enter your last name.'),
        array('MaxFieldLengthRule' ,'lastName', TABLE_ADDRESS_BOOK, 'entry_lastname'),
        array('RequiredRule' ,'addressLine1', 'Please enter your address.'),
        array('MaxFieldLengthRule' ,'addressLine1', TABLE_ADDRESS_BOOK, 'entry_street_address'),
        array('RequiredRule' ,'city', 'Please enter a city.'),
        array('MaxFieldLengthRule' ,'city', TABLE_ADDRESS_BOOK, 'entry_city'),
        array('RequiredRule' ,'postcode', 'Please enter a postcode.'),
        array('MaxFieldLengthRule' ,'postcode', TABLE_ADDRESS_BOOK, 'entry_postcode'),
        array('RequiredRule' ,'countryId', 'Please select a country.')
    ));
    if (ZMSettings::get('isAccountGender')) {
        $validator->addRule('address', array('RequiredRule' ,'gender', 'Please choose a gender.'));
    }
    if (ZMSettings::get('isAccountState')) {
        $validator->addRule('address', array('StateOrZoneIdRule' ,'state,zoneId', 'Please enter a state.'));
        $validator->addRule('address', array('MaxFieldLengthRule' ,'state', TABLE_ADDRESS_BOOK, 'entry_state'));
    }

    /* use alias */
    $validator->addAlias('address', 'shippingAddress');
    $validator->addAlias('address', 'billingAddress');

    /* advanced search */
    $validator->addRules('searchCriteria', array(
        array('RequiredRule' ,'keywords', 'Search cannot be empty.')
    ));


    /* contact us */
    $validator->addRules('contactUs', array(
        array('RequiredRule' ,'name', 'Please enter your name.'),
        array('RequiredRule' ,'email', 'Please enter your email address.'),
        array('EmailRule' ,'email', 'Please enter a valid email address.'),
        array('RequiredRule' ,'message', 'Please enter your message.')
    ));


    /* create account */
    $validator->addRules('registration', array(
        array('RequiredRule' ,'firstName', 'Please enter your first name.'),
        array('MaxFieldLengthRule' ,'firstName', TABLE_CUSTOMERS, 'customers_firstname'),
        array('RequiredRule' ,'lastName', 'Please enter your last name.'),
        array('MaxFieldLengthRule' ,'lastName', TABLE_CUSTOMERS, 'customers_lastname'),
        array('RequiredRule' ,'email', 'Please enter your email address.'),
        array('EmailRule' ,'email', 'Please enter a valid email address.'),
        array('MaxFieldLengthRule' ,'email', TABLE_CUSTOMERS, 'customers_email_address'),
        array('UniqueEmailRule' ,'email', 'The entered email address is already in use.'),
        array('RequiredRule' ,'password', 'Please enter you password.'),
        array('MinRule' ,'password', ZMSettings::get('zenmagick.core.authentication.minPasswordLength'), 'Your password must contain a minimum of %2$s characters.'),
        array('RequiredRule' ,'confirmation', 'Please confirm the password.'),
        array('FieldMatchRule' ,'password', 'confirmation', 'The password and confirm password must match.'),
        array('RequiredRule' ,'addressLine1', 'Please enter your address.'),
        array('MaxFieldLengthRule' ,'addressLine1', TABLE_ADDRESS_BOOK, 'entry_street_address'),
        array('RequiredRule' ,'city', 'Please enter a city.'),
        array('MaxFieldLengthRule' ,'city', TABLE_ADDRESS_BOOK, 'entry_city'),
        array('RequiredRule' ,'postcode', 'Please enter a postcode.'),
        array('MaxFieldLengthRule' ,'postcode', TABLE_ADDRESS_BOOK, 'entry_postcode'),
        array('RequiredRule' ,'countryId', 'Please select a country.'),
        array('RequiredRule' ,'phone', "Please enter your telephone details."),
        array('MaxFieldLengthRule' ,'phone', TABLE_CUSTOMERS, 'customers_telephone')
    ));
    if (ZMSettings::get('isPrivacyMessage')) {
        $validator->addRule('registration', array('RequiredRule' ,'privacy', 'You must agree to the privacy policy.'));
    }
    if (ZMSettings::get('isAccountGender')) {
        $validator->addRule('registration', array('RequiredRule' ,'gender', 'Please choose a gender.'));
    }
    if (ZMSettings::get('isAccountDOB')) {
        $validator->addRule('registration', array('RequiredRule' ,'dob', 'Please enter your date of birth.'));
        $validator->addRule('registration', array('DateRule' ,'dob', null, 'Please enter a valid date of birth.'));
    }
    if (ZMSettings::get('isAccountState')) {
        $validator->addRule('registration', array('StateOrZoneIdRule' ,'state,zoneId', 'Please enter a valid state/province.'));
        $validator->addRule('registration', array('MaxFieldLengthRule' ,'state', TABLE_ADDRESS_BOOK, 'entry_state'));
    }


    /* login */
    $validator->addRules('login', array(
        array('RequiredRule', 'email_address', 'Please enter your email address.'),
        array('EmailRule', 'email_address', 'Please enter a valid email address.'),
        array('RequiredRule', 'password', "Please enter your password.")
    ));


    /* checkout_guest */
    $validator->addRules('checkout_guest', array(
        array('RequiredRule' ,'email_address', 'Please enter your email address.'),
        array('EmailRule' ,'email_address', 'Please enter a valid email address.')
    ));


    /* review */
    $validator->addRules('newReview', array(
        array('RequiredRule' ,'rating', 'Please choose a rating.'),
        array('RequiredRule' ,'text', 'Please enter your review.'),
        array('MinRule' ,'text', REVIEW_TEXT_MIN_LENGTH, 'A review needs to have at least %2$s characters.')
    ));


    /* tell a friend */
    $validator->addRules('tellAFriend', array(
        array('RequiredRule' ,'fromName', 'Please enter your name.'),
        array('RequiredRule' ,'fromEmail', 'Please enter your email address.'),
        array('EmailRule' ,'fromEmail', "Please enter a valid email address."),
        array('RequiredRule' ,'toName', "Please enter your friend's name."),
        array('RequiredRule' ,'toEmail', "Please enter your friend's email address."),
        array('EmailRule' ,'toEmail', "Please enter a valid friend's email address.")
    ));


    /* guest_history */
    $validator->addRules('guest_history', array(
        array('RequiredRule' ,'email', 'Please enter your email address.'),
        array('RequiredRule' ,'orderId', 'Please enter your order number.')
    ));


    /* gvReceiver */
    $validator->addRules('gvReceiver', array(
        array('RequiredRule' ,'name', 'Please enter a receiver name.'),
        array('RequiredRule' ,'email', 'Please enter the receivers email address.'),
        array('MaxFieldLengthRule' ,'email', TABLE_COUPON_EMAIL_TRACK, 'emailed_to'),
        array('EmailRule' ,'email', "Please enter a valid receiver email address."),
        array('RequiredRule' ,'amount', 'Please enter the amount.'),
        array('GVAmountRule' ,'amount')
    ));


    /* unsubscribe */
    $validator->addRules('unsubscribe', array(
        array('RequiredRule', 'email_address', 'Please enter your email address.'),
        array('EmailRule', 'email_address', 'Please enter a valid email address.')
    ));

?>
