<?php
/*
 * ZenMagick - Smart e-commerce
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

use zenmagick\base\Runtime;

/**
 * Allow users to toggle ZenMagick themes support.
 *
 * @package org.zenmagick.plugins
 * @author DerManoMann
 */
class ZMToggleThemesPlugin extends Plugin {
    const SESS_THEME_TOGGLE_KEY = 'themeToggle';


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('Toggle themes', 'Allow users to toggle theme support');
        $this->setContext(Plugin::CONTEXT_STOREFRONT);
        $this->setLoaderPolicy(ZMPlugin::LP_NONE);
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        Runtime::getEventDispatcher()->listen($this);
    }

    /**
     * Handle init request.
     */
    public function onInitRequest($event) {
        $request = $event->get('request');
        $session = $request->getSession();

        if (null !== ($themeToggle = $request->getParameter('themeToggle'))) {
            $session->setValue(self::SESS_THEME_TOGGLE_KEY, $themeToggle);
        }

        if (null !== ($themeToggle = $session->getValue(self::SESS_THEME_TOGGLE_KEY))) {
            Runtime::getSettings()->set('isEnableZMThemes', \ZMLangUtils::asBoolean($themeToggle));
        }
    }

    /**
     * Handle contents filter event.
     */
    public function onFinaliseContents($event, $contents) {
        $request = $event->get('request');

        if (false !== strpos($contents, _zm('Toggle ZenMagick theme support'))) {
            // already done
            return $contents;
        }

        $toggleValue = \ZMSettings::get('isEnableZMThemes', true) ? 'false' : 'true';
        $url = $request->url(null, null, $request->isSecure());
        $hasParams = false !== strpos($url, '?');
        $url .= ($hasParams ? '&' : '?') . 'themeToggle='.$toggleValue;
        // special case for ZM_PAGE_KEY=category
        if ('category' == $request->getRequestId()) {
            $url = str_replace(ZM_PAGE_KEY.'=category', ZM_PAGE_KEY.'=index', $url);
        }
        $link = '<a href="'.$url.'">'._zm('Toggle ZenMagick theme support').'</a>';
        $switch = '<div id="theme-toggle" style="text-align:right;padding:2px 8px;">' . $link . '</div>';

        return  preg_replace('/(<body[^>]*>)/', '\1'.$switch, $contents, 1);
    }

}
