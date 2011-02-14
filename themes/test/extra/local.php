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
        public function onInitDone($args=null) {
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
    zenmagick\base\Runtime::getEventDispatcher()->listen(new RestrictCategory());

    if ('haml_product' == ZMRequest::instance()->getRequestId()) {
        ZMUrlManager::instance()->setMapping('haml_product', array(
          'controller' => 'ProductInfoController',
          'product_info' => array(
            'template' => 'haml_product',
            'view' => 'set::zenmagick.mvc.view.default#layout=bean::null&config='.urlencode('compiler=bean::SavantHamlCompiler')
          )
        ));
        ZMSettings::set('zenmagick.mvc.templates.ext', '.haml');
    }

    if ('twig_product' == ZMRequest::instance()->getRequestId()) {
        ZMUrlManager::instance()->setMapping('twig_product', array(
          'controller' => 'ProductInfoController',
          'product_info' => array(
            'template' => 'twig_product'
          )
        ));
        ZMSettings::set('zenmagick.mvc.templates.ext', '.twig');
    }

    class SearchLogger {
        public function onSearch($args) {
            $criteria = $args['criteria'];
				    if (!isset($_SESSION['search_log_term']) || ($_SESSION['search_log_term'] != $criteria->getKeywords())) {
					      $_SESSION['search_log_term'] = $criteria->getKeywords();
                $tableName = DB_PREFIX.'search_log';
                $resultList = $args['resultList'];
                $sql = "insert into " . $tableName . " (search_term, search_time, search_results) values (:search_term,now(),:search_results)";
                $args = array('search_term' => $criteria->getKeywords(), 'search_results' => $resultList->getNumberOfResults());
                ZMRuntime::getDatabase()->update($sql, $args, $tableName);
            }
        }
    }

    zenmagick\base\Runtime::getEventDispatcher()->listen(new SearchLogger());

    // programmatically change the url mapping
    ZMUrlManager::instance()->setMapping('privacy', array('view' => 'RedirectView#url=http://www.dilbert.com/'));
