<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenCartBundle\Entity\FilesUploaded
 *
 * @ORM\Table(name="files_uploaded",
 *  indexes={
 *      @ORM\Index(name="idx_customers_id_zen", columns={"customers_id"}),
 *  })
 * @ORM\Entity
 */
class FilesUploaded
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="files_uploaded_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $sessKey
     *
     * @ORM\Column(name="sesskey", type="string", length=32, nullable=true)
     */
    private $sessKey;

    /**
     * @var integer $accountId
     *
     * @ORM\Column(name="customers_id", type="integer", nullable=true)
     */
    private $accounId;

    /**
     * @var string $fileName
     *
     * @ORM\Column(name="files_uploaded_name", type="string", length=64, nullable=false)
     */
    private $fileName;

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
     * Set sessKey
     *
     * @param  string        $sessKey
     * @return FilesUploaded
     */
    public function setSessKey($sessKey)
    {
        $this->sessKey = $sessKey;
        return $this;
    }

    /**
     * Get sessKey
     *
     * @return string
     */
    public function getSessKey()
    {
        return $this->sessKey;
    }

    /**
     * Set accounId
     *
     * @param  integer       $accounId
     * @return FilesUploaded
     */
    public function setAccounId($accounId)
    {
        $this->accounId = $accounId;
        return $this;
    }

    /**
     * Get accounId
     *
     * @return integer
     */
    public function getAccounId()
    {
        return $this->accounId;
    }

    /**
     * Set fileName
     *
     * @param  string        $fileName
     * @return FilesUploaded
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }
}
