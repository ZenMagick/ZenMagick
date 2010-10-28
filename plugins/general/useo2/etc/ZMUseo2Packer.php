<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Packer for the <em>useo2</em> mod.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc
 */
class ZMUseo2Packer implements ZMLibraryPacker {

    /**
     * {@inheritDoc}
     */
    public function process($sourceDir, $targetDir, $version, $strip) {
        $targetLibDir = $targetDir.'lib'.DIRECTORY_SEPARATOR;

        // htaccess_sample
        unlink ($targetDir.'htaccess_sample');
        copy ($sourceDir.'htaccess_sample', $targetDir.'htaccess_sample');

        // classes
        $seoClasses = array(
            'includes/classes/seo.url.php',
            'includes/classes/seo.install.php'
        );
        foreach ($seoClasses as $file) {
            $contents = file_get_contents($sourceDir.$file);

            $contents = str_replace('require_once', '//require_once', $contents);
            $contents = str_replace('&new', 'new', $contents);
            $contents = str_replace('$this->db =', '//$this->db =', $contents);

            $contents = str_replace('$this->db->Execute($insert_group)', '$zmresult = ZMRuntime::getDatabase()->update($insert_group)', $contents);
            $contents = str_replace('$this->db->insert_ID()', '$zmresult[\'lastInsertId\']', $contents);

            // fix auto-increment SQL
            $contents = str_replace(
                'INTO `".TABLE_CONFIGURATION_GROUP."` VALUES (\'\', ',
                'INTO `".TABLE_CONFIGURATION_GROUP."` (configuration_group_title, configuration_group_description, sort_order, visible) VALUES (', 
                $contents
            );

            $confColumns = "(configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)";
            $insertAfter = "\$sql = str_replace('GROUP_INSERT_ID', \$group_id, \$value['QUERY']);";
            $contents = str_replace(
                $insertAfter,
                $insertAfter."\n"."\$sql = str_replace('VALUES (\'\',', '".$confColumns." VALUES (', \$sql);",
                $contents
            );

            // add category
            $contents = str_replace('index, product_info, products_new', 'index, category, product_info, products_new', $contents);
            $contents = str_replace('FILENAME_DEFAULT,', "FILENAME_DEFAULT,\n 'category',", $contents);
            $contents = str_replace('switch ($page) {', "switch (\$page) {\ncase 'category': \$link .= 'index.php?main_page=category'; break;", $contents);
            $contents = str_replace('case ($page == FILENAME_DEFAULT', "case ((\$page == FILENAME_DEFAULT || \$page == 'category')", $contents);
            $contents = str_replace('case ($page == FILENAME_DEFAULT)', "case (\$page == FILENAME_DEFAULT || \$page == 'category')", $contents);

            $contents = str_replace('$this->db->Execute($sort_order_query', 'ZMRuntime::getDatabase()->querySingle($sort_order_query', $contents);
            $contents = str_replace('$this->db->Execute("SELECT cache_expires FROM', 'ZMRuntime::getDatabase()->querySingle("SELECT cache_expires FROM', $contents);
            $contents = str_replace('$this->cache_query->RecordCount()', 'count($this->cache_query)', $contents);
            $contents = str_replace('this->cache_query->fields', 'this->cache_query', $contents);

            // loops
            $vars = array('parent_categories', 'ezpages', 'product', 'manufacturers', 'category', 'article', 'information', 'cache');
            foreach ($vars as $var) {
                $contents = preg_replace('/while \(!\$'.$var.'\->EOF\) {/', 'foreach ($'.$var.' as $xxxx) {', $contents);
                $contents = preg_replace('/while\(!\$'.$var.'\->EOF\){/', 'foreach ($'.$var.' as $xxxx) {', $contents);
                $contents = str_replace($var.'->fields', 'xxxx', $contents);
                $contents = str_replace('$'.$var.'->MoveNext()', '//'.$var.'->MoveNext()', $contents);
            }

            $contents = str_replace('$result = $this->db->Execute', '$result = ZMRuntime::getDatabase()->querySingle', $contents);
            $contents = str_replace('= $this->db->Execute', '= ZMRuntime::getDatabase()->query', $contents);
            $contents = str_replace('$this->db->Execute', 'ZMRuntime::getDatabase()->update', $contents);
            $contents = str_replace('$result->RecordCount()', 'count($result[\'rows\'])', $contents);
            $contents = str_replace('$cache->RecordCount()', 'count($cache)', $contents);
            $contents = str_replace('$result->fields[', '$result[', $contents);
            $contents = str_replace('$sort->fields[', '$sort[', $contents);

            $contents = preg_replace('/.*zen_db_perform.*/', 'ZMRuntime::getDatabase()->updateModel(TABLE_SEO_CACHE, $sql_data_array);', $contents);

            // other fixes
            $contents = str_replace('var $installer;', 'var $installer;'."\n".'var $keep_in_memory = false;', $contents);
            $contents = str_replace('ereg_replace($pattern', 'preg_replace(\'/\'.$pattern.\'/\'', $contents);
            $contents = str_replace("ereg_replace( ' +'", "preg_replace('/ +/'", $contents);

            // not_null
            $notnull = 
                'if (is_array($value)) { if (sizeof($value) > 0) { return true; } else { return false; }'
                .'} elseif( is_a( $value, \'queryFactoryResult\' ) ) { if (sizeof($value->result) > 0) { return true; } else { return false; }'
                .'} else { if (($value != \'\') && (strtolower($value) != \'null\') && (strlen(trim($value)) > 0)) { return true; } else { return false; } }';
            $contents = str_replace('return zen_not_null($value);', $notnull, $contents);

            file_put_contents($targetLibDir.basename($file), $contents);
        }

        // seo-functions
        unlink ($targetLibDir.'seo.functions.php');
        $seoFunctions = array(
            'admin/includes/extra_datafiles/seo.php',
            'admin/includes/functions/extra_functions/seo.php'
        );

        foreach ($seoFunctions as $file) {
            $contents = file_get_contents($sourceDir.$file);
            $contents = str_replace('$this->db->Execute', 'ZMRuntime::getDatabase()->update', $contents);
            file_put_contents($targetLibDir.'seo.functions.php', $contents, FILE_APPEND);
        }

        // and some conents
        $reset = '<?php function reset_seo_cache() { ZMRuntime::getDatabase()->update("DELETE FROM ".TABLE_SEO_CACHE." WHERE cache_name LIKE \'%seo_urls%\'"); } ?>';
        file_put_contents($targetLibDir.'seo.functions.php', $reset, FILE_APPEND);
    }

}
