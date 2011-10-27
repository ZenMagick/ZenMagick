<?php
namespace apps\store\promotions\qualifications;

use apps\store\promotions\AbstractPromotionElement;
use phprules\SingleRule;
use phprules\RuleContext;

/**
 * Registered account promotion qualification.
 */
class RegisteredAccount extends AbstractPromotionElement {

    /**
     * {@inheritDoc}
     */
    public function getRules() {
        $rule = new Rule('registeredAccountRule');
        $rule->addProposition('isRegistered');
        return array($rule);
    }

    /**
     * {@inheritDoc}
     */
    public function getRuleContexts($parameter) {
        $ruleContext = new RuleContext('manufacturerInCartRuleContext');
        $ruleContext->addVariable('isRegistered', ZMAccount::REGISTERED == $this->getAccount()->getType());
        return array($ruleContext);
    }

}
