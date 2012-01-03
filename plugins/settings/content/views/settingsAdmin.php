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
<?php $admin2->title() ?>
<h2><?php _vzm('Manage Settings') ?></h2>

<script type="text/javascript">
    var $widgetTemplates = [];
    // use jquery to toggle value element
</script>

<form action="<?php echo $admin2->url() ?>" method="POST">
    <fieldset>
        <legend>Create New Setting</legend>
        <p>
            <input type="hidden" name="fkt" value="settings_admin_manage">
            <input type="hidden" name="action" value="create">
            Title: <input type="text" name="title">
            Key: <input type="text" name="key">
            Value: <input type="text" name="value">
            Type: <select name="type">
                <option value="textFormWidget#">Text</option>
                <option value="passwordFormWidget#">Password</option>
                <option value="textAreaFormWidget#cols=80&rows=5">Text Area</option>
                <option value="textAreaFormWidget#cols=35&rows=2">Text Area (small)</option>
                <option value="booleanFormWidget#style=select">Boolean (dropdown)</option>
                <option value="booleanFormWidget#style=radio">Boolean (radio)</option>
                <option value="booleanFormWidget#style=checkbox">Boolean (checkbox)</option>
                <option value="selectFormWidget#">Generic Select (dropdown)</option>
                <option value="selectFormWidget#multiple=true&size=3">Generic Select (dropdown, multiple)</option>
                <option value="ZMManufacturerSelectFormWidget#">Manufacturer (dropdown)</option>
                <option value="ZMManufacturerSelectFormWidget#title=None&options=0= --- ">Manufacturer (dropdown, incl. empty default)</option>
                <option value="ZMOrderStatusSelectFormWidget#">Order Status (dropdown)</option>
                <option value="ZMOrderStatusSelectFormWidget#title=None&options=0= --- ">Order Status (dropdown, incl. empty default)</option>
                <option value="ZMCountrySelectFormWidget#">Country (dropdown)</option>
                <option value="ZMCountrySelectFormWidget#title=None&options=0= --- ">Country (dropdown, incl. empty default)</option>
                <option value="ZMEditorSelectFormWidget#">WYSIWYG Editor (dropdown)</option>
            </select>
        </p>
        <p>
          <input type="submit" value="create">
        </p>
    </fieldset>
</form>

<form action="<?php echo $admin2->url() ?>" method="POST">
    <fieldset>
        <legend>Current Settings</legend>
        <table class="grid">
            <thead>
                <tr>
                    <th>title</th>
                    <th>key</th>
                    <th>value</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plugin->getConfigValues() as $value) { ?>
                    <?php if (ZMLangUtils::endsWith($value->getKey(), Plugin::KEY_ENABLED) || ZMLangUtils::endsWith($value->getKey(), Plugin::KEY_SORT_ORDER)) { continue; } ?>

                    <?php if ($value instanceof zenmagick\http\widgets\Widget) { ?>
                        <?php if (ZMLangUtils::endsWith($value->getName(), Plugin::KEY_ENABLED) || ZMLangUtils::endsWith($value->getName(), Plugin::KEY_SORT_ORDER)) { continue; } ?>
                        <tr>
                            <td><?php echo $value->getTitle() ?></td>
                            <td><?php echo $value->getName() ?></td>
                            <td><?php echo $value->render($request, $view); ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
        <input type="hidden" name="fkt" value="settings_admin_manage">
        <input type="hidden" name="action" value="update">
        <p><input type="submit" value="update"></p>
    </fieldset>
</form>

