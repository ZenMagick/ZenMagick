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
 * Implementation of the ZenMagick database layer using <em>Creole</em>.
 *
 * @see http://creole.phpdb.org/trac/
 * @author mano
 * @package org.zenmagick.database.provider
 * @version $Id$
 */
class ZMCreoleDatabase extends ZMObject implements ZMDatabase {
    private $conn_;


    /**
     * Create a new instance.
     */
    function __construct() {
        parent::__construct();
        // avoid creole dot notation as that does not work here
        Creole::registerDriver('mysql', 'MySQLConnection');
        $dsn = array('phptype' => 'mysql',
                     'hostspec' => DB_SERVER,
                     'username' => DB_SERVER_USERNAME,
                     'password' => DB_SERVER_PASSWORD,
                     'database' => DB_DATABASE);
        $this->conn_ = Creole::getConnection($dsn);
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
    public function createModel($table, $model, $mapping) {
        // TODO: cache mapping
        $mapping = ZMDbUtils::parseMapping($mapping);

        $sql = 'INSERT INTO '.$table.' SET';

        $firstSet = true;
        foreach ($mapping as $field) {
            if (!$field['readonly'] && !$field['primary']) {
                if (!$firstSet) {
                    $sql .= ',';
                }
                $sql .= ' '.$field['column'].' = :'.$field['property'];
                $firstSet = false;
            }
        }

        $stmt = $this->prepareStatement($sql, $model, $mapping);
        $idgen = $this->conn_->getIdGenerator();
        $newId = null;
        //XXX: add support for SEQUENCE?
        if($idgen->isBeforeInsert()) {
            $newId = $idgen->getId();
            $stmt->executeUpdate();
        } else { // isAfterInsert()
            $stmt->executeUpdate();
            $newId = $idgen->getId();
        }

        foreach ($mapping as $property => $field) {
            if ($field['primary']) {
                $method = 'set'.ucwords($property);
                if (!method_exists($model, $method)) {
                    ZMObject::backtrace('missing primary key setter ' . $method);
                }
                $model->$method($newId);
            }
        }

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function update($sql, $data, $mapping) {
        // TODO: cache mapping
        $mapping = ZMDbUtils::parseMapping($mapping);

        $stmt = $this->prepareStatement($sql, $data, $mapping);
        $stmt->executeUpdate();
    }

    /**
     * {@inheritDoc}
     */
    public function updateModel($table, $model, $mapping) {
        // TODO: cache mapping
        $mapping = ZMDbUtils::parseMapping($mapping);

        $sql = 'UPDATE '.$table.' SET';

        $firstSet = true;
        $firstWhere = true;
        $where = ' WHERE ';
        foreach ($mapping as $field) {
            if ($field['key']) {
                if (!$firstWhere) {
                    $where .= ' AND ';
                }
                $where .= $field['column'].' = :'.$field['property'];
                $firstWhere = false;
            } else {
                if (!$field['readonly']) {
                    if (!$firstSet) {
                        $sql .= ',';
                    }
                    $sql .= ' '.$field['column'].' = :'.$field['property'];
                    $firstSet = false;
                }
            }
        }
        if (7 > strlen($where)) {
            ZMObject::backtrace('missing key');
        }
        $sql .= $where;

        $stmt = $this->prepareStatement($sql, $model, $mapping);
        $stmt->executeUpdate();
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
        // TODO: cache mapping
        $mapping = ZMDbUtils::parseMapping($mapping);


        $stmt = $this->prepareStatement($sql, $args, $mapping);
        $rs = $stmt->executeQuery();

        $results = array();
        while ($rs->next()) {
            $results[] = self::rs2model($modelClass, $rs, $mapping);
        }

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
        // make sure we are working on a map
        if (is_object($args)) {
            $args = $this->obj2map($args, $mapping);
        }

        // find out the order of args
        $regexp = ':'.implode(array_keys($args), '|:');
        preg_match_all('/'.$regexp.'/', $sql, $argOrder);
        // modify SQL replacing :key syntax with ?
        foreach (explode('|', $regexp) as $key) {
            $sql = str_replace($key, '?', $sql);
        }

        // create statement
        $stmt = $this->conn_->prepareStatement($sql);
        $index = 1;
        // set values by index
        foreach ($argOrder[0] as $name) {
            $name = substr($name, 1);
            $type = $mapping[$name]['type'];
            switch ($type) {
            case 'integer':
                $stmt->setInt($index, $args[$name]);
                break;
            case 'string':
                $stmt->setString($index, $args[$name]);
                break;
            default:
                ZMObject::backtrace('unsupported data type: '.$type);
                break;
            }
            ++$index;
        }

        return $stmt;
    }

    /**
     * Create a hash map based on the properties of the given object.
     *
     * @param obj The object.
     * @param array mapping The mapping.
     * @return array A hash map of all extracted object properties.
     * @todo cleanup all bean related code
     */
    protected function obj2map($obj, $mapping) {
        $prefixList = array('get', 'is', 'has');

        $map = array();
        foreach (array_keys($mapping) as $name) {
            $ucName = ucwords($name);
            foreach ($prefixList as $prefix) {
                $getter = $prefix . $ucName;
                if (method_exists($obj, $getter)) {
                    $map[$name] = $obj->$getter();
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * Create model and populate using the given rs and field map.
     *
     * @param string modelClass The model class.
     * @param ResultSet rs A Creole result set.
     * @param array mapping The field mapping.
     * @return mixed The model instance or array (if modelClass is <code>null</code>).
     */
    protected static function rs2model($modelClass, $rs, $mapping=null) {
        if (null != $modelClass) {
            $model = ZMLoader::make($modelClass);
        } else {
            $model = array();
        }

        foreach ($mapping as $field => $info) {
            switch ($info['type']) {
            case 'integer':
                $value = $rs->getInt($info['column']);
                break;
            case 'string':
                $value = $rs->getString($info['column']);
                break;
            case 'date':
                $value = $rs->getDate($info['column']);
                break;
            default:
                ZMObject::backtrace('unsupported data type: '.$info['type']);
                break;
            }

            if (null != $modelClass) {
                $setter = 'set' . ucwords($field);
                if (method_exists($model, $setter)) {
                    // specific method exists
                    $model->$setter($value);
                } else {
                    // use general purpose method
                    $model->set($key, $value);
                }
            } else {
                $model[$field] = $value;
            }
        }

        return $model;
    }

}

?>
