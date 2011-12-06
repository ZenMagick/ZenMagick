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

use zenmagick\base\Beans;

/**
 * Test database implementations.
 *
 * @package org.zenmagick.plugins.unitTests.tests
 * @author DerManoMann <mano@zenmagick.org>
 */
class TestZMDatabase extends ZMTestCase {
    static $PROVIDERS = array('ZMPdoDatabase');


    /**
     * Get all provider to test.
     */
    public static function getProviders() {
        //XXX: add variations with different drivers
        $providers = array();
        foreach (self::$PROVIDERS as $provider) {
            $providers[$provider] = ZMRuntime::getDatabase(array('provider' => $provider));
        }
        return $providers;
    }

    /**
     * Table meta data test runner.
     */
    public function testTableMetaData() {
        foreach ($this->getProviders() as $provider => $database) {
            $tableMeta = $database->getMetaData(TABLE_PRODUCTS_DESCRIPTION);
            $this->assertEqual(6, count($tableMeta), '%s: '.$provider);
            $this->assertTrue(array_key_exists('products_name', $tableMeta), '%s: '.$provider);
            $this->assertTrue(is_array($tableMeta['products_name']), '%s: '.$provider);
            $this->assertEqual('string', $tableMeta['products_name']['type'], '%s: '.$provider);
            $this->assertEqual(64, $tableMeta['products_name']['maxLen'], '%s: '.$provider);
        }
    }

    /**
     * Test auto mapping.
     */
    public function testAutoMapping() {
        $tname = DB_PREFIX."db_test";
        $create_table = "CREATE TABLE ".$tname." (id int(11) NOT NULL auto_increment, name varchar(32) NOT NULL, other varchar(32), PRIMARY KEY (id)) engine=MyISAM;";
        $drop_table = "DROP TABLE IF EXISTS ".$tname.";";
        $expectedMapping = array(
            'id' => 'column=id;type=integer;key=true;auto=true',
            'name' => 'column=name;type=string',
            'other' => 'column=other;type=string'
        );
        $expectedOutput = "'db_test' => array(\n    'id' => 'column=id;type=integer;key=true;auto=true',\n    'name' => 'column=name;type=string',\n    'other' => 'column=other;type=string'\n),\n";

        foreach (self::getProviders() as $provider => $database) {
            // create test tabe
            $database->update($drop_table);
            $database->update($create_table);

            ob_start();
            $mapping = ZMDbTableMapper::instance()->buildTableMapping('db_test', $database, true);
            $output = ob_get_clean();
            if ($this->assertTrue(is_array($mapping), '%s: '.$provider)) {
                $this->assertEqual($expectedMapping, $mapping, '%s: '.$provider);
            }
            $this->assertEqual($expectedOutput, $output, '%s: '.$provider);

            // drop again
            $database->update($drop_table);
        }
    }

    /**
     * Test indexed field names.
     */
    public function testIndexedFields() {
        $sql1 = "SELECT * FROM " . TABLE_COUNTRIES . " WHERE countries_id = :countryId";
        $sql2 = "SELECT * FROM " . TABLE_COUNTRIES . " WHERE countries_id = :1#countryId";

        foreach (self::getProviders() as $provider => $database) {
            // use simple country query to compare results
            $results1 = $database->query($sql1, array('countryId' => 153), TABLE_COUNTRIES);
            $results2 = $database->query($sql2, array('1#countryId' => 153), TABLE_COUNTRIES);
            $this->assertEqual($results1, $results2, '%s: '.$provider);
        }
    }

