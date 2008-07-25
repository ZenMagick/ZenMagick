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
 * Handle patches for a single file.
 *
 * @author mano
 * @package org.zenmagick.admin.misc
 * @version $Id$
 */
class ZMFilePatcher extends ZMObject {
    private $filename;
    private $patch;
    private $target;
    private $lines;


    /**
     * Create new patcher.
     *
     * @param string filename The file to patch.
     * @param array patch The patch information.
     * @param string target Optional target filename; default is <code>null</code> to update <em>filename</em>.
     */
    function __construct($filename, $patch, $target=null) {
        parent::__construct();
        $this->filename = $filename;
        $this->patch = $patch;
        $this->target = null !== $target ? $target : $filename;
        $this->lines = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Get the file content as single lines.
     *
     * @return array An array of lines.
     */
    protected function getLines() {
        if (null === $this->lines) {
            $this->lines = array();
            if (file_exists($this->filename)) {
                $handle = fopen($this->filename, 'rb');
                if ($handle) {
                    while (!feof($handle)) {
                        $this->lines[] = rtrim(fgets($handle, 4096));
                    }
                    fclose($handle);
                }
            }
        }

        return $this->lines;
    }

    /**
     * Write the given lines.
     *
     * @param array lines The  lines to write.
     * @return boolean <code>true</code> if successful, <code>false</code> if not.
     */
    protected function putLines($lines) {
        $handle = fopen($this->target, 'wb');
        if ($handle) {
            foreach ($lines as $ii => $line) {
                $eol = 0 < $ii ? "\n" : '';
                fwrite($handle, $eol.$line);
            }
            fclose($handle);
            return true;
        }

        return false;
    }

    /**
     * Apply the patch to the given lines.
     *
     * @param array lines Contents as separate lines.
     * @return array The modified array if changes have been made, <code>null</code> if unchanged.
     */
    protected function applyPatch($lines) {
        $modified = false;
        $patched = array();
        foreach ($lines as $ii => $line) {

            // for each line run through all patch instructions and see if any changes need to be done
            foreach ($this->patch as $info) {
                $pattern = is_array($info['match']) ? $info['match'] : array($info['match']);
                $matched = true;
                foreach ($pattern as $fragment) {
                    if (false === strpos($line, $fragment)) {
                        $matched = false;
                        break;
                    }
                }
                if (!$matched) {
                    $patched[] = $line;
                } else {
                    // the new/replace data
                    $data = is_array($info['data']) ? $info['data'] : array($info['data']);
                    $lastData = count($data) - 1;

                    // line matches patch pattern
                    switch ($info['action']) {
                    case 'insert-before':
                        if (-1 < $lastData && false === strpos($lines[$ii-1], $data[$lastData])) {
                            $modified = true;
                            // prepend data
                            foreach ($data as $dataLine) {
                                $patched[] = $dataLine;
                            }
                        }
                        // add original line
                        $patched[] = $line;
                        break;
                    case 'insert-after':
                        // add original line
                        $patched[] = $line;
                        if (-1 < $lastData && (($ii == count($lines)-1) || false === strpos($lines[$ii+1], $data[0]))) {
                            $modified = true;
                            // append data
                            foreach ($data as $dataLine) {
                                $patched[] = $dataLine;
                            }
                        }
                        break;
                    case 'replace':
                        $modified = true;
                        // remove matching line and append data
                        foreach ($data as $dataLine) {
                            $patched[] = $dataLine;
                        }
                        break;
                    }
                } 
            }
        }

        return $modified ? $patched : null;
    }

    /**
     * Revert the patch to the given lines.
     *
     * @param array lines Contents as separate lines.
     * @return array The modified array if changes have been made, <code>null</code> if unchanged.
     */
    protected function revertPatch($lines) {
        $modified = false;
        $patched = array();
        // used to consume unwanted lines
        $skipLines = 0;
        foreach ($lines as $ii => $line) {
            if (0 < $skipLines) {
                --$skipLines;
                $modified = true;
                continue;
            }

            // for each line run through all patch instructions and see if any changes need to be done
            foreach ($this->patch as $info) {
                $pattern = is_array($info['match']) ? $info['match'] : array($info['match']);
                $matched = true;
                foreach ($pattern as $fragment) {
                    if (false === strpos($line, $fragment)) {
                        $matched = false;
                        break;
                    }
                }
                if (!$matched) {
                    $patched[] = $line;
                } else {
                    // the new/replace data
                    $data = is_array($info['data']) ? $info['data'] : array($info['data']);

                    // line matches patch pattern
                    switch ($info['action']) {
                    case 'insert-before':
                        // expect the data before the current line
                        if (count($data) <= $ii) {
                            // got at least $lastData lines already
                            $dataFound = true;
                            foreach (array_reverse($data) as $jj => $dataLine) {
                                if (false === strpos($lines[$ii-1-$jj], $dataLine)) {
                                    // no match, so ignore
                                    $dataFound = false;
                                    break;
                                }
                            }
                            if ($dataFound) {
                                // remove data already added to patched
                                array_splice($patched, -count($data));
                                $modified = true;
                            }
                        }
                        // add original line
                        $patched[] = $line;
                        break;
                    case 'insert-after':
                        // add original line
                        $patched[] = $line;
                        // expect at least count($data) more lines
                        if ($ii <= (count($lines)+count($data))) {
                            $dataFound = true;
                            foreach ($data as $jj => $dataLine) {
                                if (false === strpos($lines[$ii+1+$jj], $dataLine)) {
                                    // no match, so ignore
                                    $dataFound = false;
                                    break;
                                }
                            }
                            if ($dataFound) {
                                $skipLines = count($data);
                                $modified = true;
                            }
                        }
                        break;
                    case 'replace':
                        $modified = true;
                        // append data
                        foreach ($data as $dataLine) {
                            $patched[] = $dataLine;
                        }
                        break;
                    }
                } 
            }
        }

        return $modified ? $patched : null;
    }

    /**
     * Checks if this patch can still be applied.
     *
     * @return boolean <code>true</code> if this patch can still be applied.
     */
    public function isOpen() {
        return null !== $this->applyPatch($this->getLines());
    }

    /**
     * Do patch.
     *
     * @return boolean <code>true</code> if patching was successful (or skipped), <code>false</code> if not.
     */
    public function patch() {
        $lines = $this->getLines();
        if (null === ($patched = $this->applyPatch($this->getLines()))) {
            // nothing to do
            return true;
        }

        if (is_writeable($this->filename)) {
            // result of file change
            return $this->putLines($patched);
        } 

        // no permission
        return false;
    }

    /**
     * Undo.
     *
     * @return boolean <code>true</code> if the undo was successful (or skipped), <code>false</code> if not.
     */
    public function undo() {
        $lines = $this->getLines();
        if (null === ($patched = $this->revertPatch($this->getLines()))) {
            // nothing to do
            return true;
        }

        if (is_writeable($this->filename)) {
            // result of file change
            return $this->putLines($patched);
        } 

        // no permission
        return false;
    }

}

?>
