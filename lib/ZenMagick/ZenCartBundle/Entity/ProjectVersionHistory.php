<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenCartBundle\Entity\ProjectVersionHistory
 *
 * @ORM\Table(name="project_version_history")
 * @ORM\Entity
 */
class ProjectVersionHistory
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="project_version_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $versionKey
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
     * @var string $versionPatch
     *
     * @ORM\Column(name="project_version_patch", type="string", length=20, nullable=false)
     */
    private $versionPatch;

    /**
     * @var string $versionComment
     *
     * @ORM\Column(name="project_version_comment", type="string", length=250, nullable=false)
     */
    private $versionComment;

    /**
     * @var datetime dateApplied
     *
     * @ORM\Column(name="project_version_date_applied", type="datetime", nullable=false)
     */
    private $dateApplied;



    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set versionKey
     *
     * @param string $versionKey
     * @return ProjectVersionHistory
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
     * @return ProjectVersionHistory
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
     * @return ProjectVersionHistory
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
     * Set versionPatch
     *
     * @param string $versionPatch
     * @return ProjectVersionHistory
     */
    public function setVersionPatch($versionPatch)
    {
        $this->versionPatch = $versionPatch;
        return $this;
    }

    /**
     * Get versionPatch
     *
     * @return string 
     */
    public function getVersionPatch()
    {
        return $this->versionPatch;
    }

    /**
     * Set versionComment
     *
     * @param string $versionComment
     * @return ProjectVersionHistory
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
     * Set dateApplied
     *
     * @param datetime $dateApplied
     * @return ProjectVersionHistory
     */
    public function setDateApplied($dateApplied)
    {
        $this->dateApplied = $dateApplied;
        return $this;
    }

    /**
     * Get dateApplied
     *
     * @return datetime 
     */
    public function getDateApplied()
    {
        return $this->dateApplied;
    }
}