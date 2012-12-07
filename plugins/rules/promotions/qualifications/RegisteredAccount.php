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
namespace ZenMagick\plugins\rules\promotions\qualifications;

use ZenMagick\plugins\rules\promotions\AbstractPromotionElement;
use ZenMagick\StoreBundle\Entity\Account;
use phprules\RuleContext;

/**
 * Registered account promotion qualification.
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class RegisteredAccount extends AbstractPromotionElement
{
    /**
     * {@inheritDoc}
     */
    public function getRules()
    {
        $rule = new Rule('registeredAccountRule');
        $rule->addProposition('isRegistered');

        return array($rule);
    }

    /**
     * {@inheritDoc}
     */
    public function getRuleContexts($parameter)
    {
        $ruleContext = new RuleContext('manufacturerInCartRuleContext');
        $ruleContext->addVariable('isRegistered', Account::REGISTERED == $this->getAccount()->getType());

        return array($ruleContext);
    }

}
