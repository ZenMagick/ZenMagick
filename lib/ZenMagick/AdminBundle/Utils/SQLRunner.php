<?php
/**
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id$
 */
namespace ZenMagick\AdminBundle\Utils;

use ZenMagick\Base\Beans;
use ZenMagick\Base\Toolbox;

if (!defined('ZC_UPG_DEBUG3')) define('ZC_UPG_DEBUG3', false);
if (!defined('REASON_TABLE_ALREADY_EXISTS')) {
  define('REASON_TABLE_ALREADY_EXISTS','Cannot create table %s because it already exists');
  define('REASON_TABLE_DOESNT_EXIST','Cannot drop table %s because it does not exist.');
  define('REASON_TABLE_NOT_FOUND','Cannot execute because table %s does not exist.');
  define('REASON_CONFIG_KEY_ALREADY_EXISTS','Cannot insert configuration_key "%s" because it already exists');
  define('REASON_COLUMN_ALREADY_EXISTS','Cannot ADD column %s because it already exists.');
  define('REASON_COLUMN_DOESNT_EXIST_TO_DROP','Cannot DROP column %s because it does not exist.');
  define('REASON_COLUMN_DOESNT_EXIST_TO_CHANGE','Cannot CHANGE column %s because it does not exist.');
  define('REASON_PRODUCT_TYPE_LAYOUT_KEY_ALREADY_EXISTS','Cannot insert prod-type-layout configuration_key "%s" because it already exists');
  define('REASON_INDEX_DOESNT_EXIST_TO_DROP','Cannot drop index %s on table %s because it does not exist.');
  define('REASON_PRIMARY_KEY_DOESNT_EXIST_TO_DROP','Cannot drop primary key on table %s because it does not exist.');
  define('REASON_INDEX_ALREADY_EXISTS','Cannot add index %s to table %s because it already exists.');
  define('REASON_PRIMARY_KEY_ALREADY_EXISTS','Cannot add primary key to table %s because a primary key already exists.');
}

class SQLRunner {
    static $prefix;

    static function get_db() {
        if (null == self::$prefix) self::$prefix = \ZMRuntime::getDatabase()->getPrefix();
        return \ZMRuntime::getDatabase();
    }

