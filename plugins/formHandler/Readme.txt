Generic form handler plugin
===========================

This plugin allows to configure new page URLs that can be used to set up forms for users to fill in.
The result will be emailed to a configurable email addres.


INSTALLAION
===========
1) Download (obvious ;)
   Download the latest version from http://www.zenmagick.org/

2) Extract into the ZenMagick plugins directory
   After that you should have a formHandler sub-directory in the plugins/general folder.

3) Install the plugin via the ZenMagick plugins admin page
    Pages:    Single name or comma separated list of page names.
              For each name a view must exist that should contain a form pointing to the same page name.
    Email:    Email address; If empty, the store email address will be used for reporting.
    Template: Name of the email template to be used for reporting.
              You can either configure a shared template (see the default templates/form_handler.text.php
              included) or leave the field empty. If empty, the page name is used as email template name.
    Secure:   If enabled, *all* forms will be forced to use SSL (if enabled for the store)

4) Create form views and either the shared email template or individual templates for each configured form.

Using the example form:
a) Set 'Pages' value to foo.
b) Copy templates/foo.php into zenmagick/themes/[YOUR_THEME]/content/views
c) Copy templates/form_handler.text.php to zenmagick/themes/[YOUR_THEME]/content/views/email
d) Navigate to index.php?main_page=foo, enter test data and submit
