<?php 

class SqlFileLoaderStrategy extends RuleContextLoaderStrategy {
	public $rule;
	public $ruleContext;


	public function loadRuleContext( $fileName, $id ) {
		$STATEMENT = 4;
		// $statements = $this->getStatements( $fileName );		
		$statements = $this->getStatements( $fileName );
		foreach( $statements as $statement ) {
			$tokens = split( '\|', $statement );
			// Every statement in the RuleContext file should have 
			// four (4) tokens. If not, ignore it.
			if( count( $tokens ) == $STATEMENT ) {
				$this->processRuleContextStatement( $tokens, $id );
			}
		}
		return $this->ruleContext;
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
	
	protected function processRuleContextStatement( $tokens, $args ) {
		$ruleElementName      = $tokens[ 1 ];
		$ruleElementValue     = null;
		
		$ruleElementValue = $this->getRuleElementValue( $tokens, $args );
		$this->ruleContext->addVariable( $ruleElementName, $ruleElementValue );
	}

}
