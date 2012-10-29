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
namespace ZenMagick\StoreBundle\Entity\Locale;

use ZenMagick\Base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * A single language.
 *
 * @author DerManoMann
 * @ORM\Table(name="languages",
 *  indexes={
 *      @ORM\Index(name="idx_languages_name_zen", columns={"name"})
 *  })
 * @ORM\Entity
 */
class Language extends ZMObject {
    /**
     * @var integer $languageId
     *
     * @ORM\Column(name="languages_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $languageId;
    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     */
    private $name;
    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=2, nullable=false)
     */
    private $code;
    /**
     * @var string $image
     *
     * @ORM\Column(name="image", type="string", length=64, nullable=true)
     */
    private $image;
    /**
     * @var string $directory
     *
     * @ORM\Column(name="directory", type="string", length=32, nullable=true)
     */
    private $directory;
    /**
     * @var integer $sortOrder
     *
     * @ORM\Column(name="sort_order", type="smallint", nullable=true)
     */
    private $sortOrder;

    // @todo deprecated, but needed until we change the property name
    public function getLanguageId() { return $this->getId(); }

    /**
     * Get the language id.
     *
     * @return int $id The language id.
     */
    public function getId() { return $this->languageId; }

    /**
     * Get the language name.
     *
     * @return string $name The language name.
     */
    public function getName() { return $this->name; }

    /**
     * Get the language image.
     *
     * @return string $image The language image.
     */
    public function getImage() { return $this->image; }

    /**
     * Get the language code.
     *
     * @return string $code The language code.
     */
    public function getCode() { return $this->code; }

    /**
     * Get the language directory name.
     *
     * @return string $directory The language directory name.
     */
    public function getDirectory() { return $this->directory; }

    /**
     * Get the language sort order.
     *
     * @return integer $sortOrder
     */
    public function getSortOrder() { return $this->sortOrder; }

    /**
     * Set the language id.
     *
     * @param int $id The language id.
     */
    public function setId($id) { $this->languageId = $id; }

    // @todo doctrine deprecated
    public function setLanguageId($id) { $this->setId($id); }

    /**
     * Set the language name.
     *
     * @param string $name The language name.
     */
    public function setName($name) { $this->name = $name; }

    /**
     * Set the language image.
     *
     * @param string $image The language image.
     */
    public function setImage($image) { $this->image = $image; }

    /**
     * Set the language code.
     *
     * @param string $code The language code.
     */
    public function setCode($code) { $this->code = $code; }

    /**
     * Set the language directory name.
     *
     * @param string $directory The language directory name.
     */
    public function setDirectory($directory) { $this->directory = $directory; }

    /**
     * Set the language sort order.
     *
     * @param integer $sortOrder
     */
    public function setSortOrder($sortOrder) { $this->sortOrder = $sortOrder; }
}
