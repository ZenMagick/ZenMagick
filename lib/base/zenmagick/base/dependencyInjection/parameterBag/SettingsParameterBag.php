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
namespace zenmagick\base\dependencyInjection\parameterBag;

use zenmagick\base\Runtime;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Parameter bag that can also resolve against settings.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.base.dependencyInjection.parameterBag
 */
class SettingsParameterBag extends ParameterBag {

    /**
     * {@inheritDoc}
     */
    public function resolveString($value, array $resolving=array()) {
        // we do this to deal with non string values (Boolean, integer, ...)
        // as the preg_replace_callback throw an exception when trying
        // a non-string in a parameter value
        if (preg_match('/^%([^%]+)%$/', $value, $match)) {
            $key = strtolower($match[1]);
            if (Runtime::getSettings()->exists($match[1])) {
                return Runtime::getSettings()->get($match[1]);
            }
        }
        return parent::resolveString($value, $resolving);
    }

}