 static function execute_sql($lines, $debug = false) {
   $database = \ZMRuntime::getDatabase()->getDatabase();
   if (!get_cfg_var('safe_mode')) {
     @set_time_limit(1200);
   }
   $db = self::get_db();
   $sql_file='SQLPATCH';
   $newline = '';
   $saveline = '';
   $ignored_count=0;
   $ignore_line=false;
   $lines_to_keep_together_counter=0;
   $return_output=array();
   $errors = array();
   $results = 0;
   $string='';
   foreach ($lines as $line) {
     if ($debug) echo $line . '<br />';

     $line = trim($line);
     $line = str_replace('`','',$line); //remove backquotes
     $line = $saveline . $line;
     $keep_together = 1; // count of number of lines to treat as a single command

     // split the line into words ... starts at $param[0] and so on.  Also remove the ';' from end of last param if exists
     $param=explode(" ",(substr($line,-1)==';') ? substr($line,0,strlen($line)-1) : $line);

      // The following command checks to see if we're asking for a block of commands to be run at once.
      // Syntax: #NEXT_X_ROWS_AS_ONE_COMMAND:6     for running the next 6 commands together (commands denoted by a ;)
      if (substr($line,0,28) == '#NEXT_X_ROWS_AS_ONE_COMMAND:') $keep_together = substr($line,28);
      if (substr($line,0,1) != '#' && substr($line,0,1) != '-' && $line != '') {
//        if (self::$prefix != -1) {
//echo '*}'.$line.'<br>';

          $line_upper=strtoupper($line);
          switch (true) {
          case (substr($line_upper, 0, 21) == 'DROP TABLE IF EXISTS '):
            $line = 'DROP TABLE IF EXISTS ' . self::$prefix . substr($line, 21);
            break;
          case (substr($line_upper, 0, 11) == 'DROP TABLE ' && $param[2] != 'IF'):
            if (!self::table_exists(self::$prefix.$param[2]) || !Toolbox::isEmpty($result)) {
              self::write_to_upgrade_exceptions_table($line, (!Toolbox::isEmpty($result) ? $result : sprintf(_zm('Table does not exist'),$param[2])), $sql_file);
              $ignore_line=true;
              $result=(!Toolbox::isEmpty($result) ? $result : sprintf(_zm('Table does not exist'),$param[2])); //duplicated here for on-screen error-reporting
              break;
            } else {
              $line = 'DROP TABLE ' . self::$prefix . substr($line, 11);
            }
            break;
          case (substr($line_upper, 0, 13) == 'CREATE TABLE '):
            // check to see if table exists
            $table = (isset($param[4]) && (strtoupper($param[2].' '.$param[3].' '.$param[4]) == 'IF NOT EXISTS')) ? $param[5] : $param[2];
            $result=self::table_exists(self::$prefix.$table);
            if ($result==true) {
              self::write_to_upgrade_exceptions_table($line, sprintf(_zm('Table already exists'),$table), $sql_file);
              $ignore_line=true;
              $result=sprintf(_zm('Table already exists'),$table); //duplicated here for on-screen error-reporting
              break;
            } else {
              $line = (isset($param[4]) && (strtoupper($param[2].' '.$param[3].' '.$param[4]) == 'IF NOT EXISTS')) ? 'CREATE TABLE IF NOT EXISTS ' . self::$prefix . substr($line, 27) : 'CREATE TABLE ' . self::$prefix . substr($line, 13);
            }
            break;
          case (substr($line_upper, 0, 15) == 'TRUNCATE TABLE '):
            // check to see if TRUNCATE command may be safely executed
            if (!$tbl_exists = self::table_exists(self::$prefix.$param[2])) {
              $result=sprintf(_zm('Table not found'),$param[2]).' CHECK PREFIXES!' . $param[2];
              self::write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'TRUNCATE TABLE ' . self::$prefix . substr($line, 15);
            }
            break;
          case (substr($line_upper, 0, 13) == 'REPLACE INTO '):
            //check to see if table prefix is going to match
            if (!$tbl_exists = self::table_exists(self::$prefix.$param[2])) $result=sprintf(_zm('Table not found'),$param[2]).' CHECK PREFIXES!';
            // check to see if INSERT command may be safely executed for "configuration" or "product_type_layout" tables
            if (($param[2]=='configuration'       && ($result=self::check_config_key($line))) or
                ($param[2]=='product_type_layout' && ($result=self::check_product_type_layout_key($line))) or
                (!$tbl_exists)    ) {
                  self::write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'REPLACE INTO ' . self::$prefix . substr($line, 13);
            }
            break;
          case (substr($line_upper, 0, 12) == 'INSERT INTO '):
            //check to see if table prefix is going to match
            if (!$tbl_exists = self::table_exists(self::$prefix.$param[2])) $result=sprintf(_zm('Table not found'),$param[2]).' CHECK PREFIXES!';
            // check to see if INSERT command may be safely executed for "configuration" or "product_type_layout" tables
            if (($param[2]=='configuration'       && ($result=self::check_config_key($line))) or
                ($param[2]=='product_type_layout' && ($result=self::check_product_type_layout_key($line))) or
                (!$tbl_exists)    ) {
                  self::write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'INSERT INTO ' . self::$prefix . substr($line, 12);
            }
            break;
          case (substr($line_upper, 0, 19) == 'INSERT IGNORE INTO '):
            //check to see if table prefix is going to match
            if (!$tbl_exists = self::table_exists(self::$prefix.$param[3])) {
              $result=sprintf(_zm('Table not found'),$param[3]).' CHECK PREFIXES!';
              self::write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'INSERT IGNORE INTO ' . self::$prefix . substr($line, 19);
            }
            break;
          case (substr($line_upper, 0, 12) == 'ALTER TABLE '):
            // check to see if ALTER command may be safely executed
            if ($result=self::check_alter_command($param)) {
              self::write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'ALTER TABLE ' . self::$prefix . substr($line, 12);
            }
            break;
          case (substr($line_upper, 0, 13) == 'RENAME TABLE '):
            // RENAME TABLE command cannot be parsed to insert table prefixes, so skip if zen is using prefixes
            if (!Toolbox::isEmpty(self::$prefix)) {
              self::write_to_upgrade_exceptions_table($line, 'RENAME TABLE command not supported by upgrader. Please use phpMyAdmin instead.', $sql_file);
              $messageStack->addMessage('RENAME TABLE command not supported by upgrader. Please use phpMyAdmin instead.', 'caution');

              $ignore_line=true;
            }
            break;
          case (substr($line_upper, 0, 7) == 'UPDATE '):
            //check to see if table prefix is going to match
            if (!$tbl_exists = self::table_exists(self::$prefix.$param[1])) {
              self::write_to_upgrade_exceptions_table($line, sprintf(_zm('Table not found'),$param[1]).' CHECK PREFIXES!', $sql_file);
              $result=sprintf(_zm('Table not found'),$param[1]).' CHECK PREFIXES!';
              $ignore_line=true;
              break;
            } else {
              $line = 'UPDATE ' . self::$prefix . substr($line, 7);
            }
            break;
          case (substr($line_upper, 0, 14) == 'UPDATE IGNORE '):
            //check to see if table prefix is going to match
            if (!$tbl_exists = self::table_exists(self::$prefix.$param[2])) {
              self::write_to_upgrade_exceptions_table($line, sprintf(_zm('Table not found'),$param[2]).' CHECK PREFIXES!', $sql_file);
              $result=sprintf(_zm('Table not found'),$param[2]).' CHECK PREFIXES!';
              $ignore_line=true;
              break;
            } else {
              $line = 'UPDATE IGNORE ' . self::$prefix . substr($line, 14);
            }
            break;
         case (substr($line_upper, 0, 12) == 'DELETE FROM '):
            $line = 'DELETE FROM ' . self::$prefix . substr($line, 12);
            break;
          case (substr($line_upper, 0, 11) == 'DROP INDEX '):
            // check to see if DROP INDEX command may be safely executed
            if ($result=self::drop_index_command($param)) {
              self::write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              $line = 'DROP INDEX ' . $param[2] . ' ON ' . self::$prefix . $param[4];
            }
            break;
          case (substr($line_upper, 0, 13) == 'CREATE INDEX ' || (strtoupper($param[0])=='CREATE' && strtoupper($param[2])=='INDEX')):
            // check to see if CREATE INDEX command may be safely executed
            if ($result=self::create_index_command($param)) {
              self::write_to_upgrade_exceptions_table($line, $result, $sql_file);
              $ignore_line=true;
              break;
            } else {
              if (strtoupper($param[1])=='INDEX') {
                $line = trim('CREATE INDEX ' . $param[2] .' ON '. self::$prefix . implode(' ',array($param[4],$param[5],$param[6],$param[7],$param[8],$param[9],$param[10],$param[11],$param[12],$param[13])) ).';'; // add the ';' back since it was removed from $param at start
              } else {
                $line = trim('CREATE '. $param[1] .' INDEX ' .$param[3]. ' ON '. self::$prefix . implode(' ',array($param[5],$param[6],$param[7],$param[8],$param[9],$param[10],$param[11],$param[12],$param[13])) ); // add the ';' back since it was removed from $param at start
              }
            }
            break;
          case (substr($line_upper, 0, 8) == 'SELECT (' && substr_count($line,'FROM ')>0):
            $line = str_replace('FROM ','FROM '. self::$prefix, $line);
            break;
          case (substr($line_upper, 0, 10) == 'LEFT JOIN '):
            $line = 'LEFT JOIN ' . self::$prefix . substr($line, 10);
            break;
          case (substr($line_upper, 0, 5) == 'FROM '):
            if (substr_count($line,',')>0) { // contains FROM and a comma, thus must parse for multiple tablenames
              $tbl_list = explode(',',substr($line,5));
              $line = 'FROM ';
              foreach($tbl_list as $val) {
                $line .= self::$prefix . trim($val) . ','; // add prefix and comma
              } //end foreach
              if (substr($line,-1)==',') $line = substr($line,0,(strlen($line)-1)); // remove trailing ','
            } else { //didn't have a comma, but starts with "FROM ", so insert table prefix
              $line = str_replace('FROM ', 'FROM '.self::$prefix, $line);
            }//endif substr_count(,)
            break;
          default:
            break;
          } //end switch
//        } // endif self::$prefix
        $newline .= $line . ' ';

        if ( substr($line,-1) ==  ';') {
          //found a semicolon, so treat it as a full command, incrementing counter of rows to process at once
          if (substr($newline,-1)==' ') $newline = substr($newline,0,(strlen($newline)-1));

          $complete_line = false;
          $lines_to_keep_together_counter++;
          if ($lines_to_keep_together_counter == $keep_together) { // if all grouped rows have been loaded, go to execute.
            $complete_line = true;
            $lines_to_keep_together_counter=0;
          }
        } //endif found ';'

        if (isset($complete_line) && $complete_line) {
          if ($debug==true) echo ((!$ignore_line) ? '<br />About to execute.': 'Ignoring statement. This command WILL NOT be executed.').'<br />Debug info:<br>$ line='.$line.'<br>$ complete_line='.$complete_line.'<br>$ keep_together='.$keep_together.'<br>SQL='.$newline.'<br><br>';
          if (get_magic_quotes_runtime() > 0  && $keepslashes != true ) $newline=stripslashes($newline);
          if (trim(str_replace(';','',$newline)) != '' && !$ignore_line) {
            if (!in_array(strtolower(substr($newline, 0, 3)), array('des', 'sel', 'sho'))) {
              $output = $db->executeUpdate($newline);
            } else {
              $output = $db->executeQuery($newline);
            }
          }
          $results++;
          $string .= $newline.'<br />';
          $return_output[]=$output;
          if (isset($result) && !Toolbox::isEmpty($result)) $errors[]=$result;
          // reset var's
          $newline = '';
          $keep_together=1;
          $complete_line = false;
          if ($ignore_line) $ignored_count++;
          $ignore_line=false;

          // show progress bar
          global $zc_show_progress;
          if ($zc_show_progress=='yes') {
             $counter++;
             if ($counter/5 == (int)($counter/5)) echo '~ ';
             if ($counter>200) {
               echo '<br /><br />';
               $counter=0;
             }
             @ob_flush();
             @flush();
          }

        } //endif $complete_line

      } //endif ! # or -
    } // end foreach $lines
   return array('queries'=> $results, 'string'=>$string, 'output'=>$return_output, 'ignored'=>($ignored_count), 'errors'=>$errors);
  } //end function

