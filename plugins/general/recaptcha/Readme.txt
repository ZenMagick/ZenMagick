This is the ZenMagick plugin for ReCAPTCHA support.
For details visit: http://recaptcha.net/


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure ReCAPTCH as required

After the plugin has been installed you should have the ReCAPTCHA
configuration options available in the admin interface.


Usage
=====
This plugin uses the ReCAPTCHA PH library. 

To use the captcha, you'll need to configure the pages you want to protect and then
add something like the following to your templates:

    <?php if (is_object($recaptcha)) { ?>
        <?php $recaptcha->showCaptcha(); ?>
    <?php } ?>

If the page is configured to *not* require a captcha, no output will be generated.


Troubelshooting
===============
The ReCAPTCHA library will need to be able to access the internet (no proxy/firewall!)
in order to validate the entered data.
