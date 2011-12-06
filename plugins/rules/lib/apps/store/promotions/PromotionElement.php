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
namespace apps\store\promotions;

/**
 * Element in a promotion.
 *
 * <p>Container for <code>Rule</code> and <code>RuleContext</code> for a specific promotion element.</p>
 *
 * @package apps.store.promotions
 * @author DerManoMann <mano@zenmagick.org>
 */
interface PromotionElement {

    /**
     * Get parameter config.
     *
     * @return array List of widgets to configure this element.
     */
    public function getParameterConfig();

    /**
     * Get rules.
     *
     * @return array List of <code>Rule</code> elements that make up this element.
     */
    public function getRules();

    /**
     * Get context.
     *
     * @param array parameter The parameter to configure this context.
     * @return array List of <code>RuleContext</code>s.
     */
    public function getRuleContexts($parameter);

}
