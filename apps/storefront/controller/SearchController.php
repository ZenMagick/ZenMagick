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
namespace zenmagick\apps\storefront\controller;

use zenmagick\base\Beans;
use zenmagick\base\Toolbox;
use zenmagick\base\events\Event;

/**
 * Search controller.
 *
 * <p>The default for <em>autoSearch</em> is <code>true</code>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SearchController extends \ZMController {
    private $autoSearch_;

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->autoSearch_ = true;
    }


    /**
     * Set the auto search flag.
     *
     * <p>If enabled, the controller will automatically run a search even if only the keyword is set.
     * This allows to create simple URLs that run a search.</p>
     *
     * @param boolean autoSearch The new value.
     */
    public function setAutoSearch($autoSearch) {
        $this->autoSearch_ = Toolbox::asBoolean($autoSearch);
    }

    /**
     * Get the auto search setting.
     *
     * @return boolean The auto search flag.
     */
    public function isAutoSearch() {
        return $this->autoSearch_;
    }

    /**
     * {@inheritDoc}
     */
    public function isFormSubmit($request) {
        return $this->isAutoSearch();
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $searchCriteria = $this->getFormData($request);
        // never search inactive products
        $searchCriteria->setSearchAll(false);

        if (!Toolbox::isEmpty($searchCriteria->getKeywords()) && $this->autoSearch_) {
            $resultList = Beans::getBean('ZMResultList');
            //TODO: filter??
            foreach (explode(',', $this->container->get('settingsService')->get('resultListProductSorter')) as $sorter) {
                $resultList->addSorter(Beans::getBean($sorter));
            }

            $resultSource = $this->container->get('ZMSearchResultSource');
            $resultSource->setSearchCriteria($searchCriteria);
            $resultList->setResultSource($resultSource);
            $resultList->setPageNumber($request->query->getInt('page'));
            $args = array('request' => $request, 'searchCriteria' => $searchCriteria, 'resultList' => $resultList, 'autoSearch' => $this->isAutoSearch());
            $this->container->get('event_dispatcher')->dispatch('search', new Event($this, $args));
            return $this->findView('results', array('resultList' => $resultList));
        }

        return $this->findView();
    }

}
