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
namespace ZenMagick\AdminBundle\Dashboard\Widgets\Status;

use ZenMagick\Base\ZMObject;
use ZenMagick\StoreBundle\Widgets\StatusCheck;

/**
 * Installation status check.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class InstallationStatusCheck extends ZMObject implements StatusCheck
{
    /**
     * {@inheritDoc}
     */
    public function getStatusMessages()
    {
        $messages = array();
        $zcPath = $this->container->getParameter('zencart.root_dir');
        $translator = $this->container->get('translator');
        $installDir = $zcPath.'/zc_install';
        if (is_dir($installDir)) {
            $message = $translator->trans('Installation directory exists at: %install_dir%. Please remove this directory for security reasons.', array('%install_dir%' => $installDir));
            $messages[] = array(StatusCheck::STATUS_NOTICE, $message);
        }

        return $messages;
    }

}
