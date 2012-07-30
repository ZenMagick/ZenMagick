Subscription plugin
===================
This plugin allows users to flag orders as subscriptions, selecting from a configurable range of schedules.



Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.
3) Configure the cron plugin to execute the cron job UpdateSubscriptionsCronJob once a day
Example configuration:
#mi  h    d    m    dow      job                                  comment
0    5    *    *    *        zenmagick\plugins\subscriptions\cron\UpdateSubscriptionsCronJob         # every sunday at 5 am


File Permissions
================
The plugin will attempt to create a file named cronhistory.txt in the folder zenmagick/config/cron/etc.
It is important to check that the file exists once the plugin has been run the first time. Also, file permissions
on that file need to be set to allow the webserver to update this file (typically 666).

The file is under zenmagick/config/cron/etc and named cronhistory.txt. If it doesn't exist, chmod the etc folder to 777, wait until the file is there, change the folder back to 755 and then the file to 666.


This plugin doesn't do anything itself, but provides a service for other plugins or core logic.
If used, the code should be aware of the fact that the service might not be available (ie. not installed) and handle this gracefully.


Templates
=========
Email templates
---------------
The plugin will try to use three different email templates, depending on the configuration:
a) subscription_request.[text|html].php
A notification email to the store owner (or other configured email address) about a subscription form request.
The name may be overridden with the setting 'plugins.subscriptions.email.templates.request'.

b) subscription_cancel.[text|html].php
Confirmation email for customer (and admin) about a canceled order
The name may be overridden with the setting 'plugins.subscriptions.email.templates.cancel'.

3) checkout.[text|html].php
Customer notification email about a scheduled order. The default is to use the regular 'checkout' email template.

All email templates may be changed by configuring the following settings:
- plugins.subscriptions.email.templates.request
- plugins.subscriptions.email.templates.cancel
- plugins.subscriptions.email.templates.schedule


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
      $schedule = $subscriptions->getSelectedSchedule();
      $paymentTypes = $shoppingCart->getSelectedPaymentTypes();
      $single = 1 == count($paymentTypes);
      foreach ($paymentTypes as $type) {
        // check against list of allowed subscription payment types
        $isSubscriptionPaytype = in_array($type->getId(), 'cc');
        if ((null != $schedule && !$isSubscriptionPaytype) || (null == $schedule && $isSubscriptionPaytype)) {
            continue;
        }
        ...
      }


Schedule options
================
The defauls may be changed by configuring a plugin setting 'plugins.subscriptions.schedules':
Example:

storefront,admin:
  settings:
    subscriptions:
      schedules:
        1w: { name: Weekly, active: true }
        10d: { name: 'Every 10 days', active: true }
        4w: { name: 'Every four weeks', active: true }
        1m: { name: 'Once a month', active: true }


Subscription request types
==========================
Similar to the schedule options, the enquiry types can be configured via a setting, namely 'plugins.subscriptions.request.types':
Example:

storefront,admin:
  settings:
    subscriptions:
      request:
        types:
          - { cancel: 'Cancel Subscription' }
          - { enquire: 'Enquire order status' }
          - { other: 'Other' }



TODO
====
Rotate/truncate histoy  - there should be a maximum based on the configurable 
values (12 month+1week+7days+24hrs perhaps?)
