<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 */
?>
<?php


/**
 * Plugin view that generates a (view-less) simple form based on all plugin settings.
 *
 * @author DerManoMann
 * @package zenmagick.store.shared.services.plugins
 */
class ZMSimplePluginFormView extends ZMView {
    private $plugin_;
    private $function_;


    /**
     * Create instance.
     *
     * @param mixed plugin The parent plugin.
     * @param string function The function/controller name.
     */
    function __construct($plugin, $function) {
        parent::__construct();
        $this->setPlugin($plugin);
        $this->function_ = $function;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the plugin.
     *
     * @param mixed plugin A <code>ZMPlugin</code> instance or plugin id.
     */
    public function setPlugin($plugin) { 
        $this->plugin_ = $plugin;
    }

    /**
     * Get the plugin.
     *
     * @return ZMPlugin The plugin.
     */
    public function getPlugin() {
        if (!is_object($this->plugin_)) {
            $this->plugin_ = ZMPlugins::instance()->getPluginForId($this->plugin_);
        }

        return $this->plugin_;
    }

    /**
     * Generate form element for the given config value.
     *
     * @param ZMRequest request The current request.
     * @param mixed value The value; can be a <code>ZMWidget</code> or <code>ZMConfigValue</code>.
     * @return string HTML code.
     */
    protected function valueElement($request, $value) {
        if ($value instanceof ZMWidget) {
            return $value->render($request);
        } else if ($value->hasSetFunction()) {
            eval('$set = ' . $value->getSetFunction() . "'" . $value->getValue() . "', '" . $value->getKey() . "');");
            return str_replace('<br>', '', $set);
        } else {
            throw new ZMException('invalid element');
        }
    }


    /**
     * Create a simple plugin config form.
     *
     * @param ZMRequest request The current request.
     * @param string function The view function.
     * @param string title Optional title; default is <code>null</code> to use the plugin name.
     * @param boolean all Allows to exclude the common values (status/sort order); default is <code>true</code> to show all.
     * @return ZMPluginPage The plugin page instance.
     */
    protected function generateSimpleConfigForm($request, $function, $title=null, $all=true) {
        $plugin = $this->getPlugin();
        $title = null == $title ? $this->getPlugin()->getName() : $title;
        $title = _zm($title);
        $contents = <<<EOT
<h2><?php echo \$title ?></h2>
<form action="<?php \$request->getToolbox()->admin->url() ?>" method="POST">
    <table cellspacing="0" cellpadding="0" id="plugin-config">
        <?php foreach (\$plugin->getConfigValues() as \$value) { ?>
            <?php if (!\$all && (ZMLangUtils::endsWith(\$value->getKey(), Plugin::KEY_ENABLED) || ZMLangUtils::endsWith(\$value->getKey(), Plugin::KEY_SORT_ORDER))) { continue; } ?>
            <?php if (!\$all && (\$value->getName() == Plugin::KEY_ENABLED || \$value->getName() == Plugin::KEY_SORT_ORDER)) { continue; } ?>
            <tr>
                <td><?php echo \$value->getName() ?></td>
                <td><?php echo \$this->valueElement(\$request, \$value) ?></td>
            </tr>
        <?php } ?>
    </table>
    <input type="hidden" name="fkt" value="<?php echo \$function ?>">
    <input type="submit" value="<?php echo _zm("Update") ?>">
</form>
EOT;
        // XXX: use eval for PHP4 compatibility
        ob_start();
        eval('?>'.$contents);
        $contents = ob_get_clean();

        return $contents;
    }

    /**
     * {@inheritDoc}
     */
    public function generate($request) {
        return $this->generateSimpleConfigForm($request, $this->function_, null, false);
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($request, $template) {
        throw new ZMException('not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function exists($request, $template, $type=ZMView::TEMPLATE) {
        // always exists
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function asUrl($request, $template, $type=ZMView::TEMPLATE) {
        throw new ZMException('not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function path($filename, $type=ZMView::TEMPLATE) {
        throw new ZMException('not supported');
    }

}
