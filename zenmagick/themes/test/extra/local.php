<?php

    ZMSettings::set('resultListProductFilter', null);
    //ZMSettings::set('resultListProductSorter', null);

    // do not create default address during sign up
    ZMUrlMapper::instance()->setMappingInfo('create_account', array('controllerDefinition' => 'CreateAccountController#createDefaultAddress=false'));

?>
