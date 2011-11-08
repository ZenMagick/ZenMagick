<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
?>
<?php
namespace apps\store\promotions\conditions\cart;

use apps\store\promotions\CatalogPromotionElement;
use phprules\Operator;
use phprules\SingleRule;
use phprules\RuleContext;

/**
 * Manufacturer catalog promotion condition.
 *
 * @package apps.store.promotions.conditions.catalog
 * @author DerManoMann
 */
class Manufacturer extends CatalogPromotionElement {

    /**
     * {@inheritDoc}
     */
    public function getParameterConfig() {
        return array(
            _zm('Manufacturer is [manufacturer]') => array(
                'ZMManufacturerSelectFormWidget#name=manufacturer'
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getRules() {
        $rule = new SingleRule('manufacturerRule');
        $rule->addVariable('productManufacturer');
        $rule->addVariable('manufacturer');
        $rule->addOperator(Operator::EQUAL_TO);
        return array($rule);
    }

    /**
     * {@inheritDoc}
     */
    public function getRuleContexts($parameter) {
        $manufacturer = $this->getProduct()->getManufacturer();
        $ruleContext = new RuleContext('manufacturerRuleContext');
        $ruleContext->addVariable('productManufacturer', null != $manufacturer ? $manufacturer->getId() : null);
        $ruleContext->addVariable('manufacturer', $parameter['manufacturer']);

        return array($ruleContext);
    }

}
