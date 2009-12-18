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
3) Configure the zm_cron plugin to execute the cron job ZMUpdateSubscriptionsCronJob once a day
Example configuration:
#mi  h    d    m    dow      job                                  comment
0    5    *    *    *        ZMUpdateSubscriptionsCronJob         # every sunday at 5 am


File Permissions
================
The plugin will attempt to create a file named cronhistory.txt in the folder zenmagick/config/zm_cron/etc.
It is important to check that the file exists once the plugin has been run the first time. Also, file permissions
on that file need to be set to allow the webserver to update this file (typically 666).

The file is under zenmagick/config/zm_cron/etc and named cronhistory.txt. If it doesn't exist, chmod the etc folder to 777, wait until the file is there, change the folder back to 755 and then the file to 666.


This plugin doesn't do anything itself, but provides a service for other plugins or core logic.
If used, the code should be aware of the fact that the service might not be available (ie. not installed) and handle this gracefully.


Templates
=========
Email templates
---------------
The plugin will try to use three different email templates, depending on the configuration:
a) subscription_request.[text|html].php
A notification email to the store owner (or other configured email address) about a subscription form request.
The name may be overridden with the setting 'plugins.zm_subscriptions.email.templates.request'.

b) subscription_cancel.[text|html].php
Confirmation email for customer (and admin) about a canceled order
The name may be overridden with the setting 'plugins.zm_subscriptions.email.templates.cancel'.

3) checkout.[text|html].php
Customer notification email about a scheduled order. The default is to use the regular 'checkout' email template.

All email templates may be changed by configuring the following settings:
- plugins.zm_subscriptions.email.templates.request
- plugins.zm_subscriptions.email.templates.cancel
- plugins.zm_subscriptions.email.templates.schedule


store templates
---------------
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


Schedule options
================
The defauls may be changed by configuring a plugin setting 'plugins.zm_subscriptions.schedules':
Example:

    ZMSettings::set('plugins.zm_subscriptions.schedules', array(
        '1w' => array('name' => 'Weekly', 'active' => true),
        '10d' => array('name' => 'Every 10 days', 'active' => true),
        '4w' => array('name' => 'Every four weeks', 'active' => true),
        '1m' => array('name' => 'Once a month', 'active' => true)
    ));


Subscription request types
==========================
Similar to the schedule options, the enquiry types can be configured via a setting, namely 'plugins.zm_subscriptions.request.types':
Example:

    ZMSettings::set('plugins.zm_subscriptions.request.types', array(
        'cancel' => "Cancel Subscription",
        'enquire' => "Enquire order status",
        'other' => "Other",
    ));



TODO
====
Rotate/truncate histoy  - there should be a maximum based on the configurable 
values (12 month+1week+7days+24hrs perhaps?)
