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
 *      @ORM\Index(name="idx_ip_zen", columns={"ip_address"})
 *  })
 * @ORM\Entity
 */
class AdminActivityLog
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="log_id", type="integer", nullable=false)
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
     * @ORM\Column(name="ip_address", type="string", length=15, nullable=false)
     */
    private $ipAddress;

    public function __construct() {
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
     * @param datetime $accessDate
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
     * @param integer $adminId
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
     * @param string $pageAccessed
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
     * @param text $pageParameters
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
     * @param string $ipAddress
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
}
