<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
<h1><?php _vzm('Oops') ?></h1><div>
<p><?php _vzm('Something went wrong.') ?></p>
<?php /* TODO: need to create conventions for messages, etc */ if (isset($message)) { ?><h2><?php echo $message ?></h2><?php } ?>
<?php if (ZM_ENVIRONMENT == 'development') { ?>
  <pre>
  <?php
     if (isset($exception)) {
        echo $exception->getTraceAsString();
    } else { // we don't know what happened! @todo try to figure it out
        debug_print_backtrace();
    }
  ?>
  </pre>
<?php } ?>
