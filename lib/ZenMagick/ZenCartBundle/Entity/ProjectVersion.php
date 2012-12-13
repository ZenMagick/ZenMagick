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
     * @var integer $projectVersionId
     *
     * @ORM\Column(name="project_version_id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $projectVersionId;

    /**
     * @var string $projectVersionKey
     *
     * @ORM\Column(name="project_version_key", type="string", unique=true, length=40, nullable=false)
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
     * @var string $projectVersionPatch1
     *
     * @ORM\Column(name="project_version_patch1", type="string", length=20, nullable=false)
     */
    private $projectVersionPatch1;

    /**
     * @var string $projectVersionPatch2
     *
     * @ORM\Column(name="project_version_patch2", type="string", length=20, nullable=false)
     */
    private $projectVersionPatch2;

    /**
     * @var string $projectVersionPatch1Source
     *
     * @ORM\Column(name="project_version_patch1_source", type="string", length=20, nullable=false)
     */
    private $projectVersionPatch1Source;

    /**
     * @var string $projectVersionPatch2Source
     *
     * @ORM\Column(name="project_version_patch2_source", type="string", length=20, nullable=false)
     */
    private $projectVersionPatch2Source;

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
     * @param  string         $projectVersionKey
     * @return ProjectVersion
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
     * @param  string         $projectVersionMajor
     * @return ProjectVersion
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
     * @param  string         $projectVersionMinor
     * @return ProjectVersion
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
     * Set projectVersionPatch1
     *
     * @param  string         $projectVersionPatch1
     * @return ProjectVersion
     */
    public function setProjectVersionPatch1($projectVersionPatch1)
    {
        $this->projectVersionPatch1 = $projectVersionPatch1;

        return $this;
    }

    /**
     * Get projectVersionPatch1
     *
     * @return string
     */
    public function getProjectVersionPatch1()
    {
        return $this->projectVersionPatch1;
    }

    /**
     * Set projectVersionPatch2
     *
     * @param  string         $projectVersionPatch2
     * @return ProjectVersion
     */
    public function setProjectVersionPatch2($projectVersionPatch2)
    {
        $this->projectVersionPatch2 = $projectVersionPatch2;

        return $this;
    }

    /**
     * Get projectVersionPatch2
     *
     * @return string
     */
    public function getProjectVersionPatch2()
    {
        return $this->projectVersionPatch2;
    }

    /**
     * Set projectVersionPatch1Source
     *
     * @param  string         $projectVersionPatch1Source
     * @return ProjectVersion
     */
    public function setProjectVersionPatch1Source($projectVersionPatch1Source)
    {
        $this->projectVersionPatch1Source = $projectVersionPatch1Source;

        return $this;
    }

    /**
     * Get projectVersionPatch1Source
     *
     * @return string
     */
    public function getProjectVersionPatch1Source()
    {
        return $this->projectVersionPatch1Source;
    }

    /**
     * Set projectVersionPatch2Source
     *
     * @param  string         $projectVersionPatch2Source
     * @return ProjectVersion
     */
    public function setProjectVersionPatch2Source($projectVersionPatch2Source)
    {
        $this->projectVersionPatch2Source = $projectVersionPatch2Source;

        return $this;
    }

    /**
     * Get projectVersionPatch2Source
     *
     * @return string
     */
    public function getProjectVersionPatch2Source()
    {
        return $this->projectVersionPatch2Source;
    }

    /**
     * Set projectVersionComment
     *
     * @param  string         $projectVersionComment
     * @return ProjectVersion
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
     * @param  \DateTime      $projectVersionDateApplied
     * @return ProjectVersion
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
