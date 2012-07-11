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
namespace zenmagick\apps\store\admin\installation\patches;

use zenmagick\base\Runtime;
use zenmagick\apps\store\admin\installation\InstallationPatch;
use zenmagick\apps\store\admin\utils\SQLRunner;

/**
 * Generic SQL patch.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SQLPatch extends InstallationPatch {
    protected $tables;

    /**
     * Create new patch.
     *
     * @param string id Id of the patch.
     */
    public function __construct($id) {
        parent::__construct($id);
        $this->tables = array();
    }


    function setTables($tables) {
        $this->tables = (array)$tables;
    }

    function getTables() {
        $tables = array();
        foreach($this->tables as $table) {
            $tables[] = \ZMRuntime::getDatabase()->getPrefix().$table;
        }
        return $tables;
    }

    /**
     * Get the patch group id.
     *
     * @return string The patch group id.
     */
    function getGroupId() {
        return 'sql';
    }

    /**
     * Get the precondition message.
     *
     * <p>This will return an empty string when <code>isReady()</code> returns <code>true</code>.</p>
     *
     * @return string The preconditions message or an empty string.
     */
    function getPreconditionsMessage() {
        return "";
    }

    function tablesExist() {
        $sm = \ZMRuntime::getDatabase()->getSchemaManager();
        return $sm->tablesExist($this->getTables());
    }

    /**
     * Revert the patch.
     *
     * @return boolean <code>true</code> if patching was successful, <code>false</code> if not.
     */
    function undo() {
        if ($this->isOpen()) return true;

        $sm = \ZMRuntime::getDatabase()->getSchemaManager();
        foreach ($this->getTables() as $table) {
            $sm->dropTable($table);
        }
        return parent::undo();
    }

    /**
     * Execute the given SQL.
     *
     * @param string sql Some sql.
     */
    function _runSQL($sql) {
        $sql = trim(preg_replace('/[<>]/', '_', $sql));
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $sql = stripslashes($sql);
        }

        if (!empty($sql)) {
            $results = SQLRunner::execute_sql($sql);
            $messages = SQLRunner::process_patch_results($results);
            foreach ($messages as $message) {
                $this->messages_[] = $message;
            }
            return empty($results['error']);
        }

        return true;
    }

}