  static function table_exists($tablename, $pre_install=false) {
    $sm = \ZMRuntime::getDatabase()->getSchemaManager();
    $exists = $sm->tablesExist(array($tablename));
    if (ZC_UPG_DEBUG3==true) echo 'Table check ('.$tablename.') = '. ($exists ? 'true': 'false') .'<br>';
    return $exists;
  }

  static function drop_index_command($param) {
    //this is only slightly different from the ALTER TABLE DROP INDEX command
    $db = self::get_db();
    if (Toolbox::isEmpty($param)) return "Empty SQL Statement";
    $index = $param[2];
    $sql = "show index from " . self::$prefix . $param[4];
    $rows = $db->fetchAll($sql);
    foreach ($rows as $fields) {
      if (ZC_UPG_DEBUG3==true) echo $fields['Key_name'].'<br />';
      if  ($fields['Key_name'] == $index) {
        return; // if we get here, the index exists, and we have index privileges, so return with no error
      }
    }
    // if we get here, then the index didn't exist
    return sprintf(REASON_INDEX_DOESNT_EXIST_TO_DROP,$index,$param[4]);
  }

  static function create_index_command($param) {
    //this is only slightly different from the ALTER TABLE CREATE INDEX command
    $db = self::get_db();
    if (Toolbox::isEmpty($param)) return "Empty SQL Statement";
    $index = (strtoupper($param[1])=='INDEX') ? $param[2] : $param[3];
    if (in_array('USING',$param)) return 'USING parameter found. Cannot validate syntax. Please run manually in phpMyAdmin.';
    $table = (strtoupper($param[2])=='INDEX' && strtoupper($param[4])=='ON') ? $param[5] : $param[4];
    $sql = "show index from " . self::$prefix . $table;
    $rows = $db->fetchAll($sql);
    foreach ($rows as $fields) {
      if (ZC_UPG_DEBUG3==true) echo $fields['Key_name'].'<br />';
      if (strtoupper($fields['Key_name']) == strtoupper($index)) {
        return sprintf(REASON_INDEX_ALREADY_EXISTS,$index,$table);
      }
    }
/*
 * @TODO: verify that individual columns exist, by parsing the index_col_name parameters list
 *        Structure is (colname(len)),
 *                  or (colname),
 */
  }

