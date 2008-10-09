<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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

/**
 * Custom simpletest <code>HtmlReporter</code> implementation.
 *
 * @package org.zenmagick.plugins.zm_tests
 * @author DerManoMann
 * @version $Id$
 */
class ZMHtmlReporter extends HtmlReporter {
    private $currentCase_;
    private $currentTest_;
    private $results_;
    private $enabled_;


    /**
     * Create new instance.
     */
    function __construct() {
        $this->HtmlReporter('ISO-8859-1');
        $this->currentCase_ = null;
        $this->currentTest_ = null;
        $this->results_ = array();
        $this->enabled_ = array();
    }

    /**
     * Get all results.
     *
     * @return array A map of all results.
     */
    public function getResults() {
        return $this->results_;
    }

    /**
     * Add a selected test for the given test case.
     *
     * @param string testCase The test case.
     * @param string test The test.
     */
    public function enableTest($testCase, $test) {
        if (!array_key_exists($testCase, $this->enabled_)) {
            $this->enabled_[$testCase] = array();
        }
        $this->enabled_[$testCase][$test] = true;
    }

    /**
     * {@inheritDoc}
     */
    public function paintHeader($name) {
    }

    /**
     * {@inheritDoc}
     */
    public function paintFooter($test_name) {
        // paint, but we only want the actual view contents
        ob_start();
        parent::paintFooter($test_name);
        $html = ob_get_clean();
        echo str_replace("</body>\n</html>", '', $html);
    }

    /**
     * {@inheritDoc}
     */
    public function shouldInvoke($testCase, $test) {
        return array_key_exists($testCase, $this->enabled_) && array_key_exists($test, $this->enabled_[$testCase]);
    }

    /**
     * {@inheritDoc}
     */
    public function paintCaseStart($testCase) {
        parent::paintCaseStart($testCase);
        $this->currentCase_ = $testCase;
        $this->results_[$testCase] = array();
        $this->results_[$testCase]['tests'] = array();
    }

    /**
     * {@inheritDoc}
     */
    public function paintCaseEnd($testCase) {
        parent::paintCaseEnd($testCase);
        $result = true;
        foreach ($this->results_[$testCase]['tests'] as $test => $details) {
            if (!$details['status']) {
                $result = false;
                break;
            }
        }
        $this->results_[$testCase]['status'] = $result;
        $this->currentCase_ = null;
    }

    /**
     * {@inheritDoc}
     */
    public function paintMethodStart($test) {
        parent::paintMethodStart($test);
        $this->currentTest_ = $test;
        $this->results_[$this->currentCase_]['tests'][$this->currentTest_] = array();
        $this->results_[$this->currentCase_]['tests'][$this->currentTest_]['status'] = true;
        $this->results_[$this->currentCase_]['tests'][$this->currentTest_]['messages'] = array();
    }

    /**
     * {@inheritDoc}
     */
    public function paintMethodEnd($test) {
        parent::paintMethodEnd($test);
        $this->currentTest_ = null;
    }

    /**
     * {@inheritDoc}
     */
    public function paintException($exception) {
        ob_start(); parent::paintException($exception); $html = ob_get_clean();
        echo $html;
        $this->results_[$this->currentCase_]['tests'][$this->currentTest_]['status'] = false;
        //TODO: improve: var_dump($exception);
    }

    /**
     * {@inheritDoc}
     */
    public function paintPass($message) {
        parent::paintPass($message);
    }

    /**
     * {@inheritDoc}
     */
    public function paintFail($message) {
        ob_start(); parent::paintFail($message); $html = ob_get_clean();
        //echo $html;
        $this->results_[$this->currentCase_]['tests'][$this->currentTest_]['status'] = false;
    }

    /**
     * {@inheritDoc}
     */
    public function zmPaintFail($info) {
        $cmp = $info['expectation']->overlayMessage($info['compare'], $this->getDumper());
        $msg = sprintf('line %s: %s; %s', $info['line'], $cmp, $info['message']);
        if (!array_key_exists($msg, $this->results_[$this->currentCase_]['tests'][$this->currentTest_]['messages'])) {
            $this->results_[$this->currentCase_]['tests'][$this->currentTest_]['messages'][$msg] = $msg;
        }
        if (1 == count($this->results_[$this->currentCase_]['tests'][$this->currentTest_]['messages'])) {
            echo '<div class="fail">'.$this->currentCase_.'::'.$this->currentTest_.':</div>';
        }
        echo '<div class="msg">'.$msg.'</div>';
    }

}

?>
