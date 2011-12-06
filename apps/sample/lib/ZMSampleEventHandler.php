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


/**
 * Handle a few events.
 *
 * <p>Hooked up automatically via configs.yaml.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.sample
 */
class ZMSampleEventHandler {

    /**
     * Init done callback.
     */
    public function onInitDone($event) {
        $request = $event->get('request');
        if (null != ($locale = $request->getParameter('locale'))) {
            $this->container->get('localeService')->getLocale(true, $locale);
            $request->getSession()->setValue('locale', $locale);
        } else if (null != ($locale = $request->getSession()->getValue('locale'))) {
            $this->container->get('localeService')->getLocale(true, $locale);
        }
    }

}