    /**
     * Test value array.
     */
    public function testValueArray() {
        $sql = "SELECT * FROM " . TABLE_COUNTRIES . " WHERE countries_id IN (:countryId)";

        foreach (self::getProviders() as $provider => $database) {
            $results = $database->query($sql, array('countryId' => array(81, 153)), TABLE_COUNTRIES);
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
            $result = $database->loadModel(TABLE_COUNTRIES, 153, 'ZMCountry');
            if ($this->assertTrue($result instanceof ZMCountry, '%s: '.$provider)) {
                $this->assertEqual('NZ', $result->getIsoCode2(), '%s: '.$provider);
            }
        }

        // createModel
        $deleteTestModelSql = "DELETE from " . TABLE_COUNTRIES . " WHERE countries_iso_code_3 = :isoCode3";
        foreach (self::getProviders() as $provider => $database) {
            // first delete, just in case
            $database->update($deleteTestModelSql, array('isoCode3' => '@@@'), TABLE_COUNTRIES);

            // set up test data
            $model = Beans::getBean('ZMCountry#name="test&isoCode2=@@&isoCode3=@@@&addressFormatId=1');
            $result = $database->createModel(TABLE_COUNTRIES, $model);
            if ($this->assertNotNull($result, '%s: '.$provider)) {
                $this->assertTrue(0 != $result->getId(), '%s: '.$provider);
            }

            // clean up
            $database->update($deleteTestModelSql, array('isoCode3' => '@@@'), TABLE_COUNTRIES);
        }

        // updateModel
        $reset = "UPDATE " . TABLE_COUNTRIES . " SET countries_iso_code_3 = :isoCode3 WHERE countries_id = :countryId";
        foreach (self::getProviders() as $provider => $database) {
            $country = $database->loadModel(TABLE_COUNTRIES, 153, 'ZMCountry');
            if ($this->assertNotNull($country, '%s: '.$provider)) {
                $isCode3 = $country->getIsoCode3();
                $country->setIsoCode3('###');
                $database->updateModel(TABLE_COUNTRIES, $country);
                $updated = $database->loadModel(TABLE_COUNTRIES, 153, 'ZMCountry');
                if ($this->assertNotNull($updated, '%s: '.$provider)) {
                    $this->assertEqual('###', $updated->getIsoCode3(), '%s: '.$provider);
                }
                // clean up
                $database->update($reset, array('countryId' => 153, 'isoCode3' => 'NZL'), TABLE_COUNTRIES);
            }
        }

        // removeModel
        $deleteTestModelSql = "DELETE from " . TABLE_COUNTRIES . " WHERE countries_iso_code_3 = :isoCode3";
        $findTestModelSql = "SELECT * from " . TABLE_COUNTRIES . " WHERE countries_iso_code_3 = :isoCode3";
        foreach (self::getProviders() as $provider => $database) {
            // first delete, just in case
            $database->update($deleteTestModelSql, array('isoCode3' => '%%%'), TABLE_COUNTRIES);

            // set up test data
            $model = Beans::getBean('ZMCountry#name="test&isoCode2=%%&isoCode3=%%%&addressFormatId=1');
            $result = $database->createModel(TABLE_COUNTRIES, $model);
            if ($this->assertNotNull($result, '%s: '.$provider)) {
                $database->removeModel(TABLE_COUNTRIES, $result);
                $this->assertNull($database->querySingle($findTestModelSql, array('isoCode3' => '%%%'), TABLE_COUNTRIES, 'ZMCountry'), '%s: '.$provider);
            }

            // clean up
            $database->update($deleteTestModelSql, array('isoCode3' => '%%%'), TABLE_COUNTRIES);
        }
    }

    /**
     * Test exceptions (and dynamic table mapping without prefix).
     */
    public function testExceptions() {
        $tname = DB_PREFIX."db_test";
        $create_table = "CREATE TABLE ".$tname." (id int(11) NOT NULL auto_increment, name varchar(32) NOT NULL, other varchar(32), PRIMARY KEY (id)) engine=MyISAM;";
        $drop_table = "DROP TABLE IF EXISTS ".$tname.";";
        $insert = "INSERT INTO ".$tname." SET name = :name;";

        foreach (self::getProviders() as $provider => $database) {
            // create test table
            $database->update($drop_table);
            $database->update($create_table);

            try {
                $database->update($insert, array('name' => 'foo'), $tname);
            } catch (ZMDatabaseException $e) {
            } catch (Exception $e) {
                $this->fail('unexpected exception: '.$e);
            }

            // drop again
            $database->update($drop_table);
        }
    }

    /**
     * Test exceptions (and dynamic table mapping with prefix).
     */
    public function testExceptionsPrefix() {
        $tname = DB_PREFIX."db_test";
        $create_table = "CREATE TABLE ".$tname." (id int(11) NOT NULL auto_increment, name varchar(32) NOT NULL, other varchar(32), PRIMARY KEY (id)) engine=MyISAM;";
        $drop_table = "DROP TABLE IF EXISTS ".$tname.";";
        $insert = "INSERT INTO ".$tname." SET name = :name;";

        foreach (self::getProviders() as $provider => $database) {
            // create test table
            $database->update($drop_table);
            $database->update($create_table);

            try {
                $database->update($insert, array('name' => 'foo'), $tname);
            } catch (ZMDatabaseException $e) {
            } catch (Exception $e) {
                $this->fail('unexpected exception: '.$e);
            }

            // drop again
            $database->update($drop_table);
        }
    }

    /**
     * Test unmapped columns.
     */
    public function testUnmapped() {
        // count orders
        $sql = "SELECT count(*) AS count FROM " . TABLE_ORDERS . " where orders_status = :orderStatusId";

        foreach (self::getProviders() as $provider => $database) {
            try {
                $result = $database->querySingle($sql, array('orderStatusId' => 1), TABLE_ORDERS);
                $this->assertTrue(array_key_exists('count', $result), '%s: '.$provider);
            } catch (Exception $e) {
                $this->fail('unexpected exception: '.$e);
            }
        }
    }

}
