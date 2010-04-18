<?php

class RuleContext {
	var $name;
	var $elements;

	
	function __construct( $name='' ) {
		$this->name = $name;
		// elements is a dictionary - a set of {name, value} pairs
		// The names are Proposition or Variable names and
		// the values are the Propositions or Variables themselves
		$this->elements = array();
	}
	
	function addProposition( $statement, $value ) {
		$this->elements[ $statement ] = new Proposition( $statement, $value );
	}
	
	function addVariable( $name, $value ) {
		$this->elements[ $name ] = new Variable( $name, $value );
	}
	
	function findElement( $name ) {
		return $this->elements[ $name ];
	}
	
	function append( $ruleContext ) {
		foreach( $ruleContext->elements as $e ) {
			$this->elements[ $e->name ] = $e;
		}
	}
	
	function toString() {
		$result = "";
		foreach( array_values( $this->elements ) as $e ) {
			$result = $result . $e->toString() . "\n";
		}
		return $result;
	}

}
