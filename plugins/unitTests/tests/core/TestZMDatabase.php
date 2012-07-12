<?php
/*
 * ZenMagick - Smart e-commerce
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

use zenmagick\base\Beans;
use zenmagick\base\database\DatabaseException;
use zenmagick\apps\store\model\location\Country;
use zenmagick\plugins\unitTests\simpletest\TestCase;

/**
 * Test database implementations.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMDatabase extends TestCase {

    /**
     * Get all provider to test.
     */
    public static function getProviders() {
        return array('ZMDatabase' => ZMRuntime::getDatabase());
    }

    /**
     * Table meta data test runner.
     */
    public function testTableMetaData() {
        foreach ($this->getProviders() as $provider => $database) {
            $tableMeta = $database->getMetaData('products_description');
            $this->assertEqual(6, count($tableMeta), '%s: '.$provider);
            $this->assertTrue(array_key_exists('products_name', $tableMeta), '%s: '.$provider);
            $this->assertTrue(is_array($tableMeta['products_name']), '%s: '.$provider);
            $this->assertEqual('string', $tableMeta['products_name']['type'], '%s: '.$provider);
            $this->assertTrue(64 <= $tableMeta['products_name']['length'], '%s: '.$provider);
        }
    }

    /**
     * Test auto mapping.
     */
    public function testAutoMapping() {
        $tname = ZMRuntime::getDatabase()->getPrefix().'db_test';
        $create_table = "CREATE TABLE ".$tname." (id int(11) NOT NULL auto_increment, name varchar(32) NOT NULL, other varchar(32), PRIMARY KEY (id)) engine=MyISAM;";
        $drop_table = "DROP TABLE IF EXISTS ".$tname.";";
        $expectedMapping = array(
            'id' => array('column' => 'id', 'type' => 'integer', 'key' => true, 'auto' => true, 'length' => null, 'default' => null),
            'name' => array('column' => 'name', 'type' => 'string', 'key' => false, 'auto' => false, 'length' => 32, 'default' => null),
            'other' => array('column' => 'other', 'type' => 'string', 'key' => false, 'auto' => false, 'length' => 32, 'default' => null)
        );

        foreach (self::getProviders() as $provider => $database) {
            // create test tabe
            $database->executeUpdate($drop_table);
            $database->executeUpdate($create_table);

            $mapping = ZMRuntime::getDatabase()->getMetaData('db_test');
            if ($this->assertTrue(is_array($mapping), '%s: '.$provider)) {
                $this->assertEqual($expectedMapping, $mapping, '%s: '.$provider);
            }

            // drop again
            $database->executeUpdate($drop_table);
        }
    }

    /**
     * Test indexed field names.
     */
    public function testIndexedFields() {
        $sql1 = "SELECT * FROM %table.countries% WHERE countries_id = :countryId";
        $sql2 = "SELECT * FROM %table.countries% WHERE countries_id = :1#countryId";

        foreach (self::getProviders() as $provider => $database) {
            // use simple country query to compare results
            $results1 = $database->fetchAll($sql1, array('countryId' => 153), 'countries');
            $results2 = $database->fetchAll($sql2, array('1#countryId' => 153), 'countries');
            $this->assertEqual($results1, $results2, '%s: '.$provider);
        }
    }

    /**
     * Test value array.
     */
    public function testValueArray() {
        $sql = "SELECT * FROM %table.countries% WHERE countries_id IN (:countryId)";

        foreach (self::getProviders() as $provider => $database) {
            $results = $database->fetchAll($sql, array('countryId' => array(81, 153)), 'countries');
            if ($this->assertEqual(2, count($results), '%s: '.$provider)) {
                $this->assertEqual(81, $results[0]['countryId'], '%s: '.$provider);
                $this->assertEqual('DE', $results[0]['isoCode2'], '%s: '.$provider);
                $this->assertEqual(153, $results[1]['countryId'], '%s: '.$provider);
                $this->assertEqual('NZL', $results[1]['isoCode3'], '%s: '.$provider);
            }
        }
    }

    /**
     * Test model based methods.
     */
    public function testModelMethods() {
        // loadModel
        foreach (self::getProviders() as $provider => $database) {
            $result = $database->loadModel('countries', 153, 'zenmagick\apps\store\model\location\Country');
            if ($this->assertTrue($result instanceof Country, '%s: '.$provider)) {
                $this->assertEqual('NZ', $result->getIsoCode2(), '%s: '.$provider);
            }
        }

        // createModel
        $deleteTestModelSql = "DELETE from %table.countries% WHERE countries_iso_code_3 = :isoCode3";
        foreach (self::getProviders() as $provider => $database) {
            // first delete, just in case
            $database->updateObj($deleteTestModelSql, array('isoCode3' => '@@@'), 'countries');

            // set up test data
            $model = Beans::getBean('zenmagick\apps\store\model\location\Country#name="test&isoCode2=@@&isoCode3=@@@&addressFormatId=1');
            $result = $database->createModel('countries', $model);
            if ($this->assertNotNull($result, '%s: '.$provider)) {
                $this->assertTrue(0 != $result->getId(), '%s: '.$provider);
            }

            // clean up
            $database->updateObj($deleteTestModelSql, array('isoCode3' => '@@@'), 'countries');
        }

        // updateModel
        $reset = "UPDATE %table.countries% SET countries_iso_code_3 = :isoCode3 WHERE countries_id = :countryId";
        foreach (self::getProviders() as $provider => $database) {
            $country = $database->loadModel('countries', 153, 'zenmagick\apps\store\model\location\Country');
            if ($this->assertNotNull($country, '%s: '.$provider)) {
                $isCode3 = $country->getIsoCode3();
                $country->setIsoCode3('###');
                $database->updateModel('countries', $country);
                $updated = $database->loadModel('countries', 153, 'zenmagick\apps\store\model\location\Country');
                if ($this->assertNotNull($updated, '%s: '.$provider)) {
                    $this->assertEqual('###', $updated->getIsoCode3(), '%s: '.$provider);
                }
                // clean up
                $database->updateObj($reset, array('countryId' => 153, 'isoCode3' => 'NZL'), 'countries');
            }
        }

        // removeModel
        $deleteTestModelSql = "DELETE from %table.countries% WHERE countries_iso_code_3 = :isoCode3";
        $findTestModelSql = "SELECT * from %table.countries% WHERE countries_iso_code_3 = :isoCode3";
        foreach (self::getProviders() as $provider => $database) {
            // first delete, just in case
            $database->updateObj($deleteTestModelSql, array('isoCode3' => '%%%'), 'countries');

            // set up test data
            $model = Beans::getBean('zenmagick\apps\store\model\location\Country#name="test&isoCode2=%%&isoCode3=%%%&addressFormatId=1');
            $result = $database->createModel('countries', $model);
            if ($this->assertNotNull($result, '%s: '.$provider)) {
                $database->removeModel('countries', $result);
                $this->assertNull($database->querySingle($findTestModelSql, array('isoCode3' => '%%%'), 'countries', 'zenmagick\apps\store\model\location\Country'), '%s: '.$provider);
            }

            // clean up
            $database->updateObj($deleteTestModelSql, array('isoCode3' => '%%%'), 'countries');
        }
    }

    /**
     * Test exceptions (and dynamic table mapping without prefix).
     */
    public function testExceptions() {
        $tname = ZMRuntime::getDatabase()->getPrefix()."db_test";
        $create_table = "CREATE TABLE ".$tname." (id int(11) NOT NULL auto_increment, name varchar(32) NOT NULL, other varchar(32), PRIMARY KEY (id)) engine=MyISAM;";
        $drop_table = "DROP TABLE IF EXISTS ".$tname.";";
        $insert = "INSERT INTO ".$tname." SET name = :name;";

        foreach (self::getProviders() as $provider => $database) {
            // create test table
            $database->executeUpdate($drop_table);
            $database->executeUpdate($create_table);

            try {
                $database->updateObj($insert, array('name' => 'foo'), $tname);
            } catch (DatabaseException $e) {
            } catch (Exception $e) {
                $this->fail('unexpected exception: '.$e);
            }

            // drop again
            $database->executeUpdate($drop_table);
        }
    }

    /**
     * Test exceptions (and dynamic table mapping with prefix).
     */
    public function testExceptionsPrefix() {
        $tname = ZMRuntime::getDatabase()->getPrefix()."db_test";
        $create_table = "CREATE TABLE ".$tname." (id int(11) NOT NULL auto_increment, name varchar(32) NOT NULL, other varchar(32), PRIMARY KEY (id)) engine=MyISAM;";
        $drop_table = "DROP TABLE IF EXISTS ".$tname.";";
        $insert = "INSERT INTO ".$tname." SET name = :name;";

        foreach (self::getProviders() as $provider => $database) {
            // create test table
            $database->executeUpdate($drop_table);
            $database->executeUpdate($create_table);

            try {
                $database->updateObj($insert, array('name' => 'foo'), $tname);
            } catch (DatabaseException $e) {
            } catch (Exception $e) {
                $this->fail('unexpected exception: '.$e);
            }

            // drop again
            $database->executeUpdate($drop_table);
        }
    }

    /**
     * Test unmapped columns.
     */
    public function testUnmapped() {
        // count orders
        $sql = "SELECT count(*) AS count FROM %table.orders% where orders_status = :orderStatusId";

        foreach (self::getProviders() as $provider => $database) {
            try {
                $result = $database->querySingle($sql, array('orderStatusId' => 1), 'orders');
                $this->assertTrue(array_key_exists('count', $result), '%s: '.$provider);
            } catch (Exception $e) {
                $this->fail('unexpected exception: '.$e);
            }
        }
    }

}
