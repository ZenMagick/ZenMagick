<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
        $sql = \ZMRequest::sanitize($sql);
        if (!empty($sql)) {
            $results = SQLRunner::execute_sql($sql);
            $this->_processSQLMessages($results);
            return empty($results['error']);
        }

        return true;
    }

    /**
     * Add a message.
     *
     * @param string msg The message.
     * @param string type The type.
     */
    private function addMessage($msg, $type) {
        $message = Runtime::getContainer()->get('zenmagick\http\messages\Message');
        $message->setText($msg);
        $message->setType($type);
        $this->messages_[] = $message;
    }

    /**
     * Process messages.
     */
    function _processSQLMessages($results) {
        if ($results['queries'] > 0 && $results['queries'] != $results['ignored']) {
            $this->addMessage($results['queries'].' statements processed.', 'success');
        } else {
            $this->addMessage('Failed: '.$results['queries'].'.', 'error');
        }

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $value) {
                $this->addMessage('ERROR: '.$value.'.', 'error');
            }
        }
        if ($results['ignored'] != 0) {
            $this->addMessage('Note: '.$results['ignored'].' statements ignored. See "upgrade_exceptions" table for additional details.', 'warn');
        }
    }

}
