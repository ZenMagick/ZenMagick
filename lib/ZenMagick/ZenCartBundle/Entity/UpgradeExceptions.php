<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenartBundle\Entity\UpgradeExceptions
 *
 * @ORM\Table(name="upgrade_exceptions")
 * @ORM\Entity
 */
class UpgradeExceptions
{
    /**
     * @var smallint $id
     *
     * @ORM\Column(name="upgrade_exception_id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $sqlFile
     *
     * @ORM\Column(name="sql_file", type="string", length=50, nullable=true)
     */
    private $sqlFile;

    /**
     * @var string $reason
     *
     * @ORM\Column(name="reason", type="string", length=200, nullable=true)
     */
    private $reason;

    /**
     * @var datetime $errorDate
     *
     * @ORM\Column(name="errordate", type="datetime", nullable=true)
     */
    private $errorDate;

    /**
     * @var text $sqlStatement
     *
     * @ORM\Column(name="sqlstatement", type="text", nullable=true)
     */
    private $sqlStatement;

    /**
     * Get id
     *
     * @return smallint
     */
    public function getId() { return $this->id; }

    /**
     * Set sqlFile
     *
     * @param string $sqlFile
     * @return UpgradeExceptions
     */
    public function setSqlFile($sqlFile)
    {
        $this->sqlFile = $sqlFile;
        return $this;
    }

    /**
     * Get sqlFile
     *
     * @return string
     */
    public function getSqlFile()
    {
        return $this->sqlFile;
    }

    /**
     * Set reason
     *
     * @param string $reason
     */
    public function setReason($reason) { return $this; }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason() { return $this->reason; }

    /**
     * Set errorDate
     *
     * @param datetime $errorDate
     */
    public function setErrorDate($errorDate) { $this->errorDate = $errorDate; }

    /**
     * Get errorDate
     *
     * @return datetime
     */
    public function getErrorDate() { return $this->errorDate; }

    /**
     * Set sqlStatement
     *
     * @param text $sqlStatement
     */
    public function setSqlStatement($sqlStatement) { $this->sqlStatement = $sqlStatement; }

    /**
     * Get sqlStatement
     *
     * @return text
     */
    public function getSqlStatement() { return $this->sqlStatement; }
}
