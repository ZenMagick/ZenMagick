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
 * Locale utils.
 *
 * @author DerManoMann
 * @package org.zenmagick.core.utils
 */
class ZMlocaleUtils {
    const L10N_PATTERNS = 'zm_l10n,_zmn,_zm,_vzm';

    /**
     * Build a language map for all found l10n strings in the given directory tree.
     *
     * @param string directory The base folder of the directory tree to scan.
     * @param string ext File extension to look for; default is <em>.php</em>.
     * @return array A map of l10n strings for each file.
     */
    public static function buildL10nMap($baseDir, $ext='.php') {
        $patterns = explode(',', self::L10N_PATTERNS);
        $map = array();
        foreach (ZMLoader::findIncludes($baseDir.DIRECTORY_SEPARATOR, $ext, true) as $filename) {
            $strings = array();
            $contents = file_get_contents($filename);
            $pos = 0;
            while (-1 < $pos) {
                // search for multiple patterns
                foreach ($patterns as $pattern) {
                    $next = strpos($contents, $pattern, $pos);
                    if ($next !== false) {
                        break;
                    }
                }
                if ($next === false || -1 == $next) {
                    // nothing more to do
                    break;
                }

                $pos = $next;

                $ob = strpos($contents, '(', $pos+1);
                // allow for 10 chars between pattern and '('
                if ($pos < $ob && ($ob-$pos) < (strlen($pattern) + 10)) {
                    // found something
                    // examine first non whitespace char to figure out which quote to look for
                    $quote = '';
                    $qi = $ob+1;
                    while (true) {
                        $quote = trim(substr($contents, $qi, 1));
                        if ("'" == $quote || '"' == $quote) {
                            break;
                        }
                        if ('' != $quote) {
                            // not a string
                            $quote = null;
                            break;
                        }

                        ++$qi;
                        // sanity check
                        if ($qi-$ob > 10)
                          break;
                    }

                    if ('' != $quote) {
                        // have a quote
                        $pos += $qi-$ob+1;
                        $text = '';
                        $lastChar = '';
                        $start = $qi+1;
                        $len = 0;
                        $char = '';
                        while (true) {
                            $char = substr($contents, $start+$len, 1);
                            $len++;
                            if ($char == $quote && $lastChar != '\\') {
                                break;
                            }
                            $lastChar = $char;
                            $text .= $char;
                            // sanity check
                            if ($len > 1000) {
                                ZMLogging::instance()->log('unbound string in '.$filename.' around char '.$pos.'; skipping', ZMLogging::WARN);
                                ++$pos;
                                break;
                            }
                        }
                        $strings[$text] = $text;
                    } else {
                        // found something, but not a string
                        ZMLogging::instance()->log('found something: '.substr($contents, $qi-10, 20), ZMLogging::TRACE);
                        ++$pos; //avoid getting stuck
                    }
                } else {
                    ++$pos; //avoid getting stuck
                    break;
                }
            }
            if (0 < count($strings)) {
                $map[$filename] = $strings;
            }
        }

        return $map;
    }

}
