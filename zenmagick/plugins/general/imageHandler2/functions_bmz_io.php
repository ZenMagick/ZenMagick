<?php
/**
 * functions_bmz_io.php
 * general filesystem access handling
 *
 * @author  Tim Kroeger (original author)
 * @author Andreas Gohr
 * @copyright Copyright 2005-2006
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 * @version $Id: functions_bmz_io.php,v 2.0 Rev 8 2010-05-31 23:46:5 DerManoMann Exp $
 * Last modified by DerManoMann 2010-05-31 23:46:50 
 */

/**
 * Tries to lock a file
 *
 * Locking uses directories inside $bmzConf['lockdir']
 *
 * It waits maximal 3 seconds for the lock, after this time
 * the lock is assumed to be stale and the function goes on 
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Tim Kroeger <tim@breakmyzencart.com>
 */
function io_lock($file){
  global $bmzConf;
  // no locking if safemode hack
  if ($bmzConf['safemodehack']) return;

  $lockDir = $bmzConf['lockdir'] . '/' . md5($file);
  @ignore_user_abort(1);

  
  $timeStart = time();
  do {
    //waited longer than 3 seconds? -> stale lock
    if ((time() - $timeStart) > 3) break;
    $locked = @mkdir($lockDir);
  } while ($locked === false);
}

/**
 * Unlocks a file
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Tim Kroeger <tim@breakmyzencart.com>
 */
function io_unlock($file){
  global $bmzConf;

  // no locking if safemode hack
  if($bmzConf['safemodehack']) return;

  $lockDir = $bmzConf['lockdir'] . '/' . md5($file);
  @rmdir($lockDir);
  @ignore_user_abort(0);
}

/**
 * Returns the name of a cachefile from given data
 *
 * The needed directory is created by this function!
 *
 * @author Andreas Gohr <andi@splitbrain.org>
 * @author Tim Kroeger <tim@breakmyzencart.com>
 *
 * @param string $data  This data is used to create a unique md5 name
 * @param string $ext   This is appended to the filename if given
 * @return string       The filename of the cachefile
 */
function getCacheName($data, $ext='') {
  global $bmzConf;

  $md5  = md5($data);
  $file = $bmzConf['cachedir'] . '/' . $md5{0} . '/' . $md5.$ext;
  io_makeFileDir($file);
  return $file;
}

/**
 * Create the directory needed for the given file
 *
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @author  Tim Kroeger <tim@breakmyzencart.com>
 */
function io_makeFileDir($file){
  global $messageStack;
  global $bmzConf;

  $dir = dirname($file);
  $dmask = $bmzConf['dmask'];
  umask($dmask);
  if(!is_dir($dir)){
    io_mkdir_p($dir) || $messageStack->add("Creating directory $dir failed", "error");
  }
  umask($bmzConf['umask']); 
}

/**
 * Creates a directory hierachy.
 *
 * @link    http://www.php.net/manual/en/function.mkdir.php
 * @author  <saint@corenova.com>
 * @author  Andreas Gohr <andi@splitbrain.org>
 * @author  Tim Kroeger <tim@breakmyzencart.com>
 */
function io_mkdir_p($target){
  global $bmzConf;

  if (is_dir($target) || empty($target)) return 1; // best case check first
  if (@file_exists($target) && !is_dir($target)) return 0;
  //recursion
  if (io_mkdir_p(substr($target, 0, strrpos($target, '/')))){
    if($bmzConf['safemodehack']){
      $dir = preg_replace('/^' . preg_quote(realpath($bmzConf['ftp']['root']), '/') . '/', '', $target);
      return io_mkdir_ftp($dir);
    }else{
      return @mkdir($target, 0777); // crawl back up & create dir tree
    }
  }
  return 0;
}

/**
 * Creates a directory using FTP
 *
 * This is used when the safemode workaround is enabled
 *
 * @author <andi@splitbrain.org>
 */
function io_mkdir_ftp($dir){
  global $messageStack;
  global $bmzConf;

  if(!function_exists('ftp_connect')){
    $messageStack->add("FTP support not found - safemode workaround not usable", "error");
    return false;
  }
  
  $conn = @ftp_connect($bmzConf['ftp']['host'], $bmzConf['ftp']['port'], 10);
  if(!$conn){
    $messageStack->add("FTP connection failed", "error");
    return false;
  }

  if(!@ftp_login($conn, $bmzConf['ftp']['user'], $bmzConf['ftp']['pass'])){
    $messageStack->add("FTP login failed", "error");
    return false;
  }

  //create directory
  $ok = @ftp_mkdir($conn, $dir);
  //set permissions (using the directory umask)
  @ftp_site($conn, sprintf("CHMOD %04o %s", (0777 - $bmzConf['dmask']), $dir));

  @ftp_close($conn);
  return $ok;
}


/**
 * functions_bmz_io.php
 * admin filesystem functions for image handler
 *
 * @author  Tim Kroeger (original author)
 * @copyright Copyright 2005-2006
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 * @version $Id: functions_bmz_io.php,v 2.0 Rev 8 2010-05-31 23:46:5 DerManoMann Exp $
 * Last modified by DerManoMann 2010-05-31 23:46:50 
 */

function bmz_clear_cache() {
	global $bmzConf;
	return remove_dir($bmzConf['cachedir']);
}

function remove_dir($dirname) {
  global $messageStack;
  $error = false;
  if ($dir = @dir($dirname)) {
    $dir->rewind();
    while ($file = $dir->read()) {
      //echo $dirname . '/' . $file . '<br />';
      if (($file != ".") && ($file != "..")) {
        if (is_dir($dirname . '/' . $file)) {
      	// another directory, recurse
          $error |= remove_dir($dirname . '/' . $file);
	      // if it was a directory, it should be empty now
          if (!@rmdir($dirname . '/' . $file)) {
            $error |= true;
            $messageStack->add('Couldn\'t delete ' . $dirname . '/' . $file . '.', 'error');
          }
        } else {
          if (!@unlink($dirname . '/' . $file)) {
            $error |= true;
            $messageStack->add('Couldn\'t delete ' . $dirname . '/' . $file . '.', 'error');
          }
        }
      }
    }
    $dir->close();
  } else {
  	 $error |= true;
  }
  return $error;
}
