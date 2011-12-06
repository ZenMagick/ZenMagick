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
 * @package zenmagick.base
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
     * Load a <em>YAML</em> file and automatically merge any <code>environment</code> settings contained.
     *
     * @param string filename The file to load.
     * @param string environment Optional environment; default is the value of <code>ZM_ENVIRONMENT</code>.
     * @param boolean useEnvFile Optional flag to load the <em>file_[$environemnt].yaml</em> file if available; default is <code>true</code>.
     * @param boolean clearImports Optional flag to remove imports details from the loaded data before returning; default is <code>true</code>.
     * @return mixed The parsed YAML.
     */
    public static function loadWithEnv($filename, $environment=ZM_ENVIRONMENT, $useEnvFile=true, $clearImports=true) {
        if ($useEnvFile) {
            $filename = self::resolveWithEnv($filename, $environment);
        }

        $data = array();
        if (!file_exists($filename)) {
            // @todo If this is meant to be a warning it should be moved the the caller, otherwise it might be better as trace()
            //Runtime::getLogging()->warn("skipping missing file: ".$filename);
            return $data;
        }

        $filename = realpath($filename);

        try {
            $yaml = Yaml::parse($filename);
            if (is_array($yaml)) {
                if (null != $environment && array_key_exists($environment, $yaml)) {
                    $data = self::arrayMergeRecursive($yaml, $yaml[$environment]);
                } else {
                    $data = $yaml;
                }
            }
        } catch (\InvalidArgumentException $e) {
            Runtime::getLogging()->dump($e);
        }

        // check for imports:
        if (array_key_exists('imports', $data)) {
            $currentDir = dirname($filename).DIRECTORY_SEPARATOR;

            $imports = $data['imports'];
            // split into prepend/append mode
            $prepend = array();
            $append = array();
            foreach ($imports as $ii => $import) {
                if (!array_key_exists('mode', $import)) {
                    $import['mode'] = 'prepend';
                }
                if ('append' == $import['mode']) {
                    $append[] = $import;
                } else if ('prepend' == $import['mode']) {
                    $prepend[] = $import;
                } else if ('ignore' == $import['mode']) {
                    // ignore
                } else {
                    throw new \InvalidArgumentException(sprintf('unknown mode: %s', $import['mode']));
                }
            }
            unset($data['imports']);

            $tmp = array();
            foreach ($prepend as $import) {
                $tmp = self::arrayMergeRecursive($tmp, self::loadWithEnv($currentDir.$import['resource'], $environment, false));
            }
            $data = self::arrayMergeRecursive($tmp, $data);
            foreach ($append as $import) {
                $data = self::arrayMergeRecursive($data, self::loadWithEnv($currentDir.$import['resource'], $environment, false));
            }

            if ($clearImports) {
                foreach ($imports as $ii => $import) {
                    if (!array_key_exists('mode', $import) || 'ignore' != $import['mode']) {
                        unset($imports[$ii]);
                    }
                }
                if (0 != count($imports)) {
                    $data['imports'] = $imports;
                }
            }
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
     * @param string environment Optional environment; default is the value of <code>ZM_ENVIRONMENT</code>.
     * @return string The most specific filename with respect to the given <em>environment</em>.
     */
    public static function resolveWithEnv($filename, $environment=ZM_ENVIRONMENT) {
        $filename = realpath($filename);
        $envFilename = preg_replace('/(.*)\.(.*)/', '$1_'.$environment.'.$2', $filename);
        if (file_exists($envFilename)) {
            // load that and expect the 'import:' data in the file to pull in whatever is needed.
            $filename = $envFilename;
        }
        return $filename;
    }

    /**
     * Test if the given context string matches.
     *
     * <p>The context string may be a single context name or a comma separated list of context strings.</p>
     * <p>If the context is found in the given context string it is considered as matched.</p>
     *
     * @param string s The context string to test for a match.
     * @param string context Optional context; default is <code>null</code> to use the current context.
     * @return boolean <code>true</code> if the current context is either <code>null</code> or matched inside the given string.
     */
    public static function isContextMatch($s, $context=null) {
        if (null === $context) {
            $context = Runtime::getContext();
        }
        if (null === $context) {
            return true;
        }

        // string match, avoid whitespace before/after comma
        $cs = ','.str_replace(' ', '', $s).',';
        return false !== strpos($cs, ','.$context.',');
    }

}
