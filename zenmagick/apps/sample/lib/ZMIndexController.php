<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2010 zenmagick.org
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
 * Empty default controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.sample
 */
class ZMIndexController extends ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        return array('currentLocale' => ZMLocales::instance()->getCode(), 'languages' => array('en' => 'English', 'de_DE' => 'Deutsch'));
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        $data = array();
        $name = $request->getSession()->getValue('name');
        if (!empty($name)) {
            $data['name'] = $name;
        }

        if (ZMLangUtils::asBoolean($request->getParameter('clear'))) {
            $data = array();
            $request->getSession()->destroy();
        }

        return $this->findView(null, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $name = $request->getParameter('myname');
        $data = array();
        $viewId = null;
        if (empty($name)) {
            ZMMessages::instance()->error('Don\'t be shy!');
        } else {
            $request->getSession()->setValue('name', $name);
            $data['name'] = $name;
            $viewId = 'success';
        }

        return $this->findView($viewId, $data);
    }

}
