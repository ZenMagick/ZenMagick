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

    /*
     * The central place for all form validation. The validations configured here
     * will be used by both the client (JavaScript) and the server (controller).
     *
     * Themes are free to replace single or all validations.
     *
     * The ruleset id must match the id attribute of the HTML form.
     */


    $validator = $this->container->get('validator');
    $settingsService = $this->container->get('settingsService');

    /* edit account */
    $validator->addRules('account', array(
        array('ZMRequiredRule' ,'firstName', 'Please enter your first name.'),
        array('ZMMaxFieldLengthRule' ,'firstName', 'customers', 'customers_firstname'),
        array('ZMRequiredRule' ,'lastName', 'Please enter your last name.'),
        array('ZMMaxFieldLengthRule' ,'lastName', 'customers', 'customers_lastname'),
        array('ZMRequiredRule' ,'email', 'Please enter your email address.'),
        array('ZMMaxFieldLengthRule' ,'email', 'customers', 'customers_email_address'),
        array('ZMEmailRule' ,'email', 'Please enter a valid email address.'),
        array('ZMRequiredRule' ,'phone', "Please enter your telephone details."),
        array('ZMMaxFieldLengthRule' ,'phone', 'customers', 'customers_telephone'),
        array('ZMMinRule' ,'phone', ENTRY_TELEPHONE_MIN_LENGTH, 'Your Telephone Number must contain a minimum of %2$s characters.')
    ));
    if ($settingsService->get('isAccountGender')) {
        $validator->addRule('account', array('ZMRequiredRule' ,'gender', 'Please choose a gender.'));
    }
    if ($settingsService->get('isAccountDOB')) {
        $validator->addRule('account', array('ZMRequiredRule' ,'dob', 'Please enter your date of birth.'));
        $validator->addRule('account', array('ZMDateRule' ,'dob', null, 'Please enter a valid date of birth.'));
    }


    /* account password */
    $validator->addRules('account_password', array(
        array('ZMRequiredRule' ,'password_current', 'Please enter you current password.'),
        array('ZMRequiredRule' ,'password_new', 'Please enter the new password.'),
        array('ZMMinRule' ,'password_new', $settingsService->get('zenmagick.base.authentication.minPasswordLength'), 'Your password must contain a minimum of %2$s characters.'),
        array('ZMRequiredRule' ,'password_confirmation', 'Please confirm the new password.'),
        array('ZMFieldMatchRule' ,'password_new', 'password_confirmation', 'The new password and confirm password must match.')
    ));


    /* address */
    $validator->addRules('address', array(
        array('ZMRequiredRule' ,'firstName', 'Please enter your first name.'),
        array('ZMMaxFieldLengthRule' ,'firstName', 'address_book', 'entry_firstname'),
        array('ZMRequiredRule' ,'lastName', 'Please enter your last name.'),
        array('ZMMaxFieldLengthRule' ,'lastName', 'address_book', 'entry_lastname'),
        array('ZMRequiredRule' ,'addressLine1', 'Please enter your address.'),
        array('ZMMaxFieldLengthRule' ,'addressLine1', 'address_book', 'entry_street_address'),
        array('ZMRequiredRule' ,'city', 'Please enter a city.'),
        array('ZMMaxFieldLengthRule' ,'city', 'address_book', 'entry_city'),
        array('ZMRequiredRule' ,'postcode', 'Please enter a postcode.'),
        array('ZMMaxFieldLengthRule' ,'postcode', 'address_book', 'entry_postcode'),
        array('ZMRequiredRule' ,'countryId', 'Please select a country.')
    ));
    if ($settingsService->get('isAccountGender')) {
        $validator->addRule('address', array('ZMRequiredRule' ,'gender', 'Please choose a gender.'));
    }
    if ($settingsService->get('isAccountState')) {
        $validator->addRule('address', array('ZMStateOrZoneIdRule' ,'state,zoneId', 'Please enter a state.'));
        $validator->addRule('address', array('ZMMaxFieldLengthRule' ,'state', 'address_book', 'entry_state'));
    }

    /* use alias */
    $validator->addAlias('address', 'shippingAddress');
    $validator->addAlias('address', 'billingAddress');

    /* advanced search */
    $validator->addRules('searchCriteria', array(
        array('ZMRequiredRule' ,'keywords', 'Search cannot be empty.')
    ));


    /* contact us */
    $validator->addRules('contactUs', array(
        array('ZMRequiredRule' ,'name', 'Please enter your name.'),
        array('ZMRequiredRule' ,'email', 'Please enter your email address.'),
        array('ZMEmailRule' ,'email', 'Please enter a valid email address.'),
        array('ZMRequiredRule' ,'message', 'Please enter your message.')
    ));


    /* create account */
    $validator->addRules('registration', array(
        array('ZMRequiredRule' ,'firstName', 'Please enter your first name.'),
        array('ZMMaxFieldLengthRule' ,'firstName', 'customers', 'customers_firstname'),
        array('ZMRequiredRule' ,'lastName', 'Please enter your last name.'),
        array('ZMMaxFieldLengthRule' ,'lastName', 'customers', 'customers_lastname'),
        array('ZMRequiredRule' ,'email', 'Please enter your email address.'),
        array('ZMEmailRule' ,'email', 'Please enter a valid email address.'),
        array('ZMMaxFieldLengthRule' ,'email', 'customers', 'customers_email_address'),
        array('ZMUniqueEmailRule' ,'email', 'The entered email address is already in use.'),
        array('ZMRequiredRule' ,'password', 'Please enter you password.'),
        array('ZMMinRule' ,'password', $settingsService->get('zenmagick.base.authentication.minPasswordLength'), 'Your password must contain a minimum of %2$s characters.'),
        array('ZMRequiredRule' ,'confirmation', 'Please confirm the password.'),
        array('ZMFieldMatchRule' ,'password', 'confirmation', 'The password and confirm password must match.'),
        array('ZMRequiredRule' ,'addressLine1', 'Please enter your address.'),
        array('ZMMaxFieldLengthRule' ,'addressLine1', 'address_book', 'entry_street_address'),
        array('ZMRequiredRule' ,'city', 'Please enter a city.'),
        array('ZMMaxFieldLengthRule' ,'city', 'address_book', 'entry_city'),
        array('ZMRequiredRule' ,'postcode', 'Please enter a postcode.'),
        array('ZMMaxFieldLengthRule' ,'postcode', 'address_book', 'entry_postcode'),
        array('ZMRequiredRule' ,'countryId', 'Please select a country.'),
        array('ZMRequiredRule' ,'phone', "Please enter your telephone details."),
        array('ZMMaxFieldLengthRule' ,'phone', 'customers', 'customers_telephone')
    ));
    if ($settingsService->get('isPrivacyMessage')) {
        $validator->addRule('registration', array('ZMRequiredRule' ,'privacy', 'You must agree to the privacy policy.'));
    }
    if ($settingsService->get('isAccountGender')) {
        $validator->addRule('registration', array('ZMRequiredRule' ,'gender', 'Please choose a gender.'));
    }
    if ($settingsService->get('isAccountDOB')) {
        $validator->addRule('registration', array('ZMRequiredRule' ,'dob', 'Please enter your date of birth.'));
        $validator->addRule('registration', array('ZMDateRule' ,'dob', null, 'Please enter a valid date of birth.'));
    }
    if ($settingsService->get('isAccountState')) {
        $validator->addRule('registration', array('ZMStateOrZoneIdRule' ,'state,zoneId', 'Please enter a valid state/province.'));
        $validator->addRule('registration', array('ZMMaxFieldLengthRule' ,'state', 'address_book', 'entry_state'));
    }


    /* login */
    $validator->addRules('login', array(
        array('ZMRequiredRule', 'email_address', 'Please enter your email address.'),
        array('ZMEmailRule', 'email_address', 'Please enter a valid email address.'),
        array('ZMRequiredRule', 'password', "Please enter your password.")
    ));


    /* checkout_guest */
    $validator->addRules('checkout_guest', array(
        array('ZMRequiredRule' ,'email_address', 'Please enter your email address.'),
        array('ZMEmailRule' ,'email_address', 'Please enter a valid email address.')
    ));
    if ($settingsService->get('isGuestCheckoutAskAddress')) {
        $validator->addRules('checkout_guest', $validator->getRuleSet('address', false));
    }


    /* review */
    $validator->addRules('newReview', array(
        array('ZMRequiredRule' ,'rating', 'Please choose a rating.'),
        array('ZMRequiredRule' ,'text', 'Please enter your review.'),
        array('ZMMinRule' ,'text', REVIEW_TEXT_MIN_LENGTH, 'A review needs to have at least %2$s characters.')
    ));


    /* tell a friend */
    $validator->addRules('tellAFriend', array(
        array('ZMRequiredRule' ,'fromName', 'Please enter your name.'),
        array('ZMRequiredRule' ,'fromEmail', 'Please enter your email address.'),
        array('ZMEmailRule' ,'fromEmail', "Please enter a valid email address."),
        array('ZMRequiredRule' ,'toName', "Please enter your friend's name."),
        array('ZMRequiredRule' ,'toEmail', "Please enter your friend's email address."),
        array('ZMEmailRule' ,'toEmail', "Please enter a valid friend's email address.")
    ));


    /* guest_history */
    $validator->addRules('guest_history', array(
        array('ZMRequiredRule' ,'email', 'Please enter your email address.'),
        array('ZMRequiredRule' ,'orderId', 'Please enter your order number.')
    ));


    /* gvReceiver */
    $validator->addRules('gvReceiver', array(
        array('ZMRequiredRule' ,'name', 'Please enter a receiver name.'),
        array('ZMRequiredRule' ,'email', 'Please enter the receivers email address.'),
        array('ZMMaxFieldLengthRule' ,'email', 'coupon_email_track', 'emailed_to'),
        array('ZMEmailRule' ,'email', "Please enter a valid receiver email address."),
        array('ZMRequiredRule' ,'amount', 'Please enter the amount.'),
        array('ZMGVAmountRule' ,'amount')
    ));


    /* unsubscribe */
    $validator->addRules('unsubscribe', array(
        array('ZMRequiredRule', 'email_address', 'Please enter your email address.'),
        array('ZMEmailRule', 'email_address', 'Please enter a valid email address.')
    ));
