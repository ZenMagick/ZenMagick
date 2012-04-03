Surcharge order total plugin


Installation
============
1) Unzip this plugin into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Make sure the Zen Cart order total module ot_zenmagick is installed and enabled.
4) The Zen Cart class 'payment' in includes/classes/payment.php need some patching to avoid
   errors for loading the same class twice. In order to do that you need to edit the class file
   and wrap the whole class with 
   if (!class_exists('payment')) {
    // the class code
   }
5) Enjoy


Configuration
=============
Currently there is no UI to configure this pluing. All configuration is done via the
setting 'plugins.paymentSurcharge.conditions'.
The setting is expected to be a list of arrays with each array being a condition.

Condition consists of the following key/value pairs:
* code
  This identifies the payment module. Allowed values are
  # a single string, for example 'cc'
  # a comma separated list; example: 'cc,eway'
  # an array of payment module ids; example array('cc', eway')
  # null, to match all payment modules

* cvalue
  The fieldname of the payment module to evaluate. If more than one field is specified (separated by ';'),
  the first existing one will be used.
  To match just the module, set cvalue to null.

* regexp
  The regular expression to evaluate a match on the configured field(s).

* value
  The surcharge value. This is either a numeric value or percentage. Absolute amounts are specified as
  numeric value, for example 3.99. A percentage value needs to be prefixed with '%:'; for example '%:3' for
  a three percent surcharge.

* title
  The order total title/name.


Examples
========

1) Configure a 3% surcharge for AMEX cards:

/**
 * code: Either the code of a payment module or null
 * cvalue: The fieldname (or ';' separated list) of the payment module to evaluate; may be prefixed with 'field:'
 * regexp: The regular expression to evalue the field value
 * value: The value; numeric value; if prefixed with '%:' a percent value will be calculated, otherwise the amount taken as is
 * title: The display title
 */

storefront:
  settings:
    paymentSurcharge:
      conditions:
        - { code: cc, cvalue: 'field:cc_card_number;cc_number', regexp: '^3[47][0-9]{13}$', value: '%:3', title: 'AMEX Surcharge' }

2) A $3.00 surcharge for money orders:

storefront:
  settings:
    paymentSurcharge:
      conditions:
        - { code: 'moneyorder', value: '3', title: 'Money Order Surcharge' }
