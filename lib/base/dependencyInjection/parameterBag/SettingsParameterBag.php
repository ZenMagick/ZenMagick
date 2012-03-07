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
?>
<?php
namespace zenmagick\base\dependencyInjection\parameterBag;

use zenmagick\base\Runtime;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Parameter bag that can also resolve against settings.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SettingsParameterBag extends ParameterBag {

    /**
     * {@inheritDoc}
     */
    public function has($name) {
        return parent::has($name) || Runtime::getSettings()->exists($name);
    }

    /**
     * {@inheritDoc}
     */
    public function get($name) {
        $settingsService = Runtime::getSettings();
        if ($settingsService->exists($name)) {
            return $settingsService->get($name);
        }

        return parent::get($name);
    }

    /**
     * {@inheritDoc}
     */
    public function set($name, $value) {
        parent::set($name, $value);
        Runtime::getSettings()->set($name, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveString($value, array $resolving=array()) {
        if (preg_match('/^%([^%]+)%$/', $value, $match)) {
            $key = strtolower($match[1]);

            // case sensitive lookup
            $settingsService = Runtime::getSettings();
            if ($settingsService->exists($match[1])) {
                return $settingsService->get($match[1]);
            }

            if (isset($resolving[$key])) {
                throw new ParameterCircularReferenceException(array_keys($resolving));
            }

            $resolving[$key] = true;

            return $this->resolved ? $this->get($key) : $this->resolveValue($this->get($key), $resolving);
        }

        $self = $this;

        return preg_replace_callback('/(?<!%)%([^%]+)%/', function ($match) use ($self, $resolving, $value) {
            $key = strtolower($match[1]);

            // case sensitive lookup
            $settingsService = Runtime::getSettings();
            if ($settingsService->exists($match[1])) {
                return $settingsService->get($match[1]);
            }

            if (isset($resolving[$key])) {
                throw new ParameterCircularReferenceException(array_keys($resolving));
            }

            $resolved = $self->get($key);

            if (!is_string($resolved) && !is_numeric($resolved)) {
                throw new RuntimeException(sprintf('A string value must be composed of strings and/or numbers, but found parameter "%s" of type %s inside string value "%s".', $key, gettype($resolved), $value));
            }

            $resolved = (string) $resolved;
            $resolving[$key] = true;

            return $self->isResolved() ? $resolved : $self->resolveString($resolved, $resolving);
        }, $value);
    }

}
