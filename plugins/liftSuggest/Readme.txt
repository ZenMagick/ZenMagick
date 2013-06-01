ZenMagick Lift Suggest Plugin
=============================
Lift Suggest support for ZenMagick. Based on the Lift Suggest zencart mod v1.4.
(http://www.zen-cart.com/index.html.php?main_page=product_contrib_info&cPath=40_60&products_id=1907)


NOTE
====
TO use this plugin you will need to register with Lift Suggest (http://www.liftsuggest.com/).


INSTALLAION
===========
* Download the latest version from http://www.zenmagick.org
* Extract into the ZenMagick plugins directory
* Install the plugin via the ZenMagick plugins admin page
* Configure via ZenMagick plugin admin.


Templates
=========
The plugin comes with a default template file that can be included in product pages and/or the
shopping cart page as required.

Right now there is no styling, so you'll need to do this yourself.

Alternatively, the plugin has a very basic interface that should allow to pull recommendations without
too much hassle.

NOTE: The API allows to pull more than one set of recommendations per page. The Lift Suggest API also supports this.
However, the effects on performance tracking of the suggestions are not defined and may distort the results.

Example:

Include the basic lift suggest template in your product page or shopping cart:

{{ include('@Storefront/lift-suggestions.html.twig') }}
