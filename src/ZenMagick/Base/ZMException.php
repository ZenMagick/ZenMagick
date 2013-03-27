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
namespace ZenMagick\Base;

/**
 * Exception base class.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class ZMException extends \Exception
{
    /**
     * Create new instance.
     *
     * @param string message The message; default is <code>null</code>.
     * @param int code The exception code; default is <em>0</em>.
     * @param Exception previous The original exception (if any) for chaining; default is <code>null</code>.
     */
    public function __construct($message=null, $code=0, $previous=null)
    {
        parent::__construct((string) $message, (int) $code, $previous);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        $s =  '['.get_class($this);
        $s .= ' message='.$this->getMessage();
        $s .= ', file='.$this->getFile();
        $s .= ', line='.$this->getLine();
        $s .= ', previous='.$this->getPrevious();
        $s .= ']';

        return $s;
    }

    /**
     * Format a value.
     */
    protected static function formatValue($value, $recursive=true)
    {
        if (is_string($value)) {
            return "'".$value."'";
        } elseif (is_array($value)) {
            $va = array();
            foreach ($value as $ve) {
                $va[] = self::formatValue($ve, false);
            }

            return implode(', ', $va);
        } elseif (is_object($value)) {
            $rc = new \ReflectionClass($value);
            if ($rc->hasMethod('__toString')) {
                return (string) $value;
            } else {
                return get_class($value);
            }
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (null === $value) {
            return 'null';
        }

        return $value;
    }

    /**
     * Format a stack trace.
     *
     * @param array lines The stack info.
     * @return array Formatted lines.
     */
    public static function formatStackTrace(array $lines)
    {
        $stack = array();
        $index = 0;
        foreach ($lines as $line) {
            $entry = '#'.$index++.' ';
            if (isset($line['file'])) {
                $file = $line['file'];
                $location = $file.'('.$line['line'].')';
            } else {
                $location = '[no source]';
            }
            $entry .= $location.': ';
            $keys = array('class', 'type', 'function');
            foreach ($keys as $key) {
                if (array_key_exists($key, $line)) {
                    $entry .= $line[$key];
                }
            }
            if (array_key_exists('function', $line)) {
                $entry .= '(';
                $args = array();
                if (array_key_exists('args', $line)) {
                    $largs = $line['args'];
                    if (in_array($line['function'], array('require', 'require_once', 'include', 'include_once')) && 1 == count($largs)) {
                        $largs[0] = $largs[0];
                    }

                    foreach ($largs as $arg) {
                        $args[] = self::formatValue($arg);
                    }
                }
                $entry .= implode(', ', $args);
                $entry .= ')';
            }
            $stack[] = $entry;
        }

        return $stack;
    }

}
