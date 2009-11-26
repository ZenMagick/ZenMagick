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
 */
?>
<?php

/**
 * Allow users to toggle ZenMagick themes.
 *
 * @package org.zenmagick.plugins
 * @author DerManoMann
 * @version $Id: zm_toggle_zm_themes.php 2560 2009-11-02 20:08:36Z dermanomann $
 */
class ZMPluginToggleZMThemes extends Plugin {
    const SESS_THEME_TOGGLE_KEY = 'themeToggle';


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Toggle themes', 'Allow users to toggle theme support');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Init this plugin.
     */
    public function init() {
        parent::init();

        ZMEvents::instance()->attach($this);

        $session = ZMRequest::instance()->getSession();
        if (null != ($themeToggle = ZMRequest::instance()->getParameter('themeToggle'))) {
            $session->setValue(self::SESS_THEME_TOGGLE_KEY, $themeToggle);
        }

        if (null != ($themeToggle = $session->getValue(self::SESS_THEME_TOGGLE_KEY))) {
            ZMSettings::set('isEnableZMThemes', ZMLangUtils::asBoolean($themeToggle));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function onZMFinaliseContents($args) {
        $request = $args['request'];
        $contents = $args['contents'];

        if (false !== strpos($contents, zm_l10n_get('Toggle ZenMagick theme support'))) {
            // already done
            return null;
        }

        $toggleValue = ZMSettings::get('isEnableZMThemes') ? 'false' : 'true';
        $url = $request->getToolbox()->net->url(null, 'themeToggle='.$toggleValue, ZMRequest::instance()->isSecure(), false);
        // special case for ZM_PAGE_KEY=category
        if ('category' == ZMRequest::instance()->getRequestId()) {
            $url = str_replace(ZM_PAGE_KEY.'=category', ZM_PAGE_KEY.'=index', $url);
        }      
        $link = '<a href="'.$url.'">'.zm_l10n_get('Toggle ZenMagick theme support').'</a>';
        $switch = '<div id="theme-toggle" style="text-align:right;padding:2px 8px;">' . $link . '</div>';

        $args['contents'] = preg_replace('/(<body[^>]*>)/', '\1'.$switch, $contents, 1);
        return $args;
    }

}

?>
