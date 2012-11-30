<?php
/*
 * ZenMagick - Another PHP framework.
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

namespace ZenMagick\ZenCartBundle\Compat;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * ZenCart admin messageStack wrapper
 */
class AdminMessageStack
{
    private $flashBag;

    /**
     * @param FlashBagInterface
     */
    public function __construct(FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
    }

    public function add($message, $type = 'error')
    {
        $type = in_array($type, array('caution', 'warning')) ? 'warn' : $type;
        $this->flashBag->addMessage($message, $type);
    }

    public function add_session($message, $type = 'error')
    {
        $this->add($message, $type);
    }
    public function reset()
    {
    }
}
