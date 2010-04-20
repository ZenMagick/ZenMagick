This is a preliminary plugin to evalute PHP Rules (http://lasse.net/mods/rules/), a rule engine that models formal propositional logic.


TODO

* add namespace/class prefix - 'RE' for RuleEngine?
* RuleContext: allow to append more than one or create a new one with 1-n existing (via c'tor args)?
* RuleContext: extend append method to check if overriding existing context data and if so if different (bad!)
* add wrapper classes/interfaces that allow to create store related rules; for example: ZMProductInCart
* should wrapper include code to reduce everything to a single boolean? Example: ZMProductInCart could wrap around a (dynamic)
  rule that does: foreach (cartItems as item) rule->addVariable('itemProductId'.n, 0); if(next) rule->addOp("OR"); context->addValue('itemProductId'.n, item->getProduct()->getId()); 
  or: use PHP code to iterate over cart items and do test, add single: rule->addProp('productInCar'.hash(), false); context->addProp('productInCar'.hash(), [the calculated boolean]);

