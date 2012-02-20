<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace zenmagick\base;

use Symfony\Component\Yaml\Yaml;

/**
 * Shared tools.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class Toolbox {
    /** Random type digits only. */
    const RANDOM_DIGITS = 'digits';
    /** Random type characters only. */
    const RANDOM_CHARS = 'chars';
    /** Random type mixed (digits and characters). */
    const RANDOM_MIXED = 'mixed';
    /** Random type hexadecimal. */
    const RANDOM_HEX = 'hex';

    private static $seedDone_;


    /**
     * Generate a random value.
     *
     * @param int length The length of the random value.
     * @param string type Optional type; predefined values are: <em>mixed</em>, <em>chars</em>, <em>digits</em> or <em>hex</em>; default is <em>mixed</em>.
     *  Any other value will be used as the valid character range.
     * @return string The random string.
     */
    public static function random($length, $type='mixed') {
        static $types	=	array(
        self::RANDOM_DIGITS => '0123456789',
        self::RANDOM_CHARS => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::RANDOM_MIXED => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
        self::RANDOM_HEX => '0123456789abcdef',
        );

        if (!self::$seedDone_) {
            mt_srand((double)microtime() * 1000200);
            self::$seedDone_ = true;
        }

        $chars = array_key_exists($type, $types) ? $types[$type] : $type;
        $max=	strlen($chars) - 1;
        $token = '';
        for ($ii=0; $ii < $length; ++$ii) {
            $token .=	$chars[(rand(0, $max))];
        }

        return $token;
    }

    /**
     * Simple, recursive array merge.
     *
     * @param mixed args..
     * @return array Recursively merged arrays.
     */
    public static function arrayMergeRecursive() {
        $arrays = func_get_args();
        $base = array_shift($arrays);

        if (!is_array($base)) {
            $base = empty($base) ? array() : array($base);
        }

        foreach ($arrays as $append) {
            if (!is_array($append)) {
                $append = array($append);
            }
            foreach($append as $key => $value) {
                if (!array_key_exists($key, $base) && !is_numeric($key)) {
                    $base[$key] = $append[$key];
                    continue;
                }
                if (is_array($value) || (isset($base[$key]) && is_array($base[$key]))) {
                    $base[$key] = self::arrayMergeRecursive($base[$key], $append[$key]);
                /* this would make it drop duplicate values (not keys)
                } else if(is_numeric($key)) {
                    if(!in_array($value, $base)) $base[] = $value;
                */
                } else {
                    $base[$key] = $value;
                }
            }
        }
        return $base;
    }

    /**
     * Load a <em>YAML</em> file, respecting the environment setting.
     *
     * @param string filename The file to load.
     * @param string environment Optional environment; default is the value of <code>Application::getEnvironment()</code>.
     * @param boolean useEnvFile Optional flag to load the <em>file_[$environemnt].yaml</em> file if available; default is <code>true</code>.
     * @return mixed The parsed data.
     */
    public static function loadWithEnv($filename, $environment = null, $useEnvFile = true) {
        $environment = null != $environment ? $environment : Runtime::getApplication()->getEnvironment();

        if ($useEnvFile) {
            $filename = self::resolveWithEnv($filename, $environment);
        }

        $data = array();
        if (!file_exists($filename)) {
            Runtime::getLogging()->trace("skipping missing file: ".$filename);
            return $data;
        }

        $filename = realpath($filename);

        try {
            $data = Yaml::parse($filename);
        } catch (\InvalidArgumentException $e) {
            Runtime::getLogging()->dump($e);
        }

        // check for imports:
        if (array_key_exists('imports', (array)$data)) {
            $currentDir = dirname($filename).'/';

            $imports = $data['imports'];
            // split into prepend/append mode
            foreach ($imports as $import) {
                $data = self::arrayMergeRecursive($data, self::loadWithEnv($currentDir.$import['resource'], $environment, false));
            }
            unset($data['imports']);
        }

        return $data;
    }

    /**
     * Explode on multiple chars.
     *
     * @param string delims The delimiter chars.
     * @param string s The string to explode.
     * @return array A token list.
     */
    public static function mexplode($delims, $s) {
        $tokens = array();
        $token = strtok($s, $delims);
        while (false !== $token) {
            $tokens[] = $token;
            $token = strtok($delims);
        }
        return $tokens;
    }

    /**
     * Evaluate a string value as boolean.
     *
     * @param mixed value The value.
     * @return boolean The boolean value.
     */
    public static function asBoolean($value) {
        if (is_integer($value)) {
            return (bool)$value;
        }
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower($value), array('on', 'true', 'yes', '1'));
    }

    /**
     * Resolve a filename with respect to the given environment.
     *
     * @param string filename The file to load.
     * @param string environment Optional environment; default is the value of <code>Application::getEnvironment()</code>.
     * @return string The most specific filename with respect to the given <em>environment</em>.
     */
    public static function resolveWithEnv($filename, $environment = null) {
        $environment = null != $environment ? $environment : Runtime::getApplication()->getEnvironment();
        $filename = realpath($filename);
        $envFilename = preg_replace('/(.*)\.(.*)/', '$1_'.$environment.'.$2', $filename);
        if (file_exists($envFilename)) {
            $filename = $envFilename;
        }
        return $filename;
    }

    /**
     * Convert values to array where reasonable.
     *
     * @param mixed value The value to convert; either already an array or a URL query form string.
     * @return array The value as array.
     */
    public static function toArray($value) {
        if (null === $value) {
            return array();
        }
        if (is_array($value)) {
            return $value;
        }
        parse_str(urldecode($value), $map);
        // handle booleans
        foreach ($map as $key => $value) {
            if ('false' == $value || 'true' == $value) {
                $map[$key] = self::asBoolean($value);
            }
        }
        return $map;
    }

}
