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
namespace zenmagick\base\locales\handler;

use zenmagick\base\locales\Locale;

/**
 * Echo locale.
 *
 * @author DerManoMann <mano@zenmagick.org> <mano@zenmagick.org>
 * @package zenmagick.base.locales.handler
 */
class EchoLocale extends Locale {

    /**
     * {@inheritDoc}
     */
    public function addResource($resource, $locale=null, $domain=Locale::DEFAULT_DOMAIN) {
        // nothing
    }

    /**
     * {@inheritDoc}
     */
    public function translate($text, $context=null, $domain=Locale::DEFAULT_DOMAIN) {
        return $text;
    }

    /**
     * {@inheritDoc}
     */
    public function translatePlural($single, $number, $plural=null, $context=null, $domain=Locale::DEFAULT_DOMAIN) {
        return (1 < $number && null != $plural) ? $plural : $single;
    }

}
