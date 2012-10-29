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

namespace ZenMagick\StoreBundle\Entity\Templating;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="banners_history",
 *  indexes={
 *      @ORM\Index(name="idx_banners_id_zen", columns={"banners_id"}),
 *  })
 * @ORM\Entity
 */
class BannerHistory
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="banners_history_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $bannerId
     *
     * @ORM\Column(name="banners_id", type="integer", nullable=false)
     */
    private $bannerId;

    /**
     * @var integer $shown
     *
     * @ORM\Column(name="banners_shown", type="integer", nullable=false)
     */
    private $shown;

    /**
     * @var integer $clicked
     *
     * @ORM\Column(name="banners_clicked", type="integer", nullable=false)
     */
    private $clicked;

    /**
     * @var \DateTime $historyDate
     *
     * @ORM\Column(name="banners_history_date", type="datetime", nullable=false)
     */
    private $historyDate;

}
