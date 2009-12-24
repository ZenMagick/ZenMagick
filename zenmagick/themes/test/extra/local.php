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

    function split_desc($s, $first=true) {
        $token = explode('.', $s);
        if ($first) {
            if (3 > count($token)) {
                return $s;
            } else {
                return $token[0].'.'.$token[1].'.';
            }
        } else {
            if (3 > count($token)) {
                return '';
            } else {
                $token = array_splice($token, 2);
                return implode('.', $token);
            }
        }
    }

    $s = 'abc. def. ghi.';
    //echo split_desc($s, true)."<BR>"; echo split_desc($s, false)."<BR>";

    class RestrictCategory {
        public function onZMInitDone($args=null) {
            $request = $args['request'];
            if (in_array($request->getRequestId(), array('index', 'category', 'product_info'))) {
                $catPath = $request->getCategoryPath();
                $productId = $request->getProductId();
                if (0 === strpos($catPath, '3_') || '3' == $catPath || 34 == $productId) {
                    $session = $request->getSession();
                    if (null !== $session->getValue('catAllowed')) {
                        // all good
                    } else {
                        // ask for password
                        // redirect with this URL to return to if sucessful
                    }
                }
            }
        }
    }
    ZMEvents::instance()->attach(new RestrictCategory());

    if ('haml1' == ZMRequest::instance()->getRequestId()) {
      ZMUrlManager::instance()->setMapping(null, array('template' => 'haml1', 'view' => 'SavantView#layout=ref::null&config='.urlencode('compiler=ref::SavantHamlCompiler')));
      ZMSettings::set('zenmagick.mvc.templates.ext', '.haml');
    }

?>
