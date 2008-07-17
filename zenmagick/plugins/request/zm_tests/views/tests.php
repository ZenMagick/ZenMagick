<h1>yo</h1>
<?php echo $zm_tests->getPluginDir() ?>

<?php

    // todo:
    // - move into controller
    // - create ZenMagick suite class to collect tests
    // - move zm tests into separate subfolder?

    // load tests
    $testsLoader = ZMLoader::make("Loader");
    $testsLoader->addPath($zm_tests->getPluginDir().'tests/');
    // tests are lower case for simplicity...
    $testsLoader->loadStatic();

    $suite = new TestSuite('ZenMagick Tests');
    $suite->addTestCase(new ProductPricing());
    $suite->addTestCase(new AttributePricing());
    $suite->run(new HtmlReporter());

?>
