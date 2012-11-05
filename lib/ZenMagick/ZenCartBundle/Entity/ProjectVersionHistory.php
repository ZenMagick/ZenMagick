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


}