  static function check_alter_command($param) {
    $db = self::get_db();
    if (Toolbox::isEmpty($param)) return "Empty SQL Statement";
    switch (strtoupper($param[3])) {
      case ("ADD"):
        if (strtoupper($param[4]) == 'INDEX') {
          // check that the index to be added doesn't already exist
          $index = $param[5];
          $sql = "show index from " . self::$prefix . $param[2];
          $rows = $db->fetchAll($sql);
          foreach ($rows as $fields) {
            if (ZC_UPG_DEBUG3==true) echo 'KEY: '.$fields['Key_name'].'<br />';
            if  ($fields['Key_name'] == $index) {
              return sprintf(REASON_INDEX_ALREADY_EXISTS,$index,$param[2]);
            }
          }
        } elseif (strtoupper($param[4])=='PRIMARY') {
          // check that the primary key to be added doesn't exist
          if ($param[5] != 'KEY') return;
          $sql = "show index from " . self::$prefix . $param[2];
          $rows = $db->fetchAll($sql);
          foreach ($rows as $fields) {
            if (ZC_UPG_DEBUG3==true) echo $fields['Key_name'].'<br />';
            if  ($fields['Key_name'] == 'PRIMARY') {
              return sprintf(REASON_PRIMARY_KEY_ALREADY_EXISTS,$param[2]);
            }
          }

        } elseif (!in_array(strtoupper($param[4]),array('CONSTRAINT','UNIQUE','PRIMARY','FULLTEXT','FOREIGN','SPATIAL') ) ) {
        // check that the column to be added does not exist
          $colname = ($param[4]=='COLUMN') ? $param[5] : $param[4];
          $sql = "show fields from " . self::$prefix . $param[2];
          $rows = $db->fetchAll($sql);
          foreach ($rows as $fields) {
            if (ZC_UPG_DEBUG3==true) echo $fields['Field'].'<br />';
            if  ($fields['Field'] == $colname) {
              return sprintf(REASON_COLUMN_ALREADY_EXISTS,$colname);
            }
          }

        } elseif (strtoupper($param[5])=='AFTER') {
          // check that the requested "after" field actually exists
          $colname = ($param[6]=='COLUMN') ? $param[7] : $param[6];
          $sql = "show fields from " . self::$prefix . $param[2];
          $rows = $db->fetchAll($sql);
          foreach ($rows as $fields) {
            if (ZC_UPG_DEBUG3==true) echo $fields['Field'].'<br />';
            if  ($fields['Field'] == $colname) {
              return; // exists, so return with no error
            }
          }

        } elseif (strtoupper($param[6])=='AFTER') {
          // check that the requested "after" field actually exists
          $colname = ($param[7]=='COLUMN') ? $param[8] : $param[7];
          $sql = "show fields from " . self::$prefix . $param[2];
          $rows = $db->fetchAll($sql);
          foreach ($rows as $fields) {
            if (ZC_UPG_DEBUG3==true) echo $fields['Field'].'<br />';
            if  ($fields['Field'] == $colname) {
              return; // exists, so return with no error
            }
          }
/*
 * @TODO -- add check for FIRST parameter, to check that the FIRST colname specified actually exists
 */
        }
        break;
      case ("DROP"):
        if (strtoupper($param[4]) == 'INDEX') {
          // check that the index to be dropped exists
          $index = $param[5];
          $sql = "show index from " . self::$prefix . $param[2];
          $rows = $db->fetchAll($sql);
          foreach ($rows as $fields) {
            if (ZC_UPG_DEBUG3==true) echo $fields['Key_name'].'<br />';
            if  ($fields['Key_name'] == $index) {
              return; // exists, so return with no error
            }
          }
          // if we get here, then the index didn't exist
          return sprintf(REASON_INDEX_DOESNT_EXIST_TO_DROP,$index,$param[2]);

        } elseif (strtoupper($param[4])=='PRIMARY') {
          // check that the primary key to be dropped exists
          if ($param[5] != 'KEY') return;
          $sql = "show index from " . self::$prefix . $param[2];
          $rows = $db->fetchAll($sql);
          foreach ($rows as $fields) {
            if (ZC_UPG_DEBUG3==true) echo $fields['Key_name'].'<br />';
            if  ($fields['Key_name'] == 'PRIMARY') {
              return; // exists, so return with no error
            }
          }
          // if we get here, then the primary key didn't exist
          return sprintf(REASON_PRIMARY_KEY_DOESNT_EXIST_TO_DROP,$param[2]);

        } elseif (!in_array(strtoupper($param[4]),array('CONSTRAINT','UNIQUE','PRIMARY','FULLTEXT','FOREIGN','SPATIAL'))) {
          // check that the column to be dropped exists
          $colname = ($param[4]=='COLUMN') ? $param[5] : $param[4];
          $sql = "show fields from " . self::$prefix . $param[2];
          $rows = $db->fetchAll($sql);
          foreach ($rows as $fields) {
            if (ZC_UPG_DEBUG3==true) echo $fields['Field'].'<br />';
            if  ($fields['Field'] == $colname) {
              return; // exists, so return with no error
            }
          }
          // if we get here, then the column didn't exist
          return sprintf(REASON_COLUMN_DOESNT_EXIST_TO_DROP,$colname);
        }//endif 'DROP'
        break;
      case ("ALTER"):
      case ("MODIFY"):
      case ("CHANGE"):
        // just check that the column to be changed 'exists'
        $colname = ($param[4]=='COLUMN') ? $param[5] : $param[4];
        $sql = "show fields from " . self::$prefix . $param[2];
        $rows = $db->fetchAll($sql);
        foreach ($rows as $fields) {
          if (ZC_UPG_DEBUG3==true) echo $fields['Field'].'<br />';
          if  ($fields['Field'] == $colname) {
            return; // exists, so return with no error
          }
        }
        // if we get here, then the column didn't exist
        return sprintf(REASON_COLUMN_DOESNT_EXIST_TO_CHANGE,$colname);
        break;
      default:
        // if we get here, then we're processing an ALTER command other than what we're checking for, so let it be processed.
        return;
        break;
    } //end switch
  }

