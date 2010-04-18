<?php

class Operator extends RuleElement {
	var $operators;

	
	function __construct( $operator ) {
		$this->operators = array( "AND", "OR", "NOT", "EQUALTO", "NOTEQUALTO", "LESSTHAN", "GREATERTHAN", "LESSTHANOREQUALTO", "GREATERTHANOREQUALTO" );
		if( in_array( $operator, $this->operators ) ) {
			parent::__construct( $operator );
		}
		else {
			throw new Exception( $operator . " is not a valid operator." );
		}
	}
	
	function getType() {
		return "Operator";
	}
	
	function toString() {
		return $this->name;
	}

}
