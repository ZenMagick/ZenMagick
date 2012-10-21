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
namespace ZenMagick\StorefrontBundle\Http\Sacs;

use ZenMagick\StoreBundle\Entity\Account\Account;
use ZenMagick\Base\ZMObject;
use ZenMagick\Http\Sacs\SacsHandler;

/**
 * Handle access control and security mappings.
 *
 * <p>Access control mappings define the level of authentication required for resources.
 * Resources in this context are controller or page requests.</p>
 *
 * <p>Controller/resources marked as secure will be enforcer by redirects using SSL (if configured), if
 * non secure HTTP is used to access them.</p>
 *
 * @author DerManoMann
 */
class StorefrontAccountSacsHandler extends ZMObject implements SacsHandler {
    private $levelMap_;


    /**
     * Create new instance.
     */
    public function __construct() {
        // which level allows what
        $this->levelMap_ = array(
            Account::ANONYMOUS => array(Account::ANONYMOUS, Account::GUEST, Account::REGISTERED),
            Account::GUEST => array(Account::GUEST, Account::REGISTERED),
            Account::REGISTERED => array(Account::REGISTERED)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName() {
        return get_class();
    }


    /**
     * {@inheritDoc}
     */
    public function evaluate($requestId, $credentials, $manager) {
        $requiredLevel = $manager->getMappingValue($requestId, 'level', $this->container->get('settingsService')->get('apps.store.defaultAccessLevel'));
        if (null == $requiredLevel || Account::ANONYMOUS == $requiredLevel) {
            return true;
        }

        if (null == $credentials || !($credentials instanceof Account)) {
            return null;
        }

        $level = Account::ANONYMOUS;
        if (null != $credentials && $credentials instanceof Account) {
            $level = $credentials->getType();
        }

        if (!in_array($level, $this->levelMap_[$requiredLevel])) {
            $this->container->get('logger')->debug('missing authorization for '.$requestId.'; current='.$level.', required='.$requiredLevel);
            return false;
        }

        return true;
    }

}
