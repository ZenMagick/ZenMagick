This is a ZenMagick plugin adding product associations support.


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.


Database changes
================
The plugin adds two new tables:

1) zm_association_types
Contains a list of association types that are configured. Types can be addressed using 
global constants. For example, the type 'cross-sell' can be specified using the define
ZM_PA_CROSS_SELL.

2) zm_product_associations
The actual associations. Associations have a type, a start and optional end date and a sort order.


Usage
=====
To display associated products, for example, cross sells, the following code could be used
in your template:

    $crossSells = $zm_associations->getProductAssociations($product->getId(), ZM_PA_CROSS_SELL);
    foreach ($crossSells as $crossSell) {
        $product = $crossSell->getTargetProduct();
        echo $product->getName()."<BR>";
    }


$product is the current product, so the id is used to look up associated products of the specified
type (cross-sell).
