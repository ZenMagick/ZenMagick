<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenCartBundle\Entity\ProjectVersion
 *
 * @ORM\Table(name="project_version")
 * @ORM\Entity
 */
class ProjectVersion
{
    /**
     * @var boolean $id
     *
     * @ORM\Column(name="project_version_id", type="boolean", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string versionKey
     *
     * @ORM\Column(name="project_version_key", type="string", length=40, nullable=false)
     */
    private $versionKey;

    /**
     * @var string $versionMajor
     *
     * @ORM\Column(name="project_version_major", type="string", length=20, nullable=false)
     */
    private $versionMajor;

    /**
     * @var string $versionMinor
     *
     * @ORM\Column(name="project_version_minor", type="string", length=20, nullable=false)
     */
    private $versionMinor;

    /**
     * @var string $versionPatch1
     *
     * @ORM\Column(name="project_version_patch1", type="string", length=20, nullable=false)
     */
    private $versionPatch1;

    /**
     * @var string $versionPatch2
     *
     * @ORM\Column(name="project_version_patch2", type="string", length=20, nullable=false)
     */
    private $versionPatch2;

    /**
     * @var string $versionPatch1Source
     *
     * @ORM\Column(name="project_version_patch1_source", type="string", length=20, nullable=false)
     */
    private $versionPatch1Source;

    /**
     * @var string $versionPatch2Source
     *
     * @ORM\Column(name="project_version_patch2_source", type="string", length=20, nullable=false)
     */
    private $versionPatch2Source;

    /**
     * @var string $versionComment
     *
     * @ORM\Column(name="project_version_comment", type="string", length=250, nullable=false)
     */
    private $versionComment;

    /**
     * @var datetime $versionDateApplied
     *
     * @ORM\Column(name="project_version_date_applied", type="datetime", nullable=false)
     */
    private $versionDateApplied;



    /**
     * Get id
     *
     * @return boolean 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set versionKey
     *
     * @param string $versionKey
     * @return ProjectVersion
     */
    public function setVersionKey($versionKey)
    {
        $this->versionKey = $versionKey;
        return $this;
    }

    /**
     * Get versionKey
     *
     * @return string 
     */
    public function getVersionKey()
    {
        return $this->versionKey;
    }

    /**
     * Set versionMajor
     *
     * @param string $versionMajor
     * @return ProjectVersion
     */
    public function setVersionMajor($versionMajor)
    {
        $this->versionMajor = $versionMajor;
        return $this;
    }

    /**
     * Get versionMajor
     *
     * @return string 
     */
    public function getVersionMajor()
    {
        return $this->versionMajor;
    }

    /**
     * Set versionMinor
     *
     * @param string $versionMinor
     * @return ProjectVersion
     */
    public function setVersionMinor($versionMinor)
    {
        $this->versionMinor = $versionMinor;
        return $this;
    }

    /**
     * Get versionMinor
     *
     * @return string 
     */
    public function getVersionMinor()
    {
        return $this->versionMinor;
    }

    /**
     * Set versionPatch1
     *
     * @param string $versionPatch1
     * @return ProjectVersion
     */
    public function setVersionPatch1($versionPatch1)
    {
        $this->versionPatch1 = $versionPatch1;
        return $this;
    }

    /**
     * Get versionPatch1
     *
     * @return string 
     */
    public function getVersionPatch1()
    {
        return $this->versionPatch1;
    }

    /**
     * Set versionPatch2
     *
     * @param string $versionPatch2
     * @return ProjectVersion
     */
    public function setVersionPatch2($versionPatch2)
    {
        $this->versionPatch2 = $versionPatch2;
        return $this;
    }

    /**
     * Get versionPatch2
     *
     * @return string 
     */
    public function getVersionPatch2()
    {
        return $this->versionPatch2;
    }

    /**
     * Set versionPatch1Source
     *
     * @param string $versionPatch1Source
     * @return ProjectVersion
     */
    public function setVersionPatch1Source($versionPatch1Source)
    {
        $this->versionPatch1Source = $versionPatch1Source;
        return $this;
    }

    /**
     * Get versionPatch1Source
     *
     * @return string 
     */
    public function getVersionPatch1Source()
    {
        return $this->versionPatch1Source;
    }

    /**
     * Set versionPatch2Source
     *
     * @param string $versionPatch2Source
     * @return ProjectVersion
     */
    public function setVersionPatch2Source($versionPatch2Source)
    {
        $this->versionPatch2Source = $versionPatch2Source;
        return $this;
    }

    /**
     * Get versionPatch2Source
     *
     * @return string 
     */
    public function getVersionPatch2Source()
    {
        return $this->versionPatch2Source;
    }

    /**
     * Set versionComment
     *
     * @param string $versionComment
     * @return ProjectVersion
     */
    public function setVersionComment($versionComment)
    {
        $this->versionComment = $versionComment;
        return $this;
    }

    /**
     * Get versionComment
     *
     * @return string 
     */
    public function getVersionComment()
    {
        return $this->versionComment;
    }

    /**
     * Set versionDateApplied
     *
     * @param datetime $versionDateApplied
     * @return ProjectVersion
     */
    public function setVersionDateApplied($versionDateApplied)
    {
        $this->versionDateApplied = $versionDateApplied;
        return $this;
    }

    /**
     * Get versionDateApplied
     *
     * @return datetime 
     */
    public function getVersionDateApplied()
    {
        return $this->versionDateApplied;
    }
}