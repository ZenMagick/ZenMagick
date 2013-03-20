<?php

namespace ZenMagick\ZenCartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZenMagick\ZenCartBundle\Entity\QueryBuilder
 *
 * @ORM\Table(name="query_builder",
 *  uniqueConstraints={@ORM\UniqueConstraint(name="idx_name",columns={"query_name"})}
 * )
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
     * @var string $name
     *
     * @ORM\Column(name="query_name", type="string", length=80, nullable=false, unique=true)
     */
    private $name;

    /**
     * @var text $description
     *
     * @ORM\Column(name="query_description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var text $query
     *
     * @ORM\Column(name="query_string", type="text", nullable=false)
     */
    private $query;

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
     * Set name
     *
     * @param  string       $name
     * @return QueryBuilder
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Set query
     *
     * @param  text         $query
     * @return QueryBuilder
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get query
     *
     * @return text
     */
    public function getQuery()
    {
        return $this->query;
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
