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
 *
 * $Id$
 */
?>
<?php

/**
 * Analyze dependencies of a given (PHP) source.
 *
 * @author DerManoMann
 * @package org.zenmagick.core.utils
 * @version $Id$
 */
class ZMPhpSourceAnalyzer {

    /**
     * Get next token of a certain type.
     *
     * @param array tokens List of all token.
     * @param int key Key to start searching from.
     * @param int type Type of token to look for.
     * @return array (key, token found) or <code>null</code>.
     */
    private static function getToken($tokens, $key, $type) {
        ++$key;
        if (!is_array($type)) $type = array($type);
        while (!is_array($tokens[$key]) || !in_array($tokens[$key][0], $type)) {
            ++$key;
            if (!isset($tokens[$key])) {
                return null;
            }
        }

        return array($key, $tokens[$key]);
    }

    /**
     * Get source info.
     *
     * <p>Analyzes the given PHP source and extracts the following information:</p>
     * <ul>
     *  <li>Included classes and interfaces</li>
     *  <li>Classes and interfaces this PHP source code depends on.</li>
     * </ul>
     *
     * <p>There are a few assumptions about PHP sources that affect how these results may be used:</p>
     * <ol>
     *  <li>A source is assumed to be valid. That means it will load without errors.</li>
     *  <li>The above implies that dependencies within a single source are resolved by having classes/interfaces
     *   in the right order in the source.</li>
     * </ol>
     *
     * @param string source The file source.
     * @return array Two element map with the keys <em>contains</em> and <em<depends</em>. Each value is also a two
     *  element map with the keys <em>classes</em> and <em>interfaces</em>. Values are arrays containing class and 
     *  interface names.
     */
    public static function getDependencies($source) {
        $deps = array(
            'contains' => array(
                'classes' => array(), 
                'interfaces' => array()
            ), 
            'depends' => array(
                'classes' => array(), 
                'interfaces' => array()
            )
        );
        $tokens = token_get_all($source);
        // needed to correctly assign extends to either interface or class
        $lastContains = null;
        foreach ($tokens as $key => $token) {
            if (!is_string($token)) {
                // token array
                list($id, $text) = $token;
                switch ($id) {
                    case T_INTERFACE:
                        if (null != ($ts = self::getToken($tokens, $key, T_STRING))) {
                            $deps['contains']['interfaces'][$ts[1][1]] = $ts[1][1];
                            $lastContains = 'interfaces';
                        }
                        break;
                    case T_IMPLEMENTS:
                        if (null != ($ts = self::getToken($tokens, $key, T_STRING))) {
                            $deps['depends']['interfaces'][$ts[1][1]] = $ts[1][1];

                            // check for multiple interfaces
                            $next = $tokens[$ts[0]+1];
                            if (!is_array($next) && ',' === $next) {
                                if (null != ($ns = self::getToken($tokens, $ts[0], T_STRING))) {
                                    $deps['depends']['interfaces'][$ns[1][1]] = $ns[1][1];
                                }
                            }
                        }
                        break;
                    case T_EXTENDS:
                        if (null != ($ts = self::getToken($tokens, $key, T_STRING))) {
                            $name = self::getToken($tokens, $key, T_STRING);
                            $deps['depends'][$lastContains][$ts[1][1]] = $ts[1][1];

                            // check for multiple interfaces
                            $next = $tokens[$ts[0]+1];
                            if (!is_array($next) && ',' === $next) {
                                if (null != ($ns = self::getToken($tokens, $ts[0], T_STRING))) {
                                    // must be interfaces!
                                    $deps['depends']['interfaces'][$ns[1][1]] = $ns[1][1];
                                }
                            }
                        }
                        break;
                    case T_CLASS:
                        if (null != ($ts = self::getToken($tokens, $key, T_STRING))) {
                            $deps['contains']['classes'][$ts[1][1]] = $ts[1][1];
                            $lastContains = 'classes';
                        }
                        break;
                }
            }
        }

        // drop map and return simple arrays
        $deps['contains']['classes'] = array_keys($deps['contains']['classes']);
        $deps['contains']['interfaces'] = array_keys($deps['contains']['interfaces']);
        $deps['depends']['classes'] = array_keys($deps['depends']['classes']);
        $deps['depends']['interfaces'] = array_keys($deps['depends']['interfaces']);

        return $deps;
    }

}
