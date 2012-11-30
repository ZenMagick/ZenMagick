<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */

namespace ZenMagick\ZenCartBundle\Compat;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMException;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Cache\QueryCacheProfile;

/**
  * ZenCart database abstraction layer implementation
  *
  * This class relies on an instantiated <code>Doctrine\DBAL\Connection</code> object
  * for everything, including result caching.
  *
  * @author Johnny Robeson
  */
class QueryFactory
{
    /**
     * mysql ext resource often used in admin contributions
     *
     * @var resource $link mysql_ext resource
     */
    public $link;

    private $conn;

    private $hasResultCache = false;

    /**
     * @param Connection a doctrine dbal connection object
     */
    public function __construct(Connection $conn)
    {
        $this->hasResultCache = null != $conn->getConfiguration()->getResultCacheImpl();
        $this->conn = $conn;
    }

    /**
     * Get a ext/mysql resource
     * @param array $params an array of mysql connection optons that match the PDO dsn.
     *                     If you do not specify any parameters. it will use the
     *                     <code>apps.store.database.default</code> settings.
     * @return resource an ext/mysql resource
     */
    public function mysql_connect($params = null) {
        if(!function_exists('mysql_connect')) {
            throw new ZMException('Install `ext/mysql` extension to enable mysql_* functions.');
        }
        $defaults = $this->conn->getParams();
        $params = array_merge($defaults, (array)$params);
        $link = mysql_connect($params['host'], $params['user'], $params['password'], true);

        if (is_resource($link) && mysql_select_db($params['dbname'])) {
            if (!isset($params['charset']) && !empty($params['charset'])) {
                mysql_set_charset($params['charset']);
            }
            $this->link = $link;

            return $this->link;
        } else {
            throw new ZMException(mysql_error(), mysql_errno());
        }
    }

    /**
     * Execute a query.
     *
     * If a query is one of DESCRIBE, SELECT, or SHOW
     * then use DBAL executeQuery otherwise pass it off to
     * DBAL executeUpdate
     *
     * @param string $sql sql query string
     * @param int $limit limit the number of results
     * @param bool $useCache cache the query
     * @param int $cacheTime how long to cache the query for (in seconds)
     * @return object QueryFactoryResult
     */
    public function Execute($sql, $limit = null, $useCache = false, $cacheTime = 0)
    {
        $sql = trim($sql);
        $commandType = strtolower(substr($sql, 0, 3));
        if (!in_array($commandType, array('des', 'sel', 'sho'))) {
            try {

                return  $this->conn->executeUpdate($sql);
            } catch (\PDOException $e) {
               throw new ZMException($e->getMessage(), $e->getCode(), $e);
            }
        }
        if ($limit) $sql .= ' LIMIT ' . $limit;

        $qcp = null;
        if ($useCache && $this->hasResultCache) {
           $qcp =  new QueryCacheProfile($cacheTime, md5($sql));
        }
        try {
            $stmt = $this->conn->executeQuery($sql, array(), array(), $qcp);
        } catch (\PDOException $e) {
            throw new ZMException($e->getMessage(), $e->getCode(), $e);
        }

        $obj = new QueryFactoryResult($stmt);
        $obj->MoveNext();

        return $obj;
    }

    /**
     * Execute a SQL query and return a set of random results
     *
     * This method currently checks for <code>order by rand()</code>
     * in the sql query, and if it doesn't find it, then it will append it.
     *
     * This is different from the previous implementation that got all the results
     * and filtered them after the fact.
     *
     * NOTE: the original method took cache arguments but never implemented them..
     *
     * @param string $sql sql query string
     * @param int $limit limit the number of returned results
     * @param object QueryFactoryResult
     * @todo we could be a lot more strict in detecting <code>ORDER BY RAND()</code>
     *       but it is probably not worth it.
     */
    public function ExecuteRandomMulti($sql, $limit = null, $useCache = false, $cacheTime = 0)
    {
        if (!preg_match('/ORDER\ BY\ RAND/i', $sql)) {
            $sql .=  ' ORDER BY RAND() ';
        }
        return $this->Execute($sql, $limit, $useCache, $cacheTime);
    }

