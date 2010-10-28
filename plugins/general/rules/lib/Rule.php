<?php
/**
 * A <code>Rule</code> is a constraint on the operation of business systems. They:
 * <ol>
 * <li>Constrain business strucuture.</li>
 * <li>Constrain busines operations, i.e., they determine the sequence of actions in business workflows.</li>
 * </ol>
 */
class Rule {
	var $name;
	var $cannonicalName;
	var $description;
	var $elements;
	var $stack;
	

	function Rule( $name='' ) {
		$this->name = $name;
		$this->elements = array();
	}
	

	function addProposition( $name, $truthValue ) {
		$this->elements[] = new Proposition( $name, $truthValue );
	}
	
	function addVariable( $name, $value ) {
		$this->elements[] = new Variable( $name, $value );
	}
	
	function addOperator( $operator ) {
		$this->elements[] = new Operator( $operator );
	}
	
	function evaluate( $ruleContext ) {
		// The context contains Propositions and Variables that have
		// specific values. To apply the context, simply copy these values
		// into the corresponding Propositions and Variables in the Rule
		$this->stack = array();
		foreach( $this->elements as $e ) {
			if( $e->getType() == "Proposition" or $e->getType() == "Variable" ) {
				$element = $ruleContext->findElement( $e->name );
				if( $element ) {
					$e->value = $element->value;
				}
				else {
					$e->value = NULL;
				}
			}
		}
		return $this->process();
	}
	
	function process() {
		$this->stack = array();
		foreach( $this->elements as $e ) {
			if( $e->getType() == "Operator" ) {
				$this->processOperator( $e, $this->stack );
			}
			elseif( $e->getType() == "Proposition" ) {
				$this->processProposition( $e, $this->stack );
			}
			elseif( $e->getType() == "Variable" ) {
				$this->processVariable( $e, $this->stack );
			}
			else {
				echo( "Syntax error: " . $e->getType() );
				throw new Exception( "Syntax error: " . $e->getType() );
			}
		}
		return array_pop( $this->stack );
	}
	
	function processOperator( $operator ) {
		if( $operator->name == "AND" ) {
			$this->processAnd();
		}
		elseif( $operator->name == "OR" ) {
			$this->processOr();
		}
		elseif( $operator->name == "NOT" ) {
			$this->processNot();
		}
		elseif( $operator->name == "EQUALTO" ) {
			$this->processEqualTo();
		}
		elseif( $operator->name == "NOTEQUALTO" ) {
			$this->processNotEqualTo();
		}
		elseif( $operator->name == "LESSTHAN" ) {
			$this->processLessThan();
		}
		if( $operator->name == "GREATERTHAN" ) {
			$this->processGreaterThan();
		}
		if( $operator->name == "LESSTHANOREQUALTO" ) {
			$this->processLessThanOrEqualTo();
		}
		if( $operator->name == "GREATERTHANOREQUALTO" ) {
			$this->processGreaterThanOrEqualTo();
		}
	}
	
	function processProposition( $proposition ) {
		$this->stack[] = $proposition;
	}
	
	function processVariable( $variable ) {
		$this->stack[] = $variable;
	}
	
	function processAnd() {
		$rhs = array_pop( $this->stack );
		$lhs = array_pop( $this->stack );
		$this->stack[] = $rhs->logicalAnd( $lhs );
	}
	
	function processOr() {
		$rhs = array_pop( $this->stack );
		$lhs = array_pop( $this->stack );
		$this->stack[] = $rhs->logicalOr( $lhs );
	}
	
	function processXor() {
		$rhs = array_pop( $this->stack );
		$lhs = array_pop( $this->stack );
		$this->stack[] = $rhs->logicalXor( $lhs );
	}
	
	function processNot() {
		$rhs = array_pop( $this->stack );
		$this->stack[] = $rhs->logicalNot();
	}
	
	function processEqualTo() {
		$rhs = array_pop( $this->stack );
		$lhs = array_pop( $this->stack );
		$this->stack[] = $rhs->equalTo( $lhs );
	}
	
	function processNotEqualTo() {
		$rhs = array_pop( $this->stack );
		$lhs = array_pop( $this->stack );
		$this->stack[] = $rhs->notEqualTo( $lhs );
	}
	
	function processLessThan() {
		$rhs = array_pop( $this->stack );
		$lhs = array_pop( $this->stack );
		$this->stack[] = $rhs->lessThan( $lhs );
	}
	
	function processGreaterThan() {
		$rhs = array_pop( $this->stack );
		$lhs = array_pop( $this->stack );
		$this->stack[] = $rhs->greaterThan( $lhs );
	}
	
	function processLessThanOrEqualTo() {
		$rhs = array_pop( $this->stack );
		$lhs = array_pop( $this->stack );
		$this->stack[] = $rhs->lessThanOrEqualTo( $lhs );
	}
	
	function processGreaterThanOrEqualTo() {
		$rhs = array_pop( $this->stack );
		$lhs = array_pop( $this->stack );
		$this->stack[] = $rhs->greaterThanOrEqualTo( $lhs );
	}
	
	function toString() {
		$result = $this->name . "\n";
		foreach( $this->elements as $e ) {
			$result = $result . $e->toString() . "\n";
		}
		return $result;
	}

}
