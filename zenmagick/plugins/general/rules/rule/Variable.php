<?php

class Variable extends RuleElement {
	var $value;

	
	function __construct( $name, $value ) {
		$this->value = $value;
		parent::__construct( $name );
	}
	
	function getType() {
		return "Variable";
	}
	
	function toString() {
		return "Variable name = " . $this->name . ", value = " . $this->value;
	}
	
	function equalTo( $variable ) {
		$statement = "( " . $this->name . " == " . $variable->name . " )";
		$truthValue = ( $this->value == $variable->value );
		return new Proposition( $statement, $truthValue );
	}
	
	function notEqualTo( $variable ) {
		$statement = "( " . $this->name . " != " . $variable->name . " )";
		$truthValue = ( $this->value != $variable->value );
		return new Proposition( $statement, $truthValue );
	}
	
	function lessThan( $variable ) {
		$statement = "( " . $this->name . " < " . $variable->name . " )";
		$truthValue = ( $this->value < $variable->value );
		return new Proposition( $statement, $truthValue );
	}
	
	function lessThanOrEqualTo( $variable ) {
		$statement = "( " . $this->name . " <= " . $variable->name . " )";
		$truthValue = ( $this->value <= $variable->value );
		return new Proposition( $statement, $truthValue );
	}
	
	function greaterThan( $variable ) {
		$statement = "( " . $this->name . " > " . $variable->name . " )";
		$truthValue = ( $this->value > $variable->value );
		return new Proposition( $statement, $truthValue );
	}
	
	function greaterThanOrEqualTo( $variable ) {
		$statement = "( " . $this->name . " >= " . $variable->name . " )";
		$truthValue = ( $this->value >= $variable->value );
		return new Proposition( $statement, $truthValue );
	}

}