  static function check_config_key($line) {
    $db = self::get_db();
    $values=array();
    $values=explode("'",$line);
     //INSERT INTO configuration blah blah blah VALUES ('title','key', blah blah blah);
     //[0]=INSERT INTO.....
     //[1]=title
     //[2]=,
     //[3]=key
     //[4]=blah blah
    if(!isset($values[1])) return; // Can't search for what we don't have!
    $title = $values[1];
    $key  =  $values[3];
    $sql = "select configuration_title from " . self::$prefix . "configuration where configuration_key='".$key."'";
    $result = $db->querySingle($sql);
    if (!empty($result)) return sprintf(REASON_CONFIG_KEY_ALREADY_EXISTS,$key);
  }

  static function check_product_type_layout_key($line) {
    $db = self::get_db();
    $values=array();
    $values=explode("'",$line);
    $title = $values[1];
    $key  =  $values[3];
    $sql = "select configuration_title from " . self::$prefix . "product_type_layout where configuration_key='".$key."'";
    $result = $db->querySingle($sql);
    if (!empty($result)) return sprintf(REASON_PRODUCT_TYPE_LAYOUT_KEY_ALREADY_EXISTS,$key);
  }

  static function write_to_upgrade_exceptions_table($line, $reason, $sql_file) {
    $db = self::get_db();
    self::create_exceptions_table();
    $sql="INSERT INTO " . self::$prefix . "upgrade_exceptions VALUES (0,'". $sql_file."','".$reason."', now(), '".addslashes($line)."')";
     if (ZC_UPG_DEBUG3==true) echo '<br />sql='.$sql.'<br />';
    $result = $db->executeUpdate($sql);
    return $result;
  }

