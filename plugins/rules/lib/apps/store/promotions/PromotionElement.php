<?php
namespace apps\store\promotions;

/**
 * Element in a promotion.
 *
 * <p>Container for <code>Rule</code> and <code>RuleContext</code> for a specific promotion element.</p>
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
