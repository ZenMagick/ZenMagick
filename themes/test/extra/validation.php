<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
     * The ruleset id must match the id attribute of the HTML form.
     */


    $validator = ZMValidator::instance();

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
        array('MinRule' ,'password', ZMSettings::get('minPasswordLength'), 'Your password must contain a minimum of %2$s characters.'),
        array('RequiredRule' ,'confirmation', 'Please confirm the password.'),
        array('FieldMatchRule' ,'password', 'confirmation', 'The password and confirm password must match.'),
    ), true);
    if (ZMSettings::get('isPrivacyMessage')) {
        $validator->addRule('registration', array('RequiredRule' ,'privacy', 'You must agree to the privacy policy.'));
    }

?>