  static function purge_exceptions_table() {
    $db = self::get_db();
    self::create_exceptions_table();
    $result = $db->executeUpdate("TRUNCATE TABLE " . self::$prefix."upgrade_exceptions");
    return $result;
  }

  static function create_exceptions_table() {
    $db = self::get_db();
    if (!self::table_exists(self::$prefix.'upgrade_exceptions')) {
        $result = $db->executeUpdate("CREATE TABLE " . self::$prefix . "upgrade_exceptions (
            upgrade_exception_id smallint(5) NOT NULL auto_increment,
            sql_file varchar(50) default NULL,
            reason varchar(200) default NULL,
            errordate datetime default '0001-01-01 00:00:00',
            sqlstatement text, PRIMARY KEY  (upgrade_exception_id)
          ) engine=MyISAM   ");
    return $result;
    }
  }

  static function create_message($msg, $type) {
    $message = Beans::getBean('ZenMagick\Http\Messages\Message');
    $message->setText($msg);
    $message->setType($type);
    return $message;
  }

  /**
   * Process SQL patch messages.
   *
   * @param array The execution results.
   * @return array The results converted to messages.
   */
  static function process_patch_results($results) {
    $messages = array();
    if ($results['queries'] > 0 && $results['queries'] != $results['ignored']) {
      $messages[] = self::create_message($results['queries'].' statements processed.', 'success');
    } else {
      $messages[] = self::create_message('Failed: '.$results['queries'].'.', 'error');
    }

    if (!empty($results['errors'])) {
      foreach ($results['errors'] as $value) {
        $messages[] = self::create_message('ERROR: '.$value.'.', 'error');
      }
    }
    if ($results['ignored'] != 0) {
      $messages[] = self::create_message('Note: '.$results['ignored'].' statements ignored. See "upgrade_exceptions" table for additional details.', 'warn');
    }

    return $messages;
  }
}
