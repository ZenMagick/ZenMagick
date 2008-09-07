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
The plugin requires additions/changes to the following views:

1) checkout_shipping.php
Add the following form block to allow users to mark an order for subscription:


    <fieldset>
        <legend><?php zm_l10n("Subscriptions") ?></legend>
        <p class="inst"><?php zm_l10n("To make this order a scheduled subscription, please tick <em>Subscribe</em> and select the schedule.") ?></p>
        <?php $schedule = $zm_subscriptions->getSelectedSchedule(); ?>
        <p><input type="checkbox" name="subscription" id="subscription" value="1" <?php $form->checked(null!=$schedule) ?>> <label for="subscription">Subscribe</label></p>
        <p>
          <label for="schedule">Order Interval</label>
          <select name="schedule" id="schedule">
            <?php foreach ($zm_subscriptions->getSchedules() as $key => $name) { ?>
                <option value="<?php echo $key ?>"><?php zm_l10n($name) ?></option>
            <?php } ?>
          </select> 
        </p>
    </fieldset>


2) checkout_confirmation.php (optional)

<fieldset>
    <legend><?php zm_l10n("Subscription") ?></legend>
    <div class="btn"><a class="btn" href="<?php $net->url(FILENAME_CHECKOUT_SHIPPING, '', true) ?>"><?php zm_l10n("Change") ?></a></div>
    <div>
        <?php $schedule = $zm_subscriptions->getSelectedSchedule(); ?>
        <p><?php zm_l10n("This order is %s a subscription!", (null != $schedule ? '' : '*NOT*')) ?></p>
        <?php if (null != $schedule) { ?>
            <?php $schedules = $zm_subscriptions->getSchedules(); ?>
            <p><?php zm_l10n("Order schedule: '%s.'", $schedules[$schedule]) ?></p>
        <?php } ?>
    </div>
</fieldset>



