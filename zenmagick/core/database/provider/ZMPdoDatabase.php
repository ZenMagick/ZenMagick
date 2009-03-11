<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
?>
<?php


/**
 * Implementation of the ZenMagick database layer using <em>PDA</em>.
 *
 * @author DerManoMann
 * @package org.zenmagick.database.provider
 * @version $Id$
 */
class ZMPdoDatabase extends ZMObject implements ZMDatabase {
    private $pdo_;
    private $config_;
    private $autoCommit_;
    private $inTransaction_;
    private $queriesCount;
    private $queriesTime;
    private $queriesMap = array();
    private $mapper;


    /**
     * Create a new instance.
     *
     * <p>Supports the custom configuration setting <em>persistent</em> (<code>true</code> | <code>false</code>)</p>.
     *
     * @param array conf Configuration properties.
     */
    function __construct($conf) {
        parent::__construct();
        if (false !== ($colon = strpos($conf['host'], ':'))) {
            $conf['port'] = substr($conf['host'], $colon+1);
            $conf['host'] = substr($conf['host'], 0, $colon);
        }
        if (isset($conf['port']) && 'localhost' == $conf['host']) {
            // can't do port on localhost?
            $conf['host'] = '127.0.0.1';
        }

        $url = $conf['driver'].':'.'host='.$conf['host'];
        if (isset($conf['port'])) {
            $url .= ';port='.$conf['port'];
        }
        $url .= ';dbname='.$conf['database'];
        $params = array();
        if (isset($conf['persistent']) && $conf['persistent']) {
            $params[PDO::ATTR_PERSISTENT] = true;
        }
        $this->config_ = $conf;
        $this->pdo_ = new PDO($url, $conf['username'], $conf['password'], $params);
        $this->mapper = ZMDbTableMapper::instance();
        $this->queriesCount = 0;
        $this->queriesTime = 0;
        $this->autoCommit_ = true;
        $this->inTransaction_ = false;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function getConfig() {
        return $this->config_;
    }

    /**
     * {@inheritDoc}
     */
    public function setAutoCommit($value) {
        $this->autoCommit_ = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function commit() {
        $this->pdo_->commit();
        $this->inTransaction_ = false;
    }

    /**
     * {@inheritDoc}
     */
    public function rollback() {
        $this->pdo_->rollBack();
    }

    /**
     * {@inheritDoc}
     */
    public function getStats() {
        $stats = array();
        $stats['time'] = $this->queriesTime;
        $stats['queries'] = $this->queriesCount;
        $stats['details'] = $this->queriesMap;
        return $stats;
    }

    /**
     * Get the elapsed time since <code>$start</code>.
     *
     * @param string start The starting time.
     * @return long The time in milliseconds.
     */
    protected function getExecutionTime($start) {
        $start = explode (' ', $start);
        $end = explode (' ', microtime());
        return $end[1]+$end[0]-$start[1]-$start[0];
    }

    /**
     * {@inheritDoc}
     */
    public function createModel($table, $model, $mapping=null) {
    }

    /**
     * {@inheritDoc}
     */
    public function update($sql, $data=array(), $mapping=null) {
    }

    /**
     * {@inheritDoc}
     */
    public function updateModel($table, $model, $mapping=null) {
    }

    /**
     * {@inheritDoc}
     */
    public function querySingle($sql, $args=array(), $mapping=null, $modelClass=null) {
        $results = $this->query($sql, $args, $mapping, $modelClass);
        return 0 < count($results) ? $results[0] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function query($sql, $args=array(), $mapping=null, $modelClass=null) {
        $startTime = microtime();
        $mapping = $this->mapper->ensureMapping($mapping);

        $stmt = $this->prepareStatement($sql, $args, $mapping);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        ++$this->queriesCount;

        $results = array();
        foreach ($rows as $result) {
            if (null !== $mapping && ZMDatabase::MODEL_RAW != $modelClass) {
                $result = $this->translateRow($result, $mapping);
            }
            if (null != $modelClass && ZMDatabase::MODEL_RAW != $modelClass) {
                $result = ZMBeanUtils::map2obj($modelClass, $result);
            }
            $results[] = $result;
        }

        $this->queriesMap[] = array('time'=>$this->getExecutionTime($startTime), 'sql'=>$sql);
        $this->queriesTime += $this->getExecutionTime($startTime);
        return $results;
    }

    /**
     * Create a prepared statement.
     *
     * @param string sql The initial SQL.
     * @param mixed args The data either as map or ZMModel instance.
     * @param array mapping The field mapping.
     * @return A <code>PreparedStatement</code> or null;
     */
    protected function prepareStatement($sql, $args, $mapping=null) {
        // TODO: store centrally
        $typeMap = array('integer' => PDO::PARAM_INT);

        // make sure we are working on a map
        if (is_object($args)) {
            $args = ZMBeanUtils::obj2map($args, array_keys($mapping));
        }

        // TODO: value arrays

        // create statement
        $stmt = $this->pdo_->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($args as $name => $value) {
            $type = $mapping[$name]['type'];
            if (!array_key_exists($type, $typeMap)) {
                throw ZMLoader::make('ZMException', 'unsupported data(prepare) type='.$type.' for name='.$name);
            }
            $stmt->bindValue(':'.$name, $value, $typeMap[$type]);
        }

        return $stmt;
    }

    /**
     * Translate a given raw database row with the given mapping.
     *
     * @param array row The database row map.
     * @param array mapping The mapping (may be <code>null</code>).
     * @return array The mapped row.
     */
    protected function translateRow($row, $mapping) {
        if (null == $mapping) {
            return $row;
        }

        $mappedRow = array();
        foreach ($mapping as $field) {
            if (array_key_exists($field['column'], $row)) {
                $mappedRow[$field['property']] = $row[$field['column']];
                //TODO: is is ok?
                if ('date' == $field['type']) {
                    if (ZMDatabase::NULL_DATETIME == $mappedRow[$field['property']]) {
                        $mappedRow[$field['property']] = null;
                    }
                }
            }
        }

        return $mappedRow;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetaData($table=null) {
    }

}

?>
