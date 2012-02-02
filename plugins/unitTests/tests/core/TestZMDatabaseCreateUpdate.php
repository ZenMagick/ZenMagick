<?php
/*
 * ZenMagick - Smart e-commerce
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
?>
<?php

define('TABLE_CREATE_UPDATE_TESTS', DB_PREFIX . 'create_update_tests');

use zenmagick\base\ZMObject;

/**
 * Test create / update methods.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMDatabaseCreateUpdate extends ZMTestCase {

    /**
     * {@inheritDoc}
     */
    public function setUp() {
        parent::setUp();
        // split as some implementations might not support multiple commands per call!
        $sql = "DROP TABLE IF EXISTS " . TABLE_CREATE_UPDATE_TESTS .";";
        ZMRuntime::getDatabase()->updateObj($sql, array());
        $sql = "CREATE TABLE " . TABLE_CREATE_UPDATE_TESTS . " (
                  row_id int(11) NOT NULL auto_increment,
                  name varchar(128) NOT NULL,
                  class_name varchar(128) NOT NULL,
                  method_name varchar(128) NOT NULL,
                  parameter_list varchar(511) NOT NULL DEFAULT '',
                  PRIMARY KEY (row_id)
                ) ENGINE=InnoDB;";
        ZMRuntime::getDatabase()->updateObj($sql, array());
    }

    /**
     * {@inheritDoc}
     */
    public function tearDown() {
        parent::tearDown();
        ZMRuntime::getDatabase()->updateObj('DROP TABLE IF EXISTS ' . TABLE_CREATE_UPDATE_TESTS. ';', array());
    }

    /**
     * Register table mapping.
     */
    protected function registerTableMapping() {
        ZMDbTableMapper::instance()->setMappingForTable('create_update_tests',
            array(
                'myId' => 'column=row_id;type=integer;key=true',
                'name' => 'column=name;type=string',
                'className' => 'column=class_name;type=string',
                'methodName' => 'column=method_name;type=string',
                'parameterList' => 'column=parameter_list;type=string',
            )
        );
    }

    /**
     * Test create model with object.
     */
    public function testCreateModelObj() {
        $obj = new ZMObject();
        $obj->setName('foo1');
        $obj->setClass_name('class1');
        $obj->setMethod_name('method1');
        foreach (TestZMDatabase::getProviders() as $provider => $database) {
            $database->createModel(TABLE_CREATE_UPDATE_TESTS, $obj);
        }
    }

    /**
     * Test create model with map.
     */
    public function testCreateModelMap() {
        $map = array();
        $map['name'] = 'foo2';
        $map['class_name'] = 'class2';
        $map['method_name'] = 'method2';
        $map['parameter_list'] = 'parameter2';
        foreach (TestZMDatabase::getProviders() as $provider => $database) {
            $database->createModel(TABLE_CREATE_UPDATE_TESTS, $map);
        }
    }

    /**
     * Test create SQL.
     */
    public function testCreateSQL() {
        $map = array();
        $map['name'] = 'foo3';
        $map['class_name'] = 'class3';
        $map['method_name'] = 'method3';
        $map['parameter_list'] = '';
        $sql = "INSERT INTO ".TABLE_CREATE_UPDATE_TESTS."
                (name, class_name, method_name, parameter_list) VALUES
                (:name, :class_name, :method_name, :parameter_list)";
        foreach (TestZMDatabase::getProviders() as $provider => $database) {
            $database->updateObj($sql, $map, TABLE_CREATE_UPDATE_TESTS);
        }
    }

    /**
     * Test create mapped SQL using obj.
     */
    public function testCreateMappedObjSQL() {
        $obj = new ZMObject();
        $obj->setName('foo4');
        $obj->setClassName('class4');
        $obj->setMethodName('method4');
        $obj->setParameterList('parameter4');

        $this->registerTableMapping();

        $sql = "INSERT INTO ".TABLE_CREATE_UPDATE_TESTS."
                (name, class_name, method_name, parameter_list) VALUES
                (:name, :className, :methodName, :parameterList)";
        foreach (TestZMDatabase::getProviders() as $provider => $database) {
            $database->updateObj($sql, $obj, TABLE_CREATE_UPDATE_TESTS);
        }
    }

    /**
     * Test create mapped SQL using map.
     */
    public function testCreateMappedMapSQL() {
        $map = array();
        $map['name'] = 'foo5';
        $map['className'] = 'class5';
        $map['methodName'] = 'method5';
        $map['parameterList'] = '';

        $this->registerTableMapping();

        $sql = "INSERT INTO ".TABLE_CREATE_UPDATE_TESTS."
                (name, class_name, method_name, parameter_list) VALUES
                (:name, :className, :methodName, :parameterList)";
        foreach (TestZMDatabase::getProviders() as $provider => $database) {
            $database->updateObj($sql, $map, TABLE_CREATE_UPDATE_TESTS);
        }
    }

}
