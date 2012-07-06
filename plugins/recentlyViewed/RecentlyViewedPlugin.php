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
namespace zenmagick\plugins\recentlyViewed;

use Plugin;
use zenmagick\base\Toolbox;
use zenmagick\http\view\TemplateView;


/**
 * Plugin adding support for recently viewed products.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RecentlyViewedPlugin extends Plugin {
    const RECENTLY_VIEWED_KEY = 'recentlyViewedProducts';

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct('Recently Viewed', 'Track and display recently viewed products.', '${plugin.version}');
        $this->setContext('storefront');
    }


    /**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();

        $this->addConfigValue('History Maximum', 'maxProducts', 10, 'Maximum number of products to keep');
    }

    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        $this->container->get('eventDispatcher')->listen($this);
    }

    /**
     * Handle auto login.
     */
    public function onViewStart($event) {
        $request = $event->get('request');
        $view = $event->get('view');

        if ($view instanceof TemplateView) {
            $session = $request->getSession();
            if (null == ($recentlyViewedProducts = $session->getValue(self::RECENTLY_VIEWED_KEY))) {
                $recentlyViewedProducts = array();
            }
            if (0 < ($productId = $request->query->get('productId'))) {
                $recentlyViewedProducts[] = $productId;
                $recentlyViewedProducts = array_unique(array_reverse($recentlyViewedProducts));

                // limit
                $recentlyViewedProducts = array_slice($recentlyViewedProducts, 0, (int)$this->get('maxProducts'));

                // save in original order
                $session->setValue(self::RECENTLY_VIEWED_KEY, array_reverse($recentlyViewedProducts));
            }
            // tell view
            $view->setVariable('recentlyViewedProducts', $recentlyViewedProducts);
        }
    }

}
