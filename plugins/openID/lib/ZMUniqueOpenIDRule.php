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


/**
 * Check for unique openID.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.plugins.openID
 */
class ZMUniqueOpenIDRule extends ZMRule {

    /**
     * Create new required rule.
     *
     * @param string name The field name.
     * @param string msg Optional message.
     */
    public function __construct($name, $msg=null) {
        parent::__construct($name, "OpenID already in use.", $msg);
    }


    /**
     * {@inheritDoc}
     */
    public function validate($request, $data) {
        $plugin = $this->container->get('pluginService')->getPluginForId('openID');
        $openid = $data[$this->getName()];
        $idExists = null != $plugin->getAccountForOpenID($openid);
        // empty or doesn't exist or exists but same as current account (account update)
        return empty($openid) || !$idExists || $openid == $request->getAccount()->get('openid');
    }


    /**
     * {@inheritDoc}
     */
    public function toJSString() {
        return '';
    }

}
