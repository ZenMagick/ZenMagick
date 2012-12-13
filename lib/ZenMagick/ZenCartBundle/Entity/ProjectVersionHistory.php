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
     * @var smallint $projectVersionId
     *
     * @ORM\Column(name="project_version_id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $projectVersionId;

    /**
     * @var string $projectVersionKey
     *
     * @ORM\Column(name="project_version_key", type="string", length=40, nullable=false)
     */
    private $projectVersionKey;

    /**
     * @var string $projectVersionMajor
     *
     * @ORM\Column(name="project_version_major", type="string", length=20, nullable=false)
     */
    private $projectVersionMajor;

    /**
     * @var string $projectVersionMinor
     *
     * @ORM\Column(name="project_version_minor", type="string", length=20, nullable=false)
     */
    private $projectVersionMinor;

    /**
     * @var string $projectVersionPatch
     *
     * @ORM\Column(name="project_version_patch", type="string", length=20, nullable=false)
     */
    private $projectVersionPatch;

    /**
     * @var string $projectVersionComment
     *
     * @ORM\Column(name="project_version_comment", type="string", length=250, nullable=false)
     */
    private $projectVersionComment;

    /**
     * @var \DateTime $projectVersionDateApplied
     *
     * @ORM\Column(name="project_version_date_applied", type="datetime", nullable=false)
     */
    private $projectVersionDateApplied;

    public function __construct()
    {
        $this->projectVersionDateApplied = new \DateTime();
    }

    /**
     * Get projectVersionId
     *
     * @return integer
     */
    public function getProjectVersionId()
    {
        return $this->projectVersionId;
    }

    /**
     * Set projectVersionKey
     *
     * @param  string                $projectVersionKey
     * @return ProjectVersionHistory
     */
    public function setProjectVersionKey($projectVersionKey)
    {
        $this->projectVersionKey = $projectVersionKey;

        return $this;
    }

    /**
     * Get projectVersionKey
     *
     * @return string
     */
    public function getProjectVersionKey()
    {
        return $this->projectVersionKey;
    }

    /**
     * Set projectVersionMajor
     *
     * @param  string                $projectVersionMajor
     * @return ProjectVersionHistory
     */
    public function setProjectVersionMajor($projectVersionMajor)
    {
        $this->projectVersionMajor = $projectVersionMajor;

        return $this;
    }

    /**
     * Get projectVersionMajor
     *
     * @return string
     */
    public function getProjectVersionMajor()
    {
        return $this->projectVersionMajor;
    }

    /**
     * Set projectVersionMinor
     *
     * @param  string                $projectVersionMinor
     * @return ProjectVersionHistory
     */
    public function setProjectVersionMinor($projectVersionMinor)
    {
        $this->projectVersionMinor = $projectVersionMinor;

        return $this;
    }

    /**
     * Get projectVersionMinor
     *
     * @return string
     */
    public function getProjectVersionMinor()
    {
        return $this->projectVersionMinor;
    }

    /**
     * Set projectVersionPatch
     *
     * @param  string                $projectVersionPatch
     * @return ProjectVersionHistory
     */
    public function setProjectVersionPatch($projectVersionPatch)
    {
        $this->projectVersionPatch = $projectVersionPatch;

        return $this;
    }

    /**
     * Get projectVersionPatch
     *
     * @return string
     */
    public function getProjectVersionPatch()
    {
        return $this->projectVersionPatch;
    }

    /**
     * Set projectVersionComment
     *
     * @param  string                $projectVersionComment
     * @return ProjectVersionHistory
     */
    public function setProjectVersionComment($projectVersionComment)
    {
        $this->projectVersionComment = $projectVersionComment;

        return $this;
    }

    /**
     * Get projectVersionComment
     *
     * @return string
     */
    public function getProjectVersionComment()
    {
        return $this->projectVersionComment;
    }

    /**
     * Set projectVersionDateApplied
     *
     * @param  \DateTime             $projectVersionDateApplied
     * @return ProjectVersionHistory
     */
    public function setProjectVersionDateApplied(\DateTime $projectVersionDateApplied)
    {
        $this->projectVersionDateApplied = $projectVersionDateApplied;

        return $this;
    }

    /**
     * Get projectVersionDateApplied
     *
     * @return \DateTime
     */
    public function getProjectVersionDateApplied()
    {
        return $this->projectVersionDateApplied;
    }
}