    /**
     * Get meta details about a database table in a format known to ZenCart
     *
     * @param string $table table to get meta details for
     * @return array
     */
    public function metaColumns($table)
    {
        $details = array();
        foreach ($this->conn->getMetaData($table) as $name => $attrs) {
            $meta = new \stdClass();
            $meta->type = $attrs['type'];
            $meta->max_length = isset($attrs['length']) && null != $attrs['length'] ? $attrs['length'] : 3;
            $details[strtoupper($name)] = $meta;
        }

        return $details;
    }

    /**
     * Perform an insert or update with $tableData map
     *
     * @param string $table table to insert/update
     * @param array $tableData
     * @param string $type type of query to perform (insert|update)
     * @param string $filter WHERE ...
     */
    public function perform($table, $tableData, $type = 'insert', $filter = '')
    {
        $data = array();
        $types = array();
        foreach ($tableData as $key => $value) {
            $type = $value['type'];
            if ('noquotestring' == $type) {
                // only supporting the now() function until otherwise necessary
                if (preg_match('/now\(\)/i', $value['value'])) {
                    $type = 'datetime';
                    $value['value'] = new \DateTime;
                } else {
                    $type = 'string';
                }
            }

            $data[$value['fieldName']] = $value['value'];
            $types[] = $type;
        }

        switch (strtolower($type)) {
            case 'insert':
                $this->conn->insert($table, $data, $types);
            break;
            case 'update':
                $filter = str_replace(array(' and ', ' AND '), '|', $filter);
                $filters = explode(' | ', $filter);
                $identifiers = array();
                foreach ($filters as $v) {
                    list($field, $value) = explode(' = ', $v);
                    $identifiers[$field] = $value;
                }
                $this->conn->update($table, $data, $identifiers, $types);
            break;
        }
    }

    /**
     * Bind a value to a type
     *
     * This function doesn't do real bind vars, just replaces :column to value by
     * str_replace and performs some basic XSS protection.
     *
     *
     * @author ZenCart <http://www.zen-cart.com>
     * @copyright ZenCart developers
     * @param mixed $value value to bind
     * @param string $type type of value
     * @return mixed transformed value
     */
    public function getBindVarValue($value, $type)
    {
        $typeArray = explode(':',$type);
        $type = $typeArray[0];
        switch ($type) {
            case 'csv':
            case 'passthru':
                return $value;
            break;
            case 'float':
                return empty($value) ? 0 : $value;
            break;
            case 'integer':
                return (int)$value;
            break;
            case 'string':
                if (isset($typeArray[1])) {
                    $regexp = $typeArray[1];
                }

                return $this->conn->quote($value);
            break;
            case 'noquotestring':
                return $this->prepare_input($value);
            break;
            case 'currency':
            case 'date':
            case 'enum':
                if (isset($typeArray[1])) {
                    $enumArray = explode('|', $typeArray[1]);
                }
                return $this->conn->quote($value);
            case 'regexp':
                $searchArray = array('[', ']', '(', ')', '{', '}', '|', '*', '?', '.', '$', '^');
                foreach ($searchArray as $searchTerm) {
                    $value = str_replace($searchTerm, '\\' . $searchTerm, $value);
                }

                return $this->prepare_input($value);
            default:
                throw new ZMException('var-type undefined: ' . $type . '('.$value.')');
        }
    }

    /**
     * Bind variables to a sql query.
     *
     * @author ZenCart <http://www.zen-cart.com>
     * @copyright ZenCart developers
     *
     * @param string $sql sql query string
     * @param string $param param to bind
     * @param mixed $value value to bind
     * @param string $type type to bind
     * @return string modified sql query
     * @todo attempt to actually bind some of these parameters? It seems a bit more difficult since
     *       bindVar isn't only used for sql, but also generic str cleaning elsewhere.
     */
    public function bindVars($sql, $param, $value, $type)
    {
        $sqlFix = $this->getBindVarValue($value, $type);
        return str_replace($param, $sqlFix, $sql);
    }

    // compatibility wrappers
    public function connect()
    {
        return true;
    }

    public function close()
    {
        $this->conn->close();
    }

    public function get_server_info()
    {
        return $this->conn->getWrappedConnection()->getAttribute(\PDO::ATTR_SERVER_VERSION);
    }

    public function insert_ID()
    {
        return $this->conn->lastInsertId();
    }

    public function prepare_input($string)
    {
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $string);
    }

    public function prepareInput($string)
    {
        return $this->prepare_input($string);
    }

    // @todo could implement these if we need to.
    public function queryCount()
    {
        return 0;
    }

    public function queryTime()
    {
        return 0;
    }
}
