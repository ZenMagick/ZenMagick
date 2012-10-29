<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @todo session_id = text? index on session_id?
 * @ORM\Table(name="paypal_session")
 * @ORM\Entity
 */
class PaypalSession
{
    /**
     * @var integer $uniqueId
     *
     * @ORM\Column(name="unique_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $uniqueId;

    /**
     * @var string $sessionId
     *
     * @ORM\Column(name="session_id", type="text", nullable=false)
     */
    private $sessionId;

    /**
     * @var string $savedSession
     *
     * @ORM\Column(name="saved_session", type="blob", nullable=false)
     */
    private $savedSession;

    /**
     * @var integer $expiry
     *
     * @ORM\Column(name="expiry", type="integer", nullable=false)
     */
    private $expiry;

}
