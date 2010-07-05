Snap affiliate plugin
=====================

This plugin implements a simple affiliate program for ZenMagick driven stores. The code
is inspired/based/compatible with the snap affiliate mod as found at: http://www.filterswept.com/snap-affiliates/


INSTALLAION
===========
1) Download (obvious ;)
   Download the latest version from http://www.zenmagick.org

2) Extract into the ZenMagick plugins directory
   After that you should have a zm_snap_affiliate sub-directory in the plugins/request folder.

3) Install the plugin via the ZenMagick plugins admin page
    Prefix:             A configurable prefix to be used for all affiliate keys generated.
    DefaultCommision:   The default commision for a new affiliate.
    TemplateLocation:   Controls whether to use templates in the plugin folder or if templates are 
                        loaded from the active theme.

4) Start using :)


Using
=====
The plugin adds three new pages to the storefront and a management view to the admin section of your store.
The storefront pages are:

index.php?main_page=affiliate_signup
The first page a potential affiliate would see. It displays the Terms & Conditions (or whatever you want) and,
depending on the users state (logged in or not), a signup form.

index.php?main_page=affiliate_terms
Just the terms and conditions.

index.php?main_page=affiliate_main
Main view for registered and approved affiliates.
