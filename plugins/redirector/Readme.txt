This is a ZenMagick plugin to manage redirects for missing categories and products.

NOTE: This plugin is functional, but lacks a nice UI.


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure via settings.


Configure
=========
Products
For products add something like the following to your global.yaml or theme.yaml:

storefront,admin:
  settings:
    plugins:
      redirector:
        productMappings:
          1: 19
          2: 3

This example will result in requests to the product with id 1 forwarded to product #19 and requests to
product #id 2 to product #3.


Categories:
For categories add something like the following to your global.yaml or theme.yaml:

storefront,admin:
  settings:
    plugins:
      redirector:
        categoryMappings:
          1: 19
          2: 3

This example will result in requests to category with id 1 forwarded to category #19 and requests to
category #id 2 to category #3.

