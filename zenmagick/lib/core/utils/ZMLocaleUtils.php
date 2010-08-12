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
    const L10N_PATTERNS = 'zm_l10n_get,zm_l10n,_zmn,_zm,_vzm';


    /**
     * Build a language map for all found l10n strings in the given directory tree.
     *
     * @param string baseDir The base folder of the directory tree to scan.
     * @param string ext File extension to look for; default is <em>.php</em>.
     * @return array A map of l10n strings for each file.
     */
    public static function buildL10nMap($baseDir, $ext='.php') {
        $relBase = ZMFileUtils::normalizeFilename(dirname($baseDir));

        $patterns = explode(',', self::L10N_PATTERNS);
        $map = array();
        foreach (ZMLoader::findIncludes($baseDir.DIRECTORY_SEPARATOR, $ext, true) as $filename) {
            $strings = array();
            $contents = file_get_contents($filename);
            // try to convert into relative path
            $filename = ZMFileUtils::normalizeFilename($filename);
            $filename = str_replace($relBase, '', $filename);

            foreach ($patterns as $pattern) {
                $pos = 0;
                while (-1 < $pos) {
                    // search for multiple patterns
                    $pos = strpos($contents, $pattern, $pos);
                    if (false === $pos || -1 == $pos) {
                        // nothing more to do
                        break;
                    }

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
                            $strings[$text] = array('msg' => $text, 'line' => substr_count($contents, "\n", 0, $pos));
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

        }

        return $map;
    }

    /**
     * Create a yaml file from a l10n map.
     *
     * <p>The created YAML will include comments with the filename and warnings about duplicate mappings, etc.</p>
     *
     * @param array map The map.
     * @return string The formatted YAML.
     */
    public static function map2yaml($map) {
        $lines = array();
        $lines[] = '# language mapping generated by ZenMagick Admin v'.ZMSettings::get('zenmagick.version');
        $globalMap = array();
        foreach ($map as $filename => $strings) {
            if (null === $strings) {
                continue;
            }

            $lines[] = '#: '.$filename;
            foreach ($strings as $key => $info) {
                $quote = '"';
                // either we have escaped single quotes or double quotes that are not escaped
                if (false !== strpos($key, '\\\'') || (false !== strpos($key, '"') && false === strpos($key, '\\"'))) {
                    $quote = "'";
                }

                $line = '';
                if (array_key_exists($key, $globalMap)) {
                    // key exists!
                    if ($globalMap[$key] != $info['msg']) {
                        // same key different value!
                        $line = '#. ** WARNING: key exists with different translation : ';
                    } else {
                        $line = '#. ** DUPLICATE: ';
                    }
                }
                $globalMap[$key] = $info;

                // format the actual line
                $line .= $quote.$key.$quote.': '.$quote.$info['msg'].$quote;
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Create a po file from a l10n map.
     *
     * @param array map The map.
     * @param boolean pot Optional flag to indicate pot format (empty translations); default is <code>false</code>.
     * @return string The formatted po content.
     */
    public static function map2po($map, $pot=false) {
        $lines = array();
        if (!$pot) {
            $lines[] = 'msgid ""';
            $lines[] = 'msgstr ""';
            $lines[] = '"Project-Id-Version: '.ZMSettings::get('zenmagick.version').'\n"';
            $lines[] = '"POT-Creation-Date: '.date().'\n"';
            $lines[] = '"PO-Revision-Date: \n"';
            $lines[] = '"Last-Translator: \n"';
            $lines[] = '"Language-Team: \n"';
            $lines[] = '"MIME-Version: 1.0\n"';
            $lines[] = '"Content-Type: text/plain; charset=UTF-8\n"';
            $lines[] = '"Content-Transfer-Encoding: 8bit\n"';
            $lines[] = '';
        }

        $globalMap = array();
        foreach ($map as $filename => $strings) {
            if (null === $strings) {
                continue;
            }

            foreach ($strings as $key => $info) {
                $quote = '"';
                // either we have escaped single quotes or double quotes that are not escaped
                if (false !== strpos($key, '\\\'') || (false !== strpos($key, '"') && false === strpos($key, '\\"'))) {
                    $quote = "'";
                }

                if (array_key_exists($key, $globalMap)) {
                    // key exists!
                    $global = $globalMap[$key];
                    if ($global['msg'] != $info['msg']) {
                        // same key different value!
                        $lines[] = '#. ** WARNING: key exists with different translation: '.$global['filename'].':'.$global['line'];
                    } else {
                        $lines[] = '#. ** DUPLICATE: '.$global['filename'].':'.$global['line'];
                    }
                }
                // track already processed
                $info['filename'] = $filename;
                $globalMap[$key] = $info;

                // format the actual line
                $lines[] = '#: '.$filename.':'.$info['line'];
                $lines[] = 'msgid '.$quote.$key.$quote;
                $lines[] = $pot ? 'msgstr ""' : 'msgstr '.$quote.$info['msg'].$quote;
                $lines[] = '';
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Resolve a locale path.
     *
     * <p>The path given is assumed to contain the full locale as specified in the <code>$locale</code> parameter.</p>
     * <p>The function will validate the path and if not valid will default to using just the language.</p>
     *  
     * @param string path The full path.
     * @param string locale The locale.
     * @return string A valid path or <code>null</code>.
     *
     */
    public static function resolvePath($path, $locale) {
        if (file_exists($path)) {
            return $path;
        }

        $lt = explode('_', $locale);
        if (2 > count($lt)) {
            return null;
        }

        // try language
        $path = str_replace($locale, $lt[0], $path);
        if (file_exists($path)) {
            return $path;
        }

        return null;
    }

    /**
     * Format a date as short.
     *
     * @param Date date A date.
     * @return string A short version.
     */
    public static function dateShort($date) {
        $format = ZMLocales::instance()->getLocale()->getFormat('date', 'short');
        return strftime($format, strtotime($date));
    }

    /**
     * Format a date as long.
     *
     * @param Date date A date.
     * @return string A long version.
     */
    public static function dateLong($date) {
        $format = ZMLocales::instance()->getLocale()->getFormat('date', 'long');
        return strftime($format, strtotime($date));
    }

}
