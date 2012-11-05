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


}
