<?php
/*
 * ZenMagick - Another PHP framework.
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

use ZenMagick\base\ZMObject;

/**
 * A set of validation rules.
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package org.zenmagick.mvc.validation
 */
class ZMRuleSet extends ZMObject {
    private $id_;
    private $rules_;


    /**
     * Create new rule set.
     *
     * @param string id The id.
     * @param array rules Optional initial list of rules.
     */
    function __construct($id, $rules=null) {
        parent::__construct();
        $this->id_ = $id;
        $this->rules_ = null != $rules ? $rules : array();
    }


    /**
     * Get the rule set id.
     *
     * @return int The rule set id.
     */
    public function getId() { return $this->id_; }

    /**
     * Add a new <code>ZMRule</code>.
     *
     * @param ZMRule rule A new rule.
     */
    public function addRule($rule) {
        $this->rules_[] = $rule;
    }

    /**
     * Add a list of <code>ZMRule</code>s.
     *
     * @param array rules A list of <code>ZMRule</code> instances.
     */
    public function addRules($rules) {
        $this->rules_ = array_merge($this->rules_, $rules);
    }

    /**
     * Remove a rule.
     *
     * @param string type The type (class name).
     * @param string name The field name.
     */
    public function removeRule($type, $name) {
        $tmp = array();
        foreach ($this->rules_ as $rule) {
            if (get_class($rule) == $type && $rule->getName() == $name) {
                continue;
            }
            $tmp[] = $rule;
        }
        $this->rules_ = $tmp;
    }

    /**
     * Get the validation rules.
     *
     * @return array A list of <code>ZMRule</code> objects.
     */
    public function getRules() {
        return $this->rules_;
    }

}
