<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenCartBundle\Entity\GetTermsToFilter
 *
 * @ORM\Table(name="get_terms_to_filter")
 * @ORM\Entity
 */
class GetTermsToFilter
{
    /**
     * @var string termName
     *
     * @ORM\Column(name="get_term_name", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $termName;

    /**
     * @var string $termTable
     *
     * @ORM\Column(name="get_term_table", type="string", length=64, nullable=false)
     */
    private $termTable;

    /**
     * @var string $termField
     *
     * @ORM\Column(name="get_term_name_field", type="string", length=64, nullable=false)
     */
    private $termField;

    /**
     * Get termName
     *
     * @return string
     */
    public function getTermName()
    {
        return $this->termName;
    }

    /**
     * Set termTable
     *
     * @param string $termTable
     * @return GetTermsToFilter
     */
    public function setTermTable($termTable)
    {
        $this->termTable = $termTable;
        return $this;
    }

    /**
     * Get termTable
     *
     * @return string
     */
    public function getTermTable()
    {
        return $this->termTable;
    }

    /**
     * Set termField
     *
     * @param string $termField
     * @return GetTermsToFilter
     */
    public function setTermField($termField)
    {
        $this->termField = $termField;
        return $this;
    }

    /**
     * Get termField
     *
     * @return string
     */
    public function getTermField()
    {
        return $this->termField;
    }
}
