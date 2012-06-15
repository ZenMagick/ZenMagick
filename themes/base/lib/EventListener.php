<?php
/*
 * ZenMagick - Smart e-commerce
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
namespace zenmagick\apps\store\themes\base;

use zenmagick\apps\store\themes\ThemeEventListener;

/**
 * Default theme event listener.
 *
 * @author DerManoMann
 * @package zenmagick.themes.base
 */
class EventListener extends ThemeEventListener {

    /**
     * {@inheritDoc}
     */
    public function themeLoaded($event) {
        //TODO: change validation
        $theme = $event->get('theme');
        $validation = $theme->getBasePath().'/lib/validation.php';
        if (file_exists($validation)) {
            require_once $validation;
        }
    }
}
