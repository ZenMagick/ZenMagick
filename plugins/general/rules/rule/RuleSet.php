<?php

class RuleSet {
	var $name;
	var $rules;
	var $ruleOverrides;

	
	function __construct( $name ) {
		$this->name = $name;
		$this->rules = array();
		$this->ruleOverrides = array();
	}
	
	function addRule( $rule ) {
		array_push( $this->rules, $rule );
	}
	
	function addRuleOverride( $ruleOverride ) {
		array_push( $this->ruleOverrides, $ruleOverride );
	}
	
	function evaluate( $ruleContext ) {
		// Each Rule in the RuleSet is evaluated, and the 
		// results ANDed together taking account of any RuleOverrides
		$resultsForRules = array();
		// Accumulate the results of evaluating the Rules
		foreach( $this->rules as $r ) {
			$result = $r->evaluate( $ruleContext );
			$resultsForRules[ $r->name ] = $result;
		}
		// Apply the RuleOverrides
		foreach( $this->ruleOverrides as $ro ) {
			$result = $resultsForRules[ $ro->ruleName ];
			if( $result ) {
				$result->value = $ro->value;
			}
		}
		// Work out the final result
		$finalResult = true;
		foreach( array_values( $resultsForRules ) as $res ) {
			$finalResult = ( $finalResult and $res->value );
		}
		return new Proposition( $this->name, $finalResult );
	}

}
