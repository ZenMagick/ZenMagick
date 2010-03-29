<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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
 * USEO3 rewriter.
 *
 * @package org.zenmagick.plugins.useo3
 * @author mano
 * @version $Id$
 */
class ZMUseo3SeoRewriter implements ZMSeoRewriter {

    /**
     * {@inheritDoc}
     */
    public function rewrite($request, $args) {
        $requestId = $args['requestId'];
        $params = $args['params'];
        $secure = $args['secure'];
        $addSessionId = $args['addSessionId'];
        $isStatic = $args['isStatic'];
        $useContext = $args['useContext'];

        if ($requestId == 'category') { $requestId = 'index'; }
        if (isset($GLOBALS['SeoUrl']) && (null == ZMSettings::get('plugins.useo3.seoEnabled') || ZMLangUtils::inArray($view, ZMSettings::get('plugins.useo3.seoEnabled')))) {
            // no $seo parameter
            return $GLOBALS['SeoUrl']->buildHrefLink($requestId, $params, $secure ? 'SSL' : 'NONSSL', $addSessionId, $isStatic, $useContext);
        }

        return null;
    }

}
