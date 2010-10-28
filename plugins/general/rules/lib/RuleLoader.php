<?php 
class RuleLoader {
	private $strategy = null;

	
	public function setStrategy( $strategy ) {
		$this->strategy = $strategy;
		$strategy->rule = $this->rule;
		$strategy->ruleContext = $this->ruleContext;
	}
	
	public function loadRule( $fileName ) {
		return $this->strategy->loadRule( $fileName );		
	}
	
	public function loadRuleContext( $fileName, $id ) {
		return $this->strategy->loadRuleContext( $fileName, $id );
	}

}
