This is a Catalog Manager plugin to allow to tag products
=========================================================

Once installed a new tab will appear in Catalog Manager if a product is selected.
Tags may be entered as comma separated list of text or, alternatively, it is possible to select
from the list of already used tags by just clicking to add.


Tag groups
==========
It is possible to find related products by doing the following:

    $product = ZMProducts::instance()->getProductForId(3);
    $similarTaggedProducts = $product->getProductAssociationsForType('tags');


Tag Cloud
=========
To implement a tag cloud, the following code may be used to get details about how often
a each available tag is used:

  $tagStats = ZMTags::instance()->getStats();

