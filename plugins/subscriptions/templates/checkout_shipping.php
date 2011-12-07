
    <?php if ($subscriptions->qualifies($zm_cart)) { ?>
        <fieldset>
            <legend><?php _vzm("Subscriptions") ?></legend>
            <p class="inst"><?php _vzm("To make this order a scheduled subscription, please tick <em>Subscribe</em> and select the schedule.") ?></p>
            <?php $schedule = $subscriptions->getSelectedSchedule(); ?>
            <p><input type="checkbox" name="subscription" id="subscription" value="1" <?php $form->checked(null!=$schedule) ?>> <label for="subscription">Subscribe</label></p>
            <p>
              <label for="schedule">Order Interval</label>
              <select name="schedule" id="schedule">
                <?php foreach ($subscriptions->getSchedules() as $key => $schedule) { if (!$schedule['active']) { continue; } ?>
                    <option value="<?php echo $key ?>"><?php _vzm($schedule['name']) ?></option>
                <?php } ?>
              </select>
            </p>
        </fieldset>
    <?php } ?>

