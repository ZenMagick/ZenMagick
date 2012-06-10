<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */ $admin->title() ?>

<script>
$(function() {
    $('.datepicker').datepicker();
});
</script>

<h2><?php _vzm('Export orders') ?></h2>
<div>
  <form class="export-orders-form" action="<?php echo $admin->url() ?>" method="GET">
    <input type="hidden" name="rid" value="export_orders">
    <p>
      <label for="dateFrom"><?php _vzm('From:') ?></label>
      <input class="datepicker" id="fromDate" name="fromDate" date:dateFormat="<?php echo $dateFormat ?>" type="text" value="<?php echo $fromDate ?>">
    </p>
    <p>
    <label for="dateTo"><?php _vzm('To:') ?></label>
      <input class="datepicker" id="toDate" name="toDate" date:dateFormat="<?php echo $dateFormat ?>" type="text" value="<?php echo $toDate ?>">
    </p>
    <p>
      <label for="exportFormat"><?php _vzm('Format:') ?></label>
      <select id="exportFormat" name="exportFormat">
        <option value="display"><?php _vzm('Display') ?></option>
        <option value="csv"><?php _vzm('CSV') ?></option>
      </select>
    </p>
    <p><input type="submit" class="<?php echo $buttonClasses ?>" value="Find Orders"></p>
  </form>
</div>

<?php if (isset($header) && isset($rows)) { ?>
  <table class="grid">
    <tr>
      <?php foreach ($header as $column) { ?>
        <th><?php echo $column ?></th>
      <?php } ?>
    </tr>
    <?php foreach ($rows as $orderRows) { ?>
      <?php foreach ($orderRows as $row) { ?>
        <tr>
          <?php foreach ($row as $column) { ?>
            <td><?php echo $column ?></td>
          <?php } ?>
        </tr>
      <?php } ?>
    <?php } ?>
  </table>
<?php } ?>
