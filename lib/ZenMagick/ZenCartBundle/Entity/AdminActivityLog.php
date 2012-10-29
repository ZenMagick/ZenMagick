<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenCartBundle\Entity\AdminActivityLog
 *
 * @ORM\Table(name="admin_activity_log",
 *  indexes={
 *      @ORM\Index(name="idx_page_accessed_zen", columns={"page_accessed"}),
 *      @ORM\Index(name="idx_access_date_zen", columns={"access_date"}),
 *      @ORM\Index(name="idx_ip_zen", columns={"ip_address"}),
 *      @ORM\Index(name="idx_flagged_zen", columns={"flagged"})
 *  })
 * @ORM\Entity
 */
class AdminActivityLog
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="log_id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var datetime $accessDate
     *
     * @ORM\Column(name="access_date", type="datetime", nullable=false)
     */
    private $accessDate;

    /**
     * @var integer $adminId
     *
     * @ORM\Column(name="admin_id", type="integer", nullable=false)
     */
    private $adminId;

    /**
     * @var string $pageAccessed
     *
     * @ORM\Column(name="page_accessed", type="string", length=80, nullable=false)
     */
    private $pageAccessed;

    /**
     * @var text $pageParameters
     *
     * @ORM\Column(name="page_parameters", type="string", length=1024, nullable=true)
     */
    private $pageParameters;

    /**
     * @var string $ipAddress
     *
     * @ORM\Column(name="ip_address", type="string", length=45, nullable=false)
     */
    private $ipAddress;

    /**
     * @var boolean $flagged
     *
     * @ORM\Column(name="flagged", type="boolean", nullable=false)
     */
    private $flagged;

    /**
     * @var string $attention
     *
     * @ORM\Column(name="attention", type="string", length=255, nullable=false)
     */
    private $attention;

    /**
     * @var string $gzPost
     *
     * @ORM\Column(name="gzpost", type="text")
     */
    private $gzPost;

    public function __construct()
    {
        $this->adminId = 0;
    }

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
     * Set accessDate
     *
     * @param  datetime         $accessDate
     * @return AdminActivityLog
     */
    public function setAccessDate($accessDate)
    {
        $this->accessDate = $accessDate;
        return $this;
    }

    /**
     * Get accessDate
     *
     * @return datetime
     */
    public function getAccessDate()
    {
        return $this->accessDate;
    }

    /**
     * Set adminId
     *
     * @param  integer          $adminId
     * @return AdminActivityLog
     */
    public function setAdminId($adminId)
    {
        $this->adminId = $adminId;
        return $this;
    }

    /**
     * Get adminId
     *
     * @return integer
     */
    public function getAdminId()
    {
        return $this->adminId;
    }

    /**
     * Set pageAccessed
     *
     * @param  string           $pageAccessed
     * @return AdminActivityLog
     */
    public function setPageAccessed($pageAccessed)
    {
        $this->pageAccessed = $pageAccessed;
        return $this;
    }

    /**
     * Get pageAccessed
     *
     * @return string
     */
    public function getPageAccessed()
    {
        return $this->pageAccessed;
    }

    /**
     * Set pageParameters
     *
     * @param  text             $pageParameters
     * @return AdminActivityLog
     */
    public function setPageParameters($pageParameters)
    {
        $this->pageParameters = $pageParameters;
        return $this;
    }

    /**
     * Get pageParameters
     *
     * @return text
     */
    public function getPageParameters()
    {
        return $this->pageParameters;
    }

    /**
     * Set ipAddress
     *
     * @param  string           $ipAddress
     * @return AdminActivityLog
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set flagged
     *
     * @param  boolean          $flagged
     * @return AdminActivityLog
     */
    public function setFlagged($flagged)
    {
        $this->flagged = $flagged;

        return $this;
    }

    /**
     * Get flagged
     *
     * @return boolean
     */
    public function getFlagged()
    {
        return $this->flagged;
    }

    /**
     * Set attention
     *
     * @param  string           $attention
     * @return AdminActivityLog
     */
    public function setAttention($attention)
    {
        $this->attention = $attention;

        return $this;
    }

    /**
     * Get attention
     *
     * @return string
     */
    public function getAttention()
    {
        return $this->attention;
    }

    /**
     * Set gzPost
     *
     * @param  string           $gzPost
     * @return AdminActivityLog
     */
    public function setGzPost($gzPost)
    {
        $this->gzPost = $gzPost;

        return $this;
    }

    /**
     * Get gzPost
     *
     * @return string
     */
    public function getGzPost()
    {
        return $this->gzPost;
    }
}
