<?php

/**
 * Simple <code>ZMProductAssociationHandler</code> implementation.
 *
 * @package org.zenmagick.plugins.zm_token.tests
 * @author DerManoMann
 * @version $Id: SimpleProductAssociationHandler.php 2427 2009-07-14 10:24:33Z dermanomann $
 */
class SimpleProductAssociationHandler extends ZMTestCase implements ZMProductAssociationHandler {

    /**
     * {@inheritDoc}
     */
    public function getType() {
        return "simple";
    }

    /**
     * Return some hardcoded test data.
     *
     * @param int productId The source product id.
     * @param array args Optional parameter that might be required by the used type; default is <code>null</code> for none.
     * @param boolean all Optional flag to load all configured products, regardless of start/end date, etc; default is <code>false</code>.
     * @return array A list of <code>ZMProductAssociation</code> instances.
     */
    public function getProductAssociationsForProductId($productId, $args=null, $all=false) {
        $assoc = array();
        if (13 == $productId) {
            $assoc[] = new ZMProductAssociation(13);
        }

        return $assoc;

    }

}

?>
