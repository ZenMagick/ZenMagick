<?php 

class TxtFileLoaderStrategy extends RuleContextLoaderStrategy {
	public $rule;
	public $ruleContext;

	
	public function loadRuleContext( $fileName, $id ) {
		$STATEMENT = 3;
		// $statements = $this->getStatements( $fileName );		
		$statements = $this->getStatements( $fileName );
		foreach( $statements as $statement ) {
			$tokens = split( ' ', $statement );
			// Every statement in the RuleContext file should have 
			// four (4) tokens. If not, ignore it.
			if( count( $tokens ) == $STATEMENT ) {
				$this->processRuleContextStatement( $tokens, $id );
			}
		}
		return $this->ruleContext;
	}
	
	protected function getRuleElementValue( $tokens, $args ) {
		return $tokens[ 2 ];
	}
	
	protected function processRuleContextStatement( $tokens, $args ) {
		if( $tokens[ 1 ] == 'EQUALS' ) {
			// It's a Variable
			$this->ruleContext->addVariable( $tokens[ 0 ], $tokens[ 2 ] );
		}
		elseif( $tokens[ 1 ] == 'IS' ) {
			// It's a Proposition
			$this->ruleContext->addProposition( $tokens[ 0 ], $tokens[ 2 ] );
		}
	}

}
