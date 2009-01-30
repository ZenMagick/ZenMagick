<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
 * @package org.zenmagick.validation
 * @version $Id$
 */
class ZMValidator extends ZMObject {
    private $sets_;
    private $messages_;


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
        return ZMObject::singleton('Validator');
    }


    /**
     * Add a new rule for the given rule set id.
     *
     * @param string id The rule set id.
     * @param array rule A plain text rule.
     */
    public function addRule($id, $rule) {
        if (array_key_exists($id, $this->sets_)) {
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
     */
    public function addRules($id, $rules) {
        if (array_key_exists($id, $this->sets_)) {
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
        $ruleSet = null;
        if (array_key_exists($id, $this->sets_)) {
            $ruleSet = $this->sets_[$id];
        }

        if ($compile) {
            $rules = $ruleSet;
            $ruleSet = ZMLoader::make('RuleSet', $id);
            foreach ($rules as $rule) {
                $ruleSet->addRule(ZMLoader::make($rule));
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
     * Convert an object to map.
     *
     * @param mixed obj An object.
     * @param ZMRuleSet set The rule set.
     * @return array A name/value map.
     */
    protected function obj2map($obj, $set) {
        //XXX: use beans?
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
    public function validate($req, $id) {
        $this->messages_ = array();

        $set = $this->getRuleSet($id, true);
        if (null == $set) {
            return true;
        }

        if (is_object($req)) {
            $map = $this->obj2map($req, $set);
            $map['__obj'] = $req;
        } else {
            $map = $req;
        }

        // initial status
        $status = $this->validateSession($map, $id);

        // iterate over rules
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
     * Validate session token.
     *
     * @param mixed req A (request) map or an object.
     * @param string id The ruleset id.
     * @return boolean <code>true</code> if the validation was successful, <code>false</code> if not.
     */
    protected function validateSession($req, $id) {
        $valid = true;
        if (ZMTools::inArray($id, ZMSettings::get('tokenSecuredForms'))) {
            $valid = false;
            if (isset($req[ZMSession::TOKEN_NAME])) {
                $valid = (ZMRequest::getSession()->getToken() == $req[ZMSession::TOKEN_NAME]);
            }
        }

        if (!$valid) {
            $this->messages_[ZMSession::TOKEN_NAME] = array(zm_l10n_get('Invalid session request.'));
        }
        return $valid;
    }

    /**
     * Create JS validation rules for the given rule set.
     *
     * @param string id The id of the form to validate (the <code>ZMRuleSet</code> name).
     * @param boolean echo If <code>true</code>, the JavaScript will be echo'ed as well as returned.
     * @return string Formatted JavaScript .
     */
    public function toJSString($id, $echo=ZM_ECHO_DEFAULT) {
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

        if ($echo) echo $js;
        return $js;
    }

    /**
     * Convenience method that will generate the JavaScript validation rules and
     * include the generic validation code.
     *
     * @param string id The id of the form to validate (the <code>ZMRuleSet</code> name).
     */
    public function insertJSValidation($id) {
        $this->toJSString($id);
        // inline JS
        include_once ZMRuntime::getTheme()->themeFile("validation.js");
    }

}

?>
