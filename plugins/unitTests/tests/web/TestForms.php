<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
<?php

/**
 * Storefront forms testing.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann
 */
class TestForms extends ZMWebTestCase {
    
    /**
     * Test contact us page.
     */
    public function testContactUs() {
        $this->get($this->getRequest()->url('contact_us'), array('themeId' => 'default'));
        $this->assertResponse(200);
        $this->assertTitle('Contact Us :: ZenMagick');
        $this->assertText(' > Contact Us');
    }

}

?>
<?php
/*
assertTitle($title)	Pass if title is an exact match
assertText($text)	Pass if matches visible and "alt" text
assertNoText($text)	Pass if doesn't match visible and "alt" text
assertPattern($pattern)	A Perl pattern match against the page content
assertNoPattern($pattern)	A Perl pattern match to not find content
assertLink($label)	Pass if a link with this text is present
assertNoLink($label)	Pass if no link with this text is present
assertLinkById($id)	Pass if a link with this id attribute is present
assertNoLinkById($id)	Pass if no link with this id attribute is present
assertField($name, $value)	Pass if an input tag with this name has this value
assertFieldById($id, $value)	Pass if an input tag with this id has this value
assertResponse($codes)	Pass if HTTP response matches this list
assertMime($types)	Pass if MIME type is in this list
assertAuthentication($protocol)	Pass if the current challenge is this protocol
assertNoAuthentication()	Pass if there is no current challenge
assertRealm($name)	Pass if the current challenge realm matches
assertHeader($header, $content)	Pass if a header was fetched matching this value
assertNoHeader($header)	Pass if a header was not fetched
assertCookie($name, $value)	Pass if there is currently a matching cookie
assertNoCookie($name)	Pass if there is currently no cookie of this name

$this->setMaximumRedirects(2);


 function testContact() {
        $this->get('http://www.lastcraft.com/');
        $this->clickLink('About');
        $this->assertTitle(new PatternExpectation('/About Last Craft/'));
    }



        $this->get('http://www.my-site.com/');
        $this->assertField('a', 'A default');
        $this->setField('a', 'New value');
        $this->click('Go');




class SimpleFormTests extends WebTestCase {
    ...
    function testNoSuperuserChoiceAvailable() {
        $this->get('http://www.lastcraft.com/form_testing_documentation.php');
        $this->assertFalse($this->setField('type', 'Superuser'));
    }
}

The current selection will not be changed if the new value is not an option.

Here is the full list of widgets currently supported...

    * Text fields, including hidden and password fields.
    * Submit buttons including the button tag, although not yet reset buttons
    * Text area. This includes text wrapping behaviour.
    * Checkboxes, including multiple checkboxes in the same form.
    * Drop down selections, including multiple selects.
    * Radio buttons.
    * Images.

    *



class SimpleFormTests extends WebTestCase {
    function testMyJavascriptForm() {
        $this->clickSubmit('OK', array('a_hidden_field'=>'123'));
    }

}

Bear in mind that in doing this you're effectively stubbing out a part of your software (the javascript code in the form), and perhaps you might be better off using something like Selenium to ensure a complete test.
Raw posting

If you want to test a form handler, but have not yet written or do not have access to the form itself, you can create a form submission by hand.

class SimpleFormTests extends WebTestCase {
    ...    
    function testAttemptedHack() {
        $this->post(
                'http://www.my-site.com/add_user.php',
                array('type' => 'superuser'));
        $this->assertNoText('user created');





 */
