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
 * A validator framework.
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.validation
 * @version $Id$
 */
class ZMValidator extends ZMObject {
    private $sets_;
    private $alias_;
    private $messages_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->sets_ = array();
        $this->alias_ = array();
        $this->messages_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

    /**
     * Get instance.
     */
    public static function instance() {
        return ZMObject::singleton('Validator');
    }


    /**
     * Add an alias to share rules between different forms.
     *
     * <p>Adding / modifying an alias is not permitted and the result not defined.</p>
     *
     * @param string id A rule id of an existing rule set.
     * @param string alias An alias.
     */
    public function addAlias($id, $alias) {
        $this->alias_[$alias] = $id;
    }

    /**
     * Resolve alias.
     *
     * @param string id A rule set id.
     * @return string Either the same id or the aliased id.
     */
    protected function resolveAlias($id) {
        if (array_key_exists($id, $this->alias_)) {
            return $this->alias_[$id];
        }
        return $id;
    }

    /**
     * Add a new rule for the given rule set id.
     *
     * @param string id The rule set id.
     * @param array rule A plain text rule.
     * @param boolean override Optional flag to override or add; default is <code>false</code> (add).
     */
    public function addRule($id, $rule, $override=false) {
        if (array_key_exists($id, $this->sets_) && !$override) {
            $this->sets_[$id][] = $rule;
        } else {
            $this->sets_[$id] = array($rule);
        }
    }

    /**
     * Add a list of new rules for the given rule set id.
     *
     * @param string id The rule set id.
     * @param array rules A list of plain text rules.
     * @param boolean override Optional flag to override or add; default is <code>false</code> (add).
     */
    public function addRules($id, $rules, $override=false) {
        if (array_key_exists($id, $this->sets_) && !$override) {
            $this->sets_[$id] = array_merge($this->sets_[$id], $rules);
        } else {
            $this->sets_[$id] = $rules;
        }
    }


    /**
     * Get a <code>ZMRuleSet</code> for the given id/name.
     *
     * @param string id The id/name of the set.
     * @param boolean compile If set to <code>true</code>, evaluate the rule data (creating objects, etc); default is <code>false</code>.
     * @return ZMRuleSet A <code>ZMRuleSet</code> instance, array or <code>null</code>.
     */
    public function getRuleSet($id, $compile=false) {
        $id = $this->resolveAlias($id);
        $ruleSet = null;
        if (array_key_exists($id, $this->sets_)) {
            $ruleSet = $this->sets_[$id];
        }

        if ($compile) {
            $rules = $ruleSet;
            $ruleSet = ZMLoader::make('RuleSet', $id);
            foreach ($rules as $ruleDef) {
                if (null == ($rule = ZMLoader::make($ruleDef))) {
                    ZMLogging::instance()->dump($ruleDef, "can't instantiate rule", ZMLogging::WARN);
                }
                $ruleSet->addRule($rule);
            }
        }

        return $ruleSet;
    }

    /**
     * Check if a <code>ZMRuleSet</code> exists for the given id.
     *
     * @param string id The id/name of the set.
     * @return boolean <code>true</code> if a <code>ZMRuleSet</code> exists, <code>false</code> if not.
     */
    public function hasRuleSet($id) {
        $id = $this->resolveAlias($id);
        return array_key_exists($id, $this->sets_);
    }

    /**
     * If a validation was not successful, corresponding error messages
     * will be available here.
     *
     * @return array A list of localized messages.
     */
    public function getMessages() {
        return $this->messages_;
    }

    /**
     * Validate the given request/object using the named (id) rule set.
     *
     * <p>If the request parameter is an object, it will be added to the
     * internally used data map using the <em>magic key</em> <code>__obj</code>.</p>
     *
     * @param ZMRequest request The current request.
     * @param mixed data The data (map or object) to validate.
     * @param string id The ruleset id.
     * @return boolean <code>true</code> if the validation was successful, <code>false</code> if not.
     */
    public function validate($request, $data, $id) {
        $this->messages_ = array();

        $set = $this->getRuleSet($id, true);
        if (null == $set) {
            return true;
        }

        if (is_object($data)) {
            $map = ZMBeanUtils::obj2map($data);
            $map['__obj'] = $data;
        } else {
            $map = $data;
        }

        // initial status
        $status = true;

        // iterate over rules
        foreach ($set->getRules() as $rule) {
            if (!$rule->validate($request, $map)) {
                $status = false;
                $msgList = array();
                if (array_key_exists($rule->getName(), $this->messages_)) {
                    $msgList = $this->messages_[$rule->getName()];
                }
                if (null != $rule->getErrorMsg()) {
                    array_push($msgList, $rule->getErrorMsg());
                }
                $this->messages_[$rule->getName()] = $msgList;
            }
        }

        return $status;
    }

    /**
     * Create JS validation rules for the given rule set.
     *
     * @param string id The id of the form to validate (the <code>ZMRuleSet</code> name).
     * @return string Formatted JavaScript .
     */
    public function toJSString($id) {
        $set = $this->getRuleSet($id, true);

        if (null == $set) {
            return '';
        }

        $n = "\n";
        $js = '';
        $js .= '<script type="text/javascript">'.$n;
        $js .= '  var ' . $id . '_rules = new Array('.$n;
        $first = true;
        foreach ($set->getRules() as $rule) {
            if (null == $rule) {
                continue;
            }
            $ruleJS = $rule->toJSString();
            if (empty($ruleJS)) {
                continue;
            }
            if (!$first) $js .= ','.$n;
            $first = false;
            $js .= $ruleJS;
        }
        $js .= $n.'  );'.$n;
        $js .= '</script>'.$n;

        return $js;
    }

}
