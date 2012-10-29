<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenCartBundle\Entity\QueryBuilder
 *
 * @ORM\Table(name="query_builder")
 * @ORM\Entity
 */
class QueryBuilder
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="query_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $category
     *
     * @ORM\Column(name="query_category", type="string", length=40, nullable=false)
     */
    private $category;

    /**
     * @var string $queryName
     *
     * @ORM\Column(name="query_name", type="string", length=80, nullable=false, unique=true)
     */
    private $queryName;

    /**
     * @var text $description
     *
     * @ORM\Column(name="query_description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var text $queryString
     *
     * @ORM\Column(name="query_string", type="text", nullable=false)
     */
    private $queryString;

    /**
     * @var text $keysList
     *
     * @ORM\Column(name="query_keys_list", type="text", nullable=false)
     */
    private $keysList;

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
     * Set category
     *
     * @param  string       $category
     * @return QueryBuilder
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set queryName
     *
     * @param  string       $queryName
     * @return QueryBuilder
     */
    public function setQueryName($queryName)
    {
        $this->queryName = $queryName;
        return $this;
    }

    /**
     * Get queryName
     *
     * @return string
     */
    public function getQueryName()
    {
        return $this->queryName;
    }

    /**
     * Set description
     *
     * @param  text         $description
     * @return QueryBuilder
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return text
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set queryString
     *
     * @param  text         $queryString
     * @return QueryBuilder
     */
    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
        return $this;
    }

    /**
     * Get queryString
     *
     * @return text
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * Set keysList
     *
     * @param  text         $keysList
     * @return QueryBuilder
     */
    public function setKeysList($keysList)
    {
        $this->keysList = $keysList;
        return $this;
    }

    /**
     * Get keysList
     *
     * @return text
     */
    public function getKeysList()
    {
        return $this->keysList;
    }
}
