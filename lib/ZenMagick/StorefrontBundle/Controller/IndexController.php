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
namespace ZenMagick\StorefrontBundle\Controller;


/**
 * Request controller for index.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class IndexController extends \ZMController {

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $viewName = null;

        // be nice to seo URL's that we do not control and also bookmarked pages
        if ($request->get('cPath')) {
            $viewName = 'category';
        } else if ($request->query->getInt('manufacturers_id')) {
            $viewName = 'category';
        }

        // @todo how is this supposed to work exactly? replace the whole page? just fit into the content area?
        // check for a static homepage.
        if (null == $viewName && ($staticHome = $this->get('settingsService')->get('staticHome'))) {
            require $staticHome;
            exit;
        }

        return $this->findView($viewName);
    }

}
