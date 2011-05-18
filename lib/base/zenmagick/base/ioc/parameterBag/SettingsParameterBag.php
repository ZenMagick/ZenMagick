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
namespace zenmagick\base\ioc\parameterBag;

use zenmagick\base\Runtime;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Exception\NonExistentParameterException;

/**
 * Parameter bag that can also resolve against settings.
 *
 * @author DerManoMann
 * @package zenmagick.base.ioc.parameterBag
 */
class SettingsParameterBag extends ParameterBag {

    /**
     * {@inheritDoc}
     *
     * <p>Will also resolve values against application settings.</p>
     */
    public function resolveValue($value) {
        if (is_array($value)) {
            $args = array();
            foreach ($value as $k => $v) {
                $args[$this->resolveValue($k)] = $this->resolveValue($v);
            }

            return $args;
        }

        if (!is_string($value)) {
            return $value;
        }

        if (preg_match('/^%([^%]+)%$/', $value, $match)) {
            // we do this to deal with non string values (Boolean, integer, ...)
            // the preg_replace_callback converts them to strings
            if ($this->has(strtolower($match[1]))) {
                return $this->get(strtolower($match[1]));
            } else if (Runtime::getSettings()->exists($match[1])) {
                return Runtime::getSettings()->get($match[1]);
            }
            throw new NonExistentParameterException($name);
        }

        return str_replace('%%', '%', preg_replace_callback(array('/(?<!%)%([^%]+)%/'), array($this, 'resolveValueCallback'), $value));
    }

    /**
     * Value callback
     *
     * @see resolveValue
     *
     * @param array $match
     * @return string
     */
    private function resolveValueCallback($match) {
        if ($this->has(strtolower($match[1]))) {
            return $this->get(strtolower($match[1]));
        } else if (Runtime::getSettings()->exists($match[1])) {
            return Runtime::getSettings()->get($match[1]);
        }
        throw new NonExistentParameterException($name);
    }

}
