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

namespace ZenMagick\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="customers_info")
 * @ORM\Entity
 */
class AccountInfo
{
    /**
     * @var integer $accountId
     *
     * @ORM\Column(name="customers_info_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $accountId;

    /**
     * @var \DateTime $lastLogonDate
     *
     * @ORM\Column(name="customers_info_date_of_last_logon", type="datetime", nullable=true)
     */
    private $lastLogonDate;

    /**
     * @var integer $numberOfLogons
     *
     * @ORM\Column(name="customers_info_number_of_logons", type="integer", nullable=true)
     */
    private $numberOfLogons;

    /**
     * @var \DateTime $accountCreateDate
     *
     * @ORM\Column(name="customers_info_date_account_created", type="datetime", nullable=true)
     */
    private $accountCreateDate;

    /**
     * @var \DateTime $lastModifiedDate
     *
     * @ORM\Column(name="customers_info_date_account_last_modified", type="datetime", nullable=true)
     */
    private $lastModifiedDate;

    /**
     * @var integer $globalProductNotifications
     *
     * @ORM\Column(name="global_product_notifications", type="integer", nullable=true)
     */
    private $globalProductNotifications;

}
