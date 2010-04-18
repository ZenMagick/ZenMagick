<?php

class Proposition extends RuleElement {
	var $value;

	
	function __construct( $name, $truthValue ) {
		$this->value = $truthValue;
		parent::__construct( $name );
	}
	
	function getType() {
		return "Proposition";
	}
	
	function toString() {
		$truthValue = "FALSE";
		if( $this->value == true ) {
			$truthValue = "TRUE";
		}
		return "Proposition statement = " . $this->name . ", value = " . $truthValue;
	}
	
	function logicalAnd( $proposition ) {
		$resultName  = "( " . $this->name . " AND " . $proposition->name . " )";
		$resultValue = ( $this->value and $proposition->value );
		return new Proposition( $resultName, $resultValue );
	}
	
	function logicalOr( $proposition ) {
		$resultName  = "( " . $this->name . " OR " . $proposition->name . " )";
		$resultValue = ( $this->value or $proposition->value );
		return new Proposition( $resultName, $resultValue );
	}
	
	function logicalNot( $proposition ) {
		$resultName  = "( NOT " . $proposition->name . " )";
		$resultValue = ( !$proposition->value );
		return new Proposition( $resultName, $resultValue );
	}
	
	function logicalXor( $proposition ) {
		$resultName  = "( " . $this->name . " XOR " . $proposition->name . " )";
		$resultValue = ( $this->value xor $proposition->value );
		return new Proposition( $resultName, $resultValue );
	}
}
