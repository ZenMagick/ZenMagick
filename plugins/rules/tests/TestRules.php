<?php

use phprules\Rule;
use phprules\RuleContext;
use phprules\RuleSet;

class TestRules extends ZMTestCase {

    public function testRule() {
        $rule = new Rule( 'passengerSuitableForUpgrade' );
        $ruleContext = new RuleContext( 'passengerIsSuitableForUpgrade' );
        // load the rule

        //(
        $rule->addProposition( 'passengerIsGoldCardHolder', true );
        $rule->addProposition( 'passengerIsSilverCardHolder', true );
        $rule->addOperator( "OR" );
        //gold or silver
        $rule->addProposition( 'passengerIsEconomy', true );
        $rule->addOperator( "AND" );
        // and economy
        //)

        //(
        $rule->addVariable( 'passengerCarryOnBaggageAllowance', 15.0 );
        $rule->addVariable( 'passengerCarryOnBaggageWeight', 0.0 );
        $rule->addOperator( "LESSTHANOREQUALTO" );
        // weight < allowance
        //)

        $rule->addOperator( "AND" );
        // block1 AND block2

        // load the rule context
        $ruleContext->addProposition( 'passengerIsEconomy', true );
        $ruleContext->addProposition( 'passengerIsGoldCardHolder', true );
        $ruleContext->addProposition( 'passengerIsSilverCardHolder', true );
        $ruleContext->addVariable( 'passengerCarryOnBaggageAllowance', 15.0 );
        $ruleContext->addVariable( 'passengerCarryOnBaggageWeight', 10.0 );
        $proposition = $rule->evaluate( $ruleContext );
        echo $proposition."<BR>";
        $this->assertTrue($proposition->getValue());
    }

    public function testRuleSet() {
        // class stuff
        $classRule = new Rule( 'classRule' );
        $classRule->addProposition( 'passengerIsGoldCardHolder', true );
        $classRule->addProposition( 'passengerIsSilverCardHolder', true );
        $classRule->addOperator( "OR" );
        $classRule->addProposition( 'passengerIsEconomy', true );
        $classRule->addOperator( "AND" );

        $classRuleContext = new RuleContext( 'classRuleContext' );
        $classRuleContext->addProposition( 'passengerIsEconomy', true );
        $classRuleContext->addProposition( 'passengerIsGoldCardHolder', true );
        $classRuleContext->addProposition( 'passengerIsSilverCardHolder', true );

        // baggage stuff
        $baggageRule = new Rule( 'baggageRule' );
        $baggageRule->addVariable( 'passengerCarryOnBaggageAllowance', 15.0 );
        $baggageRule->addVariable( 'passengerCarryOnBaggageWeight', 0.0 );
        $baggageRule->addOperator( "LESSTHANOREQUALTO" );

        $baggageRuleContext = new RuleContext( 'baggageRuleContext' );
        $baggageRuleContext->addVariable( 'passengerCarryOnBaggageAllowance', 15.0 );
        $baggageRuleContext->addVariable( 'passengerCarryOnBaggageWeight', 10.0 );

        // merge
        $ruleSet = new RuleSet ( 'passengerSuitableForUpgrade' );
        $ruleSet->addRule( $classRule );
        $ruleSet->addRule( $baggageRule );

        $ruleSetContext = new RuleContext( 'passengerSuitableForUpgrade' );
        $ruleSetContext->append( $classRuleContext );
        $ruleSetContext->append( $baggageRuleContext );

        // load the rule context
        $proposition = $ruleSet->evaluate( $ruleSetContext );
        echo $proposition."<BR>";
        $this->assertTrue($proposition->getValue());
    }

    public function testIn() {
        // rule with default null brand
        $brandRule = new Rule( 'brandRule' );
        $brandRule->addVariable( 'brandList', array() );
        $brandRule->addVariable( 'brand', null );
        $brandRule->addOperator( "IN" );

        // there might be other rules, etc to add...
        $finalRuleSet = new RuleSet ( 'finalRuleSet' );
        $finalRuleSet->addRule( $brandRule );


        // the actual values...
        $brandRuleSetContext = new RuleContext( 'brandRuleSetContex' );
        $brandRuleSetContext->addVariable( 'brand', 'yoo' );
        $brandRuleSetContext->addVariable( 'brandList', array('yoo', 'foo', 'bar') );

        // collect all variables
        $finalRuleSetContext = new RuleContext( 'finalRuleSetContex' );
        $finalRuleSetContext->append( $brandRuleSetContext );


        $proposition = $finalRuleSet->evaluate( $finalRuleSetContext );
        echo $proposition."<BR>";
        $this->assertTrue($proposition->value);
    }


  // a business 'rule' provides:
  // a) rule props/variables

}
