<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 *
 * $Id$
 */
?><?php

    // default
    $zm_server_names = array('' => '');

    // include the actual config
    if (file_exists(ZM_FILE_SITE_SWITCHER)) {
       include(ZM_FILE_SITE_SWITCHER);
    }

    zm_site_switch_check_permissions();

    if (null != ZMRequest::getParameter('save')) {
        $zm_server_names = array();
        foreach (ZMRequest::getParameterMap() as $name => $value) {
            if (ZMTools::startsWith($name, 'hostname_')) {
                $index = str_replace('hostname_', '', $name);
                $themeId = ZMRequest::getParameter('themeId_'.$index);
                if (!empty($name) && !empty($themeId)) {
                    $zm_server_names[$value] = $themeId;
                }
            }
        }
        zm_site_switch_setup_switcher();
        zm_site_switch_config_write($zm_server_names);
    }

?>

<h2>Site Switch Configuration</h2>

<script type="text/javascript">
    var _no_sites = <?php echo count($zm_server_names) ?>;
    function addSite() {
        var fieldset = $('#site_1').html();
        ++_no_sites;
        fieldset = fieldset.replace(/#1/g, '#'+_no_sites);
        fieldset = fieldset.replace(/_1/g, '_'+_no_sites);
        $('#submit').before('<fieldset id="site_'+_no_sites+'">'+fieldset+'</fieldset>');
    }
    function removeSite() {
        if (1 < _no_sites) {
            $('#site_'+_no_sites).remove();
            --_no_sites;
        }
    }
</script>

<?php zm_form('', 'fkt=zm_site_switch_admin', 'site_switch_form') ?>
    <?php $ii = 0; foreach ($zm_server_names as $hostname => $themeId) { ++$ii; ?>
        <fieldset id="site_<?php echo $ii ?>">
            <legend>Site #<?php echo $ii ?></legend>
            <p><label for="hostname_<?php echo $ii ?>">Hostname:</label> <input type="text" name="hostname_<?php echo $ii ?>" value="<?php echo $hostname ?>"></p>
            <p><label for="themeId_<?php echo $ii ?>">Theme:</label> <select name="themeId_<?php echo $ii ?>">
                <?php foreach (ZMThemes::instance()->getThemeInfoList() as $themeInfo) { $selected = $themeId == $themeInfo->getThemeId() ? ' selected' : ''; ?>
                    <option value="<?php echo $themeInfo->getThemeId() ?>"<?php echo $selected ?>><?php echo $themeInfo->getName() ?></option>
                <?php } ?>
            </select></p>
        </fieldset>
    <?php } ?>
    <p id="submit">
        <input type="submit" name="save" value="Save">
        <a href="" onclick="addSite(); return false;">Add Site</a> |
        <a href="" onclick="removeSite(); return false;">Remove Site</a>
    </p>
</form>
