<?php

    ZMSettings::set('resultListProductFilter', null);
    //ZMSettings::set('resultListProductSorter', null);

    $validator = ZMValidator::instance();
    /* checkout_refer_a_friend */
    $validator->addRules('checkout_refer_a_friend', array(
        array('EmailRule' ,'friend1', 'Please enter a valid email address for friend1.'),
        array('EmailRule' ,'friend2', 'Please enter a valid email address for friend2.'),
        array('EmailRule' ,'friend3', 'Please enter a valid email address for friend3.')
    ));

?>
