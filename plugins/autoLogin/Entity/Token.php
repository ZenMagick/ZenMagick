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
namespace ZenMagick\plugins\autoLogin\Entity;

use ZenMagick\Base\ZMObject;

use Doctrine\ORM\Mapping AS ORM;

/**
 * Token model class.
 *
 * @author DerManoMann
 * @ORM\Table(name="token")
 * @ORM\Entity
 */
class Token extends ZMObject
{
    /**
     * @var integer $hashId
     *
     * @ORM\Column(name="hash_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $hashId;
    /**
     * @var blob $hash
     *
     * @ORM\Column(name="hash", type="blob", nullable=false)
     */
    private $hash;
    /**
     * @var string $resource
     *
     * @ORM\Column(name="resource", type="string", length=128, nullable=false)
     */
    private $resource;
    /**
     * @var datetime $issued
     *
     * @ORM\Column(name="issued", type="datetime", nullable=false)
     */
    private $issued;
    /**
     * @var datetime $expires
     *
     * @ORM\Column(name="expires", type="datetime", nullable=false)
     */
    private $expires;

    /**
     * Get the id.
     *
     * @return int $id The id.
     */
    public function getId() { return $this->hashId; }

    // @todo deprecated doctrine backwards compatibility
    public function getHashId() { return $this->getId(); }

    /**
     * Get hash
     *
     * @return string $hash
     */
    public function getHash() { return $this->hash; }

    /**
     * Get resource
     *
     * @return string $resource
     */
    public function getResource() { return $this->resource; }

    /**
     * Get issued
     *
     * @return datetime $issued
     */
    public function getIssued() { return $this->issued; }

    /**
     * Get expires
     *
     * @return datetime $expires
     */
    public function getExpires() { return $this->expires; }

    /**
     * Set the id.
     *
     * @deprecated
     * @param int $id The id.
     */
    public function setId($id) { $this->hashId = $id; }

    // @todo deprecated doctrine backwards compatibility
    public function setHashId($id) { $this->setId($id); }

    /**
     * Set hash
     *
     * @param text $hash
     */
    public function setHash($hash) { $this->hash = $hash; }

    /**
     * Set resource
     *
     * @param string $resource
     */
    public function setResource($resource) { $this->resource = $resource; }

    /**
     * Set issued
     *
     * @param datetime $issued
     */
    public function setIssued($issued) { $this->issued = $issued; }

    /**
     * Set expires
     *
     * @param datetime $expires
     */
    public function setExpires($expires) { $this->expires = $expires; }
}
