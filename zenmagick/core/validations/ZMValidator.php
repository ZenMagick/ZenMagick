<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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


/**
 * A validator.
 *
 * @author mano
 * @package org.zenmagick.validations
 * @version $Id$
 */
class ZMValidator extends ZMObject {
    // each set contains rules for a single form
    var $sets_;
    var $messages_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();

        $this->sets_ = array();
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
        return parent::instance('Validator');
    }


    /**
     * Add a new <code>ZMRuleSet</code>.
     *
     * @param ZMRuleSet set A new rule set.
     */
    function addRuleSet(&$set) {
        $ruleSet = $this->getRuleSet($set->getId());
        if (null != $ruleSet) {
            $ruleSet->addRules($set->getRules());
        } else {
            $this->sets_[$set->getId()] = $set;
        }
    }

    /**
     * Add a new <code>ZMRule</code>.
     *
     * @param string id The rule set id.
     * @param ZMRule rule A new rule.
     */
    function addRule($id, &$rule) {
        $ruleSet = $this->getRuleSet($id);
        if (null == $ruleSet) {
            $ruleSet = $this->create("RuleSet", $id);
            $this->addRuleSet($ruleSet);
        }
        if (null != $rule && null != $ruleSet) {
            $ruleSet->addRule($rule);
        } else {
            $this->log("invalid set id ($id) or null rule");
        }
    }

    /**
     * Get a <code>ZMRuleSet</code> for the given id/name.
     *
     * @param string id The id/name of the set.
     * @return ZMRuleSet A <code>ZMRuleSet</code> instance or <code>null</code>.
     */
    function getRuleSet($id) {
        $ruleSet = null;
        if (array_key_exists($id, $this->sets_)) {
            $ruleSet = $this->sets_[$id];
        }
        return $ruleSet;
    }

    /**
     * Check if a <code>ZMRuleSet</code> exists for the given id.
     *
     * @param string id The id/name of the set.
     * @return boolean <code>true</code> if a <code>ZMRuleSet</code> exists, <code>false</code> if not.
     */
    function hasRuleSet($id) {
        return array_key_exists($id, $this->sets_);
    }

    /**
     * If a validation was not successful, corresponding error messages
     * will be available here.
     *
     * @return array A list of localized messages.
     */
    function getMessages() {
        return $this->messages_;
    }

    /**
     * Convert an object to map.
     *
     * @param mixed obj An object.
     * @param ZMRuleSet set The rule set.
     * @return array A name/value map.
     */
    function obj2map($obj, $set) {
        $prefixList = array('get', 'is', 'has');

        $map = array();
        foreach ($set->getRules() as $rule) {
            $name = $rule->getName();
            $ucName = ucwords($name);
            foreach ($prefixList as $prefix) {
                $getter = $prefix . $ucName;
                if (method_exists($obj, $getter)) {
                    $map[$name] = $obj->$getter();
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * Validate the given request/object using the named (id) rule set.
     *
     * <p>If the request parameter is an object, it will be added to the field map using the
     * magic key <code>__obj</code>.</p>
     *
     * @param mixed req A (request) map or an object.
     * @param string id The ruleset id.
     * @return boolean <code>true</code> if the validation was successful, <code>false</code> if not.
     */
    function validate($req, $id) {
        $this->messages_ = array();

        $set = array_key_exists($id, $this->sets_) ? $this->sets_[$id] : null;
        if (null == $set) {
            return true;
        }

        if (is_object($req)) {
            $map = $this->obj2map($req, $set);
            $map['__obj'] = $req;
        } else {
            $map = $req;
        }

        // iterate over rules
        $status = true;
        foreach ($set->getRules() as $rule) {
            if (!$rule->validate($map)) {
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
     * @param boolean echo If <code>true</code>, the JavaScript will be echo'ed as well as returned.
     * @return string Formatted JavaScript .
     */
    function toJSString($id, $echo=ZM_ECHO_DEFAULT) {
        $set = array_key_exists($id, $this->sets_) ? $this->sets_[$id] : null;

        if (null == $set) {
            return '';
        }

        $n = "\n";
        $js = '';
        $js .= '<script type="text/javascript">'.$n;
        $js .= '  var ' . $id . '_rules = new Array('.$n;
        $first = true;
        foreach ($set->getRules() as $rule) {
            $ruleJS = $rule->toJSString();
            if (zm_is_empty($ruleJS)) {
                continue;
            }
            if (!$first) $js .= ','.$n;
            $first = false;
            $js .= $ruleJS;
        }
        $js .= $n.'  );'.$n;
        $js .= '</script>'.$n;

        if ($echo) echo $js;
        return $js;
    }

    /**
     * Convenience method that will generate the JavaScript validation rules and
     * include the generic validation code.
     *
     * @param string id The id of the form to validate (the <code>ZMRuleSet</code> name).
     */
    function insertJSValidation($id) {
    global $zm_theme;

        $this->toJSString($id);
        include_once $zm_theme->themeFile("validation.js");
    }

}

?>
