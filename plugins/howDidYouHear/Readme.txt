'How did you hear about us'
===========================
Based on the Zen Cart mod as found here: http://www.zen-cart.com/index.php?main_page=product_contrib_info&products_id=186

Adds some additional fields to the registration page. This plugin is mostly
compatible with the original Zen Cart mod, except for two changes in the installation SQL:

1) The new column 'customers_info_source_id' in customers_info does allow NULL and has a 
default of 9999 (Others)
2) 'Others' (with id 9999) is also added to the sources table to make things a bit easier


INSTALLAION
===========
1) Download (obvious ;)
   Download the latest version from http://www.zenmagick.org/

2) Extract into the ZenMagick plugins directory
   After that you should have a howDidYouHear folder in the plugins folder.

3) Install the plugin via the ZenMagick plugins admin page.


TEMPALTE CHANGES
================
The plugin includes a small template file to be included into the create_account.html.twig template.

In your create_account.html.twig, after the last closing fieldset element, add the following line:

    {{ include('@Storefront/howDidYouHearOptions.html.twig') }}

In address.html.twig (for guest checkout), add the same line to the end of the template:

    {{ include('@Storefront/howDidYouHearOptions.html.twig') }}


CUSTOMISATION
=============
To customize howDidYouHearOptions.html.twig, copy the file into your theme's views folder and edit away.
