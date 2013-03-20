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
 * ZenMagick\StoreBundle\Entity\EmailArchive
 *
 * @ORM\Table(name="email_archive",
 *   indexes={
 *     @ORM\Index(name="idx_email_to_address_zen", columns={"email_to_address"}),
 *     @ORM\Index(name="idx_module_zen", columns={"module"})
 * })
 * @ORM\Entity
 */
class EmailArchive
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="archive_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $emailToName
     *
     * @ORM\Column(name="email_to_name", type="string", length=96, nullable=false)
     */
    private $toName;

    /**
     * @var string $toAddress
     *
     * @ORM\Column(name="email_to_address", type="string", length=96, nullable=false)
     */
    private $toAddress;

    /**
     * @var string $fromName
     *
     * @ORM\Column(name="email_from_name", type="string", length=96, nullable=false)
     */
    private $fromName;

    /**
     * @var string $fromAddress
     *
     * @ORM\Column(name="email_from_address", type="string", length=96, nullable=false)
     */
    private $fromAddress;

    /**
     * @var string $subject
     *
     * @ORM\Column(name="email_subject", type="string", length=255, nullable=false)
     */
    private $subject;

    /**
     * @var string $html
     *
     * @ORM\Column(name="email_html", type="text", nullable=false)
     */
    private $html;

    /**
     * @var string $text
     *
     * @ORM\Column(name="email_text", type="text", nullable=false)
     */
    private $text;

    /**
     * @var \DateTime $dateSent
     *
     * @ORM\Column(name="date_sent", type="datetime", nullable=false)
     */
    private $dateSent;

    /**
     * @var string $module
     *
     * @ORM\Column(name="module", type="string", length=64, nullable=false)
     */
    private $module;

}
