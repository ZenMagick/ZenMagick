<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\StoreBundle\Entity\WhosOnline
 *
 * @ORM\Table(name="whos_online",
 *  indexes={
 *      @ORM\Index(name="idx_ip_address_zen", columns={"ip_address"}),
 *      @ORM\Index(name="idx_session_id_zen", columns={"session_id"}),
 *      @ORM\Index(name="idx_customer_id_zen", columns={"customer_id"}),
 *      @ORM\Index(name="idx_time_entry_zen", columns={"time_entry"}),
 *      @ORM\Index(name="idx_time_last_click_zen", columns={"time_last_click"}),
 *      @ORM\Index(name="idx_last_page_url_zen", columns={"last_page_url"}),
 *  })
 * @ORM\Entity
 */
class WhosOnline
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $customerId
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=true)
     */
    private $customerId;

    /**
     * @var string $fullName
     *
     * @ORM\Column(name="full_name", type="string", length=64, nullable=false)
     */
    private $fullName;

    /**
     * @var string $sessionId
     *
     * @ORM\Column(name="session_id", type="string", length=128, nullable=false)
     */
    private $sessionId;

    /**
     * @var string $ipAddress
     *
     * @ORM\Column(name="ip_address", type="string", length=45, nullable=false)
     */
    private $ipAddress;

    /**
     * @var string $timeEntry
     *
     * @ORM\Column(name="time_entry", type="string", length=14, nullable=false)
     */
    private $timeEntry;

    /**
     * @var string $timeLastClick
     *
     * @ORM\Column(name="time_last_click", type="string", length=14, nullable=false)
     */
    private $timeLastClick;

    /**
     * @var string $lastPageUrl
     *
     * @ORM\Column(name="last_page_url", type="string", length=255, nullable=false)
     */
    private $lastPageUrl;

    /**
     * @var text $hostAddress
     *
     * @ORM\Column(name="host_address", type="string", length=512, nullable=false)
     */
    private $hostAddress;

    /**
     * @var string $userAgent
     *
     * @ORM\Column(name="user_agent", type="string", length=255, nullable=false)
     */
    private $userAgent;

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
     * Set customerId
     *
     * @param  integer    $customerId
     * @return WhosOnline
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * Get customerId
     *
     * @return integer
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Set fullName
     *
     * @param  string     $fullName
     * @return WhosOnline
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set sessionId
     *
     * @param  string     $sessionId
     * @return WhosOnline
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get sessionId
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set ipAddress
     *
     * @param  string     $ipAddress
     * @return WhosOnline
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
     * Set timeEntry
     *
     * @param  string     $timeEntry
     * @return WhosOnline
     */
    public function setTimeEntry($timeEntry)
    {
        $this->timeEntry = $timeEntry;

        return $this;
    }

    /**
     * Get timeEntry
     *
     * @return string
     */
    public function getTimeEntry()
    {
        return $this->timeEntry;
    }

    /**
     * Set timeLastClick
     *
     * @param  string     $timeLastClick
     * @return WhosOnline
     */
    public function setTimeLastClick($timeLastClick)
    {
        $this->timeLastClick = $timeLastClick;

        return $this;
    }

    /**
     * Get timeLastClick
     *
     * @return string
     */
    public function getTimeLastClick()
    {
        return $this->timeLastClick;
    }

    /**
     * Set lastPageUrl
     *
     * @param  string     $lastPageUrl
     * @return WhosOnline
     */
    public function setLastPageUrl($lastPageUrl)
    {
        $this->lastPageUrl = $lastPageUrl;

        return $this;
    }

    /**
     * Get lastPageUrl
     *
     * @return string
     */
    public function getLastPageUrl()
    {
        return $this->lastPageUrl;
    }

    /**
     * Set hostAddress
     *
     * @param  text       $hostAddress
     * @return WhosOnline
     */
    public function setHostAddress($hostAddress)
    {
        $this->hostAddress = $hostAddress;

        return $this;
    }

    /**
     * Get hostAddress
     *
     * @return text
     */
    public function getHostAddress()
    {
        return $this->hostAddress;
    }

    /**
     * Set userAgent
     *
     * @param  string     $userAgent
     * @return WhosOnline
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Get userAgent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }
}
