<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Runtime;
use Doctrine\DBAL\Cache\QueryCacheProfile;

/**
  * ZenCart database abstraction layer implementation
  *
  * This class relies on an instantiated <code>Doctrine\DBAL\Connection</code> object
  * for everything, including result caching.
  *
  * @author Johnny Robeson
  */
class queryFactory {
    private $hasResultCache = false;

    public function __construct() {
        $this->hasResultCache = null != ZMRuntime::getDatabase()->getConfiguration()->getResultCacheImpl();
    }

    /** 
     * Get a ext/mysql resource
     * @param array $params an array of mysql connection optons that match the PDO dsn.
     *                     If you do not specify any parameters. it will use the
     *                     <code>apps.store.database.default</code> settings.
     * @return resource an ext/mysql resource
     */
    public static function mysql_connect($params = null) {
        if(!function_exists('mysql_connect')) {
            throw new ZMDatabaseException('Install `ext/mysql` extension to enable mysql_* functions.');
        }
        $defaults = Runtime::getSettings()->get('apps.store.database.default');
        if (null != $params) $params = array_merge($defaults, $params);
        $link = mysql_connect($params['host'], $params['user'], $params['password'], true);

        if (is_resource($link) && mysql_select_db($params['dbname'])) {
            if (!isset($params['charset']) && !empty($params['charset'])) {
                mysql_set_charset($params['charset']);
            }
            return $link;
        } else {
            throw new ZMDatabaseException(mysql_error(), mysql_errno()); 
        }
    }

    /**
     * Execute a query
     *
     * @param string $sql sql query string
     * @param int $limit limit the number of results
     * @param bool $useCache cache the query
     * @param int $cacheTime how long to cache the query for (in seconds)
     * @return object queryFactoryResult
     */
    public function Execute($sql, $limit = null, $useCache = false, $cacheTime = 0) {
        if (!preg_match('/^select/i', $sql)) {
            try {
                return  ZMRuntime::getDatabase()->executeUpdate($sql);
            } catch (PDOException $e) {
               throw new ZMDatabaseException($e->getMessage(), $e->getCode(), $e); 
            }
        }
        if ($limit) $sql .= ' LIMIT ' . $limit;

        $qcp = null;
        if ($useCache && $this->hasResultCache) {
           $qcp =  new QueryCacheProfile($cacheTime, md5($sql));
        }
        try {
            $stmt = ZMRuntime::getDatabase()->executeQuery($sql, array(), array(), $qcp);
        } catch (PDOException $e) {
            throw new ZMDatabaseException($e->getMessage(), $e->getCode(), $e); 
        }

        $obj = new queryFactoryResult($stmt);
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
     * @param object queryFactoryResult
     * @todo we could be a lot more strict in detecting <code>ORDER BY RAND()</code>
     *       but it is probably not worth it.
     */
    public function ExecuteRandomMulti($sql, $limit = null, $useCache = false, $cacheTime = 0) {
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
    public function metaColumns($table) {
        $details = array();
        foreach (ZMRuntime::getDatabase()->getMetaData($table) as $name => $attrs) {
            $meta = new \stdClass();
            $meta->type = $attrs['type'];
            $meta->max_length = !is_null($attrs['maxLen']) ? $attrs['maxLen'] : 3;
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
     * @return void
     */
    public function perform($table, $tableData, $type = 'insert', $filter = '') {
        $data = array();
        $types = array();
        foreach ($tableData as $key => $value) {
            $data[$value['fieldName']] = $value['value'];
            $types[] = $value['type'];
        }

        switch (strtolower($type)) {
            case 'insert':
                ZMRuntime::getDatabase()->insert($table, $data, $types);
            break;
            case 'update':
                $filter = str_replace(array(' and ', ' AND '), '|', $filter);
                $filters = explode(' | ', $filter);
                $identifiers = array();
                foreach ($filters as $v) {
                    list($field, $value) = explode(' = ', $v);
                    $identifiers[$field] = $value;
                }
                ZMRuntime::getDatabase()->update($table, $data, $identifiers, $types);
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
    public function getBindVarValue($value, $type) {
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
                return '\'' . $this->prepare_input($value) . '\'';
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
                return '\'' . $this->prepare_input($value) . '\'';
            case 'regexp':
                $searchArray = array('[', ']', '(', ')', '{', '}', '|', '*', '?', '.', '$', '^');
                foreach ($searchArray as $searchTerm) {
                    $value = str_replace($searchTerm, '\\' . $searchTerm, $value);
                }
                return $this->prepare_input($value);
            default:
                throw new ZMDatabaseException('var-type undefined: ' . $type . '('.$value.')');
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
    public function bindVars($sql, $param, $value, $type) {
        $sqlFix = $this->getBindVarValue($value, $type);
        return str_replace($param, $sqlFix, $sql);
    }

    // compatibility wrappers
    public function connect() { return true; }
    public function close() { ZMRuntime::getDatabase()->close(); }
    public function get_server_info() { return ZMRuntime::getDatabase()->getWrappedConnection()->getAttribute(PDO::ATTR_SERVER_VERSION); }
    public function insert_ID() { return ZMRuntime::getDatabase()->lastInsertId(); }
    public function prepare_input($string) { return ZMRuntime::getDatabase()->quote($string); }
    public function prepareInput($string) { return $this->prepare_input($string); }
    // @todo could implement these if we need to.
    public function queryCount() { return 0; }
    public function queryTime() { return 0; }

}

/**
 * Wrapper around a DBAL provided statement object
 */
class queryFactoryResult {
    public $EOF = false;
    private $stmt;

    /**
     * Initialize result set.
     *
     * @param object $stmt a doctrine dbal provided statement object
     */
    public function __construct($stmt) {
        $this->stmt = $stmt;
    }

    /**
     * Get the number of records in the result set.
     * @return int number of rows
     */
    public function RecordCount() {
        return $this->stmt->rowCount();
    }

    /**
     * Move the pointer to the next row in the result set.
     *
     * if there are no results then then <code>$this->EOF</code> is set to true
     * and <code>$this->fields</code> is not populated.
     */
    public function MoveNext() {
        $result = $this->stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $this->fields = $result;
        } else {
            $this->EOF = true;
        }
    }

    /**
     * Iterate over a result set that has already been randomized.
     *
     * This is different behaviour than the original class, but this should
     * be much faster.
     */
    public function MoveNextRandom() {
        $this->MoveNext();
    }

    /**
     * Move to a specified row in the result set.
     *
     * This cursor only moves forward. There is only one caller
     * (<code>zen_random_row</code>) of this method in ZenCart
     * and all callers of that are commented out as of ZenCart 1.5.0 
     * so it doesn't seem worth it to implement a scrollable cursor here.
     *
     * This method also silently stops when it reaches the last result
     *
     * @param int $row. Which row to scroll to
     */
    public function Move($row) {
        $row -=1;
        while (0 < $row) {
            $this->MoveNext();
        }
    }
}
