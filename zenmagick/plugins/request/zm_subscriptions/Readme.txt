Secure token service plugin
===========================

This plugin adds a new service to ZenMagick that allows to manage secure token.
Possible use cases might be:
* auto login
  Store a managed hash in the auto login cookie rather than the encoded password
* newsletter
  - Make unsubscribe subject to a valid hash being passed back in the URL. That way 
    it would no not be possible to unsubscribe random email addresses.
  - Implement a proper opt-in with an email containing a confirmation URL that will
    perform the actual subscribe (for anonymous subscriptions)


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.


This plugin doesn't do anything itself, but provides a service for other plugins or core logic.
If used, the code should be aware of the fact that the service might not be available (ie. not installed) and handle this gracefully.


Templates
=========
The plugin requires/allows additions/changes to a few templates.
See the templates folder for some sample code.


Payments
========
This plugin is not concerned about payments. One model of handling payments would be to allow the offline CC payment
type for subscriptions and other for regular orders.
A simple way of doing this would be to modify the checkout_payments.php template in the following fashion:


      ....
      $schedule = $zm_subscriptions->getSelectedSchedule();
      $paymentTypes = $zm_cart->getPaymentTypes();
      $single = 1 == count($paymentTypes);
      foreach ($paymentTypes as $type) {
        // check against list of allowed subscription payment types
        $isSubscriptionPaytype = ZMTools::inArray($type->getId(), 'cc');
        if ((null != $schedule && !$isSubscriptionPaytype) || (null == $schedule && $isSubscriptionPaytype)) {
            continue;
        }
        ...
      }


Emails
======
If the name of an email template is configured, the plugin will send an customer notification email for each scheduled
order. The email context is the same as for the 'checkout' template.
So, the simplest way is to just re-use that.
