<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace apps\store\promotions\conditions\cart;

use apps\store\promotions\CartPromotionElement;
use phprules\Operator;
use phprules\SingleRule;
use phprules\RuleContext;

/**
 * Manufacturer in shopping cart promotion condition.
 *
 * @package apps.store.promotions.conditions.cart
 * @author DerManoMann <mano@zenmagick.org>
 */
class ManufacturerInCart extends CartPromotionElement {

    /**
     * {@inheritDoc}
     */
    public function getParameterConfig() {
        return array(
            _zm('Produt of [manufacturer] in cart') => array(
                'manufacturerSelectFormWidget#name=manufacturer'
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
        $rule->addOperator(Operator::IN);
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
