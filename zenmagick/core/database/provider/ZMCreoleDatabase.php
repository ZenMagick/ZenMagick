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
    public function update($sql, $data, $mapping) {
        if (is_array($data)) {
            // TODO: cache mapping
            $mapping = ZMDbUtils::parseMapping($mapping);
            // bind query parameter
            foreach ($data as $name => $value) {
              //TODO:
                $sql = $this->db_->bindVars($sql, ':'.$name, $value, $mapping[$name]['type']);
            }
        } else if (is_object($data)) {
            $sql = ZMDbUtils::bindObject($sql, $data, false);
        } else {
            ZMObject::backtrace('invalid data type');
        }
        $this->db_->Execute($sql);
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
            if ($field['primary']) {
                if (!$firstWhere) {
                    $where .= ' AND ';
                }
                $where .= $field['column'].' = :'.$field['property'].';'.$field['type'];
                $firstWhere = false;
            } else {
                if (!$field['readonly']) {
                    if (!$firstSet) {
                        $sql .= ',';
                    }
                    $sql .= ' '.$field['column'].' = :'.$field['property'].';'.$field['type'];
                    $firstSet = false;
                }
            }
        }
        if (7 > strlen($where)) {
            ZMObject::backtrace('missing primary key');
        }
        $sql .= $where;
        //TODO:
        $sql = ZMDbUtils::bindObject($sql, $model, false);
        $this->db_->Execute($sql);
    }

    /**
     * {@inheritDoc}
     */
    public function querySingle($sql, $args=array(), $mapping=null, $modelClass=null) {
        $results = $this->query($sql, $args, $mapping, $modelClass);
        return 1 == count($results) ? $results[0] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function query($sql, $args=array(), $mapping=null, $modelClass=null) {
        // TODO: cache mapping
        $mapping = ZMDbUtils::parseMapping($mapping);

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
            default:
                ZMObject::backtrace('unsupported data type: '.$type);
                break;
            }
            ++$index;
        }
        $rs = $stmt->executeQuery();

        $results = array();
        while ($rs->next()) {
            $results[] = self::rs2modelList($modelClass, $rs, $mapping);
        }

        return $results;
    }

    /**
     * Create model and populate using the given rs and field map.
     *
     * @param string modelClass The model class.
     * @param ResultSet rs A Creole result set.
     * @param array mapping The field mapping.
     * @return mixed The model instance.
     */
    protected static function rs2modelList($modelClass, $rs, $mapping=null) {
        $model = ZMLoader::make($modelClass);

        foreach ($mapping as $field => $info) {
            $setter = 'set' . ucwords($field);
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

            if (method_exists($model, $setter)) {
                // specific method exists
                $model->$setter($value);
            } else {
                // use general purpose method
                $model->set($key, $value);
            }
        }

        return $model;
    }

}

?>
