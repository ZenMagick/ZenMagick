<?php

class RuleElement {
	var $name;

	
	function __construct( $name ) {
		$this->name = $name;		
	}
	
	function getType() {
		return "RuleElement";
	}

}
