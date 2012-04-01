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
 */ $selectedLanguageId = $currentLanguage->getId(); ?>

<?php $admin->title() ?>
<form action="<?php echo $admin->url() ?>" method="GET">
  <input type="hidden" name="rid" value="content_editor">
  <h2><?php _vzm('Content Manager') ?> (
          <select id="languageId" name="languageId" onchange="this.form.submit();">
            <?php foreach ($this->container->get('languageService')->getLanguages() as $lang) { ?>
              <?php $selected = $selectedLanguageId == $lang->getId() ? ' selected="selected"' : ''; ?>
              <option value="<?php echo $lang->getId() ?>"<?php echo $selected ?>><?php echo $lang->getName() ?></option>
            <?php } ?>
          </select>
        )
      <a href="<?php echo $admin->url('content_edit', 'editId=0&languageId='.$selectedLanguageId) ?>">Create new</a>
  </h2>
</form>

<table class="grid">
  <tr>
    <th><?php _vzm("Id") ?></th>
    <th><?php _vzm("Title") ?></th>
    <th><?php _vzm("Type") ?></th>
    <th><?php _vzm("Action") ?></th>
  </tr>
  <?php foreach ($resultList->getResults() as $ezPage) { ?>
    <tr>
      <td><?php echo $ezPage->getId() ?></td>
      <td><a href="<?php echo $admin->url('content_edit', 'editId='.$ezPage->getId().'&languageId='.$selectedLanguageId) ?>"><?php echo $html->encode($ezPage->getTitle()) ?></a></td>
      <td><?php echo $ezPage->isStatic() ? _zm('Static') : _zm('EZPage') ?></td>
      <td>
        <form action="<?php echo $admin->url('content_edit') ?>" method="POST" onsubmit="return ZenMagick.confirm('<?php _vzm('Delete page id:#%s?', $ezPage->getId()) ?>', this);">
          <input type="hidden" name="rid" value="content_editor">
          <input type="hidden" name="languageId" value="<?php echo $selectedLanguageId ?>">
          <input type="hidden" name="deleteId" value="<?php echo $ezPage->getId() ?>">
          <input class="<?php echo $buttonClasses ?>" type="submit" value="Delete">
          <a class="<?php echo $buttonClasses ?>" href="<?php echo $admin->url('content_edit', 'editId='.$ezPage->getId().'&languageId='.$selectedLanguageId) ?>">Edit</a>
        </form>
      </td>
    </tr>
  <?php } ?>
</table>
<?php echo $this->fetch('pagination.php'); ?>
