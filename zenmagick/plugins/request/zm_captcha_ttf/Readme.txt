This is the ZenMagick plugin for CAPTCHA TTF support.


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure CAPTCH TTF as required - if CAPTCHA TTF was already installed,
   you are already done!

After the plugin has been installed you should have the CAPTCHA TTF
configuration options available in the admin interface.



captcha modifications
=====================
There are a few modifications that I needed to make to the original captcha class
in order ot get it to work the way I wanted. So, if you need to upgrade the captcha
class you'll need to reapply those changes.

1) Renamed class to pcaptcha in order to avoid name collisions

2) added a new line in the c'tor to set the fonts directory. That line needs
   to be placed after the original code that initialises dir_fs_fonts:

		$this->dir_fs_fonts = $zm_captcha_ttf->getPluginDir() . 'fonts/';

3) At the end of the c'tor, the img_href gets set. I removed the '.php' sufix
   from captcha_img.php to make it point to the new controller included in this
   plugin.

4) Changed a single ampersand '&' to the HTML entity &amp;.

I also modified the install.sql script to make it work in batch mode. All required is
to replace backslash-single quote:  \'  with two single quotes:  ''



Usage
=====
The captcha fieldname is 'captcha'. If you need to change that, please modify
the 'ZM_CAPTCHA_TTF_FIELD' define at the top of zm_captcha_ttf.php.

To use the captcha, you'll need to configure the pages you want to protect and then
add something like the following to your templates.
Please note that the only page specific bit is the filename for the reload. This is
an example for the contact_us page:

    <?php if (is_object($zm_captcha_ttf) && $zm_captcha_ttf->isCaptchaEnabled()) { ?>
        <?php $zm_captcha_ttf->showImage(); ?>
        <a href="<?php zm_href(null) ?>"><?php zm_l10n("Click to refresh page")?></a><br />
        <label for="captcha"><?php zm_l10n("Captcha") ?><span>*</span></label>
        <input type="text" id="captcha" name="captcha" value="" /><br />
    <?php } ?>

