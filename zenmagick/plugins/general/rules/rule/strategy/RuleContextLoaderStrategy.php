<?php 
abstract class RuleContextLoaderStrategy {

	abstract public function loadRuleContext( $fileName, $id );
	
	abstract protected function getRuleElementValue( $tokens, $args );
	
	abstract protected function processRuleContextStatement( $tokens, $args );
	
	public function loadRule( $fileName ) {
		$OPERATOR = 1;
		$STATEMENT = 3;
		$statements = $this->getStatements( $fileName );				
		foreach( $statements as $statement ) {
			$tokens = split( ' ', $statement );
			$statementType = count( $tokens );
			if( $statementType == $OPERATOR ) {
				$this->processOperator( $tokens, $this->rule );
			}
			elseif( $statementType == $STATEMENT ) {
				$this->processRuleStatement( $tokens, $this->rule );
			}
		}
		return $this->rule;		
	}
	
	protected function processRuleStatement( $tokens, $ruleOrRuleContext ) {
		if( $tokens[ 1 ] == 'IS' ) {
			$ruleOrRuleContext->addProposition( $tokens[ 0 ], (bool)$tokens[ 1 ] );
		}
		elseif( $tokens[ 1 ] == 'EQUALS' ) {
			$ruleOrRuleContext->addVariable( $tokens[ 0 ], $tokens[ 2 ] );
		}
	}

	protected function processOperator( $tokens, $ruleOrRuleContext ) {
		$ruleOrRuleContext->addOperator( $tokens[ 0 ] );
	}
	
	protected function getStatements( $fileName ) {
		$lines = array();
		$ruleFile = fopen( $fileName, 'r' );
		if( $ruleFile ) {
			while( !feof( $ruleFile ) ) {
				$line = trim( fgets( $ruleFile ) );
				if( !empty( $line ) and !$this->isCommentStatement( $line ) ) {
					$lines[] = $line;
				}
			}
		}
		else {
			die( 'Failed to open stream: ' . $fileName . ' does not exist.' );
		}
		fclose( $ruleFile );
		return $lines;
	}
	
	protected function isCommentStatement( $text ) {
		$text = trim( $text );
		return ( strstr( $text, '#' ) == $text );
	}	

}
