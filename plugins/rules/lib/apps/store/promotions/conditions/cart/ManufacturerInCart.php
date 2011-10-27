<?php
namespace apps\store\promotions\conditions\cart;

use apps\store\promotions\CartPromotionElement;
use phprules\SingleRule;
use phprules\RuleContext;

/**
 * Manufacturer in shopping cart promotion condition.
 */
class ManufacturerInCart extends CartPromotionElement {

    /**
     * {@inheritDoc}
     */
    public function getParameterConfig() {
        return array(
            _zm('Produt of [manufacturer] in cart') => array(
                'ZMManufacturerSelectFormWidget#name=manufacturer'
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getRules() {
        $rule = new SingleRule('manufacturerInCartRule');
        $rule->addVariable('manufacturerList');
        $rule->addVariable('manufacturer');
        $rule->addOperator('IN');
        return array($rule);
    }

    /**
     * {@inheritDoc}
     */
    public function getRuleContexts($parameter) {
        $manufacturerList = array();
        foreach ($this->getShoppingCart()->getItems() as $item) {
            if (null != ($item->getProduct()->getManufacturer())) {
                $manufacturerList[$manufacturer->getName()] = true;
            }
        }

        $ruleContext = new RuleContext('manufacturerInCartRuleContext');
        $ruleContext->addVariable('manufacturer', $parameter['manufacturer']);
        $ruleContext->addVariable('manufacturerList', $manufacturerList);

        return array($ruleContext);
    }

}
