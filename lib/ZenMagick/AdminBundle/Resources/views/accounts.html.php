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
 */
use ZenMagick\StoreBundle\Entity\Account;

$admin->title() ?>

<table class="grid">
  <tr>
    <th><?php _vzm('ID') ?></th>
    <th><?php _vzm('Name') ?></th>
    <th><?php _vzm('Created') ?></th>
    <th><?php _vzm('Authorization') ?></th>
  </tr>
  <?php foreach ($resultList->getResults() as $account) { ?>
    <tr>
      <td><?php echo $account->getId() ?></td>
      <?php $name = $account->getType() == Account::REGISTERED ? $account->getFullName() : _zm('** Guest **'); ?>
      <td><a href="<?php echo $net->url('account_show', 'accountId='.$account->getId()) ?>"><?php echo $name ?></a></td>
      <td><?php echo $locale->shortDate($account->getAccountCreateDate()) ?></td>
      <td><?php echo ($account->getAuthorization() ? _vzm('Pending') : _vzm('Approved')) ?></td>
    </tr>
  <?php } ?>
</table>
<?php echo $this->fetch('pagination.html.php'); ?>
