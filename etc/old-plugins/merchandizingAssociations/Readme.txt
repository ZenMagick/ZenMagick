This is a ZenMagick plugin adding support for generic product associations.


Installation
============
1) Unzip the plugin package into the zenmagick/plugins directory.
2) Install plugin using the ZenMagick Plugin Manager.


Usage
=====
To display associated products, for example, cross sells, the following code could be used
in your template:

    $crossSells = $product->getProductAssociationsForType('xsell');
    foreach ($crossSells as $productAssociation) {
        $crossSell = $productAssociation->getTargetProduct();
        echo $crossSell->getName()."<BR>";
    }

