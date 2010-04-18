<?php 
abstract class RuleLoaderStrategy {

	abstract public function loadRule( $fileName );
	
	abstract public function loadRuleContext( $id );
	
	protected function processRuleStatement( $tokens, $ruleOrRuleContext ) {
		if( $tokens[ 1 ] == 'IS' ) {
			$ruleOrRuleContext->addProposition( $tokens[ 0 ], (bool)$tokens[ 1 ] );
		}
		elseif( $tokens[ 1 ] == 'EQUALS' ) {
			$ruleOrRuleContext->addVariable( $tokens[ 0 ], $tokens[ 2 ] );
		}
	}
	
	protected function processRuleContextStatement( $tokens, $args ) {
		$ruleElementName      = $tokens[ 1 ];
		$ruleElementValue     = null;
		
		$ruleElementValue = $this->getRuleElementValue( $tokens, $args );
		$this->ruleContext->addVariable( $ruleElementName, $ruleElementValue );
	}	
	
	protected function processOperator( $tokens, $ruleOrRuleContext ) {
		$ruleOrRuleContext->addOperator( $tokens[ 0 ] );
	}
	
	protected function getRuleElementValue( $tokens, $args ) {
		$ruleElementType      = $tokens[ 0 ];
		$ruleElementName      = $tokens[ 1 ];
		$sql                  = $tokens[ 2 ];
		$ruleElementValueType = trim( $tokens[ 3 ] );
		$ruleElementValue     = null;
		// Query the database
		$query = $this->db->query( $sql, $args );
		$result = $query->result_array();
		// Get the value
		$ruleElementValue = array_pop( array_values( $result[ 0 ] ) );
		// Set the data type
		settype( $ruleElementValue, $ruleElementValueType );
		return $ruleElementValue;
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
?>
