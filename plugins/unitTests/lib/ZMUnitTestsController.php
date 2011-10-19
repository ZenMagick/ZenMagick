<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<?php

use zenmagick\base\Beans;
use zenmagick\base\ClassLoader;
use zenmagick\base\Runtime;

/**
 * Unit testing controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.unitTests
 */
class ZMUnitTestsController extends \ZMController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Find tests in the given path.
     *
     * @param string path The path.
     * @return array List of test classes.
     */
    protected function findTests($path) {
        $tests = array();
        $ext = '.php';
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $filename => $fileInfo) {
            if ($fileInfo->isFile() && $ext == substr($fileInfo->getFilename(), -strlen($ext))) {
                $className = substr($fileInfo->getFilename(), 0, strlen($fileInfo->getFilename())-strlen($ext));
                if (ZMLangUtils::startsWith($className, 'Test')) {
                    $tests[$className] = $fileInfo->getPathname();
                }
            }
        }
        return $tests;
    }

    /**
     * Process a HTTP GET request.
     *
     * @return ZMView A <code>ZMView</code> that handles presentation or <code>null</code>
     * if the controller generates the contents itself.
     */
    public function processGet($request) {

        // XXX: for adminusers testcase...
        $classLoader = new ClassLoader();
        $classLoader->register();
        $classLoader->addPath(Runtime::getInstallationPath().'apps/admin/lib/services');
        $classLoader->addPath(Runtime::getInstallationPath().'apps/admin/lib/model');
        $classLoader->addPath($this->getTestPlugin()->getPluginDirectory().'tests');

        // add tests folder to class path
        $testBaseDir = $this->getTestPlugin()->getPluginDirectory().'tests';
        $tests = $this->findTests($testBaseDir);

        // group tests
        $allTests = array();
        foreach ($tests as $class => $file) {
            $dir = $file;
            $group = UNIT_TESTS_GROUP_DEFAULT;
            do {
                $dir = dirname($dir);
                if ($dir != $testBaseDir) {
                    $group = basename($dir);
                }
            } while ($dir != $testBaseDir);
            if (!array_key_exists($group, $allTests)) {
                $allTests[$group] = array();
            }
            $allTests[$group][] = $class;
        }

        // add plugins/tests folder of all available plugins to loader
        foreach (\ZMPlugins::instance()->getAllPlugins() as $plugin) {
            if ($plugin instanceof \ZMUnitTestsPlugin) {
                continue;
            }
            $ptests = $plugin->getPluginDirectory().'tests' . DIRECTORY_SEPARATOR;
            $classLoader->addPath($ptests);
            if (is_dir($ptests)) {
                foreach ($this->findTests($ptests) as $className => $file) {
                    $this->getTestPlugin()->addTest($className);
                }
            }
        }

        // merge in all custom registered tests
        $allTests = array_merge($allTests, $this->getTestPlugin()->getTests());
        ksort($allTests);

        // create instances rather than just class names
        foreach ($allTests as $group => $tests) {
            foreach ($tests as $key => $clazz) {
                if (null != ($test = Beans::getBean($clazz))) {
                    $allTests[$group][$key] = $test;
                } else {
                    $this->messageService->warn('could not create instance of '.$clazz);
                    unset($allTests[$group][$key]);
                }
            }
        }

        $context = array();

        $context['all_tests'] = $allTests;

        $testCases = $request->getParameter('testCases', array());
        $tests = $request->getParameter('tests', array());
        // build testCases from tests as there might be tests selected, but not the testCase
        $testCaseMap = array();
        foreach ($tests as $id) {
            // XXX: this should not be handled by the reporter
            list($testCase, $test) = explode('-', $id);
            $testCaseMap[$testCase] = $testCase;
        }
        $testCases = array();
        foreach ($testCaseMap as $testCase) {
            $testCases[] = $testCase;
        }

        $hideErrors = \ZMLangUtils::asBoolean($request->getParameter('hideErrors', false));
        $context['hideErrors'] = $hideErrors;

        $context['all_selected_testCases'] = array_flip($testCases);
        $context['all_selected_tests'] = array_flip($tests);
        $context['all_results'] = array();
        if (0 < count($testCases)) {
            // prepare selected tests
            $suite = new TestSuite('ZenMagick Tests');
            foreach ($testCases as $name) {
                $testCase = Beans::getBean($name);
                if ($testCase instanceof SimpleTestCase) {
                    $suite->add($name);
                }
            }

            // allow for more time to run tests
            set_time_limit(300);

            // run tests
            $reporter = new \ZMHtmlReporter($hideErrors);
            // enable all selected tests
            foreach ($tests as $id) {
                // XXX: this should not be handled by the reporter
                list($testCase, $test) = explode('-', $id);
                $reporter->enableTest($testCase, $test);
            }
            ob_start();
            $suite->run($reporter);
            $report = ob_get_clean();
            $context['all_results'] = $reporter->getResults();
        } else {
            $report = '';
        }

        $context['html_report'] = $report;

        return $this->findView(null, $context);
    }

    /**
     * Get the test plugin.
     *
     * @return ZMPlugin The plugin.
     */
    public function getTestPlugin() {
        return $this->container->get('pluginService')->getPluginForId('unitTests');
    }

}
