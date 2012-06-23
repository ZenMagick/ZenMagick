<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
 */
?>

<h1><?php _vzm("What is CVV?") ?></h1>

<div>
  <?php echo $utils->staticPageContent('popup_cvv_visa_master') ?>
  <img src="<?php echo $this->asUrl("images/icons/cvv2visa.gif") ?>" alt="<?php _vzm("cvv sample 1") ?>" />
</div>

<div>
  <?php echo $utils->staticPageContent('popup_cvv_amex') ?>
  <img src="<?php echo $this->asUrl("images/icons/cvv2amex.gif") ?>" alt="<?php _vzm("cvv sample 2") ?>" />
</div>

<div id="close"><a href="#" onclick="javascript:window.close()"><?php _vzm("Close Window [x]") ?></a></div>
