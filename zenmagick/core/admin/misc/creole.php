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
 *
 * $Id$
 */
?>
<?php  

/*
$creole = 'creole-1.1.0';
zm_creole_import('C:/TEMP/'.$creole);

$comp = new ZMPhpCompressor();
$comp->setRoot('C:\Program Files\Apache Group\Apache2\htdocs\zen-cart\zenmagick\core\ext/'.$creole);
$comp->setOut('C:\Program Files\Apache Group\Apache2\htdocs\zen-cart\zenmagick\core\ext/'.$creole.'.php');
$comp->setTemp('C:\Program Files\Apache Group\Apache2\htdocs\zen-cart\tmp');
//$comp->setStripCode(false);
$comp->compress();
ZMTools::rmdir('C:\Program Files\Apache Group\Apache2\htdocs\zen-cart\zenmagick\core\ext/'.$creole);
*/

    /**
     * Import Creole into a subdirectory under <code>core/ext</code>.
     *
     * <p>Depends on <code>ZMLoader</code> and <code>ZMTools</code>.</p>
     *
     * @param string creoleDir The Creole root directory, for example '<em>C:/TEMP/creole-1.1.0</em>'.
     * @param string outDir The output directory for the prepared files; default is <code>null</code> 
     *  for <code>ZMRuntime::getZMRootPath().'/core/ext/'.basename($creoleDir).'/'</code>.
     * @param boolean debug If true create debug output; default is false.
     */
    function zm_creole_import($creoleDir, $outDir=null, $debug=false) {
        if (null != $outDir && is_dir($outDir)) {
            $creoleExtDir = $outDir;
        } else {
            $creoleExtDir = ZMRuntime::getZMRootPath().'/core/ext/'.basename($creoleDir).'/';
        }
        $creoleDir .= '/classes/';
        $creoleFiles = ZMLoader::findIncludes($creoleDir, true);
        ZMLoader::instance()->resolve('InstallationPatch');
        $patch = ZMLoader::make('FilePatch', 'patch');

        $fileMap = array();
        $dependsOn = array();
        foreach ($creoleFiles as $file) {
            $lines = $patch->getFileLines($file);
            $patched = false;
            $class = str_replace('.php', '', basename($file));
            $fileMap[$class] = $file;
            $dependsOn[$class] = array();
            foreach ($lines as $ii => $line) {
                if (preg_match('/^\s*\/?\/?\s*(require_once|require|include_once|include){1}\s*\(?\s*[\'"](.*)[\'"]\s*\)?\s*;.*$/', $line, $matches)) {
                    $dependsOn[$class][] = str_replace('.php', '', basename($matches[2]));
                }
            }
            if ('DebugConnection' == $class) {
                // FIX: missing require/include
                $dependsOn[$class][] = 'Connection';
            }
        }

        $resolved = array();

        $levelIndex = 0;
        $treeMap = array();
        // while not all resolved
        while (count($resolved) < count($dependsOn)) {
            $level = array();
            // iterate through all classes
            foreach ($dependsOn as $class => $dependencies) {
                if (isset($resolved[$class])) {
                    // already good
                    continue;
                }

                $clear = true;
                // check if all dependencies are resolved
                foreach ($dependencies as $dclass) {
                    if (!isset($resolved[$dclass])) {
                        $clear = false;
                        if ($debug) echo $class."; missing dep: ".$dclass."<BR>";
                    }
                }

                // record contains a circular reference as far as this logic is concerned
                if ($clear || ('Record' == $class && 1 == $levelIndex)) {
                    if ($debug) echo '<br>resolved: '.$class.' depending on';
                    if ($debug) print_r($dependencies);
                    $level[$class] = $class;
                }
            }

            $treeMap[$levelIndex] = $level;
            $resolved = array_merge($resolved, $level);

            $levelIndex++;

            if ($debug) {
                echo "<br><br>=======".$levelIndex."============<BR>";
                if (10 == $levelIndex) { break; }
            }
        }

        if ($debug) {
            echo  count($resolved) . ' - ' . count($dependsOn) . '<br>';
            var_dump($treeMap);
        }

        $currentDir = $creoleExtDir;
        foreach ($treeMap as $level => $classes) {
            if (0 < $level) {
                $currentDir .= $level.'/';
            }

            ZMTools::mkdir($currentDir);

            foreach ($classes as $class) {
                $inFile = $fileMap[$class];
                $lines = $patch->getFileLines($inFile);
                foreach ($lines as $ii => $line) {
                    if (preg_match('/^\s*\s*(require_once|require|include_once|include){1}\s*\(?\s*[\'"](.*)[\'"]\s*\)?\s*;.*$/', $line, $matches)) {
                        $lines[$ii] = '//'.$line;
                    }
                }
                // fix missing '?'.'>' at end of files
                for ($ii=count($lines); $ii>0; --$ii) {
                    $line = trim($lines[$ii-1]);
                    if (0 < strlen($line)) {
                        if ('>' != substr($line, -1)) {
                            $lines[] = '?'.'>';
                        }
                        break;
                    }
                }
                $extFile = $currentDir.basename($inFile);
                $patch->putFileLines($extFile, $lines);
            }
        }
    }

?>
