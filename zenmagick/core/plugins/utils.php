<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
?>
<?php  

    /**
     * Create a plugin admin page URL.
     *
     * @package org.zenmagick.plugins
     * @param string function The view function name; default is <code>null</code> to use the current.
     * @param string params Query string style parameter; if <code>''</code>.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     */
    function zm_plugin_admin_url($function=null, $params='', $echo=ZM_ECHO_DEFAULT) {
        if (null == $function) {
            $function = ZMRequest::getParameter('fkt');
        }
        $url = ZMToolbox::instance()->net->url('zmPluginPage.php', 'fkt='.$function.'&'.$params, 'SSL');

        if ($echo) echo $url;
        return $url;
    }

    /**
     * Create a plugin view/admin page URL.
     *
     * <p>This function can be used in places where code is executed in both storefront <strong>and</strong>
     * admin context.</p>
     *
     * <p>In contrast to <code>zm_plugin_admin_url</code>, this function will accept either a view name,
     * a function name or both as <em>target</em>.</p>
     *
     * <p>Format for <em>target</em> is as follows:</p>
     * <dl>
     *   <dt>View only</dt><dd>Same as for <code>ZMToolboxNet::url</code>.</dd>
     *   <dt>Admin only</dt><dd>Same as for <code>zm_plugin_admin_url</code> except that the function name is preceeded by <em>;</em>.</dd>
     *   <dt>View and function</dt><dd>Viewname and function separated by <em>;</em>; example: <code>wiki;zm_wiki_admin</code>.</dd>
     * </dl>
     *
     * @package org.zenmagick.plugins
     * @param string target The target.
     * @param string params Query string style parameter; if <code>null</code> add all current parameter.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A full URL.
     */
    function zm_plugin_url($target, $params='', $echo=ZM_ECHO_DEFAULT) {
        $target = explode(';', $target);
        if (ZMRequest::isAdmin()) {
            return zm_plugin_admin_url($target[1], $params, $echo);
        } else {
            return ZMToolbox::instance()->net->url($target[0], $params, false, $echo);
        }
    }

    /**
     * Generate form element for the given config value.
     *
     * @package org.zenmagick.plugins
     * @param ZMConfigValue value The value.
     * @param boolean echo If <code>true</code>, the HTML will be echo'ed as well as returned.
     * @return string HTML code.
     */
    function zm_plugin_value_element($value, $echo=ZM_ECHO_DEFAULT) {
        if ($value->hasSetFunction()) {
            eval('$set = ' . $value->getSetFunction() . "'" . $value->getValue() . "', '" . $value->getKey() . "');");
            echo str_replace('<br>', '', $set);
        } else {
            echo zen_draw_input_field('configuration[' . $value->getKey() . ']', $value->getValue());
        }
    }


    /**
     * Create a simple plugin config form.
     *
     * @package org.zenmagick.plugins
     * @param ZMPlugin plugin The plugin.
     * @param string fkt The view function.
     * @param string title Optional title; default is <code>null</code> to use the plugin name.
     * @param boolean all Allows to exclude the common values (status/sort order); default is <code>true</code> to show all.
     * @return ZMPluginPage The plugin page instance.
     */
    function zm_simple_config_form($plugin, $fkt, $title=null, $all=true) {
        // more reference stuff
        $id = $plugin->getId();
        global $id;

        $title = null == $title ? $plugin->getName() : $title;
        $title = zm_l10n_get($title);
        $contents = <<<EOT
<h2><?php echo \$title ?></h2>
<form action="<?php zm_plugin_admin_url() ?>" method="POST">
    <table cellspacing="0" cellpadding="0" id="plugin-config">
        <?php foreach (\$plugin->getConfigValues(false) as \$value) { ?>
            <?php if (!\$all && (ZMTools::endsWith(\$value->getKey(), ZMPlugin::KEY_ENABLED_SUFFIX) || ZMTools::endsWith(\$value->getKey(), ZMPlugin::KEY_ORDER_SUFFIX))) { continue; } ?>
            <tr>
                <td><?php echo \$value->getName() ?></td>
                <td><?php zm_plugin_value_element(\$value) ?></td>
            </tr>
        <?php } ?>
    </table>
    <input type="submit" value="<?php zm_l10n("Update") ?>">
</form>
EOT;

        // use eval for PHP4 compatibility
        ob_start();
        eval('?>'.$contents);
        $contents = ob_get_clean();

        return ZMLoader::make("PluginPage", $fkts, $title, $contents);
    }

?>
