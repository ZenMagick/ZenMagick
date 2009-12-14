<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
 * Scaffolding controller.
 *
 * <p>Right now the database is always the default (ie. the Zen Cart database).</p>
 *
 * @package org.zenmagick.plugins.zm_scaffold
 * @author DerManoMann
 * @version $Id$
 */
class ZMScaffoldController extends ZMController {
    private $method_;
    private $table_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->method_ = null;
        $this->table_ = null;
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
    }


    /**
     * Set the table name.
     *
     * @param string table The table name.
     */
    public function setTable($table) {
        $this->table_ = $table;
    }

    /**
     * Set the method name.
     *
     * @param string method The method name.
     */
    public function setMethod($method) {
        $this->method_ = $method;
    }

    /**
     * {@inhertDoc}
     */
    public function preProcess($request) {
        // check access on controller level
        ZMSacsManager::instance()->authorize($request, $request->getRequestId(), $request->getAccount());

        // (re-)check on method level
        $page = $this->getId().'#'.$this->method_;
        ZMSacsManager::instance()->ensureAccessMethod($page);
        ZMSacsManager::instance()->authorize($request, $page, $request->getAccount());
    }

    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        return call_user_func(array($this, $this->method_));
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        // todo: db processing
        return call_user_func(array($this, $this->method_));
    }


    /**
     * Create.
     */
    public function create() {
        $formBean = $this->getFormData();
        $model = ZMDatabase::instance()->createModel($this->table_, $formBean);
        $this->exportGlobal('data', $model);
        //todo: view
    }

    /**
     * Edit.
     */
    public function edit() {
        // todo: key
        $model = Runtime::getInstance()->loadModel($this->table_, $key, 'ZMObject');
        $this->exportGlobal('data', $model);
        //todo: view
    }

    /**
     * Update.
     */
    public function update() {
        $formBean = $this->getFormData();
        $model = ZMDatabase::instance()->updateModel($this->table_, $formBean);
        $this->exportGlobal('data', $model);
        //todo: view
    }

    /**
     * Delete.
     */
    public function delete() {
        $model = ZMDatabase::instance()->removeModel($this->table_, $formBean);
        //todo: view
    }

}

?>
