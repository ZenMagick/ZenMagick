<?php

class TestRules extends ZMTestCase {

	function testEvaluate() {
		$this->rule = new Rule( 'passengerSuitableForUpgrade' );
		$this->ruleContext = new RuleContext( 'passengerIsSuitableForUpgrade' );
		// load the rule
		$this->rule->addProposition( 'passengerIsEconomy', true );
		$this->rule->addProposition( 'passengerIsGoldCardHolder', true );
		$this->rule->addProposition( 'passengerIsSilverCardHolder', true );
		$this->rule->addOperator( "OR" );
		$this->rule->addOperator( "AND" );
		$this->rule->addVariable( 'passengerCarryOnBaggageAllowance', 15.0 );
		$this->rule->addVariable( 'passengerCarryOnBaggageWeight', 0.0 );
		$this->rule->addOperator( "LESSTHANOREQUALTO" );
		$this->rule->addOperator( "AND" );
		// load the rule context
		$this->ruleContext->addProposition( 'passengerIsEconomy', true );
		$this->ruleContext->addProposition( 'passengerIsGoldCardHolder', true );
		$this->ruleContext->addProposition( 'passengerIsSilverCardHolder', true );
		$this->ruleContext->addVariable( 'passengerCarryOnBaggageAllowance', 15.0 );
		$this->ruleContext->addVariable( 'passengerCarryOnBaggageWeight', 10.0 );
		$proposition = $this->rule->evaluate( $this->ruleContext );
		echo $proposition->toString();
    $this->assertTrue($proposition->value);
	}

}
