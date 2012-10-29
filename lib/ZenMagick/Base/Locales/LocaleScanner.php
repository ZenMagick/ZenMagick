<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace ZenMagick\Base\Locales;

use Symfony\Component\Filesystem\Filesystem;
use ZenMagick\Base\Runtime;
use ZenMagick\Base\ZMObject;

/**
 * Scan source files for translatable strings.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 */
class LocaleScanner extends ZMObject {
    /** Locale patterns. */
    const LOCALE_PATTERNS = '_zmn,_zm,_vzm';
    protected $filesystem;

    /**
     * Set the filesystem helper.
     *
     * @param Symfony\Component\Filesystem\Filesystem filesystem The helper instance.
     */
    public function setFilesystem(Filesystem $filesystem) {
        $this->filesystem = $filesystem;
    }

    /**
     * Get all parameter token for the function call pointed to by index.
     *
     * @param array tokens All tokens
     * @param int index The index of the function to examine.
     * @return array List of parameter token.
     */
    private function getParameterToken($tokens, $index) {
        $parameters = array();
        for ($ii=$index+1; $ii<count($tokens); ++$ii) {
            $token = $tokens[$ii];
            if (is_string($token) && ')' == $token) {
                break;
            }
            if (is_array($token) && T_CONSTANT_ENCAPSED_STRING == $token[0]) {
                $parameters[] = $token;
            }
        }
        return $parameters;
    }

    /**
     * Build a language map for all found l10n strings in the given directory tree.
     *
     * @param string baseDir The base folder of the directory tree to scan.
     * @param string ext File extension to look for; default is <em>.php</em>.
     * @return array A map of l10n strings for each file.
     */
    public function buildL10nMap($baseDir, $ext='.php') {
        if (!is_dir($baseDir)) return array();
        $lnPatterns = explode(',', self::LOCALE_PATTERNS);
        $map = array();
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($baseDir));
        $it = new \RegexIterator($it, '/\\'.$ext.'$/', \RegexIterator::MATCH);
        foreach ($it as $fileInfo) {
            $strings = array();
            $filename = $fileInfo->getPathname();
            $contents = file_get_contents($filename);
            // try to convert into relative path
            $filename = $this->filesystem->makePathRelative($filename, dirname($baseDir));

            // use PHP tokenizer to analyze...
            $tokens = token_get_all($contents);
            foreach ($tokens as $ii => $token) {
                // need string token to start with..
                if (is_array($token) && T_STRING == $token[0] && in_array($token[1], $lnPatterns) && ($ii+2) <= count($tokens)) {
                    $parameters = $this->getParameterToken($tokens, $ii);
                    if (0 < count($parameters)) {
                        $text = substr($parameters[0][1], 1, -1);
                        $line = $parameters[0][2];
                        $context = null;
                        $plural = null;

                        // check for context / plural
                        if ('_zm' == $token[1] && 1 < count($parameters)) {
                            $context = substr($parameters[1][1], 1, -1);
                        } elseif ('_zmn' == $token[1]) {
                            // default to single text
                            $plural = $text;
                            if (2 < count($parameters)) {
                                $plural = substr($parameters[1][1], 1, -1);
                                $context = substr($parameters[2][1], 1, -1);
                            } elseif (1 < count($parameters)) {
                                $plural = substr($parameters[1][1], 1, -1);
                            }
                        }
                        $strings[$text] = array('msg' => $text, 'plural' => $plural, 'context' => $context, 'filename' => $filename, 'line' => $line);
                    }
                }
            }

            if (0 < count($strings)) {
                $map[$filename] = $strings;
            }
        }

        return $map;
    }

    /**
     * Format a po string.
     *
     */
    protected function formatString($string) {
        // preformat string
        $string = stripslashes($string);
        $string = str_replace('"', '\"', $string);

        // newline in string?
        $nl = 0 < substr_count($string, "\n");
        if (!$nl) {
            $string = '"'.trim($string).'"';
        } else {
            $tmp = '""'."\n";
            foreach (explode("\n", $string) as $sl) {
                $tmp .= '"'.trim($sl).'"'."\n";
            }
            $string = $tmp;
        }
        return $string;
    }

    /**
     * Create a po(t) file from a l10n map.
     *
     * <p>This method operates only on the untranslated string. Translation itself happens further down the tool chain.</p>
     *
     * @param array map The map.
     * @param boolean pot Optional flag to indicate pot format (empty translations); default is <code>false</code>.
     * @return string The formatted po(t) content.
     */
    public function map2po($map, $pot=false) {
        $lines = array();
        if (!$pot) {
            $lines[] = 'msgid ""';
            $lines[] = 'msgstr ""';
            $lines[] = '"Project-Id-Version: '.Runtime::getSettings()->get('zenmagick.version').'\n"';
            $lines[] = '"POT-Creation-Date: '.date(DATE_RFC822).'\n"';
            $lines[] = '"PO-Revision-Date: \n"';
            $lines[] = '"Last-Translator: \n"';
            $lines[] = '"Language-Team: \n"';
            $lines[] = '"MIME-Version: 1.0\n"';
            $lines[] = '"Content-Type: text/plain; charset=UTF-8\n"';
            $lines[] = '"Content-Transfer-Encoding: 8bit\n"';
            $lines[] = '';
        }

        // build a unique list of strings
        $globalMap = array();
        foreach ($map as $filename => $infos) {
            if (null === $infos) {
                continue;
            }

            foreach ($infos as $key => $info) {
                $key = trim($key);
                if (!array_key_exists($key, $globalMap)) {
                    $globalMap[$key] = array();
                }
                $globalMap[$key][] = $info;
            }
        }

        // process
        $quote = '"';
        foreach ($globalMap as $string => $infos) {
            $location = '#:';
            foreach ($infos as $info) {
                $location .= ' '.$info['filename'].':'.$info['line'];
            }
            $lines[] = $location;

            $string = $this->formatString($string);

            // format the actual line(s)
            if (null != $info['context']) {
                $lines[] = 'msgctxt '.$info['context'];
            }
            $lines[] = 'msgid '.$string;
            if (null != $info['plural']) {
                $lines[] = 'msgid_plural '.$info['plural'];
                if ($pot) {
                    $lines[] = 'msgstr[0] ""';
                    $lines[] = 'msgstr[1] ""';
                }
            } else {
                $msg = $string;
                if (array_key_exists('msg', $info)) {
                    $msg = $this->formatString($info['msg']);
                }
                $lines[] = $pot ? 'msgstr ""' : 'msgstr '.$msg;
            }

            $lines[] = '';
        }

        return implode("\n", $lines);
    }

}
