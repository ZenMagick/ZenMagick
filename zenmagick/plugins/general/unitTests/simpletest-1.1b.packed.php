<?php /* .tmp\flat\arguments.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\arguments.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id: dumper.php 1909 2009-07-29 15:58:11Z dgheath $
 */

/**
 *    Parses the command line arguments.
 *    @package  SimpleTest
 *    @subpackage   UnitTester
 */
class SimpleArguments {
    private $all = array();
    
    /**
     * Parses the command line arguments. The usual formats
     * are supported:
     * -f value
     * -f=value
     * --flag=value
     * --flag value
     * -f           (true)
     * --flag       (true)
     * @param array $arguments      Normally the PHP $argv.
     */
    function __construct($arguments) {
        array_shift($arguments);
        while (count($arguments) > 0) {
            list($key, $value) = $this->parseArgument($arguments);
            $this->assign($key, $value);
        }
    }
    
    /**
     * Sets the value in the argments object. If multiple
     * values are added under the same key, the key will
     * give an array value in the order they were added.
     * @param string $key    The variable to assign to.
     * @param string value   The value that would norally
     *                       be colected on the command line.
     */
    function assign($key, $value) {
        if ($this->$key === false) {
            $this->all[$key] = $value;
        } elseif (! is_array($this->$key)) {
            $this->all[$key] = array($this->$key, $value);
        } else {
            $this->all[$key][] = $value;
        }
    }
    
    /**
     * Extracts the next key and value from the argument list.
     * @param array $arguments      The remaining arguments to be parsed.
     *                              The argument list will be reduced.
     * @return array                Two item array of key and value.
     *                              If no value can be found it will
     *                              have the value true assigned instead.
     */
    private function parseArgument(&$arguments) {
        $argument = array_shift($arguments);
        if (preg_match('/^-(\w)=(.+)$/', $argument, $matches)) {
            return array($matches[1], $matches[2]);
        } elseif (preg_match('/^-(\w)$/', $argument, $matches)) {
            return array($matches[1], $this->nextNonFlagElseTrue($arguments));
        } elseif (preg_match('/^--(\w+)=(.+)$/', $argument, $matches)) {
            return array($matches[1], $matches[2]);
        } elseif (preg_match('/^--(\w+)$/', $argument, $matches)) {
            return array($matches[1], $this->nextNonFlagElseTrue($arguments));
        }
    }
    
    /**
     * Attempts to use the next argument as a value. It
     * won't use what it thinks is a flag.
     * @param array $arguments    Remaining arguments to be parsed.
     *                            This variable is modified if there
     *                            is a value to be extracted.
     * @return string/boolean     The next value unless it's a flag.
     */
    private function nextNonFlagElseTrue(&$arguments) {
        return $this->valueIsNext($arguments) ? array_shift($arguments) : true;
    }
    
    /**
     * Test to see if the next available argument is a valid value.
     * If it starts with "-" or "--" it's a flag and doesn't count.
     * @param array $arguments    Remaining arguments to be parsed.
     *                            Not affected by this call.
     * boolean                    True if valid value.
     */
    function valueIsNext($arguments) {
        return isset($arguments[0]) && ! $this->isFlag($arguments[0]);
    }
    
    /**
     * It's a flag if it starts with "-" or "--".
     * @param string $argument       Value to be tested.
     * @return boolean               True if it's a flag.
     */
    function isFlag($argument) {
        return strncmp($argument, '-', 1) == 0;
    }
    
    /**
     * The arguments are available as individual member
     * variables on the object.
     * @param string $key              Argument name.
     * @return string/array/boolean    Either false for no value,
     *                                 the value as a string or
     *                                 a list of multiple values if
     *                                 the flag had been specified more
     *                                 than once.
     */
    function __get($key) {
        if (isset($this->all[$key])) {
            return $this->all[$key];
        }
        return false;
    }
    
    /**
     * The entire argument set as a hash.
     * @return hash         Each argument and it's value(s).
     */
    function all() {
        return $this->all;
    }
}

/**
 *    Renders the help for the command line arguments.
 *    @package  SimpleTest
 *    @subpackage   UnitTester
 */
class SimpleHelp {
    private $overview;
    private $flag_sets = array();
    private $explanations = array();
    
    /**
     * Sets up the top level explanation for the program.
     * @param string $overview        Summary of program.
     */
    function __construct($overview = '') {
        $this->overview = $overview;
    }
    
    /**
     * Adds the explanation for a group of flags that all
     * have the same function.
     * @param string/array $flags       Flag and alternates. Don't
     *                                  worry about leading dashes
     *                                  as these are inserted automatically.
     * @param string $explanation       What that flag group does.
     */
    function explainFlag($flags, $explanation) {
        $flags = is_array($flags) ? $flags : array($flags);
        $this->flag_sets[] = $flags;
        $this->explanations[] = $explanation;
    }
    
    /**
     * Generates the help text.
     * @returns string      The complete formatted text.
     */
    function render() {
        $tab_stop = $this->longestFlag($this->flag_sets) + 4;
        $text = $this->overview . "\n";
        for ($i = 0; $i < count($this->flag_sets); $i++) {
            $text .= $this->renderFlagSet($this->flag_sets[$i], $this->explanations[$i], $tab_stop);
        }
        return $this->noDuplicateNewLines($text);
    }
    
    /**
     * Works out the longest flag for formatting purposes.
     * @param array $flag_sets      The internal flag set list.
     */
    private function longestFlag($flag_sets) {
        $longest = 0;
        foreach ($flag_sets as $flags) {
            foreach ($flags as $flag) {
                $longest = max($longest, strlen($this->renderFlag($flag)));
            }
        }
        return $longest;
    }
    
    /**
     * Generates the text for a single flag and it's alternate flags.
     * @returns string           Help text for that flag group.
     */
    private function renderFlagSet($flags, $explanation, $tab_stop) {
        $flag = array_shift($flags);
        $text = str_pad($this->renderFlag($flag), $tab_stop, ' ') . $explanation . "\n";
        foreach ($flags as $flag) {
            $text .= '  ' . $this->renderFlag($flag) . "\n";
        }
        return $text;
    }
    
    /**
     * Generates the flag name including leading dashes.
     * @param string $flag          Just the name.
     * @returns                     Fag with apropriate dashes.
     */
    private function renderFlag($flag) {
        return (strlen($flag) == 1 ? '-' : '--') . $flag;
    }
    
    /**
     * Converts multiple new lines into a single new line.
     * Just there to trap accidental duplicate new lines.
     * @param string $text      Text to clean up.
     * @returns string          Text with no blank lines.
     */
    private function noDuplicateNewLines($text) {
        return preg_replace('/(\n+)/', "\n", $text);
    }
}
 /* .tmp\flat\authentication.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\authentication.php */ ?>
<?php
/**
 *  Base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */
/**
 *  include http class
 */
//require_once(dirname(__FILE__) . '/http.php');

/**
 *    Represents a single security realm's identity.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleRealm {
    private $type;
    private $root;
    private $username;
    private $password;
    
    /**
     *    Starts with the initial entry directory.
     *    @param string $type      Authentication type for this
     *                             realm. Only Basic authentication
     *                             is currently supported.
     *    @param SimpleUrl $url    Somewhere in realm.
     *    @access public
     */
    function SimpleRealm($type, $url) {
        $this->type = $type;
        $this->root = $url->getBasePath();
        $this->username = false;
        $this->password = false;
    }
    
    /**
     *    Adds another location to the realm.
     *    @param SimpleUrl $url    Somewhere in realm.
     *    @access public
     */
    function stretch($url) {
        $this->root = $this->getCommonPath($this->root, $url->getPath());
    }
    
    /**
     *    Finds the common starting path.
     *    @param string $first        Path to compare.
     *    @param string $second       Path to compare.
     *    @return string              Common directories.
     *    @access private
     */
    protected function getCommonPath($first, $second) {
        $first = explode('/', $first);
        $second = explode('/', $second);
        for ($i = 0; $i < min(count($first), count($second)); $i++) {
            if ($first[$i] != $second[$i]) {
                return implode('/', array_slice($first, 0, $i)) . '/';
            }
        }
        return implode('/', $first) . '/';
    }
    
    /**
     *    Sets the identity to try within this realm.
     *    @param string $username    Username in authentication dialog.
     *    @param string $username    Password in authentication dialog.
     *    @access public
     */
    function setIdentity($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }
    
    /**
     *    Accessor for current identity.
     *    @return string        Last succesful username.
     *    @access public
     */
    function getUsername() {
        return $this->username;
    }
    
    /**
     *    Accessor for current identity.
     *    @return string        Last succesful password.
     *    @access public
     */
    function getPassword() {
        return $this->password;
    }
    
    /**
     *    Test to see if the URL is within the directory
     *    tree of the realm.
     *    @param SimpleUrl $url    URL to test.
     *    @return boolean          True if subpath.
     *    @access public
     */
    function isWithin($url) {
        if ($this->isIn($this->root, $url->getBasePath())) {
            return true;
        }
        if ($this->isIn($this->root, $url->getBasePath() . $url->getPage() . '/')) {
            return true;
        }
        return false;
    }
    
    /**
     *    Tests to see if one string is a substring of
     *    another.
     *    @param string $part        Small bit.
     *    @param string $whole       Big bit.
     *    @return boolean            True if the small bit is
     *                               in the big bit.
     *    @access private
     */
    protected function isIn($part, $whole) {
        return strpos($whole, $part) === 0;
    }
}

/**
 *    Manages security realms.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleAuthenticator {
    private $realms;
    
    /**
     *    Clears the realms.
     *    @access public
     */
    function SimpleAuthenticator() {
        $this->restartSession();
    }
    
    /**
     *    Starts with no realms set up.
     *    @access public
     */
    function restartSession() {
        $this->realms = array();
    }
    
    /**
     *    Adds a new realm centered the current URL.
     *    Browsers privatey wildly on their behaviour in this
     *    regard. Mozilla ignores the realm and presents
     *    only when challenged, wasting bandwidth. IE
     *    just carries on presenting until a new challenge
     *    occours. SimpleTest tries to follow the spirit of
     *    the original standards committee and treats the
     *    base URL as the root of a file tree shaped realm.
     *    @param SimpleUrl $url    Base of realm.
     *    @param string $type      Authentication type for this
     *                             realm. Only Basic authentication
     *                             is currently supported.
     *    @param string $realm     Name of realm.
     *    @access public
     */
    function addRealm($url, $type, $realm) {
        $this->realms[$url->getHost()][$realm] = new SimpleRealm($type, $url);
    }
    
    /**
     *    Sets the current identity to be presented
     *    against that realm.
     *    @param string $host        Server hosting realm.
     *    @param string $realm       Name of realm.
     *    @param string $username    Username for realm.
     *    @param string $password    Password for realm.
     *    @access public
     */
    function setIdentityForRealm($host, $realm, $username, $password) {
        if (isset($this->realms[$host][$realm])) {
            $this->realms[$host][$realm]->setIdentity($username, $password);
        }
    }
    
    /**
     *    Finds the name of the realm by comparing URLs.
     *    @param SimpleUrl $url        URL to test.
     *    @return SimpleRealm          Name of realm.
     *    @access private
     */
    protected function findRealmFromUrl($url) {
        if (! isset($this->realms[$url->getHost()])) {
            return false;
        }
        foreach ($this->realms[$url->getHost()] as $name => $realm) {
            if ($realm->isWithin($url)) {
                return $realm;
            }
        }
        return false;
    }
    
    /**
     *    Presents the appropriate headers for this location.
     *    @param SimpleHttpRequest $request  Request to modify.
     *    @param SimpleUrl $url              Base of realm.
     *    @access public
     */
    function addHeaders(&$request, $url) {
        if ($url->getUsername() && $url->getPassword()) {
            $username = $url->getUsername();
            $password = $url->getPassword();
        } elseif ($realm = $this->findRealmFromUrl($url)) {
            $username = $realm->getUsername();
            $password = $realm->getPassword();
        } else {
            return;
        }
        $this->addBasicHeaders($request, $username, $password);
    }
    
    /**
     *    Presents the appropriate headers for this
     *    location for basic authentication.
     *    @param SimpleHttpRequest $request  Request to modify.
     *    @param string $username            Username for realm.
     *    @param string $password            Password for realm.
     *    @access public
     */
    static function addBasicHeaders(&$request, $username, $password) {
        if ($username && $password) {
            $request->addHeaderLine(
                'Authorization: Basic ' . base64_encode("$username:$password"));
        }
    }
}
 /* .tmp\flat\browser.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\browser.php */ ?>
<?php
/**
 *  Base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/simpletest.php');
//require_once(dirname(__FILE__) . '/http.php');
//require_once(dirname(__FILE__) . '/encoding.php');
//require_once(dirname(__FILE__) . '/page.php');
//require_once(dirname(__FILE__) . '/php_parser.php');
//require_once(dirname(__FILE__) . '/tidy_parser.php');
//require_once(dirname(__FILE__) . '/selector.php');
//require_once(dirname(__FILE__) . '/frames.php');
//require_once(dirname(__FILE__) . '/user_agent.php');
if (! SimpleTest::getParsers()) {
    SimpleTest::setParsers(array(new SimpleTidyPageBuilder(), new SimplePHPPageBuilder()));
    //SimpleTest::setParsers(array(new SimplePHPPageBuilder()));
}
/**#@-*/

if (! defined('DEFAULT_MAX_NESTED_FRAMES')) {
    define('DEFAULT_MAX_NESTED_FRAMES', 3);
}

/**
 *    Browser history list.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleBrowserHistory {
    private $sequence = array();
    private $position = -1;

    /**
     *    Test for no entries yet.
     *    @return boolean        True if empty.
     *    @access private
     */
    protected function isEmpty() {
        return ($this->position == -1);
    }

    /**
     *    Test for being at the beginning.
     *    @return boolean        True if first.
     *    @access private
     */
    protected function atBeginning() {
        return ($this->position == 0) && ! $this->isEmpty();
    }

    /**
     *    Test for being at the last entry.
     *    @return boolean        True if last.
     *    @access private
     */
    protected function atEnd() {
        return ($this->position + 1 >= count($this->sequence)) && ! $this->isEmpty();
    }

    /**
     *    Adds a successfully fetched page to the history.
     *    @param SimpleUrl $url                 URL of fetch.
     *    @param SimpleEncoding $parameters     Any post data with the fetch.
     *    @access public
     */
    function recordEntry($url, $parameters) {
        $this->dropFuture();
        array_push(
                $this->sequence,
                array('url' => $url, 'parameters' => $parameters));
        $this->position++;
    }

    /**
     *    Last fully qualified URL for current history
     *    position.
     *    @return SimpleUrl        URL for this position.
     *    @access public
     */
    function getUrl() {
        if ($this->isEmpty()) {
            return false;
        }
        return $this->sequence[$this->position]['url'];
    }

    /**
     *    Parameters of last fetch from current history
     *    position.
     *    @return SimpleFormEncoding    Post parameters.
     *    @access public
     */
    function getParameters() {
        if ($this->isEmpty()) {
            return false;
        }
        return $this->sequence[$this->position]['parameters'];
    }

    /**
     *    Step back one place in the history. Stops at
     *    the first page.
     *    @return boolean     True if any previous entries.
     *    @access public
     */
    function back() {
        if ($this->isEmpty() || $this->atBeginning()) {
            return false;
        }
        $this->position--;
        return true;
    }

    /**
     *    Step forward one place. If already at the
     *    latest entry then nothing will happen.
     *    @return boolean     True if any future entries.
     *    @access public
     */
    function forward() {
        if ($this->isEmpty() || $this->atEnd()) {
            return false;
        }
        $this->position++;
        return true;
    }

    /**
     *    Ditches all future entries beyond the current
     *    point.
     *    @access private
     */
    protected function dropFuture() {
        if ($this->isEmpty()) {
            return;
        }
        while (! $this->atEnd()) {
            array_pop($this->sequence);
        }
    }
}

/**
 *    Simulated web browser. This is an aggregate of
 *    the user agent, the HTML parsing, request history
 *    and the last header set.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleBrowser {
    private $user_agent;
    private $page;
    private $history;
    private $ignore_frames;
    private $maximum_nested_frames;
    private $parser;

    /**
     *    Starts with a fresh browser with no
     *    cookie or any other state information. The
     *    exception is that a default proxy will be
     *    set up if specified in the options.
     *    @access public
     */
    function __construct() {
        $this->user_agent = $this->createUserAgent();
        $this->user_agent->useProxy(
                SimpleTest::getDefaultProxy(),
                SimpleTest::getDefaultProxyUsername(),
                SimpleTest::getDefaultProxyPassword());
        $this->page = new SimplePage();
        $this->history = $this->createHistory();
        $this->ignore_frames = false;
        $this->maximum_nested_frames = DEFAULT_MAX_NESTED_FRAMES;
    }

    /**
     *    Creates the underlying user agent.
     *    @return SimpleFetcher    Content fetcher.
     *    @access protected
     */
    protected function createUserAgent() {
        return new SimpleUserAgent();
    }

    /**
     *    Creates a new empty history list.
     *    @return SimpleBrowserHistory    New list.
     *    @access protected
     */
    protected function createHistory() {
        return new SimpleBrowserHistory();
    }

    /**
     *    Get the HTML parser to use. Can be overridden by
     *    setParser. Otherwise scans through the available parsers and
     *    uses the first one which is available.
     *    @return object SimplePHPPageBuilder or SimpleTidyPageBuilder
     */
    protected function getParser() {
        if ($this->parser) {
            return $this->parser;
        }
        foreach (SimpleTest::getParsers() as $parser) {
            if ($parser->can()) {
                return $parser;
            }
        }
    }

    /**
     *    Override the default HTML parser, allowing parsers to be plugged in.
     *    @param object           A parser object instance.
     */
    public function setParser($parser) {
        $this->parser = $parser;
    }

    /**
     *    Disables frames support. Frames will not be fetched
     *    and the frameset page will be used instead.
     *    @access public
     */
    function ignoreFrames() {
        $this->ignore_frames = true;
    }

    /**
     *    Enables frames support. Frames will be fetched from
     *    now on.
     *    @access public
     */
    function useFrames() {
        $this->ignore_frames = false;
    }

    /**
     *    Switches off cookie sending and recieving.
     *    @access public
     */
    function ignoreCookies() {
        $this->user_agent->ignoreCookies();
    }

    /**
     *    Switches back on the cookie sending and recieving.
     *    @access public
     */
    function useCookies() {
        $this->user_agent->useCookies();
    }

    /**
     *    Parses the raw content into a page. Will load further
     *    frame pages unless frames are disabled.
     *    @param SimpleHttpResponse $response    Response from fetch.
     *    @param integer $depth                  Nested frameset depth.
     *    @return SimplePage                     Parsed HTML.
     *    @access private
     */
    protected function parse($response, $depth = 0) {
        $page = $this->buildPage($response);
        if ($this->ignore_frames || ! $page->hasFrames() || ($depth > $this->maximum_nested_frames)) {
            return $page;
        }
        $frameset = new SimpleFrameset($page);
        foreach ($page->getFrameset() as $key => $url) {
            $frame = $this->fetch($url, new SimpleGetEncoding(), $depth + 1);
            $frameset->addFrame($frame, $key);
        }
        return $frameset;
    }

    /**
     *    Assembles the parsing machinery and actually parses
     *    a single page. Frees all of the builder memory and so
     *    unjams the PHP memory management.
     *    @param SimpleHttpResponse $response    Response from fetch.
     *    @return SimplePage                     Parsed top level page.
     */
    protected function buildPage($response) {
        return $this->getParser()->parse($response);
    }

    /**
     *    Fetches a page. Jointly recursive with the parse()
     *    method as it descends a frameset.
     *    @param string/SimpleUrl $url          Target to fetch.
     *    @param SimpleEncoding $encoding       GET/POST parameters.
     *    @param integer $depth                 Nested frameset depth protection.
     *    @return SimplePage                    Parsed page.
     *    @access private
     */
    protected function fetch($url, $encoding, $depth = 0) {
        $response = $this->user_agent->fetchResponse($url, $encoding);
        if ($response->isError()) {
            return new SimplePage($response);
        }
        return $this->parse($response, $depth);
    }

    /**
     *    Fetches a page or a single frame if that is the current
     *    focus.
     *    @param SimpleUrl $url                   Target to fetch.
     *    @param SimpleEncoding $parameters       GET/POST parameters.
     *    @return string                          Raw content of page.
     *    @access private
     */
    protected function load($url, $parameters) {
        $frame = $url->getTarget();
        if (! $frame || ! $this->page->hasFrames() || (strtolower($frame) == '_top')) {
            return $this->loadPage($url, $parameters);
        }
        return $this->loadFrame(array($frame), $url, $parameters);
    }

    /**
     *    Fetches a page and makes it the current page/frame.
     *    @param string/SimpleUrl $url            Target to fetch as string.
     *    @param SimplePostEncoding $parameters   POST parameters.
     *    @return string                          Raw content of page.
     *    @access private
     */
    protected function loadPage($url, $parameters) {
        $this->page = $this->fetch($url, $parameters);
        $this->history->recordEntry(
                $this->page->getUrl(),
                $this->page->getRequestData());
        return $this->page->getRaw();
    }

    /**
     *    Fetches a frame into the existing frameset replacing the
     *    original.
     *    @param array $frames                    List of names to drill down.
     *    @param string/SimpleUrl $url            Target to fetch as string.
     *    @param SimpleFormEncoding $parameters   POST parameters.
     *    @return string                          Raw content of page.
     *    @access private
     */
    protected function loadFrame($frames, $url, $parameters) {
        $page = $this->fetch($url, $parameters);
        $this->page->setFrame($frames, $page);
        return $page->getRaw();
    }

    /**
     *    Removes expired and temporary cookies as if
     *    the browser was closed and re-opened.
     *    @param string/integer $date   Time when session restarted.
     *                                  If omitted then all persistent
     *                                  cookies are kept.
     *    @access public
     */
    function restart($date = false) {
        $this->user_agent->restart($date);
    }

    /**
     *    Adds a header to every fetch.
     *    @param string $header       Header line to add to every
     *                                request until cleared.
     *    @access public
     */
    function addHeader($header) {
        $this->user_agent->addHeader($header);
    }

    /**
     *    Ages the cookies by the specified time.
     *    @param integer $interval    Amount in seconds.
     *    @access public
     */
    function ageCookies($interval) {
        $this->user_agent->ageCookies($interval);
    }

    /**
     *    Sets an additional cookie. If a cookie has
     *    the same name and path it is replaced.
     *    @param string $name       Cookie key.
     *    @param string $value      Value of cookie.
     *    @param string $host       Host upon which the cookie is valid.
     *    @param string $path       Cookie path if not host wide.
     *    @param string $expiry     Expiry date.
     *    @access public
     */
    function setCookie($name, $value, $host = false, $path = '/', $expiry = false) {
        $this->user_agent->setCookie($name, $value, $host, $path, $expiry);
    }

    /**
     *    Reads the most specific cookie value from the
     *    browser cookies.
     *    @param string $host        Host to search.
     *    @param string $path        Applicable path.
     *    @param string $name        Name of cookie to read.
     *    @return string             False if not present, else the
     *                               value as a string.
     *    @access public
     */
    function getCookieValue($host, $path, $name) {
        return $this->user_agent->getCookieValue($host, $path, $name);
    }

    /**
     *    Reads the current cookies for the current URL.
     *    @param string $name   Key of cookie to find.
     *    @return string        Null if there is no current URL, false
     *                          if the cookie is not set.
     *    @access public
     */
    function getCurrentCookieValue($name) {
        return $this->user_agent->getBaseCookieValue($name, $this->page->getUrl());
    }

    /**
     *    Sets the maximum number of redirects before
     *    a page will be loaded anyway.
     *    @param integer $max        Most hops allowed.
     *    @access public
     */
    function setMaximumRedirects($max) {
        $this->user_agent->setMaximumRedirects($max);
    }

    /**
     *    Sets the maximum number of nesting of framed pages
     *    within a framed page to prevent loops.
     *    @param integer $max        Highest depth allowed.
     *    @access public
     */
    function setMaximumNestedFrames($max) {
        $this->maximum_nested_frames = $max;
    }

    /**
     *    Sets the socket timeout for opening a connection.
     *    @param integer $timeout      Maximum time in seconds.
     *    @access public
     */
    function setConnectionTimeout($timeout) {
        $this->user_agent->setConnectionTimeout($timeout);
    }

    /**
     *    Sets proxy to use on all requests for when
     *    testing from behind a firewall. Set URL
     *    to false to disable.
     *    @param string $proxy        Proxy URL.
     *    @param string $username     Proxy username for authentication.
     *    @param string $password     Proxy password for authentication.
     *    @access public
     */
    function useProxy($proxy, $username = false, $password = false) {
        $this->user_agent->useProxy($proxy, $username, $password);
    }

    /**
     *    Fetches the page content with a HEAD request.
     *    Will affect cookies, but will not change the base URL.
     *    @param string/SimpleUrl $url                Target to fetch as string.
     *    @param hash/SimpleHeadEncoding $parameters  Additional parameters for
     *                                                HEAD request.
     *    @return boolean                             True if successful.
     *    @access public
     */
    function head($url, $parameters = false) {
        if (! is_object($url)) {
            $url = new SimpleUrl($url);
        }
        if ($this->getUrl()) {
            $url = $url->makeAbsolute($this->getUrl());
        }
        $response = $this->user_agent->fetchResponse($url, new SimpleHeadEncoding($parameters));
        $this->page = new SimplePage($response);
        return ! $response->isError();
    }

    /**
     *    Fetches the page content with a simple GET request.
     *    @param string/SimpleUrl $url                Target to fetch.
     *    @param hash/SimpleFormEncoding $parameters  Additional parameters for
     *                                                GET request.
     *    @return string                              Content of page or false.
     *    @access public
     */
    function get($url, $parameters = false) {
        if (! is_object($url)) {
            $url = new SimpleUrl($url);
        }
        if ($this->getUrl()) {
            $url = $url->makeAbsolute($this->getUrl());
        }
        return $this->load($url, new SimpleGetEncoding($parameters));
    }

    /**
     *    Fetches the page content with a POST request.
     *    @param string/SimpleUrl $url                Target to fetch as string.
     *    @param hash/SimpleFormEncoding $parameters  POST parameters or request body.
     *    @param string $content_type                 MIME Content-Type of the request body
     *    @return string                              Content of page.
     *    @access public
     */
    function post($url, $parameters = false, $content_type = false) {
        if (! is_object($url)) {
            $url = new SimpleUrl($url);
        }
        if ($this->getUrl()) {
            $url = $url->makeAbsolute($this->getUrl());
        }
        return $this->load($url, new SimplePostEncoding($parameters, $content_type));
    }

    /**
     *    Fetches the page content with a PUT request.
     *    @param string/SimpleUrl $url                Target to fetch as string.
     *    @param hash/SimpleFormEncoding $parameters  PUT request body.
     *    @param string $content_type                 MIME Content-Type of the request body
     *    @return string                              Content of page.
     *    @access public
     */
    function put($url, $parameters = false, $content_type = false) {
        if (! is_object($url)) {
            $url = new SimpleUrl($url);
        }
        return $this->load($url, new SimplePutEncoding($parameters, $content_type));
    }

    /**
     *    Sends a DELETE request and fetches the response.
     *    @param string/SimpleUrl $url                Target to fetch.
     *    @param hash/SimpleFormEncoding $parameters  Additional parameters for
     *                                                DELETE request.
     *    @return string                              Content of page or false.
     *    @access public
     */
    function delete($url, $parameters = false) {
        if (! is_object($url)) {
            $url = new SimpleUrl($url);
        }
        return $this->load($url, new SimpleDeleteEncoding($parameters));
    }

    /**
     *    Equivalent to hitting the retry button on the
     *    browser. Will attempt to repeat the page fetch. If
     *    there is no history to repeat it will give false.
     *    @return string/boolean   Content if fetch succeeded
     *                             else false.
     *    @access public
     */
    function retry() {
        $frames = $this->page->getFrameFocus();
        if (count($frames) > 0) {
            $this->loadFrame(
                    $frames,
                    $this->page->getUrl(),
                    $this->page->getRequestData());
            return $this->page->getRaw();
        }
        if ($url = $this->history->getUrl()) {
            $this->page = $this->fetch($url, $this->history->getParameters());
            return $this->page->getRaw();
        }
        return false;
    }

    /**
     *    Equivalent to hitting the back button on the
     *    browser. The browser history is unchanged on
     *    failure. The page content is refetched as there
     *    is no concept of content caching in SimpleTest.
     *    @return boolean     True if history entry and
     *                        fetch succeeded
     *    @access public
     */
    function back() {
        if (! $this->history->back()) {
            return false;
        }
        $content = $this->retry();
        if (! $content) {
            $this->history->forward();
        }
        return $content;
    }

    /**
     *    Equivalent to hitting the forward button on the
     *    browser. The browser history is unchanged on
     *    failure. The page content is refetched as there
     *    is no concept of content caching in SimpleTest.
     *    @return boolean     True if history entry and
     *                        fetch succeeded
     *    @access public
     */
    function forward() {
        if (! $this->history->forward()) {
            return false;
        }
        $content = $this->retry();
        if (! $content) {
            $this->history->back();
        }
        return $content;
    }

    /**
     *    Retries a request after setting the authentication
     *    for the current realm.
     *    @param string $username    Username for realm.
     *    @param string $password    Password for realm.
     *    @return boolean            True if successful fetch. Note
     *                               that authentication may still have
     *                               failed.
     *    @access public
     */
    function authenticate($username, $password) {
        if (! $this->page->getRealm()) {
            return false;
        }
        $url = $this->page->getUrl();
        if (! $url) {
            return false;
        }
        $this->user_agent->setIdentity(
                $url->getHost(),
                $this->page->getRealm(),
                $username,
                $password);
        return $this->retry();
    }

    /**
     *    Accessor for a breakdown of the frameset.
     *    @return array   Hash tree of frames by name
     *                    or index if no name.
     *    @access public
     */
    function getFrames() {
        return $this->page->getFrames();
    }

    /**
     *    Accessor for current frame focus. Will be
     *    false if no frame has focus.
     *    @return integer/string/boolean    Label if any, otherwise
     *                                      the position in the frameset
     *                                      or false if none.
     *    @access public
     */
    function getFrameFocus() {
        return $this->page->getFrameFocus();
    }

    /**
     *    Sets the focus by index. The integer index starts from 1.
     *    @param integer $choice    Chosen frame.
     *    @return boolean           True if frame exists.
     *    @access public
     */
    function setFrameFocusByIndex($choice) {
        return $this->page->setFrameFocusByIndex($choice);
    }

    /**
     *    Sets the focus by name.
     *    @param string $name    Chosen frame.
     *    @return boolean        True if frame exists.
     *    @access public
     */
    function setFrameFocus($name) {
        return $this->page->setFrameFocus($name);
    }

    /**
     *    Clears the frame focus. All frames will be searched
     *    for content.
     *    @access public
     */
    function clearFrameFocus() {
        return $this->page->clearFrameFocus();
    }

    /**
     *    Accessor for last error.
     *    @return string        Error from last response.
     *    @access public
     */
    function getTransportError() {
        return $this->page->getTransportError();
    }

    /**
     *    Accessor for current MIME type.
     *    @return string    MIME type as string; e.g. 'text/html'
     *    @access public
     */
    function getMimeType() {
        return $this->page->getMimeType();
    }

    /**
     *    Accessor for last response code.
     *    @return integer    Last HTTP response code received.
     *    @access public
     */
    function getResponseCode() {
        return $this->page->getResponseCode();
    }

    /**
     *    Accessor for last Authentication type. Only valid
     *    straight after a challenge (401).
     *    @return string    Description of challenge type.
     *    @access public
     */
    function getAuthentication() {
        return $this->page->getAuthentication();
    }

    /**
     *    Accessor for last Authentication realm. Only valid
     *    straight after a challenge (401).
     *    @return string    Name of security realm.
     *    @access public
     */
    function getRealm() {
        return $this->page->getRealm();
    }

    /**
     *    Accessor for current URL of page or frame if
     *    focused.
     *    @return string    Location of current page or frame as
     *                      a string.
     */
    function getUrl() {
        $url = $this->page->getUrl();
        return $url ? $url->asString() : false;
    }

    /**
     *    Accessor for base URL of page if set via BASE tag
     *    @return string    base URL
     */
    function getBaseUrl() {
        $url = $this->page->getBaseUrl();
        return $url ? $url->asString() : false;
    }

    /**
     *    Accessor for raw bytes sent down the wire.
     *    @return string      Original text sent.
     *    @access public
     */
    function getRequest() {
        return $this->page->getRequest();
    }

    /**
     *    Accessor for raw header information.
     *    @return string      Header block.
     *    @access public
     */
    function getHeaders() {
        return $this->page->getHeaders();
    }

    /**
     *    Accessor for raw page information.
     *    @return string      Original text content of web page.
     *    @access public
     */
    function getContent() {
        return $this->page->getRaw();
    }

    /**
     *    Accessor for plain text version of the page.
     *    @return string      Normalised text representation.
     *    @access public
     */
    function getContentAsText() {
        return $this->page->getText();
    }

    /**
     *    Accessor for parsed title.
     *    @return string     Title or false if no title is present.
     *    @access public
     */
    function getTitle() {
        return $this->page->getTitle();
    }

    /**
     *    Accessor for a list of all links in current page.
     *    @return array   List of urls with scheme of
     *                    http or https and hostname.
     *    @access public
     */
    function getUrls() {
        return $this->page->getUrls();
    }

    /**
     *    Sets all form fields with that name.
     *    @param string $label   Name or label of field in forms.
     *    @param string $value   New value of field.
     *    @return boolean        True if field exists, otherwise false.
     *    @access public
     */
    function setField($label, $value, $position=false) {
        return $this->page->setField(new SimpleByLabelOrName($label), $value, $position);
    }

    /**
     *    Sets all form fields with that name. Will use label if
     *    one is available (not yet implemented).
     *    @param string $name    Name of field in forms.
     *    @param string $value   New value of field.
     *    @return boolean        True if field exists, otherwise false.
     *    @access public
     */
    function setFieldByName($name, $value, $position=false) {
        return $this->page->setField(new SimpleByName($name), $value, $position);
    }

    /**
     *    Sets all form fields with that id attribute.
     *    @param string/integer $id   Id of field in forms.
     *    @param string $value        New value of field.
     *    @return boolean             True if field exists, otherwise false.
     *    @access public
     */
    function setFieldById($id, $value) {
        return $this->page->setField(new SimpleById($id), $value);
    }

    /**
     *    Accessor for a form element value within the page.
     *    Finds the first match.
     *    @param string $label       Field label.
     *    @return string/boolean     A value if the field is
     *                               present, false if unchecked
     *                               and null if missing.
     *    @access public
     */
    function getField($label) {
        return $this->page->getField(new SimpleByLabelOrName($label));
    }

    /**
     *    Accessor for a form element value within the page.
     *    Finds the first match.
     *    @param string $name        Field name.
     *    @return string/boolean     A string if the field is
     *                               present, false if unchecked
     *                               and null if missing.
     *    @access public
     */
    function getFieldByName($name) {
        return $this->page->getField(new SimpleByName($name));
    }

    /**
     *    Accessor for a form element value within the page.
     *    @param string/integer $id  Id of field in forms.
     *    @return string/boolean     A string if the field is
     *                               present, false if unchecked
     *                               and null if missing.
     *    @access public
     */
    function getFieldById($id) {
        return $this->page->getField(new SimpleById($id));
    }

    /**
     *    Clicks the submit button by label. The owning
     *    form will be submitted by this.
     *    @param string $label    Button label. An unlabeled
     *                            button can be triggered by 'Submit'.
     *    @param hash $additional Additional form data.
     *    @return string/boolean  Page on success.
     *    @access public
     */
    function clickSubmit($label = 'Submit', $additional = false) {
        if (! ($form = $this->page->getFormBySubmit(new SimpleByLabel($label)))) {
            return false;
        }
        $success = $this->load(
                $form->getAction(),
                $form->submitButton(new SimpleByLabel($label), $additional));
        return ($success ? $this->getContent() : $success);
    }

    /**
     *    Clicks the submit button by name attribute. The owning
     *    form will be submitted by this.
     *    @param string $name     Button name.
     *    @param hash $additional Additional form data.
     *    @return string/boolean  Page on success.
     *    @access public
     */
    function clickSubmitByName($name, $additional = false) {
        if (! ($form = $this->page->getFormBySubmit(new SimpleByName($name)))) {
            return false;
        }
        $success = $this->load(
                $form->getAction(),
                $form->submitButton(new SimpleByName($name), $additional));
        return ($success ? $this->getContent() : $success);
    }

    /**
     *    Clicks the submit button by ID attribute of the button
     *    itself. The owning form will be submitted by this.
     *    @param string $id       Button ID.
     *    @param hash $additional Additional form data.
     *    @return string/boolean  Page on success.
     *    @access public
     */
    function clickSubmitById($id, $additional = false) {
        if (! ($form = $this->page->getFormBySubmit(new SimpleById($id)))) {
            return false;
        }
        $success = $this->load(
                $form->getAction(),
                $form->submitButton(new SimpleById($id), $additional));
        return ($success ? $this->getContent() : $success);
    }

    /**
     *    Tests to see if a submit button exists with this
     *    label.
     *    @param string $label    Button label.
     *    @return boolean         True if present.
     *    @access public
     */
    function isSubmit($label) {
        return (boolean)$this->page->getFormBySubmit(new SimpleByLabel($label));
    }

    /**
     *    Clicks the submit image by some kind of label. Usually
     *    the alt tag or the nearest equivalent. The owning
     *    form will be submitted by this. Clicking outside of
     *    the boundary of the coordinates will result in
     *    a failure.
     *    @param string $label    ID attribute of button.
     *    @param integer $x       X-coordinate of imaginary click.
     *    @param integer $y       Y-coordinate of imaginary click.
     *    @param hash $additional Additional form data.
     *    @return string/boolean  Page on success.
     *    @access public
     */
    function clickImage($label, $x = 1, $y = 1, $additional = false) {
        if (! ($form = $this->page->getFormByImage(new SimpleByLabel($label)))) {
            return false;
        }
        $success = $this->load(
                $form->getAction(),
                $form->submitImage(new SimpleByLabel($label), $x, $y, $additional));
        return ($success ? $this->getContent() : $success);
    }

    /**
     *    Clicks the submit image by the name. Usually
     *    the alt tag or the nearest equivalent. The owning
     *    form will be submitted by this. Clicking outside of
     *    the boundary of the coordinates will result in
     *    a failure.
     *    @param string $name     Name attribute of button.
     *    @param integer $x       X-coordinate of imaginary click.
     *    @param integer $y       Y-coordinate of imaginary click.
     *    @param hash $additional Additional form data.
     *    @return string/boolean  Page on success.
     *    @access public
     */
    function clickImageByName($name, $x = 1, $y = 1, $additional = false) {
        if (! ($form = $this->page->getFormByImage(new SimpleByName($name)))) {
            return false;
        }
        $success = $this->load(
                $form->getAction(),
                $form->submitImage(new SimpleByName($name), $x, $y, $additional));
        return ($success ? $this->getContent() : $success);
    }

    /**
     *    Clicks the submit image by ID attribute. The owning
     *    form will be submitted by this. Clicking outside of
     *    the boundary of the coordinates will result in
     *    a failure.
     *    @param integer/string $id    ID attribute of button.
     *    @param integer $x            X-coordinate of imaginary click.
     *    @param integer $y            Y-coordinate of imaginary click.
     *    @param hash $additional      Additional form data.
     *    @return string/boolean       Page on success.
     *    @access public
     */
    function clickImageById($id, $x = 1, $y = 1, $additional = false) {
        if (! ($form = $this->page->getFormByImage(new SimpleById($id)))) {
            return false;
        }
        $success = $this->load(
                $form->getAction(),
                $form->submitImage(new SimpleById($id), $x, $y, $additional));
        return ($success ? $this->getContent() : $success);
    }

    /**
     *    Tests to see if an image exists with this
     *    title or alt text.
     *    @param string $label    Image text.
     *    @return boolean         True if present.
     *    @access public
     */
    function isImage($label) {
        return (boolean)$this->page->getFormByImage(new SimpleByLabel($label));
    }

    /**
     *    Submits a form by the ID.
     *    @param string $id       The form ID. No submit button value
     *                            will be sent.
     *    @return string/boolean  Page on success.
     *    @access public
     */
    function submitFormById($id) {
        if (! ($form = $this->page->getFormById($id))) {
            return false;
        }
        $success = $this->load(
                $form->getAction(),
                $form->submit());
        return ($success ? $this->getContent() : $success);
    }

    /**
     *    Finds a URL by label. Will find the first link
     *    found with this link text by default, or a later
     *    one if an index is given. The match ignores case and
     *    white space issues.
     *    @param string $label     Text between the anchor tags.
     *    @param integer $index    Link position counting from zero.
     *    @return string/boolean   URL on success.
     *    @access public
     */
    function getLink($label, $index = 0) {
        $urls = $this->page->getUrlsByLabel($label);
        if (count($urls) == 0) {
            return false;
        }
        if (count($urls) < $index + 1) {
            return false;
        }
        return $urls[$index];
    }

    /**
     *    Follows a link by label. Will click the first link
     *    found with this link text by default, or a later
     *    one if an index is given. The match ignores case and
     *    white space issues.
     *    @param string $label     Text between the anchor tags.
     *    @param integer $index    Link position counting from zero.
     *    @return string/boolean   Page on success.
     *    @access public
     */
    function clickLink($label, $index = 0) {
        $url = $this->getLink($label, $index);
        if ($url === false) {
            return false;
        }
        $this->load($url, new SimpleGetEncoding());
        return $this->getContent();
    }

    /**
     *    Finds a link by id attribute.
     *    @param string $id        ID attribute value.
     *    @return string/boolean   URL on success.
     *    @access public
     */
    function getLinkById($id) {
        return $this->page->getUrlById($id);
    }

    /**
     *    Follows a link by id attribute.
     *    @param string $id        ID attribute value.
     *    @return string/boolean   Page on success.
     *    @access public
     */
    function clickLinkById($id) {
        if (! ($url = $this->getLinkById($id))) {
            return false;
        }
        $this->load($url, new SimpleGetEncoding());
        return $this->getContent();
    }

    /**
     *    Clicks a visible text item. Will first try buttons,
     *    then links and then images.
     *    @param string $label        Visible text or alt text.
     *    @return string/boolean      Raw page or false.
     *    @access public
     */
    function click($label) {
        $raw = $this->clickSubmit($label);
        if (! $raw) {
            $raw = $this->clickLink($label);
        }
        if (! $raw) {
            $raw = $this->clickImage($label);
        }
        return $raw;
    }

    /**
     *    Tests to see if a click target exists.
     *    @param string $label    Visible text or alt text.
     *    @return boolean         True if target present.
     *    @access public
     */
    function isClickable($label) {
        return $this->isSubmit($label) || ($this->getLink($label) !== false) || $this->isImage($label);
    }
}
 /* .tmp\flat\collector.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\collector.php */ ?>
<?php
/**
 * This file contains the following classes: {@link SimpleCollector},
 * {@link SimplePatternCollector}.
 *
 * @author Travis Swicegood <development@domain51.com>
 * @package SimpleTest
 * @subpackage UnitTester
 * @version $Id$
 */

/**
 * The basic collector for {@link GroupTest}
 *
 * @see collect(), GroupTest::collect()
 * @package SimpleTest
 * @subpackage UnitTester
 */
class SimpleCollector {

    /**
     * Strips off any kind of slash at the end so as to normalise the path.
     * @param string $path    Path to normalise.
     * @return string         Path without trailing slash.
     */
    protected function removeTrailingSlash($path) {
        if (substr($path, -1) == DIRECTORY_SEPARATOR) {
            return substr($path, 0, -1);
        } elseif (substr($path, -1) == '/') {
            return substr($path, 0, -1);
        } else {
            return $path;
        }
    }

    /**
     * Scans the directory and adds what it can.
     * @param object $test    Group test with {@link GroupTest::addTestFile()} method.
     * @param string $path    Directory to scan.
     * @see _attemptToAdd()
     */
    function collect(&$test, $path) {
        $path = $this->removeTrailingSlash($path);
        if ($handle = opendir($path)) {
            while (($entry = readdir($handle)) !== false) {
                if ($this->isHidden($entry)) {
                    continue;
                }
                $this->handle($test, $path . DIRECTORY_SEPARATOR . $entry);
            }
            closedir($handle);
        }
    }

    /**
     * This method determines what should be done with a given file and adds
     * it via {@link GroupTest::addTestFile()} if necessary.
     *
     * This method should be overriden to provide custom matching criteria,
     * such as pattern matching, recursive matching, etc.  For an example, see
     * {@link SimplePatternCollector::_handle()}.
     *
     * @param object $test      Group test with {@link GroupTest::addTestFile()} method.
     * @param string $filename  A filename as generated by {@link collect()}
     * @see collect()
     * @access protected
     */
    protected function handle(&$test, $file) {
        if (is_dir($file)) {
            return;
        }
        $test->addFile($file);
    }
    
    /**
     *  Tests for hidden files so as to skip them. Currently
     *  only tests for Unix hidden files.
     *  @param string $filename        Plain filename.
     *  @return boolean                True if hidden file.
     *  @access private
     */
    protected function isHidden($filename) {
        return strncmp($filename, '.', 1) == 0;
    }
}

/**
 * An extension to {@link SimpleCollector} that only adds files matching a
 * given pattern.
 *
 * @package SimpleTest
 * @subpackage UnitTester
 * @see SimpleCollector
 */
class SimplePatternCollector extends SimpleCollector {
    private $pattern;

    /**
     *
     * @param string $pattern   Perl compatible regex to test name against
     *  See {@link http://us4.php.net/manual/en/reference.pcre.pattern.syntax.php PHP's PCRE}
     *  for full documentation of valid pattern.s
     */
    function __construct($pattern = '/php$/i') {
        $this->pattern = $pattern;
    }

    /**
     * Attempts to add files that match a given pattern.
     *
     * @see SimpleCollector::_handle()
     * @param object $test    Group test with {@link GroupTest::addTestFile()} method.
     * @param string $path    Directory to scan.
     * @access protected
     */
    protected function handle(&$test, $filename) {
        if (preg_match($this->pattern, $filename)) {
            parent::handle($test, $filename);
        }
    }
}
 /* .tmp\flat\compatibility.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\compatibility.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @version    $Id$
 */

/**
 *  Static methods for compatibility between different
 *  PHP versions.
 *  @package    SimpleTest
 */
class SimpleTestCompatibility {

    /**
     *    Creates a copy whether in PHP5 or PHP4.
     *    @param object $object     Thing to copy.
     *    @return object            A copy.
     *    @access public
     */
    static function copy($object) {
        if (version_compare(phpversion(), '5') >= 0) {
            eval('$copy = clone $object;');
            return $copy;
        }
        return $object;
    }

    /**
     *    Identity test. Drops back to equality + types for PHP5
     *    objects as the === operator counts as the
     *    stronger reference constraint.
     *    @param mixed $first    Test subject.
     *    @param mixed $second   Comparison object.
     *    @return boolean        True if identical.
     *    @access public
     */
    static function isIdentical($first, $second) {
        if (version_compare(phpversion(), '5') >= 0) {
            return SimpleTestCompatibility::isIdenticalType($first, $second);
        }
        if ($first != $second) {
            return false;
        }
        return ($first === $second);
    }

    /**
     *    Recursive type test.
     *    @param mixed $first    Test subject.
     *    @param mixed $second   Comparison object.
     *    @return boolean        True if same type.
     *    @access private
     */
    protected static function isIdenticalType($first, $second) {
        if (gettype($first) != gettype($second)) {
            return false;
        }
        if (is_object($first) && is_object($second)) {
            if (get_class($first) != get_class($second)) {
                return false;
            }
            return SimpleTestCompatibility::isArrayOfIdenticalTypes(
                    (array) $first,
                    (array) $second);
        }
        if (is_array($first) && is_array($second)) {
            return SimpleTestCompatibility::isArrayOfIdenticalTypes($first, $second);
        }
        if ($first !== $second) {
            return false;
        }
        return true;
    }

    /**
     *    Recursive type test for each element of an array.
     *    @param mixed $first    Test subject.
     *    @param mixed $second   Comparison object.
     *    @return boolean        True if identical.
     *    @access private
     */
    protected static function isArrayOfIdenticalTypes($first, $second) {
        if (array_keys($first) != array_keys($second)) {
            return false;
        }
        foreach (array_keys($first) as $key) {
            $is_identical = SimpleTestCompatibility::isIdenticalType(
                    $first[$key],
                    $second[$key]);
            if (! $is_identical) {
                return false;
            }
        }
        return true;
    }

    /**
     *    Test for two variables being aliases.
     *    @param mixed $first    Test subject.
     *    @param mixed $second   Comparison object.
     *    @return boolean        True if same.
     *    @access public
     */
    static function isReference(&$first, &$second) {
        if (version_compare(phpversion(), '5', '>=') && is_object($first)) {
            return ($first === $second);
        }
        if (is_object($first) && is_object($second)) {
            $id = uniqid("test");
            $first->$id = true;
            $is_ref = isset($second->$id);
            unset($first->$id);
            return $is_ref;
        }
        $temp = $first;
        $first = uniqid("test");
        $is_ref = ($first === $second);
        $first = $temp;
        return $is_ref;
    }

    /**
     *    Test to see if an object is a member of a
     *    class hiearchy.
     *    @param object $object    Object to test.
     *    @param string $class     Root name of hiearchy.
     *    @return boolean         True if class in hiearchy.
     *    @access public
     */
    static function isA($object, $class) {
        if (version_compare(phpversion(), '5') >= 0) {
            if (! class_exists($class, false)) {
                if (function_exists('interface_exists')) {
                    if (! interface_exists($class, false))  {
                        return false;
                    }
                }
            }
            eval("\$is_a = \$object instanceof $class;");
            return $is_a;
        }
        if (function_exists('is_a')) {
            return is_a($object, $class);
        }
        return ((strtolower($class) == get_class($object))
                or (is_subclass_of($object, $class)));
    }

    /**
     *    Sets a socket timeout for each chunk.
     *    @param resource $handle    Socket handle.
     *    @param integer $timeout    Limit in seconds.
     *    @access public
     */
    static function setTimeout($handle, $timeout) {
        if (function_exists('stream_set_timeout')) {
            stream_set_timeout($handle, $timeout, 0);
        } elseif (function_exists('socket_set_timeout')) {
            socket_set_timeout($handle, $timeout, 0);
        } elseif (function_exists('set_socket_timeout')) {
            set_socket_timeout($handle, $timeout, 0);
        }
    }
}
 /* .tmp\flat\cookies.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\cookies.php */ ?>
<?php
/**
 *  Base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/url.php');
/**#@-*/

/**
 *    Cookie data holder. Cookie rules are full of pretty
 *    arbitary stuff. I have used...
 *    http://wp.netscape.com/newsref/std/cookie_spec.html
 *    http://www.cookiecentral.com/faq/
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleCookie {
    private $host;
    private $name;
    private $value;
    private $path;
    private $expiry;
    private $is_secure;
    
    /**
     *    Constructor. Sets the stored values.
     *    @param string $name            Cookie key.
     *    @param string $value           Value of cookie.
     *    @param string $path            Cookie path if not host wide.
     *    @param string $expiry          Expiry date as string.
     *    @param boolean $is_secure      Currently ignored.
     */
    function __construct($name, $value = false, $path = false, $expiry = false, $is_secure = false) {
        $this->host = false;
        $this->name = $name;
        $this->value = $value;
        $this->path = ($path ? $this->fixPath($path) : "/");
        $this->expiry = false;
        if (is_string($expiry)) {
            $this->expiry = strtotime($expiry);
        } elseif (is_integer($expiry)) {
            $this->expiry = $expiry;
        }
        $this->is_secure = $is_secure;
    }
    
    /**
     *    Sets the host. The cookie rules determine
     *    that the first two parts are taken for
     *    certain TLDs and three for others. If the
     *    new host does not match these rules then the
     *    call will fail.
     *    @param string $host       New hostname.
     *    @return boolean           True if hostname is valid.
     *    @access public
     */
    function setHost($host) {
        if ($host = $this->truncateHost($host)) {
            $this->host = $host;
            return true;
        }
        return false;
    }
    
    /**
     *    Accessor for the truncated host to which this
     *    cookie applies.
     *    @return string       Truncated hostname.
     *    @access public
     */
    function getHost() {
        return $this->host;
    }
    
    /**
     *    Test for a cookie being valid for a host name.
     *    @param string $host    Host to test against.
     *    @return boolean        True if the cookie would be valid
     *                           here.
     */
    function isValidHost($host) {
        return ($this->truncateHost($host) === $this->getHost());
    }
    
    /**
     *    Extracts just the domain part that determines a
     *    cookie's host validity.
     *    @param string $host    Host name to truncate.
     *    @return string        Domain or false on a bad host.
     *    @access private
     */
    protected function truncateHost($host) {
        $tlds = SimpleUrl::getAllTopLevelDomains();
        if (preg_match('/[a-z\-]+\.(' . $tlds . ')$/i', $host, $matches)) {
            return $matches[0];
        } elseif (preg_match('/[a-z\-]+\.[a-z\-]+\.[a-z\-]+$/i', $host, $matches)) {
            return $matches[0];
        }
        return false;
    }
    
    /**
     *    Accessor for name.
     *    @return string       Cookie key.
     *    @access public
     */
    function getName() {
        return $this->name;
    }
    
    /**
     *    Accessor for value. A deleted cookie will
     *    have an empty string for this.
     *    @return string       Cookie value.
     *    @access public
     */
    function getValue() {
        return $this->value;
    }
    
    /**
     *    Accessor for path.
     *    @return string       Valid cookie path.
     *    @access public
     */
    function getPath() {
        return $this->path;
    }
    
    /**
     *    Tests a path to see if the cookie applies
     *    there. The test path must be longer or
     *    equal to the cookie path.
     *    @param string $path       Path to test against.
     *    @return boolean           True if cookie valid here.
     *    @access public
     */
    function isValidPath($path) {
        return (strncmp(
                $this->fixPath($path),
                $this->getPath(),
                strlen($this->getPath())) == 0);
    }
    
    /**
     *    Accessor for expiry.
     *    @return string       Expiry string.
     *    @access public
     */
    function getExpiry() {
        if (! $this->expiry) {
            return false;
        }
        return gmdate("D, d M Y H:i:s", $this->expiry) . " GMT";
    }
    
    /**
     *    Test to see if cookie is expired against
     *    the cookie format time or timestamp.
     *    Will give true for a session cookie.
     *    @param integer/string $now  Time to test against. Result
     *                                will be false if this time
     *                                is later than the cookie expiry.
     *                                Can be either a timestamp integer
     *                                or a cookie format date.
     *    @access public
     */
    function isExpired($now) {
        if (! $this->expiry) {
            return true;
        }
        if (is_string($now)) {
            $now = strtotime($now);
        }
        return ($this->expiry < $now);
    }
    
    /**
     *    Ages the cookie by the specified number of
     *    seconds.
     *    @param integer $interval   In seconds.
     *    @public
     */
    function agePrematurely($interval) {
        if ($this->expiry) {
            $this->expiry -= $interval;
        }
    }
    
    /**
     *    Accessor for the secure flag.
     *    @return boolean       True if cookie needs SSL.
     *    @access public
     */
    function isSecure() {
        return $this->is_secure;
    }
    
    /**
     *    Adds a trailing and leading slash to the path
     *    if missing.
     *    @param string $path            Path to fix.
     *    @access private
     */
    protected function fixPath($path) {
        if (substr($path, 0, 1) != '/') {
            $path = '/' . $path;
        }
        if (substr($path, -1, 1) != '/') {
            $path .= '/';
        }
        return $path;
    }
}

/**
 *    Repository for cookies. This stuff is a
 *    tiny bit browser dependent.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleCookieJar {
    private $cookies;
    
    /**
     *    Constructor. Jar starts empty.
     *    @access public
     */
    function __construct() {
        $this->cookies = array();
    }
    
    /**
     *    Removes expired and temporary cookies as if
     *    the browser was closed and re-opened.
     *    @param string/integer $now   Time to test expiry against.
     *    @access public
     */
    function restartSession($date = false) {
        $surviving_cookies = array();
        for ($i = 0; $i < count($this->cookies); $i++) {
            if (! $this->cookies[$i]->getValue()) {
                continue;
            }
            if (! $this->cookies[$i]->getExpiry()) {
                continue;
            }
            if ($date && $this->cookies[$i]->isExpired($date)) {
                continue;
            }
            $surviving_cookies[] = $this->cookies[$i];
        }
        $this->cookies = $surviving_cookies;
    }
    
    /**
     *    Ages all cookies in the cookie jar.
     *    @param integer $interval     The old session is moved
     *                                 into the past by this number
     *                                 of seconds. Cookies now over
     *                                 age will be removed.
     *    @access public
     */
    function agePrematurely($interval) {
        for ($i = 0; $i < count($this->cookies); $i++) {
            $this->cookies[$i]->agePrematurely($interval);
        }
    }
    
    /**
     *    Sets an additional cookie. If a cookie has
     *    the same name and path it is replaced.
     *    @param string $name       Cookie key.
     *    @param string $value      Value of cookie.
     *    @param string $host       Host upon which the cookie is valid.
     *    @param string $path       Cookie path if not host wide.
     *    @param string $expiry     Expiry date.
     *    @access public
     */
    function setCookie($name, $value, $host = false, $path = '/', $expiry = false) {
        $cookie = new SimpleCookie($name, $value, $path, $expiry);
        if ($host) {
            $cookie->setHost($host);
        }
        $this->cookies[$this->findFirstMatch($cookie)] = $cookie;
    }
    
    /**
     *    Finds a matching cookie to write over or the
     *    first empty slot if none.
     *    @param SimpleCookie $cookie    Cookie to write into jar.
     *    @return integer                Available slot.
     *    @access private
     */
    protected function findFirstMatch($cookie) {
        for ($i = 0; $i < count($this->cookies); $i++) {
            $is_match = $this->isMatch(
                    $cookie,
                    $this->cookies[$i]->getHost(),
                    $this->cookies[$i]->getPath(),
                    $this->cookies[$i]->getName());
            if ($is_match) {
                return $i;
            }
        }
        return count($this->cookies);
    }
    
    /**
     *    Reads the most specific cookie value from the
     *    browser cookies. Looks for the longest path that
     *    matches.
     *    @param string $host        Host to search.
     *    @param string $path        Applicable path.
     *    @param string $name        Name of cookie to read.
     *    @return string             False if not present, else the
     *                               value as a string.
     *    @access public
     */
    function getCookieValue($host, $path, $name) {
        $longest_path = '';
        foreach ($this->cookies as $cookie) {
            if ($this->isMatch($cookie, $host, $path, $name)) {
                if (strlen($cookie->getPath()) > strlen($longest_path)) {
                    $value = $cookie->getValue();
                    $longest_path = $cookie->getPath();
                }
            }
        }
        return (isset($value) ? $value : false);
    }
    
    /**
     *    Tests cookie for matching against search
     *    criteria.
     *    @param SimpleTest $cookie    Cookie to test.
     *    @param string $host          Host must match.
     *    @param string $path          Cookie path must be shorter than
     *                                 this path.
     *    @param string $name          Name must match.
     *    @return boolean              True if matched.
     *    @access private
     */
    protected function isMatch($cookie, $host, $path, $name) {
        if ($cookie->getName() != $name) {
            return false;
        }
        if ($host && $cookie->getHost() && ! $cookie->isValidHost($host)) {
            return false;
        }
        if (! $cookie->isValidPath($path)) {
            return false;
        }
        return true;
    }
    
    /**
     *    Uses a URL to sift relevant cookies by host and
     *    path. Results are list of strings of form "name=value".
     *    @param SimpleUrl $url       Url to select by.
     *    @return array               Valid name and value pairs.
     *    @access public
     */
    function selectAsPairs($url) {
        $pairs = array();
        foreach ($this->cookies as $cookie) {
            if ($this->isMatch($cookie, $url->getHost(), $url->getPath(), $cookie->getName())) {
                $pairs[] = $cookie->getName() . '=' . $cookie->getValue();
            }
        }
        return $pairs;
    }
}
 /* .tmp\flat\detached.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\detached.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/xml.php');
//require_once(dirname(__FILE__) . '/shell_tester.php');
/**#@-*/

/**
 *    Runs an XML formated test in a separate process.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class DetachedTestCase {
    private $command;
    private $dry_command;
    private $size;

    /**
     *    Sets the location of the remote test.
     *    @param string $command       Test script.
     *    @param string $dry_command   Script for dry run.
     *    @access public
     */
    function __construct($command, $dry_command = false) {
        $this->command = $command;
        $this->dry_command = $dry_command ? $dry_command : $command;
        $this->size = false;
    }

    /**
     *    Accessor for the test name for subclasses.
     *    @return string       Name of the test.
     *    @access public
     */
    function getLabel() {
        return $this->command;
    }

    /**
     *    Runs the top level test for this class. Currently
     *    reads the data as a single chunk. I'll fix this
     *    once I have added iteration to the browser.
     *    @param SimpleReporter $reporter    Target of test results.
     *    @returns boolean                   True if no failures.
     *    @access public
     */
    function run(&$reporter) {
        $shell = &new SimpleShell();
        $shell->execute($this->command);
        $parser = &$this->createParser($reporter);
        if (! $parser->parse($shell->getOutput())) {
            trigger_error('Cannot parse incoming XML from [' . $this->command . ']');
            return false;
        }
        return true;
    }

    /**
     *    Accessor for the number of subtests.
     *    @return integer       Number of test cases.
     *    @access public
     */
    function getSize() {
        if ($this->size === false) {
            $shell = &new SimpleShell();
            $shell->execute($this->dry_command);
            $reporter = &new SimpleReporter();
            $parser = &$this->createParser($reporter);
            if (! $parser->parse($shell->getOutput())) {
                trigger_error('Cannot parse incoming XML from [' . $this->dry_command . ']');
                return false;
            }
            $this->size = $reporter->getTestCaseCount();
        }
        return $this->size;
    }

    /**
     *    Creates the XML parser.
     *    @param SimpleReporter $reporter    Target of test results.
     *    @return SimpleTestXmlListener      XML reader.
     *    @access protected
     */
    protected function &createParser(&$reporter) {
        return new SimpleTestXmlParser($reporter);
    }
}
 /* .tmp\flat\dumper.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\dumper.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */
/**
 * does type matter
 */
if (! defined('TYPE_MATTERS')) {
    define('TYPE_MATTERS', true);
}

/**
 *    Displays variables as text and does diffs.
 *    @package  SimpleTest
 *    @subpackage   UnitTester
 */
class SimpleDumper {

    /**
     *    Renders a variable in a shorter form than print_r().
     *    @param mixed $value      Variable to render as a string.
     *    @return string           Human readable string form.
     *    @access public
     */
    function describeValue($value) {
        $type = $this->getType($value);
        switch($type) {
            case "Null":
                return "NULL";
            case "Boolean":
                return "Boolean: " . ($value ? "true" : "false");
            case "Array":
                return "Array: " . count($value) . " items";
            case "Object":
                return "Object: of " . get_class($value);
            case "String":
                return "String: " . $this->clipString($value, 200);
            default:
                return "$type: $value";
        }
        return "Unknown";
    }

    /**
     *    Gets the string representation of a type.
     *    @param mixed $value    Variable to check against.
     *    @return string         Type.
     *    @access public
     */
    function getType($value) {
        if (! isset($value)) {
            return "Null";
        } elseif (is_bool($value)) {
            return "Boolean";
        } elseif (is_string($value)) {
            return "String";
        } elseif (is_integer($value)) {
            return "Integer";
        } elseif (is_float($value)) {
            return "Float";
        } elseif (is_array($value)) {
            return "Array";
        } elseif (is_resource($value)) {
            return "Resource";
        } elseif (is_object($value)) {
            return "Object";
        }
        return "Unknown";
    }

    /**
     *    Creates a human readable description of the
     *    difference between two variables. Uses a
     *    dynamic call.
     *    @param mixed $first        First variable.
     *    @param mixed $second       Value to compare with.
     *    @param boolean $identical  If true then type anomolies count.
     *    @return string             Description of difference.
     *    @access public
     */
    function describeDifference($first, $second, $identical = false) {
        if ($identical) {
            if (! $this->isTypeMatch($first, $second)) {
                return "with type mismatch as [" . $this->describeValue($first) .
                    "] does not match [" . $this->describeValue($second) . "]";
            }
        }
        $type = $this->getType($first);
        if ($type == "Unknown") {
            return "with unknown type";
        }
        $method = 'describe' . $type . 'Difference';
        return $this->$method($first, $second, $identical);
    }

    /**
     *    Tests to see if types match.
     *    @param mixed $first        First variable.
     *    @param mixed $second       Value to compare with.
     *    @return boolean            True if matches.
     *    @access private
     */
    protected function isTypeMatch($first, $second) {
        return ($this->getType($first) == $this->getType($second));
    }

    /**
     *    Clips a string to a maximum length.
     *    @param string $value         String to truncate.
     *    @param integer $size         Minimum string size to show.
     *    @param integer $position     Centre of string section.
     *    @return string               Shortened version.
     *    @access public
     */
    function clipString($value, $size, $position = 0) {
        $length = strlen($value);
        if ($length <= $size) {
            return $value;
        }
        $position = min($position, $length);
        $start = ($size/2 > $position ? 0 : $position - $size/2);
        if ($start + $size > $length) {
            $start = $length - $size;
        }
        $value = substr($value, $start, $size);
        return ($start > 0 ? "..." : "") . $value . ($start + $size < $length ? "..." : "");
    }

    /**
     *    Creates a human readable description of the
     *    difference between two variables. The minimal
     *    version.
     *    @param null $first          First value.
     *    @param mixed $second        Value to compare with.
     *    @return string              Human readable description.
     *    @access private
     */
    protected function describeGenericDifference($first, $second) {
        return "as [" . $this->describeValue($first) .
                "] does not match [" .
                $this->describeValue($second) . "]";
    }

    /**
     *    Creates a human readable description of the
     *    difference between a null and another variable.
     *    @param null $first          First null.
     *    @param mixed $second        Null to compare with.
     *    @param boolean $identical   If true then type anomolies count.
     *    @return string              Human readable description.
     *    @access private
     */
    protected function describeNullDifference($first, $second, $identical) {
        return $this->describeGenericDifference($first, $second);
    }

    /**
     *    Creates a human readable description of the
     *    difference between a boolean and another variable.
     *    @param boolean $first       First boolean.
     *    @param mixed $second        Boolean to compare with.
     *    @param boolean $identical   If true then type anomolies count.
     *    @return string              Human readable description.
     *    @access private
     */
    protected function describeBooleanDifference($first, $second, $identical) {
        return $this->describeGenericDifference($first, $second);
    }

    /**
     *    Creates a human readable description of the
     *    difference between a string and another variable.
     *    @param string $first        First string.
     *    @param mixed $second        String to compare with.
     *    @param boolean $identical   If true then type anomolies count.
     *    @return string              Human readable description.
     *    @access private
     */
    protected function describeStringDifference($first, $second, $identical) {
        if (is_object($second) || is_array($second)) {
            return $this->describeGenericDifference($first, $second);
        }
        $position = $this->stringDiffersAt($first, $second);
        $message = "at character $position";
        $message .= " with [" .
                $this->clipString($first, 200, $position) . "] and [" .
                $this->clipString($second, 200, $position) . "]";
        return $message;
    }

    /**
     *    Creates a human readable description of the
     *    difference between an integer and another variable.
     *    @param integer $first       First number.
     *    @param mixed $second        Number to compare with.
     *    @param boolean $identical   If true then type anomolies count.
     *    @return string              Human readable description.
     *    @access private
     */
    protected function describeIntegerDifference($first, $second, $identical) {
        if (is_object($second) || is_array($second)) {
            return $this->describeGenericDifference($first, $second);
        }
        return "because [" . $this->describeValue($first) .
                "] differs from [" .
                $this->describeValue($second) . "] by " .
                abs($first - $second);
    }

    /**
     *    Creates a human readable description of the
     *    difference between two floating point numbers.
     *    @param float $first         First float.
     *    @param mixed $second        Float to compare with.
     *    @param boolean $identical   If true then type anomolies count.
     *    @return string              Human readable description.
     *    @access private
     */
    protected function describeFloatDifference($first, $second, $identical) {
        if (is_object($second) || is_array($second)) {
            return $this->describeGenericDifference($first, $second);
        }
        return "because [" . $this->describeValue($first) .
                "] differs from [" .
                $this->describeValue($second) . "] by " .
                abs($first - $second);
    }

    /**
     *    Creates a human readable description of the
     *    difference between two arrays.
     *    @param array $first         First array.
     *    @param mixed $second        Array to compare with.
     *    @param boolean $identical   If true then type anomolies count.
     *    @return string              Human readable description.
     *    @access private
     */
    protected function describeArrayDifference($first, $second, $identical) {
        if (! is_array($second)) {
            return $this->describeGenericDifference($first, $second);
        }
        if (! $this->isMatchingKeys($first, $second, $identical)) {
            return "as key list [" .
                    implode(", ", array_keys($first)) . "] does not match key list [" .
                    implode(", ", array_keys($second)) . "]";
        }
        foreach (array_keys($first) as $key) {
            if ($identical && ($first[$key] === $second[$key])) {
                continue;
            }
            if (! $identical && ($first[$key] == $second[$key])) {
                continue;
            }
            return "with member [$key] " . $this->describeDifference(
                    $first[$key],
                    $second[$key],
                    $identical);
        }
        return "";
    }

    /**
     *    Compares two arrays to see if their key lists match.
     *    For an identical match, the ordering and types of the keys
     *    is significant.
     *    @param array $first         First array.
     *    @param array $second        Array to compare with.
     *    @param boolean $identical   If true then type anomolies count.
     *    @return boolean             True if matching.
     *    @access private
     */
    protected function isMatchingKeys($first, $second, $identical) {
        $first_keys = array_keys($first);
        $second_keys = array_keys($second);
        if ($identical) {
            return ($first_keys === $second_keys);
        }
        sort($first_keys);
        sort($second_keys);
        return ($first_keys == $second_keys);
    }

    /**
     *    Creates a human readable description of the
     *    difference between a resource and another variable.
     *    @param resource $first       First resource.
     *    @param mixed $second         Resource to compare with.
     *    @param boolean $identical    If true then type anomolies count.
     *    @return string              Human readable description.
     *    @access private
     */
    protected function describeResourceDifference($first, $second, $identical) {
        return $this->describeGenericDifference($first, $second);
    }

    /**
     *    Creates a human readable description of the
     *    difference between two objects.
     *    @param object $first        First object.
     *    @param mixed $second        Object to compare with.
     *    @param boolean $identical   If true then type anomolies count.
     *    @return string              Human readable description.
     */
    protected function describeObjectDifference($first, $second, $identical) {
        if (! is_object($second)) {
            return $this->describeGenericDifference($first, $second);
        }
        return $this->describeArrayDifference(
                $this->getMembers($first),
                $this->getMembers($second),
                $identical);
    }

    /**
     *    Get all members of an object including private and protected ones.
     *    A safer form of casting to an array.
     *    @param object $object     Object to list members of,
     *                              including private ones.
     *    @return array             Names and values in the object.
     */
    protected function getMembers($object) {
        $reflection = new ReflectionObject($object);
        $members = array();
        foreach ($reflection->getProperties() as $property) {
            if (method_exists($property, 'setAccessible')) {
                $property->setAccessible(true);
            }
            try {
                $members[$property->getName()] = $property->getValue($object);
            } catch (ReflectionException $e) {
                $members[$property->getName()] =
                    $this->getPrivatePropertyNoMatterWhat($property->getName(), $object);
            }
        }
        return $members;
    }

    /**
     *    Extracts a private member's value when reflection won't play ball.
     *    @param string $name        Property name.
     *    @param object $object      Object to read.
     *    @return mixed              Value of property.
     */
    private function getPrivatePropertyNoMatterWhat($name, $object) {
        foreach ((array)$object as $mangled_name => $value) {
            if ($this->unmangle($mangled_name) == $name) {
                return $value;
            }
        }
    }

    /**
     *    Removes crud from property name after it's been converted
     *    to an array.
     *    @param string $mangled     Name from array cast.
     *    @return string             Cleaned up name.
     */
    function unmangle($mangled) {
        $parts = preg_split('/[^a-zA-Z0-9_\x7f-\xff]+/', $mangled);
        return array_pop($parts);
    }

    /**
     *    Find the first character position that differs
     *    in two strings by binary chop.
     *    @param string $first        First string.
     *    @param string $second       String to compare with.
     *    @return integer             Position of first differing
     *                                character.
     *    @access private
     */
    protected function stringDiffersAt($first, $second) {
        if (! $first || ! $second) {
            return 0;
        }
        if (strlen($first) < strlen($second)) {
            list($first, $second) = array($second, $first);
        }
        $position = 0;
        $step = strlen($first);
        while ($step > 1) {
            $step = (integer)(($step + 1) / 2);
            if (strncmp($first, $second, $position + $step) == 0) {
                $position += $step;
            }
        }
        return $position;
    }

    /**
     *    Sends a formatted dump of a variable to a string.
     *    @param mixed $variable    Variable to display.
     *    @return string            Output from print_r().
     *    @access public
     */
    function dump($variable) {
        ob_start();
        print_r($variable);
        $formatted = ob_get_contents();
        ob_end_clean();
        return $formatted;
    }
}
 /* .tmp\flat\encoding.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\encoding.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */
    
/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/socket.php');
/**#@-*/

/**
 *    Single post parameter.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleEncodedPair {
    private $key;
    private $value;
    
    /**
     *    Stashes the data for rendering later.
     *    @param string $key       Form element name.
     *    @param string $value     Data to send.
     */
    function __construct($key, $value) {
        $this->key = $key;
        $this->value = $value;
    }
    
    /**
     *    The pair as a single string.
     *    @return string        Encoded pair.
     *    @access public
     */
    function asRequest() {
        return urlencode($this->key) . '=' . urlencode($this->value);
    }
    
    /**
     *    The MIME part as a string.
     *    @return string        MIME part encoding.
     *    @access public
     */
    function asMime() {
        $part = 'Content-Disposition: form-data; ';
        $part .= "name=\"" . $this->key . "\"\r\n";
        $part .= "\r\n" . $this->value;
        return $part;
    }
    
    /**
     *    Is this the value we are looking for?
     *    @param string $key    Identifier.
     *    @return boolean       True if matched.
     *    @access public
     */
    function isKey($key) {
        return $key == $this->key;
    }
    
    /**
     *    Is this the value we are looking for?
     *    @return string       Identifier.
     *    @access public
     */
    function getKey() {
        return $this->key;
    }
    
    /**
     *    Is this the value we are looking for?
     *    @return string       Content.
     *    @access public
     */
    function getValue() {
        return $this->value;
    }
}

/**
 *    Single post parameter.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleAttachment {
    private $key;
    private $content;
    private $filename;
    
    /**
     *    Stashes the data for rendering later.
     *    @param string $key          Key to add value to.
     *    @param string $content      Raw data.
     *    @param hash $filename       Original filename.
     */
    function __construct($key, $content, $filename) {
        $this->key = $key;
        $this->content = $content;
        $this->filename = $filename;
    }
    
    /**
     *    The pair as a single string.
     *    @return string        Encoded pair.
     *    @access public
     */
    function asRequest() {
        return '';
    }
    
    /**
     *    The MIME part as a string.
     *    @return string        MIME part encoding.
     *    @access public
     */
    function asMime() {
        $part = 'Content-Disposition: form-data; ';
        $part .= 'name="' . $this->key . '"; ';
        $part .= 'filename="' . $this->filename . '"';
        $part .= "\r\nContent-Type: " . $this->deduceMimeType();
        $part .= "\r\n\r\n" . $this->content;
        return $part;
    }
    
    /**
     *    Attempts to figure out the MIME type from the
     *    file extension and the content.
     *    @return string        MIME type.
     *    @access private
     */
    protected function deduceMimeType() {
        if ($this->isOnlyAscii($this->content)) {
            return 'text/plain';
        }
        return 'application/octet-stream';
    }
    
    /**
     *    Tests each character is in the range 0-127.
     *    @param string $ascii    String to test.
     *    @access private
     */
    protected function isOnlyAscii($ascii) {
        for ($i = 0, $length = strlen($ascii); $i < $length; $i++) {
            if (ord($ascii[$i]) > 127) {
                return false;
            }
        }
        return true;
    }
    
    /**
     *    Is this the value we are looking for?
     *    @param string $key    Identifier.
     *    @return boolean       True if matched.
     *    @access public
     */
    function isKey($key) {
        return $key == $this->key;
    }
    
    /**
     *    Is this the value we are looking for?
     *    @return string       Identifier.
     *    @access public
     */
    function getKey() {
        return $this->key;
    }
    
    /**
     *    Is this the value we are looking for?
     *    @return string       Content.
     *    @access public
     */
    function getValue() {
        return $this->filename;
    }
}

/**
 *    Bundle of GET/POST parameters. Can include
 *    repeated parameters.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleEncoding {
    private $request;
    
    /**
     *    Starts empty.
     *    @param array $query       Hash of parameters.
     *                              Multiple values are
     *                              as lists on a single key.
     *    @access public
     */
    function __construct($query = false) {
        if (! $query) {
            $query = array();
        }
        $this->clear();
        $this->merge($query);
    }
    
    /**
     *    Empties the request of parameters.
     *    @access public
     */
    function clear() {
        $this->request = array();
    }
    
    /**
     *    Adds a parameter to the query.
     *    @param string $key            Key to add value to.
     *    @param string/array $value    New data.
     *    @access public
     */
    function add($key, $value) {
        if ($value === false) {
            return;
        }
        if (is_array($value)) {
            foreach ($value as $item) {
                $this->addPair($key, $item);
            }
        } else {
            $this->addPair($key, $value);
        }
    }
    
    /**
     *    Adds a new value into the request.
     *    @param string $key            Key to add value to.
     *    @param string/array $value    New data.
     *    @access private
     */
    protected function addPair($key, $value) {
        $this->request[] = new SimpleEncodedPair($key, $value);
    }
    
    /**
     *    Adds a MIME part to the query. Does nothing for a
     *    form encoded packet.
     *    @param string $key          Key to add value to.
     *    @param string $content      Raw data.
     *    @param hash $filename       Original filename.
     *    @access public
     */
    function attach($key, $content, $filename) {
        $this->request[] = new SimpleAttachment($key, $content, $filename);
    }
    
    /**
     *    Adds a set of parameters to this query.
     *    @param array/SimpleQueryString $query  Multiple values are
     *                                           as lists on a single key.
     *    @access public
     */
    function merge($query) {
        if (is_object($query)) {
            $this->request = array_merge($this->request, $query->getAll());
        } elseif (is_array($query)) {
            foreach ($query as $key => $value) {
                $this->add($key, $value);
            }
        }
    }
    
    /**
     *    Accessor for single value.
     *    @return string/array    False if missing, string
     *                            if present and array if
     *                            multiple entries.
     *    @access public
     */
    function getValue($key) {
        $values = array();
        foreach ($this->request as $pair) {
            if ($pair->isKey($key)) {
                $values[] = $pair->getValue();
            }
        }
        if (count($values) == 0) {
            return false;
        } elseif (count($values) == 1) {
            return $values[0];
        } else {
            return $values;
        }
    }
    
    /**
     *    Accessor for listing of pairs.
     *    @return array        All pair objects.
     *    @access public
     */
    function getAll() {
        return $this->request;
    }
    
    /**
     *    Renders the query string as a URL encoded
     *    request part.
     *    @return string        Part of URL.
     *    @access protected
     */
    protected function encode() {
        $statements = array();
        foreach ($this->request as $pair) {
            if ($statement = $pair->asRequest()) {
                $statements[] = $statement;
            }
        }
        return implode('&', $statements);
    }
}

/**
 *    Bundle of GET parameters. Can include
 *    repeated parameters.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleGetEncoding extends SimpleEncoding {
    
    /**
     *    Starts empty.
     *    @param array $query       Hash of parameters.
     *                              Multiple values are
     *                              as lists on a single key.
     *    @access public
     */
    function __construct($query = false) {
        parent::__construct($query);
    }
    
    /**
     *    HTTP request method.
     *    @return string        Always GET.
     *    @access public
     */
    function getMethod() {
        return 'GET';
    }
    
    /**
     *    Writes no extra headers.
     *    @param SimpleSocket $socket        Socket to write to.
     *    @access public
     */
    function writeHeadersTo(&$socket) {
    }
    
    /**
     *    No data is sent to the socket as the data is encoded into
     *    the URL.
     *    @param SimpleSocket $socket        Socket to write to.
     *    @access public
     */
    function writeTo(&$socket) {
    }
    
    /**
     *    Renders the query string as a URL encoded
     *    request part for attaching to a URL.
     *    @return string        Part of URL.
     *    @access public
     */
    function asUrlRequest() {
        return $this->encode();
    }
}

/**
 *    Bundle of URL parameters for a HEAD request.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleHeadEncoding extends SimpleGetEncoding {
    
    /**
     *    Starts empty.
     *    @param array $query       Hash of parameters.
     *                              Multiple values are
     *                              as lists on a single key.
     *    @access public
     */
    function __construct($query = false) {
        parent::__construct($query);
    }
    
    /**
     *    HTTP request method.
     *    @return string        Always HEAD.
     *    @access public
     */
    function getMethod() {
        return 'HEAD';
    }
}

/**
 *    Bundle of URL parameters for a DELETE request.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleDeleteEncoding extends SimpleGetEncoding {
    
    /**
     *    Starts empty.
     *    @param array $query       Hash of parameters.
     *                              Multiple values are
     *                              as lists on a single key.
     *    @access public
     */
    function __construct($query = false) {
        parent::__construct($query);
    }
    
    /**
     *    HTTP request method.
     *    @return string        Always DELETE.
     *    @access public
     */
    function getMethod() {
        return 'DELETE';
    }
}

/**
 *    Bundles an entity-body for transporting 
 *    a raw content payload with the request.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleEntityEncoding extends SimpleEncoding {
    private $content_type;
    private $body;
    
    function __construct($query = false, $content_type = false) {
        $this->content_type = $content_type;
    	if (is_string($query)) {
            $this->body = $query;
            parent::__construct();
        } else {
            parent::__construct($query);
        }
    }
    
    /**
     *    Returns the media type of the entity body
     *    @return string
     *    @access public
     */
    function getContentType() {
        if (!$this->content_type) {
        	return ($this->body) ? 'text/plain' : 'application/x-www-form-urlencoded';
        }
    	return $this->content_type;
    }
       
    /**
     *    Dispatches the form headers down the socket.
     *    @param SimpleSocket $socket        Socket to write to.
     *    @access public
     */
    function writeHeadersTo(&$socket) {
        $socket->write("Content-Length: " . (integer)strlen($this->encode()) . "\r\n");
        $socket->write("Content-Type: " .  $this->getContentType() . "\r\n");
    }
    
    /**
     *    Dispatches the form data down the socket.
     *    @param SimpleSocket $socket        Socket to write to.
     *    @access public
     */
    function writeTo(&$socket) {
        $socket->write($this->encode());
    }
    
    /**
     *    Renders the request body
     *    @return Encoded entity body
     *    @access protected
     */
    protected function encode() {
        return ($this->body) ? $this->body : parent::encode();
    }
}

/**
 *    Bundle of POST parameters. Can include
 *    repeated parameters.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimplePostEncoding extends SimpleEntityEncoding {
    
    /**
     *    Starts empty.
     *    @param array $query       Hash of parameters.
     *                              Multiple values are
     *                              as lists on a single key.
     *    @access public
     */
    function __construct($query = false, $content_type = false) {
        if (is_array($query) and $this->hasMoreThanOneLevel($query)) {
            $query = $this->rewriteArrayWithMultipleLevels($query);
        }
        parent::__construct($query, $content_type);
    }
    
    function hasMoreThanOneLevel($query) {
        foreach ($query as $key => $value) {
            if (is_array($value)) {
                return true;
            }
        }
        return false;
    }

    function rewriteArrayWithMultipleLevels($query) {
        $query_ = array();
        foreach ($query as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $sub_key => $sub_value) {
                    $query_[$key."[".$sub_key."]"] = $sub_value;
                }
            } else {
                $query_[$key] = $value;
            }
        }
        if ($this->hasMoreThanOneLevel($query_)) {
            $query_ = $this->rewriteArrayWithMultipleLevels($query_);
        }
        
        return $query_;
    }
    
    /**
     *    HTTP request method.
     *    @return string        Always POST.
     *    @access public
     */
    function getMethod() {
        return 'POST';
    }
    
    /**
     *    Renders the query string as a URL encoded
     *    request part for attaching to a URL.
     *    @return string        Part of URL.
     *    @access public
     */
    function asUrlRequest() {
        return '';
    }
}

/**
 *    Encoded entity body for a PUT request.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimplePutEncoding extends SimpleEntityEncoding {
    
    /**
     *    Starts empty.
     *    @param array $query       Hash of parameters.
     *                              Multiple values are
     *                              as lists on a single key.
     *    @access public
     */
    function __construct($query = false, $content_type = false) {
        parent::__construct($query, $content_type);
    }
    
    /**
     *    HTTP request method.
     *    @return string        Always PUT.
     *    @access public
     */
    function getMethod() {
        return 'PUT';
    }
}

/**
 *    Bundle of POST parameters in the multipart
 *    format. Can include file uploads.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleMultipartEncoding extends SimplePostEncoding {
    private $boundary;
    
    /**
     *    Starts empty.
     *    @param array $query       Hash of parameters.
     *                              Multiple values are
     *                              as lists on a single key.
     *    @access public
     */
    function __construct($query = false, $boundary = false) {
        parent::__construct($query);
        $this->boundary = ($boundary === false ? uniqid('st') : $boundary);
    }
    
    /**
     *    Dispatches the form headers down the socket.
     *    @param SimpleSocket $socket        Socket to write to.
     *    @access public
     */
    function writeHeadersTo(&$socket) {
        $socket->write("Content-Length: " . (integer)strlen($this->encode()) . "\r\n");
        $socket->write("Content-Type: multipart/form-data; boundary=" . $this->boundary . "\r\n");
    }
    
    /**
     *    Dispatches the form data down the socket.
     *    @param SimpleSocket $socket        Socket to write to.
     *    @access public
     */
    function writeTo(&$socket) {
        $socket->write($this->encode());
    }
    
    /**
     *    Renders the query string as a URL encoded
     *    request part.
     *    @return string        Part of URL.
     *    @access public
     */
    function encode() {
        $stream = '';
        foreach ($this->getAll() as $pair) {
            $stream .= "--" . $this->boundary . "\r\n";
            $stream .= $pair->asMime() . "\r\n";
        }
        $stream .= "--" . $this->boundary . "--\r\n";
        return $stream;
    }
}
 /* .tmp\flat\expectation.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\expectation.php */ ?>
<?php
/**
 *    base include file for SimpleTest
 *    @package    SimpleTest
 *    @subpackage    UnitTester
 *    @version    $Id$
 */

/**#@+
 *    include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/dumper.php');
//require_once(dirname(__FILE__) . '/compatibility.php');
/**#@-*/

/**
 *    Assertion that can display failure information.
 *    Also includes various helper methods.
 *    @package SimpleTest
 *    @subpackage UnitTester
 *    @abstract
 */
class SimpleExpectation {
    protected $dumper = false;
    private $message;

    /**
     *    Creates a dumper for displaying values and sets
     *    the test message.
     *    @param string $message    Customised message on failure.
     */
    function __construct($message = '%s') {
        $this->message = $message;
    }

    /**
     *    Tests the expectation. True if correct.
     *    @param mixed $compare        Comparison value.
     *    @return boolean              True if correct.
     *    @access public
     *    @abstract
     */
    function test($compare) {
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     *    @abstract
     */
    function testMessage($compare) {
    }

    /**
     *    Overlays the generated message onto the stored user
     *    message. An additional message can be interjected.
     *    @param mixed $compare        Comparison value.
     *    @param SimpleDumper $dumper  For formatting the results.
     *    @return string               Description of success
     *                                 or failure.
     *    @access public
     */
    function overlayMessage($compare, $dumper) {
        $this->dumper = $dumper;
        return sprintf($this->message, $this->testMessage($compare));
    }

    /**
     *    Accessor for the dumper.
     *    @return SimpleDumper    Current value dumper.
     *    @access protected
     */
    protected function getDumper() {
        if (! $this->dumper) {
            $dumper = new SimpleDumper();
            return $dumper;
        }
        return $this->dumper;
    }

    /**
     *    Test to see if a value is an expectation object.
     *    A useful utility method.
     *    @param mixed $expectation    Hopefully an Expectation
     *                                 class.
     *    @return boolean              True if descended from
     *                                 this class.
     *    @access public
     */
    static function isExpectation($expectation) {
        return is_object($expectation) &&
                SimpleTestCompatibility::isA($expectation, 'SimpleExpectation');
    }
}

/**
 *    A wildcard expectation always matches.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class AnythingExpectation extends SimpleExpectation {

    /**
     *    Tests the expectation. Always true.
     *    @param mixed $compare  Ignored.
     *    @return boolean        True.
     *    @access public
     */
    function test($compare) {
        return true;
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        $dumper = $this->getDumper();
        return 'Anything always matches [' . $dumper->describeValue($compare) . ']';
    }
}

/**
 *    An expectation that never matches.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class FailedExpectation extends SimpleExpectation {

    /**
     *    Tests the expectation. Always false.
     *    @param mixed $compare  Ignored.
     *    @return boolean        True.
     *    @access public
     */
    function test($compare) {
        return false;
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of failure.
     *    @access public
     */
    function testMessage($compare) {
        $dumper = $this->getDumper();
        return 'Failed expectation never matches [' . $dumper->describeValue($compare) . ']';
    }
}

/**
 *    An expectation that passes on boolean true.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class TrueExpectation extends SimpleExpectation {

    /**
     *    Tests the expectation.
     *    @param mixed $compare  Should be true.
     *    @return boolean        True on match.
     *    @access public
     */
    function test($compare) {
        return (boolean)$compare;
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        $dumper = $this->getDumper();
        return 'Expected true, got [' . $dumper->describeValue($compare) . ']';
    }
}

/**
 *    An expectation that passes on boolean false.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class FalseExpectation extends SimpleExpectation {

    /**
     *    Tests the expectation.
     *    @param mixed $compare  Should be false.
     *    @return boolean        True on match.
     *    @access public
     */
    function test($compare) {
        return ! (boolean)$compare;
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        $dumper = $this->getDumper();
        return 'Expected false, got [' . $dumper->describeValue($compare) . ']';
    }
}

/**
 *    Test for equality.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class EqualExpectation extends SimpleExpectation {
    private $value;

    /**
     *    Sets the value to compare against.
     *    @param mixed $value        Test value to match.
     *    @param string $message     Customised message on failure.
     *    @access public
     */
    function __construct($value, $message = '%s') {
        parent::__construct($message);
        $this->value = $value;
    }

    /**
     *    Tests the expectation. True if it matches the
     *    held value.
     *    @param mixed $compare        Comparison value.
     *    @return boolean              True if correct.
     *    @access public
     */
    function test($compare) {
        return (($this->value == $compare) && ($compare == $this->value));
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        if ($this->test($compare)) {
            return "Equal expectation [" . $this->dumper->describeValue($this->value) . "]";
        } else {
            return "Equal expectation fails " .
                    $this->dumper->describeDifference($this->value, $compare);
        }
    }

    /**
     *    Accessor for comparison value.
     *    @return mixed       Held value to compare with.
     *    @access protected
     */
    protected function getValue() {
        return $this->value;
    }
}

/**
 *    Test for inequality.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NotEqualExpectation extends EqualExpectation {

    /**
     *    Sets the value to compare against.
     *    @param mixed $value       Test value to match.
     *    @param string $message    Customised message on failure.
     *    @access public
     */
    function __construct($value, $message = '%s') {
        parent::__construct($value, $message);
    }

    /**
     *    Tests the expectation. True if it differs from the
     *    held value.
     *    @param mixed $compare        Comparison value.
     *    @return boolean              True if correct.
     *    @access public
     */
    function test($compare) {
        return ! parent::test($compare);
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        $dumper = $this->getDumper();
        if ($this->test($compare)) {
            return "Not equal expectation passes " .
                    $dumper->describeDifference($this->getValue(), $compare);
        } else {
            return "Not equal expectation fails [" .
                    $dumper->describeValue($this->getValue()) .
                    "] matches";
        }
    }
}

/**
 *    Test for being within a range.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class WithinMarginExpectation extends SimpleExpectation {
    private $upper;
    private $lower;

    /**
     *    Sets the value to compare against and the fuzziness of
     *    the match. Used for comparing floating point values.
     *    @param mixed $value        Test value to match.
     *    @param mixed $margin       Fuzziness of match.
     *    @param string $message     Customised message on failure.
     *    @access public
     */
    function __construct($value, $margin, $message = '%s') {
        parent::__construct($message);
        $this->upper = $value + $margin;
        $this->lower = $value - $margin;
    }

    /**
     *    Tests the expectation. True if it matches the
     *    held value.
     *    @param mixed $compare        Comparison value.
     *    @return boolean              True if correct.
     *    @access public
     */
    function test($compare) {
        return (($compare <= $this->upper) && ($compare >= $this->lower));
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        if ($this->test($compare)) {
            return $this->withinMessage($compare);
        } else {
            return $this->outsideMessage($compare);
        }
    }

    /**
     *    Creates a the message for being within the range.
     *    @param mixed $compare        Value being tested.
     *    @access private
     */
    protected function withinMessage($compare) {
        return "Within expectation [" . $this->dumper->describeValue($this->lower) . "] and [" .
                $this->dumper->describeValue($this->upper) . "]";
    }

    /**
     *    Creates a the message for being within the range.
     *    @param mixed $compare        Value being tested.
     *    @access private
     */
    protected function outsideMessage($compare) {
        if ($compare > $this->upper) {
            return "Outside expectation " .
                    $this->dumper->describeDifference($compare, $this->upper);
        } else {
            return "Outside expectation " .
                    $this->dumper->describeDifference($compare, $this->lower);
        }
    }
}

/**
 *    Test for being outside of a range.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class OutsideMarginExpectation extends WithinMarginExpectation {

    /**
     *    Sets the value to compare against and the fuzziness of
     *    the match. Used for comparing floating point values.
     *    @param mixed $value        Test value to not match.
     *    @param mixed $margin       Fuzziness of match.
     *    @param string $message     Customised message on failure.
     *    @access public
     */
    function __construct($value, $margin, $message = '%s') {
        parent::__construct($value, $margin, $message);
    }

    /**
     *    Tests the expectation. True if it matches the
     *    held value.
     *    @param mixed $compare        Comparison value.
     *    @return boolean              True if correct.
     *    @access public
     */
    function test($compare) {
        return ! parent::test($compare);
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        if (! $this->test($compare)) {
            return $this->withinMessage($compare);
        } else {
            return $this->outsideMessage($compare);
        }
    }
}

/**
 *    Test for reference.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class ReferenceExpectation {
    private $value;

    /**
     *    Sets the reference value to compare against.
     *    @param mixed $value       Test reference to match.
     *    @param string $message    Customised message on failure.
     *    @access public
     */
    function __construct(&$value, $message = '%s') {
        $this->message = $message;
        $this->value = &$value;
    }

    /**
     *    Tests the expectation. True if it exactly
     *    references the held value.
     *    @param mixed $compare        Comparison reference.
     *    @return boolean              True if correct.
     *    @access public
     */
    function test(&$compare) {
        return SimpleTestCompatibility::isReference($this->value, $compare);
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        if ($this->test($compare)) {
            return "Reference expectation [" . $this->dumper->describeValue($this->value) . "]";
        } else {
            return "Reference expectation fails " .
                    $this->dumper->describeDifference($this->value, $compare);
        }
    }

    /**
     *    Overlays the generated message onto the stored user
     *    message. An additional message can be interjected.
     *    @param mixed $compare        Comparison value.
     *    @param SimpleDumper $dumper  For formatting the results.
     *    @return string               Description of success
     *                                 or failure.
     *    @access public
     */
    function overlayMessage($compare, $dumper) {
        $this->dumper = $dumper;
        return sprintf($this->message, $this->testMessage($compare));
    }

    /**
     *    Accessor for the dumper.
     *    @return SimpleDumper    Current value dumper.
     *    @access protected
     */
    protected function getDumper() {
        if (! $this->dumper) {
            $dumper = new SimpleDumper();
            return $dumper;
        }
        return $this->dumper;
    }
}

/**
 *    Test for identity.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class IdenticalExpectation extends EqualExpectation {

    /**
     *    Sets the value to compare against.
     *    @param mixed $value       Test value to match.
     *    @param string $message    Customised message on failure.
     *    @access public
     */
    function __construct($value, $message = '%s') {
        parent::__construct($value, $message);
    }

    /**
     *    Tests the expectation. True if it exactly
     *    matches the held value.
     *    @param mixed $compare        Comparison value.
     *    @return boolean              True if correct.
     *    @access public
     */
    function test($compare) {
        return SimpleTestCompatibility::isIdentical($this->getValue(), $compare);
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        $dumper = $this->getDumper();
        if ($this->test($compare)) {
            return "Identical expectation [" . $dumper->describeValue($this->getValue()) . "]";
        } else {
            return "Identical expectation [" . $dumper->describeValue($this->getValue()) .
                    "] fails with [" .
                    $dumper->describeValue($compare) . "] " .
                    $dumper->describeDifference($this->getValue(), $compare, TYPE_MATTERS);
        }
    }
}

/**
 *    Test for non-identity.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NotIdenticalExpectation extends IdenticalExpectation {

    /**
     *    Sets the value to compare against.
     *    @param mixed $value        Test value to match.
     *    @param string $message     Customised message on failure.
     *    @access public
     */
    function __construct($value, $message = '%s') {
        parent::__construct($value, $message);
    }

    /**
     *    Tests the expectation. True if it differs from the
     *    held value.
     *    @param mixed $compare        Comparison value.
     *    @return boolean              True if correct.
     *    @access public
     */
    function test($compare) {
        return ! parent::test($compare);
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        $dumper = $this->getDumper();
        if ($this->test($compare)) {
            return "Not identical expectation passes " .
                    $dumper->describeDifference($this->getValue(), $compare, TYPE_MATTERS);
        } else {
            return "Not identical expectation [" . $dumper->describeValue($this->getValue()) . "] matches";
        }
    }
}

/**
 *    Test for a pattern using Perl regex rules.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class PatternExpectation extends SimpleExpectation {
    private $pattern;

    /**
     *    Sets the value to compare against.
     *    @param string $pattern    Pattern to search for.
     *    @param string $message    Customised message on failure.
     *    @access public
     */
    function __construct($pattern, $message = '%s') {
        parent::__construct($message);
        $this->pattern = $pattern;
    }

    /**
     *    Accessor for the pattern.
     *    @return string       Perl regex as string.
     *    @access protected
     */
    protected function getPattern() {
        return $this->pattern;
    }

    /**
     *    Tests the expectation. True if the Perl regex
     *    matches the comparison value.
     *    @param string $compare        Comparison value.
     *    @return boolean               True if correct.
     *    @access public
     */
    function test($compare) {
        return (boolean)preg_match($this->getPattern(), $compare);
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        if ($this->test($compare)) {
            return $this->describePatternMatch($this->getPattern(), $compare);
        } else {
            $dumper = $this->getDumper();
            return "Pattern [" . $this->getPattern() .
                    "] not detected in [" .
                    $dumper->describeValue($compare) . "]";
        }
    }

    /**
     *    Describes a pattern match including the string
     *    found and it's position.
     *    @param string $pattern        Regex to match against.
     *    @param string $subject        Subject to search.
     *    @access protected
     */
    protected function describePatternMatch($pattern, $subject) {
        preg_match($pattern, $subject, $matches);
        $position = strpos($subject, $matches[0]);
        $dumper = $this->getDumper();
        return "Pattern [$pattern] detected at character [$position] in [" .
                $dumper->describeValue($subject) . "] as [" .
                $matches[0] . "] in region [" .
                $dumper->clipString($subject, 100, $position) . "]";
    }
}

/**
 *    Fail if a pattern is detected within the
 *    comparison.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NoPatternExpectation extends PatternExpectation {

    /**
     *    Sets the reject pattern
     *    @param string $pattern    Pattern to search for.
     *    @param string $message    Customised message on failure.
     *    @access public
     */
    function __construct($pattern, $message = '%s') {
        parent::__construct($pattern, $message);
    }

    /**
     *    Tests the expectation. False if the Perl regex
     *    matches the comparison value.
     *    @param string $compare        Comparison value.
     *    @return boolean               True if correct.
     *    @access public
     */
    function test($compare) {
        return ! parent::test($compare);
    }

    /**
     *    Returns a human readable test message.
     *    @param string $compare      Comparison value.
     *    @return string              Description of success
     *                                or failure.
     *    @access public
     */
    function testMessage($compare) {
        if ($this->test($compare)) {
            $dumper = $this->getDumper();
            return "Pattern [" . $this->getPattern() .
                    "] not detected in [" .
                    $dumper->describeValue($compare) . "]";
        } else {
            return $this->describePatternMatch($this->getPattern(), $compare);
        }
    }
}

/**
 *    Tests either type or class name if it's an object.
 *      @package SimpleTest
 *      @subpackage UnitTester
 */
class IsAExpectation extends SimpleExpectation {
    private $type;

    /**
     *    Sets the type to compare with.
     *    @param string $type       Type or class name.
     *    @param string $message    Customised message on failure.
     *    @access public
     */
    function __construct($type, $message = '%s') {
        parent::__construct($message);
        $this->type = $type;
    }

    /**
     *    Accessor for type to check against.
     *    @return string    Type or class name.
     *    @access protected
     */
    protected function getType() {
        return $this->type;
    }

    /**
     *    Tests the expectation. True if the type or
     *    class matches the string value.
     *    @param string $compare        Comparison value.
     *    @return boolean               True if correct.
     *    @access public
     */
    function test($compare) {
        if (is_object($compare)) {
            return SimpleTestCompatibility::isA($compare, $this->type);
        } else {
            return (strtolower(gettype($compare)) == $this->canonicalType($this->type));
        }
    }

    /**
     *    Coerces type name into a gettype() match.
     *    @param string $type        User type.
     *    @return string             Simpler type.
     *    @access private
     */
    protected function canonicalType($type) {
        $type = strtolower($type);
        $map = array(
                'bool' => 'boolean',
                'float' => 'double',
                'real' => 'double',
                'int' => 'integer');
        if (isset($map[$type])) {
            $type = $map[$type];
        }
        return $type;
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        $dumper = $this->getDumper();
        return "Value [" . $dumper->describeValue($compare) .
                "] should be type [" . $this->type . "]";
    }
}

/**
 *    Tests either type or class name if it's an object.
 *    Will succeed if the type does not match.
 *      @package SimpleTest
 *      @subpackage UnitTester
 */
class NotAExpectation extends IsAExpectation {
    private $type;

    /**
     *    Sets the type to compare with.
     *    @param string $type       Type or class name.
     *    @param string $message    Customised message on failure.
     *    @access public
     */
    function __construct($type, $message = '%s') {
        parent::__construct($type, $message);
    }

    /**
     *    Tests the expectation. False if the type or
     *    class matches the string value.
     *    @param string $compare        Comparison value.
     *    @return boolean               True if different.
     *    @access public
     */
    function test($compare) {
        return ! parent::test($compare);
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        $dumper = $this->getDumper();
        return "Value [" . $dumper->describeValue($compare) .
                "] should not be type [" . $this->getType() . "]";
    }
}

/**
 *    Tests for existance of a method in an object
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class MethodExistsExpectation extends SimpleExpectation {
    private $method;

    /**
     *    Sets the value to compare against.
     *    @param string $method     Method to check.
     *    @param string $message    Customised message on failure.
     *    @return void
     */
    function __construct($method, $message = '%s') {
        parent::__construct($message);
        $this->method = &$method;
    }

    /**
     *    Tests the expectation. True if the method exists in the test object.
     *    @param string $compare        Comparison method name.
     *    @return boolean               True if correct.
     */
    function test($compare) {
        return (boolean)(is_object($compare) && method_exists($compare, $this->method));
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     */
    function testMessage($compare) {
        $dumper = $this->getDumper();
        if (! is_object($compare)) {
            return 'No method on non-object [' . $dumper->describeValue($compare) . ']';
        }
        $method = $this->method;
        return "Object [" . $dumper->describeValue($compare) .
                "] should contain method [$method]";
    }
}

/**
 *    Compares an object member's value even if private.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class MemberExpectation extends IdenticalExpectation {
    private $name;

    /**
     *    Sets the value to compare against.
     *    @param string $method     Method to check.
     *    @param string $message    Customised message on failure.
     *    @return void
     */
    function __construct($name, $expected) {
        $this->name = $name;
        parent::__construct($expected);
    }

    /**
     *    Tests the expectation. True if the property value is identical.
     *    @param object $actual         Comparison object.
     *    @return boolean               True if identical.
     */
    function test($actual) {
        if (! is_object($actual)) {
            return false;
        }
        return parent::test($this->getProperty($this->name, $actual));
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     */
    function testMessage($actual) {
        return parent::testMessage($this->getProperty($this->name, $actual));
    }

    /**
     *    Extracts the member value even if private using reflection.
     *    @param string $name        Property name.
     *    @param object $object      Object to read.
     *    @return mixed              Value of property.
     */
    private function getProperty($name, $object) {
        $reflection = new ReflectionObject($object);
        $property = $reflection->getProperty($name);
        if (method_exists($property, 'setAccessible')) {
            $property->setAccessible(true);
        }
        try {
            return $property->getValue($object);
        } catch (ReflectionException $e) {
            return $this->getPrivatePropertyNoMatterWhat($name, $object);
        }
    }

    /**
     *    Extracts a private member's value when reflection won't play ball.
     *    @param string $name        Property name.
     *    @param object $object      Object to read.
     *    @return mixed              Value of property.
     */
    private function getPrivatePropertyNoMatterWhat($name, $object) {
        foreach ((array)$object as $mangled_name => $value) {
            if ($this->unmangle($mangled_name) == $name) {
                return $value;
            }
        }
    }

    /**
     *    Removes crud from property name after it's been converted
     *    to an array.
     *    @param string $mangled     Name from array cast.
     *    @return string             Cleaned up name.
     */
    function unmangle($mangled) {
        $parts = preg_split('/[^a-zA-Z0-9_\x7f-\xff]+/', $mangled);
        return array_pop($parts);
    }
}
 /* .tmp\flat\form.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\form.php */ ?>
<?php
/**
 *  Base include file for SimpleTest.
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */

/**#@+
 * include SimpleTest files
 */
//require_once(dirname(__FILE__) . '/tag.php');
//require_once(dirname(__FILE__) . '/encoding.php');
//require_once(dirname(__FILE__) . '/selector.php');
/**#@-*/

/**
 *    Form tag class to hold widget values.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleForm {
    private $method;
    private $action;
    private $encoding;
    private $default_target;
    private $id;
    private $buttons;
    private $images;
    private $widgets;
    private $radios;
    private $checkboxes;

    /**
     *    Starts with no held controls/widgets.
     *    @param SimpleTag $tag        Form tag to read.
     *    @param SimplePage $page      Holding page.
     */
    function __construct($tag, $page) {
        $this->method = $tag->getAttribute('method');
        $this->action = $this->createAction($tag->getAttribute('action'), $page);
        $this->encoding = $this->setEncodingClass($tag);
        $this->default_target = false;
        $this->id = $tag->getAttribute('id');
        $this->buttons = array();
        $this->images = array();
        $this->widgets = array();
        $this->radios = array();
        $this->checkboxes = array();
    }

    /**
     *    Creates the request packet to be sent by the form.
     *    @param SimpleTag $tag        Form tag to read.
     *    @return string               Packet class.
     *    @access private
     */
    protected function setEncodingClass($tag) {
        if (strtolower($tag->getAttribute('method')) == 'post') {
            if (strtolower($tag->getAttribute('enctype')) == 'multipart/form-data') {
                return 'SimpleMultipartEncoding';
            }
            return 'SimplePostEncoding';
        }
        return 'SimpleGetEncoding';
    }

    /**
     *    Sets the frame target within a frameset.
     *    @param string $frame        Name of frame.
     *    @access public
     */
    function setDefaultTarget($frame) {
        $this->default_target = $frame;
    }

    /**
     *    Accessor for method of form submission.
     *    @return string           Either get or post.
     *    @access public
     */
    function getMethod() {
        return ($this->method ? strtolower($this->method) : 'get');
    }

    /**
     *    Combined action attribute with current location
     *    to get an absolute form target.
     *    @param string $action    Action attribute from form tag.
     *    @param SimpleUrl $base   Page location.
     *    @return SimpleUrl        Absolute form target.
     */
    protected function createAction($action, $page) {
        if (($action === '') || ($action === false)) {
            return $page->expandUrl($page->getUrl());
        }
        return $page->expandUrl(new SimpleUrl($action));;
    }

    /**
     *    Absolute URL of the target.
     *    @return SimpleUrl           URL target.
     *    @access public
     */
    function getAction() {
        $url = $this->action;
        if ($this->default_target && ! $url->getTarget()) {
            $url->setTarget($this->default_target);
        }
        return $url;
    }

    /**
     *    Creates the encoding for the current values in the
     *    form.
     *    @return SimpleFormEncoding    Request to submit.
     *    @access private
     */
    protected function encode() {
        $class = $this->encoding;
        $encoding = new $class();
        for ($i = 0, $count = count($this->widgets); $i < $count; $i++) {
            $this->widgets[$i]->write($encoding);
        }
        return $encoding;
    }

    /**
     *    ID field of form for unique identification.
     *    @return string           Unique tag ID.
     *    @access public
     */
    function getId() {
        return $this->id;
    }

    /**
     *    Adds a tag contents to the form.
     *    @param SimpleWidget $tag        Input tag to add.
     */
    function addWidget($tag) {
        if (strtolower($tag->getAttribute('type')) == 'submit') {
            $this->buttons[] = $tag;
        } elseif (strtolower($tag->getAttribute('type')) == 'image') {
            $this->images[] = $tag;
        } elseif ($tag->getName()) {
            $this->setWidget($tag);
        }
    }

    /**
     *    Sets the widget into the form, grouping radio
     *    buttons if any.
     *    @param SimpleWidget $tag   Incoming form control.
     *    @access private
     */
    protected function setWidget($tag) {
        if (strtolower($tag->getAttribute('type')) == 'radio') {
            $this->addRadioButton($tag);
        } elseif (strtolower($tag->getAttribute('type')) == 'checkbox') {
            $this->addCheckbox($tag);
        } else {
            $this->widgets[] = &$tag;
        }
    }

    /**
     *    Adds a radio button, building a group if necessary.
     *    @param SimpleRadioButtonTag $tag   Incoming form control.
     *    @access private
     */
    protected function addRadioButton($tag) {
        if (! isset($this->radios[$tag->getName()])) {
            $this->widgets[] = new SimpleRadioGroup();
            $this->radios[$tag->getName()] = count($this->widgets) - 1;
        }
        $this->widgets[$this->radios[$tag->getName()]]->addWidget($tag);
    }

    /**
     *    Adds a checkbox, making it a group on a repeated name.
     *    @param SimpleCheckboxTag $tag   Incoming form control.
     *    @access private
     */
    protected function addCheckbox($tag) {
        if (! isset($this->checkboxes[$tag->getName()])) {
            $this->widgets[] = $tag;
            $this->checkboxes[$tag->getName()] = count($this->widgets) - 1;
        } else {
            $index = $this->checkboxes[$tag->getName()];
            if (! SimpleTestCompatibility::isA($this->widgets[$index], 'SimpleCheckboxGroup')) {
                $previous = $this->widgets[$index];
                $this->widgets[$index] = new SimpleCheckboxGroup();
                $this->widgets[$index]->addWidget($previous);
            }
            $this->widgets[$index]->addWidget($tag);
        }
    }

    /**
     *    Extracts current value from form.
     *    @param SimpleSelector $selector   Criteria to apply.
     *    @return string/array              Value(s) as string or null
     *                                      if not set.
     *    @access public
     */
    function getValue($selector) {
        for ($i = 0, $count = count($this->widgets); $i < $count; $i++) {
            if ($selector->isMatch($this->widgets[$i])) {
                return $this->widgets[$i]->getValue();
            }
        }
        foreach ($this->buttons as $button) {
            if ($selector->isMatch($button)) {
                return $button->getValue();
            }
        }
        return null;
    }

    /**
     *    Sets a widget value within the form.
     *    @param SimpleSelector $selector   Criteria to apply.
     *    @param string $value              Value to input into the widget.
     *    @return boolean                   True if value is legal, false
     *                                      otherwise. If the field is not
     *                                      present, nothing will be set.
     *    @access public
     */
    function setField($selector, $value, $position=false) {
        $success = false;
        $_position = 0;
        for ($i = 0, $count = count($this->widgets); $i < $count; $i++) {
            if ($selector->isMatch($this->widgets[$i])) {
                $_position++;
                if ($position === false or $_position === (int)$position) {
                    if ($this->widgets[$i]->setValue($value)) {
                        $success = true;
                    }
                }
            }
        }
        return $success;
    }

    /**
     *    Used by the page object to set widgets labels to
     *    external label tags.
     *    @param SimpleSelector $selector   Criteria to apply.
     *    @access public
     */
    function attachLabelBySelector($selector, $label) {
        for ($i = 0, $count = count($this->widgets); $i < $count; $i++) {
            if ($selector->isMatch($this->widgets[$i])) {
                if (method_exists($this->widgets[$i], 'setLabel')) {
                    $this->widgets[$i]->setLabel($label);
                    return;
                }
            }
        }
    }

    /**
     *    Test to see if a form has a submit button.
     *    @param SimpleSelector $selector   Criteria to apply.
     *    @return boolean                   True if present.
     *    @access public
     */
    function hasSubmit($selector) {
        foreach ($this->buttons as $button) {
            if ($selector->isMatch($button)) {
                return true;
            }
        }
        return false;
    }

    /**
     *    Test to see if a form has an image control.
     *    @param SimpleSelector $selector   Criteria to apply.
     *    @return boolean                   True if present.
     *    @access public
     */
    function hasImage($selector) {
        foreach ($this->images as $image) {
            if ($selector->isMatch($image)) {
                return true;
            }
        }
        return false;
    }

    /**
     *    Gets the submit values for a selected button.
     *    @param SimpleSelector $selector   Criteria to apply.
     *    @param hash $additional           Additional data for the form.
     *    @return SimpleEncoding            Submitted values or false
     *                                      if there is no such button
     *                                      in the form.
     *    @access public
     */
    function submitButton($selector, $additional = false) {
        $additional = $additional ? $additional : array();
        foreach ($this->buttons as $button) {
            if ($selector->isMatch($button)) {
                $encoding = $this->encode();
                $button->write($encoding);
                if ($additional) {
                    $encoding->merge($additional);
                }
                return $encoding;
            }
        }
        return false;
    }

    /**
     *    Gets the submit values for an image.
     *    @param SimpleSelector $selector   Criteria to apply.
     *    @param integer $x                 X-coordinate of click.
     *    @param integer $y                 Y-coordinate of click.
     *    @param hash $additional           Additional data for the form.
     *    @return SimpleEncoding            Submitted values or false
     *                                      if there is no such button in the
     *                                      form.
     *    @access public
     */
    function submitImage($selector, $x, $y, $additional = false) {
        $additional = $additional ? $additional : array();
        foreach ($this->images as $image) {
            if ($selector->isMatch($image)) {
                $encoding = $this->encode();
                $image->write($encoding, $x, $y);
                if ($additional) {
                    $encoding->merge($additional);
                }
                return $encoding;
            }
        }
        return false;
    }

    /**
     *    Simply submits the form without the submit button
     *    value. Used when there is only one button or it
     *    is unimportant.
     *    @return hash           Submitted values.
     *    @access public
     */
    function submit() {
        return $this->encode();
    }
}
 /* .tmp\flat\frames.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\frames.php */ ?>
<?php
/**
 *  Base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/page.php');
//require_once(dirname(__FILE__) . '/user_agent.php');
/**#@-*/

/**
 *    A composite page. Wraps a frameset page and
 *    adds subframes. The original page will be
 *    mostly ignored. Implements the SimplePage
 *    interface so as to be interchangeable.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleFrameset {
    private $frameset;
    private $frames;
    private $focus;
    private $names;

    /**
     *    Stashes the frameset page. Will make use of the
     *    browser to fetch the sub frames recursively.
     *    @param SimplePage $page        Frameset page.
     */
    function __construct($page) {
        $this->frameset = $page;
        $this->frames = array();
        $this->focus = false;
        $this->names = array();
    }

    /**
     *    Adds a parsed page to the frameset.
     *    @param SimplePage $page    Frame page.
     *    @param string $name        Name of frame in frameset.
     *    @access public
     */
    function addFrame($page, $name = false) {
        $this->frames[] = $page;
        if ($name) {
            $this->names[$name] = count($this->frames) - 1;
        }
    }

    /**
     *    Replaces existing frame with another. If the
     *    frame is nested, then the call is passed down
     *    one level.
     *    @param array $path        Path of frame in frameset.
     *    @param SimplePage $page   Frame source.
     *    @access public
     */
    function setFrame($path, $page) {
        $name = array_shift($path);
        if (isset($this->names[$name])) {
            $index = $this->names[$name];
        } else {
            $index = $name - 1;
        }
        if (count($path) == 0) {
            $this->frames[$index] = &$page;
            return;
        }
        $this->frames[$index]->setFrame($path, $page);
    }

    /**
     *    Accessor for current frame focus. Will be
     *    false if no frame has focus. Will have the nested
     *    frame focus if any.
     *    @return array     Labels or indexes of nested frames.
     *    @access public
     */
    function getFrameFocus() {
        if ($this->focus === false) {
            return array();
        }
        return array_merge(
                array($this->getPublicNameFromIndex($this->focus)),
                $this->frames[$this->focus]->getFrameFocus());
    }

    /**
     *    Turns an internal array index into the frames list
     *    into a public name, or if none, then a one offset
     *    index.
     *    @param integer $subject    Internal index.
     *    @return integer/string     Public name.
     *    @access private
     */
    protected function getPublicNameFromIndex($subject) {
        foreach ($this->names as $name => $index) {
            if ($subject == $index) {
                return $name;
            }
        }
        return $subject + 1;
    }

    /**
     *    Sets the focus by index. The integer index starts from 1.
     *    If already focused and the target frame also has frames,
     *    then the nested frame will be focused.
     *    @param integer $choice    Chosen frame.
     *    @return boolean           True if frame exists.
     *    @access public
     */
    function setFrameFocusByIndex($choice) {
        if (is_integer($this->focus)) {
            if ($this->frames[$this->focus]->hasFrames()) {
                return $this->frames[$this->focus]->setFrameFocusByIndex($choice);
            }
        }
        if (($choice < 1) || ($choice > count($this->frames))) {
            return false;
        }
        $this->focus = $choice - 1;
        return true;
    }

    /**
     *    Sets the focus by name. If already focused and the
     *    target frame also has frames, then the nested frame
     *    will be focused.
     *    @param string $name    Chosen frame.
     *    @return boolean        True if frame exists.
     *    @access public
     */
    function setFrameFocus($name) {
        if (is_integer($this->focus)) {
            if ($this->frames[$this->focus]->hasFrames()) {
                return $this->frames[$this->focus]->setFrameFocus($name);
            }
        }
        if (in_array($name, array_keys($this->names))) {
            $this->focus = $this->names[$name];
            return true;
        }
        return false;
    }

    /**
     *    Clears the frame focus.
     *    @access public
     */
    function clearFrameFocus() {
        $this->focus = false;
        $this->clearNestedFramesFocus();
    }

    /**
     *    Clears the frame focus for any nested frames.
     *    @access private
     */
    protected function clearNestedFramesFocus() {
        for ($i = 0; $i < count($this->frames); $i++) {
            $this->frames[$i]->clearFrameFocus();
        }
    }

    /**
     *    Test for the presence of a frameset.
     *    @return boolean        Always true.
     *    @access public
     */
    function hasFrames() {
        return true;
    }

    /**
     *    Accessor for frames information.
     *    @return array/string      Recursive hash of frame URL strings.
     *                              The key is either a numerical
     *                              index or the name attribute.
     *    @access public
     */
    function getFrames() {
        $report = array();
        for ($i = 0; $i < count($this->frames); $i++) {
            $report[$this->getPublicNameFromIndex($i)] =
                    $this->frames[$i]->getFrames();
        }
        return $report;
    }

    /**
     *    Accessor for raw text of either all the pages or
     *    the frame in focus.
     *    @return string        Raw unparsed content.
     *    @access public
     */
    function getRaw() {
        if (is_integer($this->focus)) {
            return $this->frames[$this->focus]->getRaw();
        }
        $raw = '';
        for ($i = 0; $i < count($this->frames); $i++) {
            $raw .= $this->frames[$i]->getRaw();
        }
        return $raw;
    }

    /**
     *    Accessor for plain text of either all the pages or
     *    the frame in focus.
     *    @return string        Plain text content.
     *    @access public
     */
    function getText() {
        if (is_integer($this->focus)) {
            return $this->frames[$this->focus]->getText();
        }
        $raw = '';
        for ($i = 0; $i < count($this->frames); $i++) {
            $raw .= ' ' . $this->frames[$i]->getText();
        }
        return trim($raw);
    }

    /**
     *    Accessor for last error.
     *    @return string        Error from last response.
     *    @access public
     */
    function getTransportError() {
        if (is_integer($this->focus)) {
            return $this->frames[$this->focus]->getTransportError();
        }
        return $this->frameset->getTransportError();
    }

    /**
     *    Request method used to fetch this frame.
     *    @return string      GET, POST or HEAD.
     *    @access public
     */
    function getMethod() {
        if (is_integer($this->focus)) {
            return $this->frames[$this->focus]->getMethod();
        }
        return $this->frameset->getMethod();
    }

    /**
     *    Original resource name.
     *    @return SimpleUrl        Current url.
     *    @access public
     */
    function getUrl() {
        if (is_integer($this->focus)) {
            $url = $this->frames[$this->focus]->getUrl();
            $url->setTarget($this->getPublicNameFromIndex($this->focus));
        } else {
            $url = $this->frameset->getUrl();
        }
        return $url;
    }

    /**
     *    Page base URL.
     *    @return SimpleUrl        Current url.
     *    @access public
     */
    function getBaseUrl() {
        if (is_integer($this->focus)) {
            $url = $this->frames[$this->focus]->getBaseUrl();
        } else {
            $url = $this->frameset->getBaseUrl();
        }
        return $url;
    }

    /**
     *    Expands expandomatic URLs into fully qualified
     *    URLs for the frameset page.
     *    @param SimpleUrl $url        Relative URL.
     *    @return SimpleUrl            Absolute URL.
     *    @access public
     */
    function expandUrl($url) {
        return $this->frameset->expandUrl($url);
    }

    /**
     *    Original request data.
     *    @return mixed              Sent content.
     *    @access public
     */
    function getRequestData() {
        if (is_integer($this->focus)) {
            return $this->frames[$this->focus]->getRequestData();
        }
        return $this->frameset->getRequestData();
    }

    /**
     *    Accessor for current MIME type.
     *    @return string    MIME type as string; e.g. 'text/html'
     *    @access public
     */
    function getMimeType() {
        if (is_integer($this->focus)) {
            return $this->frames[$this->focus]->getMimeType();
        }
        return $this->frameset->getMimeType();
    }

    /**
     *    Accessor for last response code.
     *    @return integer    Last HTTP response code received.
     *    @access public
     */
    function getResponseCode() {
        if (is_integer($this->focus)) {
            return $this->frames[$this->focus]->getResponseCode();
        }
        return $this->frameset->getResponseCode();
    }

    /**
     *    Accessor for last Authentication type. Only valid
     *    straight after a challenge (401).
     *    @return string    Description of challenge type.
     *    @access public
     */
    function getAuthentication() {
        if (is_integer($this->focus)) {
            return $this->frames[$this->focus]->getAuthentication();
        }
        return $this->frameset->getAuthentication();
    }

    /**
     *    Accessor for last Authentication realm. Only valid
     *    straight after a challenge (401).
     *    @return string    Name of security realm.
     *    @access public
     */
    function getRealm() {
        if (is_integer($this->focus)) {
            return $this->frames[$this->focus]->getRealm();
        }
        return $this->frameset->getRealm();
    }

    /**
     *    Accessor for outgoing header information.
     *    @return string      Header block.
     *    @access public
     */
    function getRequest() {
        if (is_integer($this->focus)) {
            return $this->frames[$this->focus]->getRequest();
        }
        return $this->frameset->getRequest();
    }

    /**
     *    Accessor for raw header information.
     *    @return string      Header block.
     *    @access public
     */
    function getHeaders() {
        if (is_integer($this->focus)) {
            return $this->frames[$this->focus]->getHeaders();
        }
        return $this->frameset->getHeaders();
    }

    /**
     *    Accessor for parsed title.
     *    @return string     Title or false if no title is present.
     *    @access public
     */
    function getTitle() {
        return $this->frameset->getTitle();
    }

    /**
     *    Accessor for a list of all fixed links.
     *    @return array   List of urls as strings.
     *    @access public
     */
    function getUrls() {
        if (is_integer($this->focus)) {
            return $this->frames[$this->focus]->getUrls();
        }
        $urls = array();
        foreach ($this->frames as $frame) {
            $urls = array_merge($urls, $frame->getUrls());
        }
        return array_values(array_unique($urls));
    }

    /**
     *    Accessor for URLs by the link label. Label will match
     *    regardess of whitespace issues and case.
     *    @param string $label    Text of link.
     *    @return array           List of links with that label.
     *    @access public
     */
    function getUrlsByLabel($label) {
        if (is_integer($this->focus)) {
            return $this->tagUrlsWithFrame(
                    $this->frames[$this->focus]->getUrlsByLabel($label),
                    $this->focus);
        }
        $urls = array();
        foreach ($this->frames as $index => $frame) {
            $urls = array_merge(
                    $urls,
                    $this->tagUrlsWithFrame(
                                $frame->getUrlsByLabel($label),
                                $index));
        }
        return $urls;
    }

    /**
     *    Accessor for a URL by the id attribute. If in a frameset
     *    then the first link found with that ID attribute is
     *    returned only. Focus on a frame if you want one from
     *    a specific part of the frameset.
     *    @param string $id       Id attribute of link.
     *    @return string          URL with that id.
     *    @access public
     */
    function getUrlById($id) {
        foreach ($this->frames as $index => $frame) {
            if ($url = $frame->getUrlById($id)) {
                if (! $url->gettarget()) {
                    $url->setTarget($this->getPublicNameFromIndex($index));
                }
                return $url;
            }
        }
        return false;
    }

    /**
     *    Attaches the intended frame index to a list of URLs.
     *    @param array $urls        List of SimpleUrls.
     *    @param string $frame      Name of frame or index.
     *    @return array             List of tagged URLs.
     *    @access private
     */
    protected function tagUrlsWithFrame($urls, $frame) {
        $tagged = array();
        foreach ($urls as $url) {
            if (! $url->getTarget()) {
                $url->setTarget($this->getPublicNameFromIndex($frame));
            }
            $tagged[] = $url;
        }
        return $tagged;
    }

    /**
     *    Finds a held form by button label. Will only
     *    search correctly built forms.
     *    @param SimpleSelector $selector       Button finder.
     *    @return SimpleForm                    Form object containing
     *                                          the button.
     *    @access public
     */
    function getFormBySubmit($selector) {
        return $this->findForm('getFormBySubmit', $selector);
    }

    /**
     *    Finds a held form by image using a selector.
     *    Will only search correctly built forms. The first
     *    form found either within the focused frame, or
     *    across frames, will be the one returned.
     *    @param SimpleSelector $selector  Image finder.
     *    @return SimpleForm               Form object containing
     *                                     the image.
     *    @access public
     */
    function getFormByImage($selector) {
        return $this->findForm('getFormByImage', $selector);
    }

    /**
     *    Finds a held form by the form ID. A way of
     *    identifying a specific form when we have control
     *    of the HTML code. The first form found
     *    either within the focused frame, or across frames,
     *    will be the one returned.
     *    @param string $id     Form label.
     *    @return SimpleForm    Form object containing the matching ID.
     *    @access public
     */
    function getFormById($id) {
        return $this->findForm('getFormById', $id);
    }

    /**
        *    General form finder. Will search all the frames or
        *    just the one in focus.
        *    @param string $method    Method to use to find in a page.
        *    @param string $attribute Label, name or ID.
        *    @return SimpleForm    Form object containing the matching ID.
        *    @access private
        */
    protected function findForm($method, $attribute) {
        if (is_integer($this->focus)) {
            return $this->findFormInFrame(
                    $this->frames[$this->focus],
                    $this->focus,
                    $method,
                    $attribute);
        }
        for ($i = 0; $i < count($this->frames); $i++) {
            $form = $this->findFormInFrame(
                    $this->frames[$i],
                    $i,
                    $method,
                    $attribute);
            if ($form) {
                return $form;
            }
        }
        $null = null;
        return $null;
    }

    /**
     *    Finds a form in a page using a form finding method. Will
     *    also tag the form with the frame name it belongs in.
     *    @param SimplePage $page  Page content of frame.
     *    @param integer $index    Internal frame representation.
     *    @param string $method    Method to use to find in a page.
     *    @param string $attribute Label, name or ID.
     *    @return SimpleForm       Form object containing the matching ID.
     *    @access private
     */
    protected function findFormInFrame($page, $index, $method, $attribute) {
        $form = $this->frames[$index]->$method($attribute);
        if (isset($form)) {
            $form->setDefaultTarget($this->getPublicNameFromIndex($index));
        }
        return $form;
    }

    /**
     *    Sets a field on each form in which the field is
     *    available.
     *    @param SimpleSelector $selector    Field finder.
     *    @param string $value               Value to set field to.
     *    @return boolean                    True if value is valid.
     *    @access public
     */
    function setField($selector, $value) {
        if (is_integer($this->focus)) {
            $this->frames[$this->focus]->setField($selector, $value);
        } else {
            for ($i = 0; $i < count($this->frames); $i++) {
                $this->frames[$i]->setField($selector, $value);
            }
        }
    }

    /**
     *    Accessor for a form element value within a page.
     *    @param SimpleSelector $selector    Field finder.
     *    @return string/boolean             A string if the field is
     *                                       present, false if unchecked
     *                                       and null if missing.
     *    @access public
     */
    function getField($selector) {
        for ($i = 0; $i < count($this->frames); $i++) {
            $value = $this->frames[$i]->getField($selector);
            if (isset($value)) {
                return $value;
            }
        }
        return null;
    }
}
 /* .tmp\flat\invoker.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\invoker.php */ ?>
<?php
/**
 *  Base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 * Includes SimpleTest files and defined the root constant
 * for dependent libraries.
 */
//require_once(dirname(__FILE__) . '/errors.php');
//require_once(dirname(__FILE__) . '/compatibility.php');
//require_once(dirname(__FILE__) . '/scorer.php');
//require_once(dirname(__FILE__) . '/expectation.php');
//require_once(dirname(__FILE__) . '/dumper.php');
if (! defined('SIMPLE_TEST')) {
    define('SIMPLE_TEST', dirname(__FILE__) . '/');
}
/**#@-*/

/**
 *    This is called by the class runner to run a
 *    single test method. Will also run the setUp()
 *    and tearDown() methods.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleInvoker {
    private $test_case;

    /**
     *    Stashes the test case for later.
     *    @param SimpleTestCase $test_case  Test case to run.
     */
    function __construct($test_case) {
        $this->test_case = $test_case;
    }

    /**
     *    Accessor for test case being run.
     *    @return SimpleTestCase    Test case.
     *    @access public
     */
    function getTestCase() {
        return $this->test_case;
    }

    /**
     *    Runs test level set up. Used for changing
     *    the mechanics of base test cases.
     *    @param string $method    Test method to call.
     *    @access public
     */
    function before($method) {
        $this->test_case->before($method);
    }

    /**
     *    Invokes a test method and buffered with setUp()
     *    and tearDown() calls.
     *    @param string $method    Test method to call.
     *    @access public
     */
    function invoke($method) {
        $this->test_case->setUp();
        $this->test_case->$method();
        $this->test_case->tearDown();
    }

    /**
     *    Runs test level clean up. Used for changing
     *    the mechanics of base test cases.
     *    @param string $method    Test method to call.
     *    @access public
     */
    function after($method) {
        $this->test_case->after($method);
    }
}

/**
 *    Do nothing decorator. Just passes the invocation
 *    straight through.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleInvokerDecorator {
    private $invoker;

    /**
     *    Stores the invoker to wrap.
     *    @param SimpleInvoker $invoker  Test method runner.
     */
    function __construct($invoker) {
        $this->invoker = $invoker;
    }

    /**
     *    Accessor for test case being run.
     *    @return SimpleTestCase    Test case.
     *    @access public
     */
    function getTestCase() {
        return $this->invoker->getTestCase();
    }

    /**
     *    Runs test level set up. Used for changing
     *    the mechanics of base test cases.
     *    @param string $method    Test method to call.
     *    @access public
     */
    function before($method) {
        $this->invoker->before($method);
    }

    /**
     *    Invokes a test method and buffered with setUp()
     *    and tearDown() calls.
     *    @param string $method    Test method to call.
     *    @access public
     */
    function invoke($method) {
        $this->invoker->invoke($method);
    }

    /**
     *    Runs test level clean up. Used for changing
     *    the mechanics of base test cases.
     *    @param string $method    Test method to call.
     *    @access public
     */
    function after($method) {
        $this->invoker->after($method);
    }
}
 /* .tmp\flat\page.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\page.php */ ?>
<?php
/**
 *  Base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */

/**#@+
    *   include other SimpleTest class files
    */
//require_once(dirname(__FILE__) . '/http.php');
//require_once(dirname(__FILE__) . '/php_parser.php');
//require_once(dirname(__FILE__) . '/tag.php');
//require_once(dirname(__FILE__) . '/form.php');
//require_once(dirname(__FILE__) . '/selector.php');
/**#@-*/

/**
 *    A wrapper for a web page.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimplePage {
    private $links = array();
    private $title = false;
    private $last_widget;
    private $label;
    private $forms = array();
    private $frames = array();
    private $transport_error;
    private $raw;
    private $text = false;
    private $sent;
    private $headers;
    private $method;
    private $url;
    private $base = false;
    private $request_data;

    /**
     *    Parses a page ready to access it's contents.
     *    @param SimpleHttpResponse $response     Result of HTTP fetch.
     *    @access public
     */
    function __construct($response = false) {
        if ($response) {
            $this->extractResponse($response);
        } else {
            $this->noResponse();
        }
    }

    /**
     *    Extracts all of the response information.
     *    @param SimpleHttpResponse $response    Response being parsed.
     *    @access private
     */
    protected function extractResponse($response) {
        $this->transport_error = $response->getError();
        $this->raw = $response->getContent();
        $this->sent = $response->getSent();
        $this->headers = $response->getHeaders();
        $this->method = $response->getMethod();
        $this->url = $response->getUrl();
        $this->request_data = $response->getRequestData();
    }

    /**
     *    Sets up a missing response.
     *    @access private
     */
    protected function noResponse() {
        $this->transport_error = 'No page fetched yet';
        $this->raw = false;
        $this->sent = false;
        $this->headers = false;
        $this->method = 'GET';
        $this->url = false;
        $this->request_data = false;
    }

    /**
     *    Original request as bytes sent down the wire.
     *    @return mixed              Sent content.
     *    @access public
     */
    function getRequest() {
        return $this->sent;
    }

    /**
     *    Accessor for raw text of page.
     *    @return string        Raw unparsed content.
     *    @access public
     */
    function getRaw() {
        return $this->raw;
    }

    /**
     *    Accessor for plain text of page as a text browser
     *    would see it.
     *    @return string        Plain text of page.
     *    @access public
     */
    function getText() {
        if (! $this->text) {
            $this->text = SimplePage::normalise($this->raw);
        }
        return $this->text;
    }

    /**
     *    Accessor for raw headers of page.
     *    @return string       Header block as text.
     *    @access public
     */
    function getHeaders() {
        if ($this->headers) {
            return $this->headers->getRaw();
        }
        return false;
    }

    /**
     *    Original request method.
     *    @return string        GET, POST or HEAD.
     *    @access public
     */
    function getMethod() {
        return $this->method;
    }

    /**
     *    Original resource name.
     *    @return SimpleUrl        Current url.
     *    @access public
     */
    function getUrl() {
        return $this->url;
    }

    /**
     *    Base URL if set via BASE tag page url otherwise
     *    @return SimpleUrl        Base url.
     *    @access public
     */
    function getBaseUrl() {
        return $this->base;
    }

    /**
     *    Original request data.
     *    @return mixed              Sent content.
     *    @access public
     */
    function getRequestData() {
        return $this->request_data;
    }

    /**
     *    Accessor for last error.
     *    @return string        Error from last response.
     *    @access public
     */
    function getTransportError() {
        return $this->transport_error;
    }

    /**
     *    Accessor for current MIME type.
     *    @return string    MIME type as string; e.g. 'text/html'
     *    @access public
     */
    function getMimeType() {
        if ($this->headers) {
            return $this->headers->getMimeType();
        }
        return false;
    }

    /**
     *    Accessor for HTTP response code.
     *    @return integer    HTTP response code received.
     *    @access public
     */
    function getResponseCode() {
        if ($this->headers) {
            return $this->headers->getResponseCode();
        }
        return false;
    }

    /**
     *    Accessor for last Authentication type. Only valid
     *    straight after a challenge (401).
     *    @return string    Description of challenge type.
     *    @access public
     */
    function getAuthentication() {
        if ($this->headers) {
            return $this->headers->getAuthentication();
        }
        return false;
    }

    /**
     *    Accessor for last Authentication realm. Only valid
     *    straight after a challenge (401).
     *    @return string    Name of security realm.
     *    @access public
     */
    function getRealm() {
        if ($this->headers) {
            return $this->headers->getRealm();
        }
        return false;
    }

    /**
     *    Accessor for current frame focus. Will be
     *    false as no frames.
     *    @return array    Always empty.
     *    @access public
     */
    function getFrameFocus() {
        return array();
    }

    /**
     *    Sets the focus by index. The integer index starts from 1.
     *    @param integer $choice    Chosen frame.
     *    @return boolean           Always false.
     *    @access public
     */
    function setFrameFocusByIndex($choice) {
        return false;
    }

    /**
     *    Sets the focus by name. Always fails for a leaf page.
     *    @param string $name    Chosen frame.
     *    @return boolean        False as no frames.
     *    @access public
     */
    function setFrameFocus($name) {
        return false;
    }

    /**
     *    Clears the frame focus. Does nothing for a leaf page.
     *    @access public
     */
    function clearFrameFocus() {
    }

    /**
     *    TODO: write docs
     */
    function setFrames($frames) {
        $this->frames = $frames;
    }

    /**
     *    Test to see if link is an absolute one.
     *    @param string $url     Url to test.
     *    @return boolean        True if absolute.
     *    @access protected
     */
    protected function linkIsAbsolute($url) {
        $parsed = new SimpleUrl($url);
        return (boolean)($parsed->getScheme() && $parsed->getHost());
    }

    /**
     *    Adds a link to the page.
     *    @param SimpleAnchorTag $tag      Link to accept.
     */
    function addLink($tag) {
        $this->links[] = $tag;
    }

    /**
     *    Set the forms
     *    @param array $forms           An array of SimpleForm objects
     */
    function setForms($forms) {
        $this->forms = $forms;
    }

    /**
     *    Test for the presence of a frameset.
     *    @return boolean        True if frameset.
     *    @access public
     */
    function hasFrames() {
        return count($this->frames) > 0;
    }

    /**
     *    Accessor for frame name and source URL for every frame that
     *    will need to be loaded. Immediate children only.
     *    @return boolean/array     False if no frameset or
     *                              otherwise a hash of frame URLs.
     *                              The key is either a numerical
     *                              base one index or the name attribute.
     *    @access public
     */
    function getFrameset() {
        if (! $this->hasFrames()) {
            return false;
        }
        $urls = array();
        for ($i = 0; $i < count($this->frames); $i++) {
            $name = $this->frames[$i]->getAttribute('name');
            $url = new SimpleUrl($this->frames[$i]->getAttribute('src'));
            $urls[$name ? $name : $i + 1] = $this->expandUrl($url);
        }
        return $urls;
    }

    /**
     *    Fetches a list of loaded frames.
     *    @return array/string    Just the URL for a single page.
     *    @access public
     */
    function getFrames() {
        $url = $this->expandUrl($this->getUrl());
        return $url->asString();
    }

    /**
     *    Accessor for a list of all links.
     *    @return array   List of urls with scheme of
     *                    http or https and hostname.
     *    @access public
     */
    function getUrls() {
        $all = array();
        foreach ($this->links as $link) {
            $url = $this->getUrlFromLink($link);
            $all[] = $url->asString();
        }
        return $all;
    }

    /**
     *    Accessor for URLs by the link label. Label will match
     *    regardess of whitespace issues and case.
     *    @param string $label    Text of link.
     *    @return array           List of links with that label.
     *    @access public
     */
    function getUrlsByLabel($label) {
        $matches = array();
        foreach ($this->links as $link) {
            if ($link->getText() == $label) {
                $matches[] = $this->getUrlFromLink($link);
            }
        }
        return $matches;
    }

    /**
     *    Accessor for a URL by the id attribute.
     *    @param string $id       Id attribute of link.
     *    @return SimpleUrl       URL with that id of false if none.
     *    @access public
     */
    function getUrlById($id) {
        foreach ($this->links as $link) {
            if ($link->getAttribute('id') === (string)$id) {
                return $this->getUrlFromLink($link);
            }
        }
        return false;
    }

    /**
     *    Converts a link tag into a target URL.
     *    @param SimpleAnchor $link    Parsed link.
     *    @return SimpleUrl            URL with frame target if any.
     *    @access private
     */
    protected function getUrlFromLink($link) {
        $url = $this->expandUrl($link->getHref());
        if ($link->getAttribute('target')) {
            $url->setTarget($link->getAttribute('target'));
        }
        return $url;
    }

    /**
     *    Expands expandomatic URLs into fully qualified
     *    URLs.
     *    @param SimpleUrl $url        Relative URL.
     *    @return SimpleUrl            Absolute URL.
     *    @access public
     */
    function expandUrl($url) {
        if (! is_object($url)) {
            $url = new SimpleUrl($url);
        }
        $location = $this->getBaseUrl() ? $this->getBaseUrl() : new SimpleUrl();
        return $url->makeAbsolute($location->makeAbsolute($this->getUrl()));
    }

    /**
     *    Sets the base url for the page.
     *    @param string $url    Base URL for page.
     */
    function setBase($url) {
        $this->base = new SimpleUrl($url);
    }

    /**
     *    Sets the title tag contents.
     *    @param SimpleTitleTag $tag    Title of page.
     */
    function setTitle($tag) {
        $this->title = $tag;
    }

    /**
     *    Accessor for parsed title.
     *    @return string     Title or false if no title is present.
     *    @access public
     */
    function getTitle() {
        if ($this->title) {
            return $this->title->getText();
        }
        return false;
    }

    /**
     *    Finds a held form by button label. Will only
     *    search correctly built forms.
     *    @param SimpleSelector $selector       Button finder.
     *    @return SimpleForm                    Form object containing
     *                                          the button.
     *    @access public
     */
    function getFormBySubmit($selector) {
        for ($i = 0; $i < count($this->forms); $i++) {
            if ($this->forms[$i]->hasSubmit($selector)) {
                return $this->forms[$i];
            }
        }
        return null;
    }

    /**
     *    Finds a held form by image using a selector.
     *    Will only search correctly built forms.
     *    @param SimpleSelector $selector  Image finder.
     *    @return SimpleForm               Form object containing
     *                                     the image.
     *    @access public
     */
    function getFormByImage($selector) {
        for ($i = 0; $i < count($this->forms); $i++) {
            if ($this->forms[$i]->hasImage($selector)) {
                return $this->forms[$i];
            }
        }
        return null;
    }

    /**
     *    Finds a held form by the form ID. A way of
     *    identifying a specific form when we have control
     *    of the HTML code.
     *    @param string $id     Form label.
     *    @return SimpleForm    Form object containing the matching ID.
     *    @access public
     */
    function getFormById($id) {
        for ($i = 0; $i < count($this->forms); $i++) {
            if ($this->forms[$i]->getId() == $id) {
                return $this->forms[$i];
            }
        }
        return null;
    }

    /**
     *    Sets a field on each form in which the field is
     *    available.
     *    @param SimpleSelector $selector    Field finder.
     *    @param string $value               Value to set field to.
     *    @return boolean                    True if value is valid.
     *    @access public
     */
    function setField($selector, $value, $position=false) {
        $is_set = false;
        for ($i = 0; $i < count($this->forms); $i++) {
            if ($this->forms[$i]->setField($selector, $value, $position)) {
                $is_set = true;
            }
        }
        return $is_set;
    }

    /**
     *    Accessor for a form element value within a page.
     *    @param SimpleSelector $selector    Field finder.
     *    @return string/boolean             A string if the field is
     *                                       present, false if unchecked
     *                                       and null if missing.
     *    @access public
     */
    function getField($selector) {
        for ($i = 0; $i < count($this->forms); $i++) {
            $value = $this->forms[$i]->getValue($selector);
            if (isset($value)) {
                return $value;
            }
        }
        return null;
    }

    /**
     *    Turns HTML into text browser visible text. Images
     *    are converted to their alt text and tags are supressed.
     *    Entities are converted to their visible representation.
     *    @param string $html        HTML to convert.
     *    @return string             Plain text.
     *    @access public
     */
    static function normalise($html) {
        $text = preg_replace('#<!--.*?-->#si', '', $html);
        $text = preg_replace('#<(script|option|textarea)[^>]*>.*?</\1>#si', '', $text);
        $text = preg_replace('#<img[^>]*alt\s*=\s*("([^"]*)"|\'([^\']*)\'|([a-zA-Z_]+))[^>]*>#', ' \2\3\4 ', $text);
        $text = preg_replace('#<[^>]*>#', '', $text);
        $text = html_entity_decode($text, ENT_QUOTES);
        $text = preg_replace('#\s+#', ' ', $text);
        return trim(trim($text), "\xA0");        // TODO: The \xAO is a &nbsp;. Add a test for this.
    }
}
 /* .tmp\flat\php_parser.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\php_parser.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */

/**#@+
 * Lexer mode stack constants
 */
foreach (array('LEXER_ENTER', 'LEXER_MATCHED',
                'LEXER_UNMATCHED', 'LEXER_EXIT',
                'LEXER_SPECIAL') as $i => $constant) {
    if (! defined($constant)) {
        define($constant, $i + 1);
    }
}
/**#@-*/

/**
 *    Compounded regular expression. Any of
 *    the contained patterns could match and
 *    when one does, it's label is returned.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class ParallelRegex {
    private $patterns;
    private $labels;
    private $regex;
    private $case;

    /**
     *    Constructor. Starts with no patterns.
     *    @param boolean $case    True for case sensitive, false
     *                            for insensitive.
     *    @access public
     */
    function __construct($case) {
        $this->case = $case;
        $this->patterns = array();
        $this->labels = array();
        $this->regex = null;
    }

    /**
     *    Adds a pattern with an optional label.
     *    @param string $pattern      Perl style regex, but ( and )
     *                                lose the usual meaning.
     *    @param string $label        Label of regex to be returned
     *                                on a match.
     *    @access public
     */
    function addPattern($pattern, $label = true) {
        $count = count($this->patterns);
        $this->patterns[$count] = $pattern;
        $this->labels[$count] = $label;
        $this->regex = null;
    }

    /**
     *    Attempts to match all patterns at once against
     *    a string.
     *    @param string $subject      String to match against.
     *    @param string $match        First matched portion of
     *                                subject.
     *    @return boolean             True on success.
     *    @access public
     */
    function match($subject, &$match) {
        if (count($this->patterns) == 0) {
            return false;
        }
        if (! preg_match($this->getCompoundedRegex(), $subject, $matches)) {
            $match = '';
            return false;
        }
        $match = $matches[0];
        for ($i = 1; $i < count($matches); $i++) {
            if ($matches[$i]) {
                return $this->labels[$i - 1];
            }
        }
        return true;
    }

    /**
     *    Compounds the patterns into a single
     *    regular expression separated with the
     *    "or" operator. Caches the regex.
     *    Will automatically escape (, ) and / tokens.
     *    @param array $patterns    List of patterns in order.
     *    @access private
     */
    protected function getCompoundedRegex() {
        if ($this->regex == null) {
            for ($i = 0, $count = count($this->patterns); $i < $count; $i++) {
                $this->patterns[$i] = '(' . str_replace(
                        array('/', '(', ')'),
                        array('\/', '\(', '\)'),
                        $this->patterns[$i]) . ')';
            }
            $this->regex = "/" . implode("|", $this->patterns) . "/" . $this->getPerlMatchingFlags();
        }
        return $this->regex;
    }

    /**
     *    Accessor for perl regex mode flags to use.
     *    @return string       Perl regex flags.
     *    @access private
     */
    protected function getPerlMatchingFlags() {
        return ($this->case ? "msS" : "msSi");
    }
}

/**
 *    States for a stack machine.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleStateStack {
    private $stack;

    /**
     *    Constructor. Starts in named state.
     *    @param string $start        Starting state name.
     *    @access public
     */
    function __construct($start) {
        $this->stack = array($start);
    }

    /**
     *    Accessor for current state.
     *    @return string       State.
     *    @access public
     */
    function getCurrent() {
        return $this->stack[count($this->stack) - 1];
    }

    /**
     *    Adds a state to the stack and sets it
     *    to be the current state.
     *    @param string $state        New state.
     *    @access public
     */
    function enter($state) {
        array_push($this->stack, $state);
    }

    /**
     *    Leaves the current state and reverts
     *    to the previous one.
     *    @return boolean    False if we drop off
     *                       the bottom of the list.
     *    @access public
     */
    function leave() {
        if (count($this->stack) == 1) {
            return false;
        }
        array_pop($this->stack);
        return true;
    }
}

/**
 *    Accepts text and breaks it into tokens.
 *    Some optimisation to make the sure the
 *    content is only scanned by the PHP regex
 *    parser once. Lexer modes must not start
 *    with leading underscores.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleLexer {
    private $regexes;
    private $parser;
    private $mode;
    private $mode_handlers;
    private $case;

    /**
     *    Sets up the lexer in case insensitive matching
     *    by default.
     *    @param SimpleSaxParser $parser  Handling strategy by
     *                                    reference.
     *    @param string $start            Starting handler.
     *    @param boolean $case            True for case sensitive.
     *    @access public
     */
    function __construct($parser, $start = "accept", $case = false) {
        $this->case = $case;
        $this->regexes = array();
        $this->parser = $parser;
        $this->mode = new SimpleStateStack($start);
        $this->mode_handlers = array($start => $start);
    }

    /**
     *    Adds a token search pattern for a particular
     *    parsing mode. The pattern does not change the
     *    current mode.
     *    @param string $pattern      Perl style regex, but ( and )
     *                                lose the usual meaning.
     *    @param string $mode         Should only apply this
     *                                pattern when dealing with
     *                                this type of input.
     *    @access public
     */
    function addPattern($pattern, $mode = "accept") {
        if (! isset($this->regexes[$mode])) {
            $this->regexes[$mode] = new ParallelRegex($this->case);
        }
        $this->regexes[$mode]->addPattern($pattern);
        if (! isset($this->mode_handlers[$mode])) {
            $this->mode_handlers[$mode] = $mode;
        }
    }

    /**
     *    Adds a pattern that will enter a new parsing
     *    mode. Useful for entering parenthesis, strings,
     *    tags, etc.
     *    @param string $pattern      Perl style regex, but ( and )
     *                                lose the usual meaning.
     *    @param string $mode         Should only apply this
     *                                pattern when dealing with
     *                                this type of input.
     *    @param string $new_mode     Change parsing to this new
     *                                nested mode.
     *    @access public
     */
    function addEntryPattern($pattern, $mode, $new_mode) {
        if (! isset($this->regexes[$mode])) {
            $this->regexes[$mode] = new ParallelRegex($this->case);
        }
        $this->regexes[$mode]->addPattern($pattern, $new_mode);
        if (! isset($this->mode_handlers[$new_mode])) {
            $this->mode_handlers[$new_mode] = $new_mode;
        }
    }

    /**
     *    Adds a pattern that will exit the current mode
     *    and re-enter the previous one.
     *    @param string $pattern      Perl style regex, but ( and )
     *                                lose the usual meaning.
     *    @param string $mode         Mode to leave.
     *    @access public
     */
    function addExitPattern($pattern, $mode) {
        if (! isset($this->regexes[$mode])) {
            $this->regexes[$mode] = new ParallelRegex($this->case);
        }
        $this->regexes[$mode]->addPattern($pattern, "__exit");
        if (! isset($this->mode_handlers[$mode])) {
            $this->mode_handlers[$mode] = $mode;
        }
    }

    /**
     *    Adds a pattern that has a special mode. Acts as an entry
     *    and exit pattern in one go, effectively calling a special
     *    parser handler for this token only.
     *    @param string $pattern      Perl style regex, but ( and )
     *                                lose the usual meaning.
     *    @param string $mode         Should only apply this
     *                                pattern when dealing with
     *                                this type of input.
     *    @param string $special      Use this mode for this one token.
     *    @access public
     */
    function addSpecialPattern($pattern, $mode, $special) {
        if (! isset($this->regexes[$mode])) {
            $this->regexes[$mode] = new ParallelRegex($this->case);
        }
        $this->regexes[$mode]->addPattern($pattern, "_$special");
        if (! isset($this->mode_handlers[$special])) {
            $this->mode_handlers[$special] = $special;
        }
    }

    /**
     *    Adds a mapping from a mode to another handler.
     *    @param string $mode        Mode to be remapped.
     *    @param string $handler     New target handler.
     *    @access public
     */
    function mapHandler($mode, $handler) {
        $this->mode_handlers[$mode] = $handler;
    }

    /**
     *    Splits the page text into tokens. Will fail
     *    if the handlers report an error or if no
     *    content is consumed. If successful then each
     *    unparsed and parsed token invokes a call to the
     *    held listener.
     *    @param string $raw        Raw HTML text.
     *    @return boolean           True on success, else false.
     *    @access public
     */
    function parse($raw) {
        if (! isset($this->parser)) {
            return false;
        }
        $length = strlen($raw);
        while (is_array($parsed = $this->reduce($raw))) {
            list($raw, $unmatched, $matched, $mode) = $parsed;
            if (! $this->dispatchTokens($unmatched, $matched, $mode)) {
                return false;
            }
            if ($raw === '') {
                return true;
            }
            if (strlen($raw) == $length) {
                return false;
            }
            $length = strlen($raw);
        }
        if (! $parsed) {
            return false;
        }
        return $this->invokeParser($raw, LEXER_UNMATCHED);
    }

    /**
     *    Sends the matched token and any leading unmatched
     *    text to the parser changing the lexer to a new
     *    mode if one is listed.
     *    @param string $unmatched    Unmatched leading portion.
     *    @param string $matched      Actual token match.
     *    @param string $mode         Mode after match. A boolean
     *                                false mode causes no change.
     *    @return boolean             False if there was any error
     *                                from the parser.
     *    @access private
     */
    protected function dispatchTokens($unmatched, $matched, $mode = false) {
        if (! $this->invokeParser($unmatched, LEXER_UNMATCHED)) {
            return false;
        }
        if (is_bool($mode)) {
            return $this->invokeParser($matched, LEXER_MATCHED);
        }
        if ($this->isModeEnd($mode)) {
            if (! $this->invokeParser($matched, LEXER_EXIT)) {
                return false;
            }
            return $this->mode->leave();
        }
        if ($this->isSpecialMode($mode)) {
            $this->mode->enter($this->decodeSpecial($mode));
            if (! $this->invokeParser($matched, LEXER_SPECIAL)) {
                return false;
            }
            return $this->mode->leave();
        }
        $this->mode->enter($mode);
        return $this->invokeParser($matched, LEXER_ENTER);
    }

    /**
     *    Tests to see if the new mode is actually to leave
     *    the current mode and pop an item from the matching
     *    mode stack.
     *    @param string $mode    Mode to test.
     *    @return boolean        True if this is the exit mode.
     *    @access private
     */
    protected function isModeEnd($mode) {
        return ($mode === "__exit");
    }

    /**
     *    Test to see if the mode is one where this mode
     *    is entered for this token only and automatically
     *    leaves immediately afterwoods.
     *    @param string $mode    Mode to test.
     *    @return boolean        True if this is the exit mode.
     *    @access private
     */
    protected function isSpecialMode($mode) {
        return (strncmp($mode, "_", 1) == 0);
    }

    /**
     *    Strips the magic underscore marking single token
     *    modes.
     *    @param string $mode    Mode to decode.
     *    @return string         Underlying mode name.
     *    @access private
     */
    protected function decodeSpecial($mode) {
        return substr($mode, 1);
    }

    /**
     *    Calls the parser method named after the current
     *    mode. Empty content will be ignored. The lexer
     *    has a parser handler for each mode in the lexer.
     *    @param string $content        Text parsed.
     *    @param boolean $is_match      Token is recognised rather
     *                                  than unparsed data.
     *    @access private
     */
    protected function invokeParser($content, $is_match) {
        if (($content === '') || ($content === false)) {
            return true;
        }
        $handler = $this->mode_handlers[$this->mode->getCurrent()];
        return $this->parser->$handler($content, $is_match);
    }

    /**
     *    Tries to match a chunk of text and if successful
     *    removes the recognised chunk and any leading
     *    unparsed data. Empty strings will not be matched.
     *    @param string $raw         The subject to parse. This is the
     *                               content that will be eaten.
     *    @return array/boolean      Three item list of unparsed
     *                               content followed by the
     *                               recognised token and finally the
     *                               action the parser is to take.
     *                               True if no match, false if there
     *                               is a parsing error.
     *    @access private
     */
    protected function reduce($raw) {
        if ($action = $this->regexes[$this->mode->getCurrent()]->match($raw, $match)) {
            $unparsed_character_count = strpos($raw, $match);
            $unparsed = substr($raw, 0, $unparsed_character_count);
            $raw = substr($raw, $unparsed_character_count + strlen($match));
            return array($raw, $unparsed, $match, $action);
        }
        return true;
    }
}

/**
 *    Breaks HTML into SAX events.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleHtmlLexer extends SimpleLexer {

    /**
     *    Sets up the lexer with case insensitive matching
     *    and adds the HTML handlers.
     *    @param SimpleSaxParser $parser  Handling strategy by
     *                                    reference.
     *    @access public
     */
    function __construct($parser) {
        parent::__construct($parser, 'text');
        $this->mapHandler('text', 'acceptTextToken');
        $this->addSkipping();
        foreach ($this->getParsedTags() as $tag) {
            $this->addTag($tag);
        }
        $this->addInTagTokens();
    }

    /**
     *    List of parsed tags. Others are ignored.
     *    @return array        List of searched for tags.
     *    @access private
     */
    protected function getParsedTags() {
        return array('a', 'base', 'title', 'form', 'input', 'button', 'textarea', 'select',
                'option', 'frameset', 'frame', 'label');
    }

    /**
     *    The lexer has to skip certain sections such
     *    as server code, client code and styles.
     *    @access private
     */
    protected function addSkipping() {
        $this->mapHandler('css', 'ignore');
        $this->addEntryPattern('<style', 'text', 'css');
        $this->addExitPattern('</style>', 'css');
        $this->mapHandler('js', 'ignore');
        $this->addEntryPattern('<script', 'text', 'js');
        $this->addExitPattern('</script>', 'js');
        $this->mapHandler('comment', 'ignore');
        $this->addEntryPattern('<!--', 'text', 'comment');
        $this->addExitPattern('-->', 'comment');
    }

    /**
     *    Pattern matches to start and end a tag.
     *    @param string $tag          Name of tag to scan for.
     *    @access private
     */
    protected function addTag($tag) {
        $this->addSpecialPattern("</$tag>", 'text', 'acceptEndToken');
        $this->addEntryPattern("<$tag", 'text', 'tag');
    }

    /**
     *    Pattern matches to parse the inside of a tag
     *    including the attributes and their quoting.
     *    @access private
     */
    protected function addInTagTokens() {
        $this->mapHandler('tag', 'acceptStartToken');
        $this->addSpecialPattern('\s+', 'tag', 'ignore');
        $this->addAttributeTokens();
        $this->addExitPattern('/>', 'tag');
        $this->addExitPattern('>', 'tag');
    }

    /**
     *    Matches attributes that are either single quoted,
     *    double quoted or unquoted.
     *    @access private
     */
    protected function addAttributeTokens() {
        $this->mapHandler('dq_attribute', 'acceptAttributeToken');
        $this->addEntryPattern('=\s*"', 'tag', 'dq_attribute');
        $this->addPattern("\\\\\"", 'dq_attribute');
        $this->addExitPattern('"', 'dq_attribute');
        $this->mapHandler('sq_attribute', 'acceptAttributeToken');
        $this->addEntryPattern("=\s*'", 'tag', 'sq_attribute');
        $this->addPattern("\\\\'", 'sq_attribute');
        $this->addExitPattern("'", 'sq_attribute');
        $this->mapHandler('uq_attribute', 'acceptAttributeToken');
        $this->addSpecialPattern('=\s*[^>\s]*', 'tag', 'uq_attribute');
    }
}

/**
 *    Converts HTML tokens into selected SAX events.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleHtmlSaxParser {
    private $lexer;
    private $listener;
    private $tag;
    private $attributes;
    private $current_attribute;

    /**
     *    Sets the listener.
     *    @param SimplePhpPageBuilder $listener    SAX event handler.
     *    @access public
     */
    function __construct($listener) {
        $this->listener = $listener;
        $this->lexer = $this->createLexer($this);
        $this->tag = '';
        $this->attributes = array();
        $this->current_attribute = '';
    }

    /**
     *    Runs the content through the lexer which
     *    should call back to the acceptors.
     *    @param string $raw      Page text to parse.
     *    @return boolean         False if parse error.
     *    @access public
     */
    function parse($raw) {
        return $this->lexer->parse($raw);
    }

    /**
     *    Sets up the matching lexer. Starts in 'text' mode.
     *    @param SimpleSaxParser $parser    Event generator, usually $self.
     *    @return SimpleLexer               Lexer suitable for this parser.
     *    @access public
     */
    static function createLexer(&$parser) {
        return new SimpleHtmlLexer($parser);
    }

    /**
     *    Accepts a token from the tag mode. If the
     *    starting element completes then the element
     *    is dispatched and the current attributes
     *    set back to empty. The element or attribute
     *    name is converted to lower case.
     *    @param string $token     Incoming characters.
     *    @param integer $event    Lexer event type.
     *    @return boolean          False if parse error.
     *    @access public
     */
    function acceptStartToken($token, $event) {
        if ($event == LEXER_ENTER) {
            $this->tag = strtolower(substr($token, 1));
            return true;
        }
        if ($event == LEXER_EXIT) {
            $success = $this->listener->startElement(
                    $this->tag,
                    $this->attributes);
            $this->tag = '';
            $this->attributes = array();
            return $success;
        }
        if ($token != '=') {
            $this->current_attribute = strtolower(html_entity_decode($token, ENT_QUOTES));
            $this->attributes[$this->current_attribute] = '';
        }
        return true;
    }

    /**
     *    Accepts a token from the end tag mode.
     *    The element name is converted to lower case.
     *    @param string $token     Incoming characters.
     *    @param integer $event    Lexer event type.
     *    @return boolean          False if parse error.
     *    @access public
     */
    function acceptEndToken($token, $event) {
        if (! preg_match('/<\/(.*)>/', $token, $matches)) {
            return false;
        }
        return $this->listener->endElement(strtolower($matches[1]));
    }

    /**
     *    Part of the tag data.
     *    @param string $token     Incoming characters.
     *    @param integer $event    Lexer event type.
     *    @return boolean          False if parse error.
     *    @access public
     */
    function acceptAttributeToken($token, $event) {
        if ($this->current_attribute) {
            if ($event == LEXER_UNMATCHED) {
                $this->attributes[$this->current_attribute] .=
                        html_entity_decode($token, ENT_QUOTES);
            }
            if ($event == LEXER_SPECIAL) {
                $this->attributes[$this->current_attribute] .=
                        preg_replace('/^=\s*/' , '', html_entity_decode($token, ENT_QUOTES));
            }
        }
        return true;
    }

    /**
     *    A character entity.
     *    @param string $token    Incoming characters.
     *    @param integer $event   Lexer event type.
     *    @return boolean         False if parse error.
     *    @access public
     */
    function acceptEntityToken($token, $event) {
    }

    /**
     *    Character data between tags regarded as
     *    important.
     *    @param string $token     Incoming characters.
     *    @param integer $event    Lexer event type.
     *    @return boolean          False if parse error.
     *    @access public
     */
    function acceptTextToken($token, $event) {
        return $this->listener->addContent($token);
    }

    /**
     *    Incoming data to be ignored.
     *    @param string $token     Incoming characters.
     *    @param integer $event    Lexer event type.
     *    @return boolean          False if parse error.
     *    @access public
     */
    function ignore($token, $event) {
        return true;
    }
}

/**
 *    SAX event handler. Maintains a list of
 *    open tags and dispatches them as they close.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimplePhpPageBuilder {
    private $tags;
    private $page;
    private $private_content_tag;
    private $open_forms = array();
    private $complete_forms = array();
    private $frameset = false;
    private $loading_frames = array();
    private $frameset_nesting_level = 0;
    private $left_over_labels = array();

    /**
     *    Frees up any references so as to allow the PHP garbage
     *    collection from unset() to work.
     *    @access public
     */
    function free() {
        unset($this->tags);
        unset($this->page);
        unset($this->private_content_tags);
        $this->open_forms = array();
        $this->complete_forms = array();
        $this->frameset = false;
        $this->loading_frames = array();
        $this->frameset_nesting_level = 0;
        $this->left_over_labels = array();
    }

    /**
     *    This builder is always available.
     *    @return boolean       Always true.
     */
    function can() {
        return true;
    }

    /**
     *    Reads the raw content and send events
     *    into the page to be built.
     *    @param $response SimpleHttpResponse  Fetched response.
     *    @return SimplePage                   Newly parsed page.
     *    @access public
     */
    function parse($response) {
        $this->tags = array();
        $this->page = $this->createPage($response);
        $parser = $this->createParser($this);
        $parser->parse($response->getContent());
        $this->acceptPageEnd();
        $page = $this->page;
        $this->free();
        return $page;
    }

    /**
     *    Creates an empty page.
     *    @return SimplePage        New unparsed page.
     *    @access protected
     */
    protected function createPage($response) {
        return new SimplePage($response);
    }

    /**
     *    Creates the parser used with the builder.
     *    @param SimplePhpPageBuilder $listener   Target of parser.
     *    @return SimpleSaxParser              Parser to generate
     *                                         events for the builder.
     *    @access protected
     */
    protected function createParser(&$listener) {
        return new SimpleHtmlSaxParser($listener);
    }

    /**
     *    Start of element event. Opens a new tag.
     *    @param string $name         Element name.
     *    @param hash $attributes     Attributes without content
     *                                are marked as true.
     *    @return boolean             False on parse error.
     *    @access public
     */
    function startElement($name, $attributes) {
        $factory = new SimpleTagBuilder();
        $tag = $factory->createTag($name, $attributes);
        if (! $tag) {
            return true;
        }
        if ($tag->getTagName() == 'label') {
            $this->acceptLabelStart($tag);
            $this->openTag($tag);
            return true;
        }
        if ($tag->getTagName() == 'form') {
            $this->acceptFormStart($tag);
            return true;
        }
        if ($tag->getTagName() == 'frameset') {
            $this->acceptFramesetStart($tag);
            return true;
        }
        if ($tag->getTagName() == 'frame') {
            $this->acceptFrame($tag);
            return true;
        }
        if ($tag->isPrivateContent() && ! isset($this->private_content_tag)) {
            $this->private_content_tag = &$tag;
        }
        if ($tag->expectEndTag()) {
            $this->openTag($tag);
            return true;
        }
        $this->acceptTag($tag);
        return true;
    }

    /**
     *    End of element event.
     *    @param string $name        Element name.
     *    @return boolean            False on parse error.
     *    @access public
     */
    function endElement($name) {
        if ($name == 'label') {
            $this->acceptLabelEnd();
            return true;
        }
        if ($name == 'form') {
            $this->acceptFormEnd();
            return true;
        }
        if ($name == 'frameset') {
            $this->acceptFramesetEnd();
            return true;
        }
        if ($this->hasNamedTagOnOpenTagStack($name)) {
            $tag = array_pop($this->tags[$name]);
            if ($tag->isPrivateContent() && $this->private_content_tag->getTagName() == $name) {
                unset($this->private_content_tag);
            }
            $this->addContentTagToOpenTags($tag);
            $this->acceptTag($tag);
            return true;
        }
        return true;
    }

    /**
     *    Test to see if there are any open tags awaiting
     *    closure that match the tag name.
     *    @param string $name        Element name.
     *    @return boolean            True if any are still open.
     *    @access private
     */
    protected function hasNamedTagOnOpenTagStack($name) {
        return isset($this->tags[$name]) && (count($this->tags[$name]) > 0);
    }

    /**
     *    Unparsed, but relevant data. The data is added
     *    to every open tag.
     *    @param string $text        May include unparsed tags.
     *    @return boolean            False on parse error.
     *    @access public
     */
    function addContent($text) {
        if (isset($this->private_content_tag)) {
            $this->private_content_tag->addContent($text);
        } else {
            $this->addContentToAllOpenTags($text);
        }
        return true;
    }

    /**
     *    Any content fills all currently open tags unless it
     *    is part of an option tag.
     *    @param string $text        May include unparsed tags.
     *    @access private
     */
    protected function addContentToAllOpenTags($text) {
        foreach (array_keys($this->tags) as $name) {
            for ($i = 0, $count = count($this->tags[$name]); $i < $count; $i++) {
                $this->tags[$name][$i]->addContent($text);
            }
        }
    }

    /**
     *    Parsed data in tag form. The parsed tag is added
     *    to every open tag. Used for adding options to select
     *    fields only.
     *    @param SimpleTag $tag        Option tags only.
     *    @access private
     */
    protected function addContentTagToOpenTags(&$tag) {
        if ($tag->getTagName() != 'option') {
            return;
        }
        foreach (array_keys($this->tags) as $name) {
            for ($i = 0, $count = count($this->tags[$name]); $i < $count; $i++) {
                $this->tags[$name][$i]->addTag($tag);
            }
        }
    }

    /**
     *    Opens a tag for receiving content. Multiple tags
     *    will be receiving input at the same time.
     *    @param SimpleTag $tag        New content tag.
     *    @access private
     */
    protected function openTag($tag) {
        $name = $tag->getTagName();
        if (! in_array($name, array_keys($this->tags))) {
            $this->tags[$name] = array();
        }
        $this->tags[$name][] = $tag;
    }

    /**
     *    Adds a tag to the page.
     *    @param SimpleTag $tag        Tag to accept.
     *    @access public
     */
    protected function acceptTag($tag) {
        if ($tag->getTagName() == "a") {
            $this->page->addLink($tag);
        } elseif ($tag->getTagName() == "base") {
            $this->page->setBase($tag->getAttribute('href'));
        } elseif ($tag->getTagName() == "title") {
            $this->page->setTitle($tag);
        } elseif ($this->isFormElement($tag->getTagName())) {
            for ($i = 0; $i < count($this->open_forms); $i++) {
                $this->open_forms[$i]->addWidget($tag);
            }
            $this->last_widget = $tag;
        }
    }

    /**
     *    Opens a label for a described widget.
     *    @param SimpleFormTag $tag      Tag to accept.
     *    @access public
     */
    protected function acceptLabelStart($tag) {
        $this->label = $tag;
        unset($this->last_widget);
    }

    /**
     *    Closes the most recently opened label.
     *    @access public
     */
    protected function acceptLabelEnd() {
        if (isset($this->label)) {
            if (isset($this->last_widget)) {
                $this->last_widget->setLabel($this->label->getText());
                unset($this->last_widget);
            } else {
                $this->left_over_labels[] = SimpleTestCompatibility::copy($this->label);
            }
            unset($this->label);
        }
    }

    /**
     *    Tests to see if a tag is a possible form
     *    element.
     *    @param string $name     HTML element name.
     *    @return boolean         True if form element.
     *    @access private
     */
    protected function isFormElement($name) {
        return in_array($name, array('input', 'button', 'textarea', 'select'));
    }

    /**
     *    Opens a form. New widgets go here.
     *    @param SimpleFormTag $tag      Tag to accept.
     *    @access public
     */
    protected function acceptFormStart($tag) {
        $this->open_forms[] = new SimpleForm($tag, $this->page);
    }

    /**
     *    Closes the most recently opened form.
     *    @access public
     */
    protected function acceptFormEnd() {
        if (count($this->open_forms)) {
            $this->complete_forms[] = array_pop($this->open_forms);
        }
    }

    /**
     *    Opens a frameset. A frameset may contain nested
     *    frameset tags.
     *    @param SimpleFramesetTag $tag      Tag to accept.
     *    @access public
     */
    protected function acceptFramesetStart($tag) {
        if (! $this->isLoadingFrames()) {
            $this->frameset = $tag;
        }
        $this->frameset_nesting_level++;
    }

    /**
     *    Closes the most recently opened frameset.
     *    @access public
     */
    protected function acceptFramesetEnd() {
        if ($this->isLoadingFrames()) {
            $this->frameset_nesting_level--;
        }
    }

    /**
     *    Takes a single frame tag and stashes it in
     *    the current frame set.
     *    @param SimpleFrameTag $tag      Tag to accept.
     *    @access public
     */
    protected function acceptFrame($tag) {
        if ($this->isLoadingFrames()) {
            if ($tag->getAttribute('src')) {
                $this->loading_frames[] = $tag;
            }
        }
    }

    /**
     *    Test to see if in the middle of reading
     *    a frameset.
     *    @return boolean        True if inframeset.
     *    @access private
     */
    protected function isLoadingFrames() {
        return $this->frameset and $this->frameset_nesting_level > 0;
    }

    /**
     *    Marker for end of complete page. Any work in
     *    progress can now be closed.
     *    @access public
     */
    protected function acceptPageEnd() {
        while (count($this->open_forms)) {
            $this->complete_forms[] = array_pop($this->open_forms);
        }
        foreach ($this->left_over_labels as $label) {
            for ($i = 0, $count = count($this->complete_forms); $i < $count; $i++) {
                $this->complete_forms[$i]->attachLabelBySelector(
                        new SimpleById($label->getFor()),
                        $label->getText());
            }
        }
        $this->page->setForms($this->complete_forms);
        $this->page->setFrames($this->loading_frames);
    }
}
 /* .tmp\flat\reflection_php5.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\reflection_php5.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**
 *    Version specific reflection API.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleReflection {
    private $interface;

    /**
     *    Stashes the class/interface.
     *    @param string $interface    Class or interface
     *                                to inspect.
     */
    function __construct($interface) {
        $this->interface = $interface;
    }

    /**
     *    Checks that a class has been declared. Versions
     *    before PHP5.0.2 need a check that it's not really
     *    an interface.
     *    @return boolean            True if defined.
     *    @access public
     */
    function classExists() {
        if (! class_exists($this->interface)) {
            return false;
        }
        $reflection = new ReflectionClass($this->interface);
        return ! $reflection->isInterface();
    }

    /**
     *    Needed to kill the autoload feature in PHP5
     *    for classes created dynamically.
     *    @return boolean        True if defined.
     *    @access public
     */
    function classExistsSansAutoload() {
        return class_exists($this->interface, false);
    }

    /**
     *    Checks that a class or interface has been
     *    declared.
     *    @return boolean            True if defined.
     *    @access public
     */
    function classOrInterfaceExists() {
        return $this->classOrInterfaceExistsWithAutoload($this->interface, true);
    }

    /**
     *    Needed to kill the autoload feature in PHP5
     *    for classes created dynamically.
     *    @return boolean        True if defined.
     *    @access public
     */
    function classOrInterfaceExistsSansAutoload() {
        return $this->classOrInterfaceExistsWithAutoload($this->interface, false);
    }

    /**
     *    Needed to select the autoload feature in PHP5
     *    for classes created dynamically.
     *    @param string $interface       Class or interface name.
     *    @param boolean $autoload       True totriggerautoload.
     *    @return boolean                True if interface defined.
     *    @access private
     */
    protected function classOrInterfaceExistsWithAutoload($interface, $autoload) {
        if (function_exists('interface_exists')) {
            if (interface_exists($this->interface, $autoload)) {
                return true;
            }
        }
        return class_exists($this->interface, $autoload);
    }

    /**
     *    Gets the list of methods on a class or
     *    interface.
     *    @returns array              List of method names.
     *    @access public
     */
    function getMethods() {
        return array_unique(get_class_methods($this->interface));
    }

    /**
     *    Gets the list of interfaces from a class. If the
     *    class name is actually an interface then just that
     *    interface is returned.
     *    @returns array          List of interfaces.
     *    @access public
     */
    function getInterfaces() {
        $reflection = new ReflectionClass($this->interface);
        if ($reflection->isInterface()) {
            return array($this->interface);
        }
        return $this->onlyParents($reflection->getInterfaces());
    }

    /**
     *    Gets the list of methods for the implemented
     *    interfaces only.
     *    @returns array      List of enforced method signatures.
     *    @access public
     */
    function getInterfaceMethods() {
        $methods = array();
        foreach ($this->getInterfaces() as $interface) {
            $methods = array_merge($methods, get_class_methods($interface));
        }
        return array_unique($methods);
    }

    /**
     *    Checks to see if the method signature has to be tightly
     *    specified.
     *    @param string $method        Method name.
     *    @returns boolean             True if enforced.
     *    @access private
     */
    protected function isInterfaceMethod($method) {
        return in_array($method, $this->getInterfaceMethods());
    }

    /**
     *    Finds the parent class name.
     *    @returns string      Parent class name.
     *    @access public
     */
    function getParent() {
        $reflection = new ReflectionClass($this->interface);
        $parent = $reflection->getParentClass();
        if ($parent) {
            return $parent->getName();
        }
        return false;
    }

    /**
     *    Trivially determines if the class is abstract.
     *    @returns boolean      True if abstract.
     *    @access public
     */
    function isAbstract() {
        $reflection = new ReflectionClass($this->interface);
        return $reflection->isAbstract();
    }

    /**
     *    Trivially determines if the class is an interface.
     *    @returns boolean      True if interface.
     *    @access public
     */
    function isInterface() {
        $reflection = new ReflectionClass($this->interface);
        return $reflection->isInterface();
    }

    /**
     *    Scans for final methods, as they screw up inherited
     *    mocks by not allowing you to override them.
     *    @returns boolean   True if the class has a final method.
     *    @access public
     */
    function hasFinal() {
        $reflection = new ReflectionClass($this->interface);
        foreach ($reflection->getMethods() as $method) {
            if ($method->isFinal()) {
                return true;
            }
        }
        return false;
    }

    /**
     *    Whittles a list of interfaces down to only the
     *    necessary top level parents.
     *    @param array $interfaces     Reflection API interfaces
     *                                 to reduce.
     *    @returns array               List of parent interface names.
     *    @access private
     */
    protected function onlyParents($interfaces) {
        $parents = array();
        $blacklist = array();
        foreach ($interfaces as $interface) {
            foreach($interfaces as $possible_parent) {
                if ($interface->getName() == $possible_parent->getName()) {
                    continue;
                }
                if ($interface->isSubClassOf($possible_parent)) {
                    $blacklist[$possible_parent->getName()] = true;
                }
            }
            if (!isset($blacklist[$interface->getName()])) {
                $parents[] = $interface->getName();
            }
        }
        return $parents;
    }

    /**
     * Checks whether a method is abstract or not.
     * @param   string   $name  Method name.
     * @return  bool            true if method is abstract, else false
     * @access  private
     */
    protected function isAbstractMethod($name) {
        $interface = new ReflectionClass($this->interface);
        if (! $interface->hasMethod($name)) {
            return false;
        }
        return $interface->getMethod($name)->isAbstract();
    }

    /**
     * Checks whether a method is the constructor.
     * @param   string   $name  Method name.
     * @return  bool            true if method is the constructor
     * @access  private
     */
    protected function isConstructor($name) {
        return ($name == '__construct') || ($name == $this->interface);
    }

    /**
     * Checks whether a method is abstract in all parents or not.
     * @param   string   $name  Method name.
     * @return  bool            true if method is abstract in parent, else false
     * @access  private
     */
    protected function isAbstractMethodInParents($name) {
        $interface = new ReflectionClass($this->interface);
        $parent = $interface->getParentClass();
        while($parent) {
            if (! $parent->hasMethod($name)) {
                return false;
            }
            if ($parent->getMethod($name)->isAbstract()) {
                return true;
            }
            $parent = $parent->getParentClass();
        }
        return false;
    }

    /**
     * Checks whether a method is static or not.
     * @param   string  $name   Method name
     * @return  bool            true if method is static, else false
     * @access  private
     */
    protected function isStaticMethod($name) {
        $interface = new ReflectionClass($this->interface);
        if (! $interface->hasMethod($name)) {
            return false;
        }
        return $interface->getMethod($name)->isStatic();
    }

    /**
     *    Writes the source code matching the declaration
     *    of a method.
     *    @param string $name    Method name.
     *    @return string         Method signature up to last
     *                           bracket.
     *    @access public
     */
    function getSignature($name) {
        if ($name == '__set') {
            return 'function __set($key, $value)';
        }
        if ($name == '__call') {
            return 'function __call($method, $arguments)';
        }
        if (version_compare(phpversion(), '5.1.0', '>=')) {
            if (in_array($name, array('__get', '__isset', $name == '__unset'))) {
                return "function {$name}(\$key)";
            }
        }
        if ($name == '__toString') {
            return "function $name()";
        }
        
        // This wonky try-catch is a work around for a faulty method_exists()
        // in early versions of PHP 5 which would return false for static
        // methods. The Reflection classes work fine, but hasMethod()
        // doesn't exist prior to PHP 5.1.0, so we need to use a more crude
        // detection method.
        try {
            $interface = new ReflectionClass($this->interface);
            $interface->getMethod($name);
        } catch (ReflectionException $e) {
            return "function $name()";
        }
        return $this->getFullSignature($name);
    }

    /**
     *    For a signature specified in an interface, full
     *    details must be replicated to be a valid implementation.
     *    @param string $name    Method name.
     *    @return string         Method signature up to last
     *                           bracket.
     *    @access private
     */
    protected function getFullSignature($name) {
        $interface = new ReflectionClass($this->interface);
        $method = $interface->getMethod($name);
        $reference = $method->returnsReference() ? '&' : '';
        $static = $method->isStatic() ? 'static ' : '';
        return "{$static}function $reference$name(" .
                implode(', ', $this->getParameterSignatures($method)) .
                ")";
    }

    /**
     *    Gets the source code for each parameter.
     *    @param ReflectionMethod $method   Method object from
     *                                      reflection API
     *    @return array                     List of strings, each
     *                                      a snippet of code.
     *    @access private
     */
    protected function getParameterSignatures($method) {
        $signatures = array();
        foreach ($method->getParameters() as $parameter) {
            $signature = '';
            $type = $parameter->getClass();
            if (is_null($type) && version_compare(phpversion(), '5.1.0', '>=') && $parameter->isArray()) {
                $signature .= 'array ';
            } elseif (!is_null($type)) {
                $signature .= $type->getName() . ' ';
            }
            if ($parameter->isPassedByReference()) {
                $signature .= '&';
            }
            $signature .= '$' . $this->suppressSpurious($parameter->getName());
            if ($this->isOptional($parameter)) {
                $signature .= ' = null';
            }
            $signatures[] = $signature;
        }
        return $signatures;
    }

    /**
     *    The SPL library has problems with the
     *    Reflection library. In particular, you can
     *    get extra characters in parameter names :(.
     *    @param string $name    Parameter name.
     *    @return string         Cleaner name.
     *    @access private
     */
    protected function suppressSpurious($name) {
        return str_replace(array('[', ']', ' '), '', $name);
    }

    /**
     *    Test of a reflection parameter being optional
     *    that works with early versions of PHP5.
     *    @param reflectionParameter $parameter    Is this optional.
     *    @return boolean                          True if optional.
     *    @access private
     */
    protected function isOptional($parameter) {
        if (method_exists($parameter, 'isOptional')) {
            return $parameter->isOptional();
        }
        return false;
    }
}
 /* .tmp\flat\remote.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\remote.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/browser.php');
//require_once(dirname(__FILE__) . '/xml.php');
//require_once(dirname(__FILE__) . '/test_case.php');
/**#@-*/

/**
 *    Runs an XML formated test on a remote server.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class RemoteTestCase {
    private $url;
    private $dry_url;
    private $size;
    
    /**
     *    Sets the location of the remote test.
     *    @param string $url       Test location.
     *    @param string $dry_url   Location for dry run.
     *    @access public
     */
    function __construct($url, $dry_url = false) {
        $this->url = $url;
        $this->dry_url = $dry_url ? $dry_url : $url;
        $this->size = false;
    }
    
    /**
     *    Accessor for the test name for subclasses.
     *    @return string           Name of the test.
     *    @access public
     */
    function getLabel() {
        return $this->url;
    }

    /**
     *    Runs the top level test for this class. Currently
     *    reads the data as a single chunk. I'll fix this
     *    once I have added iteration to the browser.
     *    @param SimpleReporter $reporter    Target of test results.
     *    @returns boolean                   True if no failures.
     *    @access public
     */
    function run($reporter) {
        $browser = $this->createBrowser();
        $xml = $browser->get($this->url);
        if (! $xml) {
            trigger_error('Cannot read remote test URL [' . $this->url . ']');
            return false;
        }
        $parser = $this->createParser($reporter);
        if (! $parser->parse($xml)) {
            trigger_error('Cannot parse incoming XML from [' . $this->url . ']');
            return false;
        }
        return true;
    }
    
    /**
     *    Creates a new web browser object for fetching
     *    the XML report.
     *    @return SimpleBrowser           New browser.
     *    @access protected
     */
    protected function createBrowser() {
        return new SimpleBrowser();
    }
    
    /**
     *    Creates the XML parser.
     *    @param SimpleReporter $reporter    Target of test results.
     *    @return SimpleTestXmlListener      XML reader.
     *    @access protected
     */
    protected function createParser($reporter) {
        return new SimpleTestXmlParser($reporter);
    }
    
    /**
     *    Accessor for the number of subtests.
     *    @return integer           Number of test cases.
     *    @access public
     */
    function getSize() {
        if ($this->size === false) {
            $browser = $this->createBrowser();
            $xml = $browser->get($this->dry_url);
            if (! $xml) {
                trigger_error('Cannot read remote test URL [' . $this->dry_url . ']');
                return false;
            }
            $reporter = new SimpleReporter();
            $parser = $this->createParser($reporter);
            if (! $parser->parse($xml)) {
                trigger_error('Cannot parse incoming XML from [' . $this->dry_url . ']');
                return false;
            }
            $this->size = $reporter->getTestCaseCount();
        }
        return $this->size;
    }
}
 /* .tmp\flat\scorer.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\scorer.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+*/
//require_once(dirname(__FILE__) . '/invoker.php');
/**#@-*/

/**
 *    Can receive test events and display them. Display
 *    is achieved by making display methods available
 *    and visiting the incoming event.
 *    @package SimpleTest
 *    @subpackage UnitTester
 *    @abstract
 */
class SimpleScorer {
    private $passes;
    private $fails;
    private $exceptions;
    private $is_dry_run;

    /**
     *    Starts the test run with no results.
     *    @access public
     */
    function __construct() {
        $this->passes = 0;
        $this->fails = 0;
        $this->exceptions = 0;
        $this->is_dry_run = false;
    }

    /**
     *    Signals that the next evaluation will be a dry
     *    run. That is, the structure events will be
     *    recorded, but no tests will be run.
     *    @param boolean $is_dry        Dry run if true.
     *    @access public
     */
    function makeDry($is_dry = true) {
        $this->is_dry_run = $is_dry;
    }

    /**
     *    The reporter has a veto on what should be run.
     *    @param string $test_case_name  name of test case.
     *    @param string $method          Name of test method.
     *    @access public
     */
    function shouldInvoke($test_case_name, $method) {
        return ! $this->is_dry_run;
    }

    /**
     *    Can wrap the invoker in preperation for running
     *    a test.
     *    @param SimpleInvoker $invoker   Individual test runner.
     *    @return SimpleInvoker           Wrapped test runner.
     *    @access public
     */
    function createInvoker($invoker) {
        return $invoker;
    }

    /**
     *    Accessor for current status. Will be false
     *    if there have been any failures or exceptions.
     *    Used for command line tools.
     *    @return boolean        True if no failures.
     *    @access public
     */
    function getStatus() {
        if ($this->exceptions + $this->fails > 0) {
            return false;
        }
        return true;
    }

    /**
     *    Paints the start of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @param integer $size         Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_name, $size) {
    }

    /**
     *    Paints the end of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintGroupEnd($test_name) {
    }

    /**
     *    Paints the start of a test case.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintCaseStart($test_name) {
    }

    /**
     *    Paints the end of a test case.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintCaseEnd($test_name) {
    }

    /**
     *    Paints the start of a test method.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintMethodStart($test_name) {
    }

    /**
     *    Paints the end of a test method.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintMethodEnd($test_name) {
    }

    /**
     *    Increments the pass count.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintPass($message) {
        $this->passes++;
    }

    /**
     *    Increments the fail count.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintFail($message) {
        $this->fails++;
    }

    /**
     *    Deals with PHP 4 throwing an error.
     *    @param string $message    Text of error formatted by
     *                              the test case.
     *    @access public
     */
    function paintError($message) {
        $this->exceptions++;
    }

    /**
     *    Deals with PHP 5 throwing an exception.
     *    @param Exception $exception    The actual exception thrown.
     *    @access public
     */
    function paintException($exception) {
        $this->exceptions++;
    }
    
    /**
     *    Prints the message for skipping tests.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) {
    }

    /**
     *    Accessor for the number of passes so far.
     *    @return integer       Number of passes.
     *    @access public
     */
    function getPassCount() {
        return $this->passes;
    }

    /**
     *    Accessor for the number of fails so far.
     *    @return integer       Number of fails.
     *    @access public
     */
    function getFailCount() {
        return $this->fails;
    }

    /**
     *    Accessor for the number of untrapped errors
     *    so far.
     *    @return integer       Number of exceptions.
     *    @access public
     */
    function getExceptionCount() {
        return $this->exceptions;
    }

    /**
     *    Paints a simple supplementary message.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintMessage($message) {
    }

    /**
     *    Paints a formatted ASCII message such as a
     *    privateiable dump.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintFormattedMessage($message) {
    }

    /**
     *    By default just ignores user generated events.
     *    @param string $type        Event type as text.
     *    @param mixed $payload      Message or object.
     *    @access public
     */
    function paintSignal($type, $payload) {
    }
}

/**
 *    Recipient of generated test messages that can display
 *    page footers and headers. Also keeps track of the
 *    test nesting. This is the main base class on which
 *    to build the finished test (page based) displays.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleReporter extends SimpleScorer {
    private $test_stack;
    private $size;
    private $progress;

    /**
     *    Starts the display with no results in.
     *    @access public
     */
    function __construct() {
        parent::__construct();
        $this->test_stack = array();
        $this->size = null;
        $this->progress = 0;
    }
    
    /**
     *    Gets the formatter for small generic data items.
     *    @return SimpleDumper          Formatter.
     *    @access public
     */
    function getDumper() {
        return new SimpleDumper();
    }

    /**
     *    Paints the start of a group test. Will also paint
     *    the page header and footer if this is the
     *    first test. Will stash the size if the first
     *    start.
     *    @param string $test_name   Name of test that is starting.
     *    @param integer $size       Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_name, $size) {
        if (! isset($this->size)) {
            $this->size = $size;
        }
        if (count($this->test_stack) == 0) {
            $this->paintHeader($test_name);
        }
        $this->test_stack[] = $test_name;
    }

    /**
     *    Paints the end of a group test. Will paint the page
     *    footer if the stack of tests has unwound.
     *    @param string $test_name   Name of test that is ending.
     *    @param integer $progress   Number of test cases ending.
     *    @access public
     */
    function paintGroupEnd($test_name) {
        array_pop($this->test_stack);
        if (count($this->test_stack) == 0) {
            $this->paintFooter($test_name);
        }
    }

    /**
     *    Paints the start of a test case. Will also paint
     *    the page header and footer if this is the
     *    first test. Will stash the size if the first
     *    start.
     *    @param string $test_name   Name of test that is starting.
     *    @access public
     */
    function paintCaseStart($test_name) {
        if (! isset($this->size)) {
            $this->size = 1;
        }
        if (count($this->test_stack) == 0) {
            $this->paintHeader($test_name);
        }
        $this->test_stack[] = $test_name;
    }

    /**
     *    Paints the end of a test case. Will paint the page
     *    footer if the stack of tests has unwound.
     *    @param string $test_name   Name of test that is ending.
     *    @access public
     */
    function paintCaseEnd($test_name) {
        $this->progress++;
        array_pop($this->test_stack);
        if (count($this->test_stack) == 0) {
            $this->paintFooter($test_name);
        }
    }

    /**
     *    Paints the start of a test method.
     *    @param string $test_name   Name of test that is starting.
     *    @access public
     */
    function paintMethodStart($test_name) {
        $this->test_stack[] = $test_name;
    }

    /**
     *    Paints the end of a test method. Will paint the page
     *    footer if the stack of tests has unwound.
     *    @param string $test_name   Name of test that is ending.
     *    @access public
     */
    function paintMethodEnd($test_name) {
        array_pop($this->test_stack);
    }

    /**
     *    Paints the test document header.
     *    @param string $test_name     First test top level
     *                                 to start.
     *    @access public
     *    @abstract
     */
    function paintHeader($test_name) {
    }

    /**
     *    Paints the test document footer.
     *    @param string $test_name        The top level test.
     *    @access public
     *    @abstract
     */
    function paintFooter($test_name) {
    }

    /**
     *    Accessor for internal test stack. For
     *    subclasses that need to see the whole test
     *    history for display purposes.
     *    @return array     List of methods in nesting order.
     *    @access public
     */
    function getTestList() {
        return $this->test_stack;
    }

    /**
     *    Accessor for total test size in number
     *    of test cases. Null until the first
     *    test is started.
     *    @return integer   Total number of cases at start.
     *    @access public
     */
    function getTestCaseCount() {
        return $this->size;
    }

    /**
     *    Accessor for the number of test cases
     *    completed so far.
     *    @return integer   Number of ended cases.
     *    @access public
     */
    function getTestCaseProgress() {
        return $this->progress;
    }

    /**
     *    Static check for running in the comand line.
     *    @return boolean        True if CLI.
     *    @access public
     */
    static function inCli() {
        return php_sapi_name() == 'cli';
    }
}

/**
 *    For modifying the behaviour of the visual reporters.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleReporterDecorator {
    protected $reporter;

    /**
     *    Mediates between the reporter and the test case.
     *    @param SimpleScorer $reporter       Reporter to receive events.
     */
    function __construct($reporter) {
        $this->reporter = $reporter;
    }

    /**
     *    Signals that the next evaluation will be a dry
     *    run. That is, the structure events will be
     *    recorded, but no tests will be run.
     *    @param boolean $is_dry        Dry run if true.
     *    @access public
     */
    function makeDry($is_dry = true) {
        $this->reporter->makeDry($is_dry);
    }

    /**
     *    Accessor for current status. Will be false
     *    if there have been any failures or exceptions.
     *    Used for command line tools.
     *    @return boolean        True if no failures.
     *    @access public
     */
    function getStatus() {
        return $this->reporter->getStatus();
    }

    /**
     *    The nesting of the test cases so far. Not
     *    all reporters have this facility.
     *    @return array        Test list if accessible.
     *    @access public
     */
    function getTestList() {
        if (method_exists($this->reporter, 'getTestList')) {
            return $this->reporter->getTestList();
        } else {
            return array();
        }
    }

    /**
     *    The reporter has a veto on what should be run.
     *    @param string $test_case_name  Name of test case.
     *    @param string $method          Name of test method.
     *    @return boolean                True if test should be run.
     *    @access public
     */
    function shouldInvoke($test_case_name, $method) {
        return $this->reporter->shouldInvoke($test_case_name, $method);
    }

    /**
     *    Can wrap the invoker in preparation for running
     *    a test.
     *    @param SimpleInvoker $invoker   Individual test runner.
     *    @return SimpleInvoker           Wrapped test runner.
     *    @access public
     */
    function createInvoker($invoker) {
        return $this->reporter->createInvoker($invoker);
    }
    
    /**
     *    Gets the formatter for privateiables and other small
     *    generic data items.
     *    @return SimpleDumper          Formatter.
     *    @access public
     */
    function getDumper() {
        return $this->reporter->getDumper();
    }

    /**
     *    Paints the start of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @param integer $size         Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_name, $size) {
        $this->reporter->paintGroupStart($test_name, $size);
    }

    /**
     *    Paints the end of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintGroupEnd($test_name) {
        $this->reporter->paintGroupEnd($test_name);
    }

    /**
     *    Paints the start of a test case.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintCaseStart($test_name) {
        $this->reporter->paintCaseStart($test_name);
    }

    /**
     *    Paints the end of a test case.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintCaseEnd($test_name) {
        $this->reporter->paintCaseEnd($test_name);
    }

    /**
     *    Paints the start of a test method.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintMethodStart($test_name) {
        $this->reporter->paintMethodStart($test_name);
    }

    /**
     *    Paints the end of a test method.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintMethodEnd($test_name) {
        $this->reporter->paintMethodEnd($test_name);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintPass($message) {
        $this->reporter->paintPass($message);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintFail($message) {
        $this->reporter->paintFail($message);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message    Text of error formatted by
     *                              the test case.
     *    @access public
     */
    function paintError($message) {
        $this->reporter->paintError($message);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param Exception $exception        Exception to show.
     *    @access public
     */
    function paintException($exception) {
        $this->reporter->paintException($exception);
    }
    
    /**
     *    Prints the message for skipping tests.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) {
        $this->reporter->paintSkip($message);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintMessage($message) {
        $this->reporter->paintMessage($message);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintFormattedMessage($message) {
        $this->reporter->paintFormattedMessage($message);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $type        Event type as text.
     *    @param mixed $payload      Message or object.
     *    @return boolean            Should return false if this
     *                               type of signal should fail the
     *                               test suite.
     *    @access public
     */
    function paintSignal($type, $payload) {
        $this->reporter->paintSignal($type, $payload);
    }
}

/**
 *    For sending messages to multiple reporters at
 *    the same time.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class MultipleReporter {
    private $reporters = array();

    /**
     *    Adds a reporter to the subscriber list.
     *    @param SimpleScorer $reporter     Reporter to receive events.
     *    @access public
     */
    function attachReporter($reporter) {
        $this->reporters[] = $reporter;
    }

    /**
     *    Signals that the next evaluation will be a dry
     *    run. That is, the structure events will be
     *    recorded, but no tests will be run.
     *    @param boolean $is_dry        Dry run if true.
     *    @access public
     */
    function makeDry($is_dry = true) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->makeDry($is_dry);
        }
    }

    /**
     *    Accessor for current status. Will be false
     *    if there have been any failures or exceptions.
     *    If any reporter reports a failure, the whole
     *    suite fails.
     *    @return boolean        True if no failures.
     *    @access public
     */
    function getStatus() {
        for ($i = 0; $i < count($this->reporters); $i++) {
            if (! $this->reporters[$i]->getStatus()) {
                return false;
            }
        }
        return true;
    }

    /**
     *    The reporter has a veto on what should be run.
     *    It requires all reporters to want to run the method.
     *    @param string $test_case_name  name of test case.
     *    @param string $method          Name of test method.
     *    @access public
     */
    function shouldInvoke($test_case_name, $method) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            if (! $this->reporters[$i]->shouldInvoke($test_case_name, $method)) {
                return false;
            }
        }
        return true;
    }

    /**
     *    Every reporter gets a chance to wrap the invoker.
     *    @param SimpleInvoker $invoker   Individual test runner.
     *    @return SimpleInvoker           Wrapped test runner.
     *    @access public
     */
    function createInvoker($invoker) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $invoker = $this->reporters[$i]->createInvoker($invoker);
        }
        return $invoker;
    }
    
    /**
     *    Gets the formatter for privateiables and other small
     *    generic data items.
     *    @return SimpleDumper          Formatter.
     *    @access public
     */
    function getDumper() {
        return new SimpleDumper();
    }

    /**
     *    Paints the start of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @param integer $size         Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_name, $size) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintGroupStart($test_name, $size);
        }
    }

    /**
     *    Paints the end of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintGroupEnd($test_name) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintGroupEnd($test_name);
        }
    }

    /**
     *    Paints the start of a test case.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintCaseStart($test_name) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintCaseStart($test_name);
        }
    }

    /**
     *    Paints the end of a test case.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintCaseEnd($test_name) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintCaseEnd($test_name);
        }
    }

    /**
     *    Paints the start of a test method.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintMethodStart($test_name) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintMethodStart($test_name);
        }
    }

    /**
     *    Paints the end of a test method.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintMethodEnd($test_name) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintMethodEnd($test_name);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintPass($message) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintPass($message);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintFail($message) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintFail($message);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message    Text of error formatted by
     *                              the test case.
     *    @access public
     */
    function paintError($message) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintError($message);
        }
    }
    
    /**
     *    Chains to the wrapped reporter.
     *    @param Exception $exception    Exception to display.
     *    @access public
     */
    function paintException($exception) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintException($exception);
        }
    }

    /**
     *    Prints the message for skipping tests.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintSkip($message);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintMessage($message) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintMessage($message);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintFormattedMessage($message) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintFormattedMessage($message);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $type        Event type as text.
     *    @param mixed $payload      Message or object.
     *    @return boolean            Should return false if this
     *                               type of signal should fail the
     *                               test suite.
     *    @access public
     */
    function paintSignal($type, $payload) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintSignal($type, $payload);
        }
    }
}
 /* .tmp\flat\selector.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\selector.php */ ?>
<?php
/**
 *  Base include file for SimpleTest.
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */

/**#@+
 * include SimpleTest files
 */
//require_once(dirname(__FILE__) . '/tag.php');
//require_once(dirname(__FILE__) . '/encoding.php');
/**#@-*/

/**
 *    Used to extract form elements for testing against.
 *    Searches by name attribute.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleByName {
    private $name;

    /**
     *    Stashes the name for later comparison.
     *    @param string $name     Name attribute to match.
     */
    function __construct($name) {
        $this->name = $name;
    }

    /**
     *  Accessor for name.
     *  @returns string $name       Name to match.
     */
    function getName() {
        return $this->name;
    }

    /**
     *    Compares with name attribute of widget.
     *    @param SimpleWidget $widget    Control to compare.
     *    @access public
     */
    function isMatch($widget) {
        return ($widget->getName() == $this->name);
    }
}

/**
 *    Used to extract form elements for testing against.
 *    Searches by visible label or alt text.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleByLabel {
    private $label;

    /**
     *    Stashes the name for later comparison.
     *    @param string $label     Visible text to match.
     */
    function __construct($label) {
        $this->label = $label;
    }

    /**
     *    Comparison. Compares visible text of widget or
     *    related label.
     *    @param SimpleWidget $widget    Control to compare.
     *    @access public
     */
    function isMatch($widget) {
        if (! method_exists($widget, 'isLabel')) {
            return false;
        }
        return $widget->isLabel($this->label);
    }
}

/**
 *    Used to extract form elements for testing against.
 *    Searches dy id attribute.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleById {
    private $id;

    /**
     *    Stashes the name for later comparison.
     *    @param string $id     ID atribute to match.
     */
    function __construct($id) {
        $this->id = $id;
    }

    /**
     *    Comparison. Compares id attribute of widget.
     *    @param SimpleWidget $widget    Control to compare.
     *    @access public
     */
    function isMatch($widget) {
        return $widget->isId($this->id);
    }
}

/**
 *    Used to extract form elements for testing against.
 *    Searches by visible label, name or alt text.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleByLabelOrName {
    private $label;

    /**
     *    Stashes the name/label for later comparison.
     *    @param string $label     Visible text to match.
     */
    function __construct($label) {
        $this->label = $label;
    }

    /**
     *    Comparison. Compares visible text of widget or
     *    related label or name.
     *    @param SimpleWidget $widget    Control to compare.
     *    @access public
     */
    function isMatch($widget) {
        if (method_exists($widget, 'isLabel')) {
            if ($widget->isLabel($this->label)) {
                return true;
            }
        }
        return ($widget->getName() == $this->label);
    }
}
 /* .tmp\flat\simpletest.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\simpletest.php */ ?>
<?php
/**
 *  Global state for SimpleTest and kicker script in future versions.
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 * include SimpleTest files
 */
//require_once(dirname(__FILE__) . '/reflection_php5.php');
//require_once(dirname(__FILE__) . '/default_reporter.php');
//require_once(dirname(__FILE__) . '/compatibility.php');
/**#@-*/

/**
 *    Registry and test context. Includes a few
 *    global options that I'm slowly getting rid of.
 *    @package  SimpleTest
 *    @subpackage   UnitTester
 */
class SimpleTest {

    /**
     *    Reads the SimpleTest version from the release file.
     *    @return string        Version string.
     */
    static function getVersion() {
        $content = file(dirname(__FILE__) . '/VERSION');
        return trim($content[0]);
    }

    /**
     *    Sets the name of a test case to ignore, usually
     *    because the class is an abstract case that should
     *    @param string $class        Add a class to ignore.
     */
    static function ignore($class) {
        $registry = &SimpleTest::getRegistry();
        $registry['IgnoreList'][strtolower($class)] = true;
    }

    /**
     *    Scans the now complete ignore list, and adds
     *    all parent classes to the list. If a class
     *    is not a runnable test case, then it's parents
     *    wouldn't be either. This is syntactic sugar
     *    to cut down on ommissions of ignore()'s or
     *    missing abstract declarations. This cannot
     *    be done whilst loading classes wiithout forcing
     *    a particular order on the class declarations and
     *    the ignore() calls. It's just nice to have the ignore()
     *    calls at the top of the file before the actual declarations.
     *    @param array $classes     Class names of interest.
     */
    static function ignoreParentsIfIgnored($classes) {
        $registry = &SimpleTest::getRegistry();
        foreach ($classes as $class) {
            if (SimpleTest::isIgnored($class)) {
                $reflection = new SimpleReflection($class);
                if ($parent = $reflection->getParent()) {
                    SimpleTest::ignore($parent);
                }
            }
        }
    }

    /**
     *   Puts the object to the global pool of 'preferred' objects
     *   which can be retrieved with SimpleTest :: preferred() method.
     *   Instances of the same class are overwritten.
     *   @param object $object      Preferred object
     *   @see preferred()
     */
    static function prefer($object) {
        $registry = &SimpleTest::getRegistry();
        $registry['Preferred'][] = $object;
    }

    /**
     *   Retrieves 'preferred' objects from global pool. Class filter
     *   can be applied in order to retrieve the object of the specific
     *   class
     *   @param array|string $classes       Allowed classes or interfaces.
     *   @return array|object|null
     *   @see prefer()
     */
    static function preferred($classes) {
        if (! is_array($classes)) {
            $classes = array($classes);
        }
        $registry = &SimpleTest::getRegistry();
        for ($i = count($registry['Preferred']) - 1; $i >= 0; $i--) {
            foreach ($classes as $class) {
                if (SimpleTestCompatibility::isA($registry['Preferred'][$i], $class)) {
                    return $registry['Preferred'][$i];
                }
            }
        }
        return null;
    }

    /**
     *    Test to see if a test case is in the ignore
     *    list. Quite obviously the ignore list should
     *    be a separate object and will be one day.
     *    This method is internal to SimpleTest. Don't
     *    use it.
     *    @param string $class        Class name to test.
     *    @return boolean             True if should not be run.
     */
    static function isIgnored($class) {
        $registry = &SimpleTest::getRegistry();
        return isset($registry['IgnoreList'][strtolower($class)]);
    }

    /**
     *    Sets proxy to use on all requests for when
     *    testing from behind a firewall. Set host
     *    to false to disable. This will take effect
     *    if there are no other proxy settings.
     *    @param string $proxy     Proxy host as URL.
     *    @param string $username  Proxy username for authentication.
     *    @param string $password  Proxy password for authentication.
     */
    static function useProxy($proxy, $username = false, $password = false) {
        $registry = &SimpleTest::getRegistry();
        $registry['DefaultProxy'] = $proxy;
        $registry['DefaultProxyUsername'] = $username;
        $registry['DefaultProxyPassword'] = $password;
    }

    /**
     *    Accessor for default proxy host.
     *    @return string       Proxy URL.
     */
    static function getDefaultProxy() {
        $registry = &SimpleTest::getRegistry();
        return $registry['DefaultProxy'];
    }

    /**
     *    Accessor for default proxy username.
     *    @return string    Proxy username for authentication.
     */
    static function getDefaultProxyUsername() {
        $registry = &SimpleTest::getRegistry();
        return $registry['DefaultProxyUsername'];
    }

    /**
     *    Accessor for default proxy password.
     *    @return string    Proxy password for authentication.
     */
    static function getDefaultProxyPassword() {
        $registry = &SimpleTest::getRegistry();
        return $registry['DefaultProxyPassword'];
    }

    /**
     *    Accessor for default HTML parsers.
     *    @return array     List of parsers to try in
     *                      order until one responds true
     *                      to can().
     */
    static function getParsers() {
        $registry = &SimpleTest::getRegistry();
        return $registry['Parsers'];
    }

    /**
     *    Set the list of HTML parsers to attempt to use by default.
     *    @param array $parsers    List of parsers to try in
     *                             order until one responds true
     *                             to can().
     */
    static function setParsers($parsers) {
        $registry = &SimpleTest::getRegistry();
        $registry['Parsers'] = $parsers;
    }

    /**
     *    Accessor for global registry of options.
     *    @return hash           All stored values.
     */
    protected static function &getRegistry() {
        static $registry = false;
        if (! $registry) {
            $registry = SimpleTest::getDefaults();
        }
        return $registry;
    }

    /**
     *    Accessor for the context of the current
     *    test run.
     *    @return SimpleTestContext    Current test run.
     */
    static function getContext() {
        static $context = false;
        if (! $context) {
            $context = new SimpleTestContext();
        }
        return $context;
    }

    /**
     *    Constant default values.
     *    @return hash       All registry defaults.
     */
    protected static function getDefaults() {
        return array(
                'Parsers' => false,
                'MockBaseClass' => 'SimpleMock',
                'IgnoreList' => array(),
                'DefaultProxy' => false,
                'DefaultProxyUsername' => false,
                'DefaultProxyPassword' => false,
                'Preferred' => array(new HtmlReporter(), new TextReporter(), new XmlReporter()));
    }
    
    /**
     *    @deprecated
     */
    static function setMockBaseClass($mock_base) {
        $registry = &SimpleTest::getRegistry();
        $registry['MockBaseClass'] = $mock_base;
    }

    /**
     *    @deprecated
     */
    static function getMockBaseClass() {
        $registry = &SimpleTest::getRegistry();
        return $registry['MockBaseClass'];
    }
}

/**
 *    Container for all components for a specific
 *    test run. Makes things like error queues
 *    available to PHP event handlers, and also
 *    gets around some nasty reference issues in
 *    the mocks.
 *    @package  SimpleTest
 */
class SimpleTestContext {
    private $test;
    private $reporter;
    private $resources;

    /**
     *    Clears down the current context.
     *    @access public
     */
    function clear() {
        $this->resources = array();
    }

    /**
     *    Sets the current test case instance. This
     *    global instance can be used by the mock objects
     *    to send message to the test cases.
     *    @param SimpleTestCase $test        Test case to register.
     */
    function setTest($test) {
        $this->clear();
        $this->test = $test;
    }

    /**
     *    Accessor for currently running test case.
     *    @return SimpleTestCase    Current test.
     */
    function getTest() {
        return $this->test;
    }

    /**
     *    Sets the current reporter. This
     *    global instance can be used by the mock objects
     *    to send messages.
     *    @param SimpleReporter $reporter     Reporter to register.
     */
    function setReporter($reporter) {
        $this->clear();
        $this->reporter = $reporter;
    }

    /**
     *    Accessor for current reporter.
     *    @return SimpleReporter    Current reporter.
     */
    function getReporter() {
        return $this->reporter;
    }

    /**
     *    Accessor for the Singleton resource.
     *    @return object       Global resource.
     */
    function get($resource) {
        if (! isset($this->resources[$resource])) {
            $this->resources[$resource] = new $resource();
        }
        return $this->resources[$resource];
    }
}

/**
 *    Interrogates the stack trace to recover the
 *    failure point.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleStackTrace {
    private $prefixes;

    /**
     *    Stashes the list of target prefixes.
     *    @param array $prefixes      List of method prefixes
     *                                to search for.
     */
    function __construct($prefixes) {
        $this->prefixes = $prefixes;
    }

    /**
     *    Extracts the last method name that was not within
     *    Simpletest itself. Captures a stack trace if none given.
     *    @param array $stack      List of stack frames.
     *    @return string           Snippet of test report with line
     *                             number and file.
     */
    function traceMethod($stack = false) {
        $stack = $stack ? $stack : $this->captureTrace();
        foreach ($stack as $frame) {
            if ($this->frameLiesWithinSimpleTestFolder($frame)) {
                continue;
            }
            if ($this->frameMatchesPrefix($frame)) {
                return ' at [' . $frame['file'] . ' line ' . $frame['line'] . ']';
            }
        }
        return '';
    }

    /**
     *    Test to see if error is generated by SimpleTest itself.
     *    @param array $frame     PHP stack frame.
     *    @return boolean         True if a SimpleTest file.
     */
    protected function frameLiesWithinSimpleTestFolder($frame) {
        if (isset($frame['file'])) {
            $path = substr(SIMPLE_TEST, 0, -1);
            if (strpos($frame['file'], $path) === 0) {
                if (dirname($frame['file']) == $path) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *    Tries to determine if the method call is an assert, etc.
     *    @param array $frame     PHP stack frame.
     *    @return boolean         True if matches a target.
     */
    protected function frameMatchesPrefix($frame) {
        foreach ($this->prefixes as $prefix) {
            if (strncmp($frame['function'], $prefix, strlen($prefix)) == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     *    Grabs a current stack trace.
     *    @return array        Fulle trace.
     */
    protected function captureTrace() {
        if (function_exists('debug_backtrace')) {
            return array_reverse(debug_backtrace());
        }
        return array();
    }
}
 /* .tmp\flat\socket.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\socket.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage MockObjects
 *  @version    $Id$
 */

/**#@+
 * include SimpleTest files
 */
//require_once(dirname(__FILE__) . '/compatibility.php');
/**#@-*/

/**
 *    Stashes an error for later. Useful for constructors
 *    until PHP gets exceptions.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleStickyError {
    private $error = 'Constructor not chained';

    /**
     *    Sets the error to empty.
     *    @access public
     */
    function __construct() {
        $this->clearError();
    }

    /**
     *    Test for an outstanding error.
     *    @return boolean           True if there is an error.
     *    @access public
     */
    function isError() {
        return ($this->error != '');
    }

    /**
     *    Accessor for an outstanding error.
     *    @return string     Empty string if no error otherwise
     *                       the error message.
     *    @access public
     */
    function getError() {
        return $this->error;
    }

    /**
     *    Sets the internal error.
     *    @param string       Error message to stash.
     *    @access protected
     */
    function setError($error) {
        $this->error = $error;
    }

    /**
     *    Resets the error state to no error.
     *    @access protected
     */
    function clearError() {
        $this->setError('');
    }
}

/**
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleFileSocket extends SimpleStickyError {
    private $handle;
    private $is_open = false;
    private $sent = '';
    private $block_size;

    /**
     *    Opens a socket for reading and writing.
     *    @param SimpleUrl $file       Target URI to fetch.
     *    @param integer $block_size   Size of chunk to read.
     *    @access public
     */
    function __construct($file, $block_size = 1024) {
        parent::__construct();
        if (! ($this->handle = $this->openFile($file, $error))) {
            $file_string = $file->asString();
            $this->setError("Cannot open [$file_string] with [$error]");
            return;
        }
        $this->is_open = true;
        $this->block_size = $block_size;
    }

    /**
     *    Writes some data to the socket and saves alocal copy.
     *    @param string $message       String to send to socket.
     *    @return boolean              True if successful.
     *    @access public
     */
    function write($message) {
        return true;
    }

    /**
     *    Reads data from the socket. The error suppresion
     *    is a workaround for PHP4 always throwing a warning
     *    with a secure socket.
     *    @return integer/boolean           Incoming bytes. False
     *                                     on error.
     *    @access public
     */
    function read() {
        $raw = @fread($this->handle, $this->block_size);
        if ($raw === false) {
            $this->setError('Cannot read from socket');
            $this->close();
        }
        return $raw;
    }

    /**
     *    Accessor for socket open state.
     *    @return boolean           True if open.
     *    @access public
     */
    function isOpen() {
        return $this->is_open;
    }

    /**
     *    Closes the socket preventing further reads.
     *    Cannot be reopened once closed.
     *    @return boolean           True if successful.
     *    @access public
     */
    function close() {
        if (!$this->is_open) return false;
        $this->is_open = false;
        return fclose($this->handle);
    }

    /**
     *    Accessor for content so far.
     *    @return string        Bytes sent only.
     *    @access public
     */
    function getSent() {
        return $this->sent;
    }

    /**
     *    Actually opens the low level socket.
     *    @param SimpleUrl $file       SimpleUrl file target.
     *    @param string $error         Recipient of error message.
     *    @param integer $timeout      Maximum time to wait for connection.
     *    @access protected
     */
    protected function openFile($file, &$error) {
        return @fopen($file->asString(), 'r');
    }
}

/**
 *    Wrapper for TCP/IP socket.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleSocket extends SimpleStickyError {
    private $handle;
    private $is_open = false;
    private $sent = '';
    private $lock_size;

    /**
     *    Opens a socket for reading and writing.
     *    @param string $host          Hostname to send request to.
     *    @param integer $port         Port on remote machine to open.
     *    @param integer $timeout      Connection timeout in seconds.
     *    @param integer $block_size   Size of chunk to read.
     *    @access public
     */
    function __construct($host, $port, $timeout, $block_size = 255) {
        parent::__construct();
        if (! ($this->handle = $this->openSocket($host, $port, $error_number, $error, $timeout))) {
            $this->setError("Cannot open [$host:$port] with [$error] within [$timeout] seconds");
            return;
        }
        $this->is_open = true;
        $this->block_size = $block_size;
        SimpleTestCompatibility::setTimeout($this->handle, $timeout);
    }

    /**
     *    Writes some data to the socket and saves alocal copy.
     *    @param string $message       String to send to socket.
     *    @return boolean              True if successful.
     *    @access public
     */
    function write($message) {
        if ($this->isError() || ! $this->isOpen()) {
            return false;
        }
        $count = fwrite($this->handle, $message);
        if (! $count) {
            if ($count === false) {
                $this->setError('Cannot write to socket');
                $this->close();
            }
            return false;
        }
        fflush($this->handle);
        $this->sent .= $message;
        return true;
    }

    /**
     *    Reads data from the socket. The error suppresion
     *    is a workaround for PHP4 always throwing a warning
     *    with a secure socket.
     *    @return integer/boolean           Incoming bytes. False
     *                                     on error.
     *    @access public
     */
    function read() {
        if ($this->isError() || ! $this->isOpen()) {
            return false;
        }
        $raw = @fread($this->handle, $this->block_size);
        if ($raw === false) {
            $this->setError('Cannot read from socket');
            $this->close();
        }
        return $raw;
    }

    /**
     *    Accessor for socket open state.
     *    @return boolean           True if open.
     *    @access public
     */
    function isOpen() {
        return $this->is_open;
    }

    /**
     *    Closes the socket preventing further reads.
     *    Cannot be reopened once closed.
     *    @return boolean           True if successful.
     *    @access public
     */
    function close() {
        $this->is_open = false;
        return fclose($this->handle);
    }

    /**
     *    Accessor for content so far.
     *    @return string        Bytes sent only.
     *    @access public
     */
    function getSent() {
        return $this->sent;
    }

    /**
     *    Actually opens the low level socket.
     *    @param string $host          Host to connect to.
     *    @param integer $port         Port on host.
     *    @param integer $error_number Recipient of error code.
     *    @param string $error         Recipoent of error message.
     *    @param integer $timeout      Maximum time to wait for connection.
     *    @access protected
     */
    protected function openSocket($host, $port, &$error_number, &$error, $timeout) {
        return @fsockopen($host, $port, $error_number, $error, $timeout);
    }
}

/**
 *    Wrapper for TCP/IP socket over TLS.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleSecureSocket extends SimpleSocket {

    /**
     *    Opens a secure socket for reading and writing.
     *    @param string $host      Hostname to send request to.
     *    @param integer $port     Port on remote machine to open.
     *    @param integer $timeout  Connection timeout in seconds.
     *    @access public
     */
    function __construct($host, $port, $timeout) {
        parent::__construct($host, $port, $timeout);
    }

    /**
     *    Actually opens the low level socket.
     *    @param string $host          Host to connect to.
     *    @param integer $port         Port on host.
     *    @param integer $error_number Recipient of error code.
     *    @param string $error         Recipient of error message.
     *    @param integer $timeout      Maximum time to wait for connection.
     *    @access protected
     */
    function openSocket($host, $port, &$error_number, &$error, $timeout) {
        return parent::openSocket("tls://$host", $port, $error_number, $error, $timeout);
    }
}
 /* .tmp\flat\tag.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\tag.php */ ?>
<?php
/**
 *  Base include file for SimpleTest.
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */

/**#@+
 * include SimpleTest files
 */
//require_once(dirname(__FILE__) . '/page.php');
//require_once(dirname(__FILE__) . '/encoding.php');
/**#@-*/

/**
 *    Creates tags and widgets given HTML tag
 *    attributes.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleTagBuilder {

    /**
     *    Factory for the tag objects. Creates the
     *    appropriate tag object for the incoming tag name
     *    and attributes.
     *    @param string $name        HTML tag name.
     *    @param hash $attributes    Element attributes.
     *    @return SimpleTag          Tag object.
     *    @access public
     */
    function createTag($name, $attributes) {
        static $map = array(
                'a' => 'SimpleAnchorTag',
                'title' => 'SimpleTitleTag',
                'base' => 'SimpleBaseTag',
                'button' => 'SimpleButtonTag',
                'textarea' => 'SimpleTextAreaTag',
                'option' => 'SimpleOptionTag',
                'label' => 'SimpleLabelTag',
                'form' => 'SimpleFormTag',
                'frame' => 'SimpleFrameTag');
        $attributes = $this->keysToLowerCase($attributes);
        if (array_key_exists($name, $map)) {
            $tag_class = $map[$name];
            return new $tag_class($attributes);
        } elseif ($name == 'select') {
            return $this->createSelectionTag($attributes);
        } elseif ($name == 'input') {
            return $this->createInputTag($attributes);
        }
        return new SimpleTag($name, $attributes);
    }

    /**
     *    Factory for selection fields.
     *    @param hash $attributes    Element attributes.
     *    @return SimpleTag          Tag object.
     *    @access protected
     */
    protected function createSelectionTag($attributes) {
        if (isset($attributes['multiple'])) {
            return new MultipleSelectionTag($attributes);
        }
        return new SimpleSelectionTag($attributes);
    }

    /**
     *    Factory for input tags.
     *    @param hash $attributes    Element attributes.
     *    @return SimpleTag          Tag object.
     *    @access protected
     */
    protected function createInputTag($attributes) {
        if (! isset($attributes['type'])) {
            return new SimpleTextTag($attributes);
        }
        $type = strtolower(trim($attributes['type']));
        $map = array(
                'submit' => 'SimpleSubmitTag',
                'image' => 'SimpleImageSubmitTag',
                'checkbox' => 'SimpleCheckboxTag',
                'radio' => 'SimpleRadioButtonTag',
                'text' => 'SimpleTextTag',
                'hidden' => 'SimpleTextTag',
                'password' => 'SimpleTextTag',
                'file' => 'SimpleUploadTag');
        if (array_key_exists($type, $map)) {
            $tag_class = $map[$type];
            return new $tag_class($attributes);
        }
        return false;
    }

    /**
     *    Make the keys lower case for case insensitive look-ups.
     *    @param hash $map   Hash to convert.
     *    @return hash       Unchanged values, but keys lower case.
     *    @access private
     */
    protected function keysToLowerCase($map) {
        $lower = array();
        foreach ($map as $key => $value) {
            $lower[strtolower($key)] = $value;
        }
        return $lower;
    }
}

/**
 *    HTML or XML tag.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleTag {
    private $name;
    private $attributes;
    private $content;

    /**
     *    Starts with a named tag with attributes only.
     *    @param string $name        Tag name.
     *    @param hash $attributes    Attribute names and
     *                               string values. Note that
     *                               the keys must have been
     *                               converted to lower case.
     */
    function __construct($name, $attributes) {
        $this->name = strtolower(trim($name));
        $this->attributes = $attributes;
        $this->content = '';
    }

    /**
     *    Check to see if the tag can have both start and
     *    end tags with content in between.
     *    @return boolean        True if content allowed.
     *    @access public
     */
    function expectEndTag() {
        return true;
    }

    /**
     *    The current tag should not swallow all content for
     *    itself as it's searchable page content. Private
     *    content tags are usually widgets that contain default
     *    values.
     *    @return boolean        False as content is available
     *                           to other tags by default.
     *    @access public
     */
    function isPrivateContent() {
        return false;
    }

    /**
     *    Appends string content to the current content.
     *    @param string $content        Additional text.
     *    @access public
     */
    function addContent($content) {
        $this->content .= (string)$content;
        return $this;
    }

    /**
     *    Adds an enclosed tag to the content.
     *    @param SimpleTag $tag    New tag.
     *    @access public
     */
    function addTag($tag) {
    }

    /**
     *    Adds multiple enclosed tags to the content.
     *    @param array            List of SimpleTag objects to be added.
     */
    function addTags($tags) {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }
    }
    
    /**
     *    Accessor for tag name.
     *    @return string       Name of tag.
     *    @access public
     */
    function getTagName() {
        return $this->name;
    }

    /**
     *    List of legal child elements.
     *    @return array        List of element names.
     *    @access public
     */
    function getChildElements() {
        return array();
    }

    /**
     *    Accessor for an attribute.
     *    @param string $label    Attribute name.
     *    @return string          Attribute value.
     *    @access public
     */
    function getAttribute($label) {
        $label = strtolower($label);
        if (! isset($this->attributes[$label])) {
            return false;
        }
        return (string)$this->attributes[$label];
    }

    /**
     *    Sets an attribute.
     *    @param string $label    Attribute name.
     *    @return string $value   New attribute value.
     *    @access protected
     */
    protected function setAttribute($label, $value) {
        $this->attributes[strtolower($label)] = $value;
    }

    /**
     *    Accessor for the whole content so far.
     *    @return string       Content as big raw string.
     *    @access public
     */
    function getContent() {
        return $this->content;
    }

    /**
     *    Accessor for content reduced to visible text. Acts
     *    like a text mode browser, normalising space and
     *    reducing images to their alt text.
     *    @return string       Content as plain text.
     *    @access public
     */
    function getText() {
        return SimplePage::normalise($this->content);
    }

    /**
     *    Test to see if id attribute matches.
     *    @param string $id        ID to test against.
     *    @return boolean          True on match.
     *    @access public
     */
    function isId($id) {
        return ($this->getAttribute('id') == $id);
    }
}

/**
 *    Base url.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleBaseTag extends SimpleTag {

    /**
     *    Starts with a named tag with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('base', $attributes);
    }

    /**
     *    Base tag is not a block tag.
     *    @return boolean       false
     *    @access public
     */
    function expectEndTag() {
        return false;
    }
}

/**
 *    Page title.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleTitleTag extends SimpleTag {

    /**
     *    Starts with a named tag with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('title', $attributes);
    }
}

/**
 *    Link.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleAnchorTag extends SimpleTag {

    /**
     *    Starts with a named tag with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('a', $attributes);
    }

    /**
     *    Accessor for URL as string.
     *    @return string    Coerced as string.
     *    @access public
     */
    function getHref() {
        $url = $this->getAttribute('href');
        if (is_bool($url)) {
            $url = '';
        }
        return $url;
    }
}

/**
 *    Form element.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleWidget extends SimpleTag {
    private $value;
    private $label;
    private $is_set;

    /**
     *    Starts with a named tag with attributes only.
     *    @param string $name        Tag name.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($name, $attributes) {
        parent::__construct($name, $attributes);
        $this->value = false;
        $this->label = false;
        $this->is_set = false;
    }

    /**
     *    Accessor for name submitted as the key in
     *    GET/POST privateiables hash.
     *    @return string        Parsed value.
     *    @access public
     */
    function getName() {
        return $this->getAttribute('name');
    }

    /**
     *    Accessor for default value parsed with the tag.
     *    @return string        Parsed value.
     *    @access public
     */
    function getDefault() {
        return $this->getAttribute('value');
    }

    /**
     *    Accessor for currently set value or default if
     *    none.
     *    @return string      Value set by form or default
     *                        if none.
     *    @access public
     */
    function getValue() {
        if (! $this->is_set) {
            return $this->getDefault();
        }
        return $this->value;
    }

    /**
     *    Sets the current form element value.
     *    @param string $value       New value.
     *    @return boolean            True if allowed.
     *    @access public
     */
    function setValue($value) {
        $this->value = $value;
        $this->is_set = true;
        return true;
    }

    /**
     *    Resets the form element value back to the
     *    default.
     *    @access public
     */
    function resetValue() {
        $this->is_set = false;
    }

    /**
     *    Allows setting of a label externally, say by a
     *    label tag.
     *    @param string $label    Label to attach.
     *    @access public
     */
    function setLabel($label) {
        $this->label = trim($label);
        return $this;
    }

    /**
     *    Reads external or internal label.
     *    @param string $label    Label to test.
     *    @return boolean         True is match.
     *    @access public
     */
    function isLabel($label) {
        return $this->label == trim($label);
    }

    /**
     *    Dispatches the value into the form encoded packet.
     *    @param SimpleEncoding $encoding    Form packet.
     *    @access public
     */
    function write($encoding) {
        if ($this->getName()) {
            $encoding->add($this->getName(), $this->getValue());
        }
    }
}

/**
 *    Text, password and hidden field.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleTextTag extends SimpleWidget {

    /**
     *    Starts with a named tag with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('input', $attributes);
        if ($this->getAttribute('value') === false) {
            $this->setAttribute('value', '');
        }
    }

    /**
     *    Tag contains no content.
     *    @return boolean        False.
     *    @access public
     */
    function expectEndTag() {
        return false;
    }

    /**
     *    Sets the current form element value. Cannot
     *    change the value of a hidden field.
     *    @param string $value       New value.
     *    @return boolean            True if allowed.
     *    @access public
     */
    function setValue($value) {
        if ($this->getAttribute('type') == 'hidden') {
            return false;
        }
        return parent::setValue($value);
    }
}

/**
 *    Submit button as input tag.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleSubmitTag extends SimpleWidget {

    /**
     *    Starts with a named tag with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('input', $attributes);
        if ($this->getAttribute('value') === false) {
            $this->setAttribute('value', 'Submit');
        }
    }

    /**
     *    Tag contains no end element.
     *    @return boolean        False.
     *    @access public
     */
    function expectEndTag() {
        return false;
    }

    /**
     *    Disables the setting of the button value.
     *    @param string $value       Ignored.
     *    @return boolean            True if allowed.
     *    @access public
     */
    function setValue($value) {
        return false;
    }

    /**
     *    Value of browser visible text.
     *    @return string        Visible label.
     *    @access public
     */
    function getLabel() {
        return $this->getValue();
    }

    /**
     *    Test for a label match when searching.
     *    @param string $label     Label to test.
     *    @return boolean          True on match.
     *    @access public
     */
    function isLabel($label) {
        return trim($label) == trim($this->getLabel());
    }
}

/**
 *    Image button as input tag.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleImageSubmitTag extends SimpleWidget {

    /**
     *    Starts with a named tag with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('input', $attributes);
    }

    /**
     *    Tag contains no end element.
     *    @return boolean        False.
     *    @access public
     */
    function expectEndTag() {
        return false;
    }

    /**
     *    Disables the setting of the button value.
     *    @param string $value       Ignored.
     *    @return boolean            True if allowed.
     *    @access public
     */
    function setValue($value) {
        return false;
    }

    /**
     *    Value of browser visible text.
     *    @return string        Visible label.
     *    @access public
     */
    function getLabel() {
        if ($this->getAttribute('title')) {
            return $this->getAttribute('title');
        }
        return $this->getAttribute('alt');
    }

    /**
     *    Test for a label match when searching.
     *    @param string $label     Label to test.
     *    @return boolean          True on match.
     *    @access public
     */
    function isLabel($label) {
        return trim($label) == trim($this->getLabel());
    }

    /**
     *    Dispatches the value into the form encoded packet.
     *    @param SimpleEncoding $encoding    Form packet.
     *    @param integer $x                  X coordinate of click.
     *    @param integer $y                  Y coordinate of click.
     *    @access public
     */
    function write($encoding, $x = 1, $y = 1) {
        if ($this->getName()) {
            $encoding->add($this->getName() . '.x', $x);
            $encoding->add($this->getName() . '.y', $y);
        } else {
            $encoding->add('x', $x);
            $encoding->add('y', $y);
        }
    }
}

/**
 *    Submit button as button tag.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleButtonTag extends SimpleWidget {

    /**
     *    Starts with a named tag with attributes only.
     *    Defaults are very browser dependent.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('button', $attributes);
    }

    /**
     *    Check to see if the tag can have both start and
     *    end tags with content in between.
     *    @return boolean        True if content allowed.
     *    @access public
     */
    function expectEndTag() {
        return true;
    }

    /**
     *    Disables the setting of the button value.
     *    @param string $value       Ignored.
     *    @return boolean            True if allowed.
     *    @access public
     */
    function setValue($value) {
        return false;
    }

    /**
     *    Value of browser visible text.
     *    @return string        Visible label.
     *    @access public
     */
    function getLabel() {
        return $this->getContent();
    }

    /**
     *    Test for a label match when searching.
     *    @param string $label     Label to test.
     *    @return boolean          True on match.
     *    @access public
     */
    function isLabel($label) {
        return trim($label) == trim($this->getLabel());
    }
}

/**
 *    Content tag for text area.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleTextAreaTag extends SimpleWidget {

    /**
     *    Starts with a named tag with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('textarea', $attributes);
    }

    /**
     *    Accessor for starting value.
     *    @return string        Parsed value.
     *    @access public
     */
    function getDefault() {
        return $this->wrap(html_entity_decode($this->getContent(), ENT_QUOTES));
    }

    /**
     *    Applies word wrapping if needed.
     *    @param string $value      New value.
     *    @return boolean            True if allowed.
     *    @access public
     */
    function setValue($value) {
        return parent::setValue($this->wrap($value));
    }

    /**
     *    Test to see if text should be wrapped.
     *    @return boolean        True if wrapping on.
     *    @access private
     */
    function wrapIsEnabled() {
        if ($this->getAttribute('cols')) {
            $wrap = $this->getAttribute('wrap');
            if (($wrap == 'physical') || ($wrap == 'hard')) {
                return true;
            }
        }
        return false;
    }

    /**
     *    Performs the formatting that is peculiar to
     *    this tag. There is strange behaviour in this
     *    one, including stripping a leading new line.
     *    Go figure. I am using Firefox as a guide.
     *    @param string $text    Text to wrap.
     *    @return string         Text wrapped with carriage
     *                           returns and line feeds
     *    @access private
     */
    protected function wrap($text) {
        $text = str_replace("\r\r\n", "\r\n", str_replace("\n", "\r\n", $text));
        $text = str_replace("\r\n\n", "\r\n", str_replace("\r", "\r\n", $text));
        if (strncmp($text, "\r\n", strlen("\r\n")) == 0) {
            $text = substr($text, strlen("\r\n"));
        }
        if ($this->wrapIsEnabled()) {
            return wordwrap(
                    $text,
                    (integer)$this->getAttribute('cols'),
                    "\r\n");
        }
        return $text;
    }

    /**
     *    The content of textarea is not part of the page.
     *    @return boolean        True.
     *    @access public
     */
    function isPrivateContent() {
        return true;
    }
}

/**
 *    File upload widget.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleUploadTag extends SimpleWidget {

    /**
     *    Starts with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('input', $attributes);
    }

    /**
     *    Tag contains no content.
     *    @return boolean        False.
     *    @access public
     */
    function expectEndTag() {
        return false;
    }

    /**
     *    Dispatches the value into the form encoded packet.
     *    @param SimpleEncoding $encoding    Form packet.
     *    @access public
     */
    function write($encoding) {
        if (! file_exists($this->getValue())) {
            return;
        }
        $encoding->attach(
                $this->getName(),
                implode('', file($this->getValue())),
                basename($this->getValue()));
    }
}

/**
 *    Drop down widget.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleSelectionTag extends SimpleWidget {
    private $options;
    private $choice;

    /**
     *    Starts with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('select', $attributes);
        $this->options = array();
        $this->choice = false;
    }

    /**
     *    Adds an option tag to a selection field.
     *    @param SimpleOptionTag $tag     New option.
     *    @access public
     */
    function addTag($tag) {
        if ($tag->getTagName() == 'option') {
            $this->options[] = $tag;
        }
    }

    /**
     *    Text within the selection element is ignored.
     *    @param string $content        Ignored.
     *    @access public
     */
    function addContent($content) {
        return $this;
    }

    /**
     *    Scans options for defaults. If none, then
     *    the first option is selected.
     *    @return string        Selected field.
     *    @access public
     */
    function getDefault() {
        for ($i = 0, $count = count($this->options); $i < $count; $i++) {
            if ($this->options[$i]->getAttribute('selected') !== false) {
                return $this->options[$i]->getDefault();
            }
        }
        if ($count > 0) {
            return $this->options[0]->getDefault();
        }
        return '';
    }

    /**
     *    Can only set allowed values.
     *    @param string $value       New choice.
     *    @return boolean            True if allowed.
     *    @access public
     */
    function setValue($value) {
        for ($i = 0, $count = count($this->options); $i < $count; $i++) {
            if ($this->options[$i]->isValue($value)) {
                $this->choice = $i;
                return true;
            }
        }
        return false;
    }

    /**
     *    Accessor for current selection value.
     *    @return string      Value attribute or
     *                        content of opton.
     *    @access public
     */
    function getValue() {
        if ($this->choice === false) {
            return $this->getDefault();
        }
        return $this->options[$this->choice]->getValue();
    }
}

/**
 *    Drop down widget.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class MultipleSelectionTag extends SimpleWidget {
    private $options;
    private $values;

    /**
     *    Starts with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('select', $attributes);
        $this->options = array();
        $this->values = false;
    }

    /**
     *    Adds an option tag to a selection field.
     *    @param SimpleOptionTag $tag     New option.
     *    @access public
     */
    function addTag($tag) {
        if ($tag->getTagName() == 'option') {
            $this->options[] = &$tag;
        }
    }

    /**
     *    Text within the selection element is ignored.
     *    @param string $content        Ignored.
     *    @access public
     */
    function addContent($content) {
        return $this;
    }

    /**
     *    Scans options for defaults to populate the
     *    value array().
     *    @return array        Selected fields.
     *    @access public
     */
    function getDefault() {
        $default = array();
        for ($i = 0, $count = count($this->options); $i < $count; $i++) {
            if ($this->options[$i]->getAttribute('selected') !== false) {
                $default[] = $this->options[$i]->getDefault();
            }
        }
        return $default;
    }

    /**
     *    Can only set allowed values. Any illegal value
     *    will result in a failure, but all correct values
     *    will be set.
     *    @param array $desired      New choices.
     *    @return boolean            True if all allowed.
     *    @access public
     */
    function setValue($desired) {
        $achieved = array();
        foreach ($desired as $value) {
            $success = false;
            for ($i = 0, $count = count($this->options); $i < $count; $i++) {
                if ($this->options[$i]->isValue($value)) {
                    $achieved[] = $this->options[$i]->getValue();
                    $success = true;
                    break;
                }
            }
            if (! $success) {
                return false;
            }
        }
        $this->values = $achieved;
        return true;
    }

    /**
     *    Accessor for current selection value.
     *    @return array      List of currently set options.
     *    @access public
     */
    function getValue() {
        if ($this->values === false) {
            return $this->getDefault();
        }
        return $this->values;
    }
}

/**
 *    Option for selection field.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleOptionTag extends SimpleWidget {

    /**
     *    Stashes the attributes.
     */
    function __construct($attributes) {
        parent::__construct('option', $attributes);
    }

    /**
     *    Does nothing.
     *    @param string $value      Ignored.
     *    @return boolean           Not allowed.
     *    @access public
     */
    function setValue($value) {
        return false;
    }

    /**
     *    Test to see if a value matches the option.
     *    @param string $compare    Value to compare with.
     *    @return boolean           True if possible match.
     *    @access public
     */
    function isValue($compare) {
        $compare = trim($compare);
        if (trim($this->getValue()) == $compare) {
            return true;
        }
        return trim(strip_tags($this->getContent())) == $compare;
    }

    /**
     *    Accessor for starting value. Will be set to
     *    the option label if no value exists.
     *    @return string        Parsed value.
     *    @access public
     */
    function getDefault() {
        if ($this->getAttribute('value') === false) {
            return strip_tags($this->getContent());
        }
        return $this->getAttribute('value');
    }

    /**
     *    The content of options is not part of the page.
     *    @return boolean        True.
     *    @access public
     */
    function isPrivateContent() {
        return true;
    }
}

/**
 *    Radio button.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleRadioButtonTag extends SimpleWidget {

    /**
     *    Stashes the attributes.
     *    @param array $attributes        Hash of attributes.
     */
    function __construct($attributes) {
        parent::__construct('input', $attributes);
        if ($this->getAttribute('value') === false) {
            $this->setAttribute('value', 'on');
        }
    }

    /**
     *    Tag contains no content.
     *    @return boolean        False.
     *    @access public
     */
    function expectEndTag() {
        return false;
    }

    /**
     *    The only allowed value sn the one in the
     *    "value" attribute.
     *    @param string $value      New value.
     *    @return boolean           True if allowed.
     *    @access public
     */
    function setValue($value) {
        if ($value === false) {
            return parent::setValue($value);
        }
        if ($value != $this->getAttribute('value')) {
            return false;
        }
        return parent::setValue($value);
    }

    /**
     *    Accessor for starting value.
     *    @return string        Parsed value.
     *    @access public
     */
    function getDefault() {
        if ($this->getAttribute('checked') !== false) {
            return $this->getAttribute('value');
        }
        return false;
    }
}

/**
 *    Checkbox widget.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleCheckboxTag extends SimpleWidget {

    /**
     *    Starts with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('input', $attributes);
        if ($this->getAttribute('value') === false) {
            $this->setAttribute('value', 'on');
        }
    }

    /**
     *    Tag contains no content.
     *    @return boolean        False.
     *    @access public
     */
    function expectEndTag() {
        return false;
    }

    /**
     *    The only allowed value in the one in the
     *    "value" attribute. The default for this
     *    attribute is "on". If this widget is set to
     *    true, then the usual value will be taken.
     *    @param string $value      New value.
     *    @return boolean           True if allowed.
     *    @access public
     */
    function setValue($value) {
        if ($value === false) {
            return parent::setValue($value);
        }
        if ($value === true) {
            return parent::setValue($this->getAttribute('value'));
        }
        if ($value != $this->getAttribute('value')) {
            return false;
        }
        return parent::setValue($value);
    }

    /**
     *    Accessor for starting value. The default
     *    value is "on".
     *    @return string        Parsed value.
     *    @access public
     */
    function getDefault() {
        if ($this->getAttribute('checked') !== false) {
            return $this->getAttribute('value');
        }
        return false;
    }
}

/**
 *    A group of multiple widgets with some shared behaviour.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleTagGroup {
    private $widgets = array();

    /**
     *    Adds a tag to the group.
     *    @param SimpleWidget $widget
     *    @access public
     */
    function addWidget($widget) {
        $this->widgets[] = $widget;
    }

    /**
     *    Accessor to widget set.
     *    @return array        All widgets.
     *    @access protected
     */
    protected function &getWidgets() {
        return $this->widgets;
    }

    /**
     *    Accessor for an attribute.
     *    @param string $label    Attribute name.
     *    @return boolean         Always false.
     *    @access public
     */
    function getAttribute($label) {
        return false;
    }

    /**
     *    Fetches the name for the widget from the first
     *    member.
     *    @return string        Name of widget.
     *    @access public
     */
    function getName() {
        if (count($this->widgets) > 0) {
            return $this->widgets[0]->getName();
        }
    }

    /**
     *    Scans the widgets for one with the appropriate
     *    ID field.
     *    @param string $id        ID value to try.
     *    @return boolean          True if matched.
     *    @access public
     */
    function isId($id) {
        for ($i = 0, $count = count($this->widgets); $i < $count; $i++) {
            if ($this->widgets[$i]->isId($id)) {
                return true;
            }
        }
        return false;
    }

    /**
     *    Scans the widgets for one with the appropriate
     *    attached label.
     *    @param string $label     Attached label to try.
     *    @return boolean          True if matched.
     *    @access public
     */
    function isLabel($label) {
        for ($i = 0, $count = count($this->widgets); $i < $count; $i++) {
            if ($this->widgets[$i]->isLabel($label)) {
                return true;
            }
        }
        return false;
    }

    /**
     *    Dispatches the value into the form encoded packet.
     *    @param SimpleEncoding $encoding    Form packet.
     *    @access public
     */
    function write($encoding) {
        $encoding->add($this->getName(), $this->getValue());
    }
}

/**
 *    A group of tags with the same name within a form.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleCheckboxGroup extends SimpleTagGroup {

    /**
     *    Accessor for current selected widget or false
     *    if none.
     *    @return string/array     Widget values or false if none.
     *    @access public
     */
    function getValue() {
        $values = array();
        $widgets = $this->getWidgets();
        for ($i = 0, $count = count($widgets); $i < $count; $i++) {
            if ($widgets[$i]->getValue() !== false) {
                $values[] = $widgets[$i]->getValue();
            }
        }
        return $this->coerceValues($values);
    }

    /**
     *    Accessor for starting value that is active.
     *    @return string/array      Widget values or false if none.
     *    @access public
     */
    function getDefault() {
        $values = array();
        $widgets = $this->getWidgets();
        for ($i = 0, $count = count($widgets); $i < $count; $i++) {
            if ($widgets[$i]->getDefault() !== false) {
                $values[] = $widgets[$i]->getDefault();
            }
        }
        return $this->coerceValues($values);
    }

    /**
     *    Accessor for current set values.
     *    @param string/array/boolean $values   Either a single string, a
     *                                          hash or false for nothing set.
     *    @return boolean                       True if all values can be set.
     *    @access public
     */
    function setValue($values) {
        $values = $this->makeArray($values);
        if (! $this->valuesArePossible($values)) {
            return false;
        }
        $widgets = $this->getWidgets();
        for ($i = 0, $count = count($widgets); $i < $count; $i++) {
            $possible = $widgets[$i]->getAttribute('value');
            if (in_array($widgets[$i]->getAttribute('value'), $values)) {
                $widgets[$i]->setValue($possible);
            } else {
                $widgets[$i]->setValue(false);
            }
        }
        return true;
    }

    /**
     *    Tests to see if a possible value set is legal.
     *    @param string/array/boolean $values   Either a single string, a
     *                                          hash or false for nothing set.
     *    @return boolean                       False if trying to set a
     *                                          missing value.
     *    @access private
     */
    protected function valuesArePossible($values) {
        $matches = array();
        $widgets = &$this->getWidgets();
        for ($i = 0, $count = count($widgets); $i < $count; $i++) {
            $possible = $widgets[$i]->getAttribute('value');
            if (in_array($possible, $values)) {
                $matches[] = $possible;
            }
        }
        return ($values == $matches);
    }

    /**
     *    Converts the output to an appropriate format. This means
     *    that no values is false, a single value is just that
     *    value and only two or more are contained in an array.
     *    @param array $values           List of values of widgets.
     *    @return string/array/boolean   Expected format for a tag.
     *    @access private
     */
    protected function coerceValues($values) {
        if (count($values) == 0) {
            return false;
        } elseif (count($values) == 1) {
            return $values[0];
        } else {
            return $values;
        }
    }

    /**
     *    Converts false or string into array. The opposite of
     *    the coercian method.
     *    @param string/array/boolean $value  A single item is converted
     *                                        to a one item list. False
     *                                        gives an empty list.
     *    @return array                       List of values, possibly empty.
     *    @access private
     */
    protected function makeArray($value) {
        if ($value === false) {
            return array();
        }
        if (is_string($value)) {
            return array($value);
        }
        return $value;
    }
}

/**
 *    A group of tags with the same name within a form.
 *    Used for radio buttons.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleRadioGroup extends SimpleTagGroup {

    /**
     *    Each tag is tried in turn until one is
     *    successfully set. The others will be
     *    unchecked if successful.
     *    @param string $value      New value.
     *    @return boolean           True if any allowed.
     *    @access public
     */
    function setValue($value) {
        if (! $this->valueIsPossible($value)) {
            return false;
        }
        $index = false;
        $widgets = $this->getWidgets();
        for ($i = 0, $count = count($widgets); $i < $count; $i++) {
            if (! $widgets[$i]->setValue($value)) {
                $widgets[$i]->setValue(false);
            }
        }
        return true;
    }

    /**
     *    Tests to see if a value is allowed.
     *    @param string    Attempted value.
     *    @return boolean  True if a valid value.
     *    @access private
     */
    protected function valueIsPossible($value) {
        $widgets = $this->getWidgets();
        for ($i = 0, $count = count($widgets); $i < $count; $i++) {
            if ($widgets[$i]->getAttribute('value') == $value) {
                return true;
            }
        }
        return false;
    }

    /**
     *    Accessor for current selected widget or false
     *    if none.
     *    @return string/boolean   Value attribute or
     *                             content of opton.
     *    @access public
     */
    function getValue() {
        $widgets = $this->getWidgets();
        for ($i = 0, $count = count($widgets); $i < $count; $i++) {
            if ($widgets[$i]->getValue() !== false) {
                return $widgets[$i]->getValue();
            }
        }
        return false;
    }

    /**
     *    Accessor for starting value that is active.
     *    @return string/boolean      Value of first checked
     *                                widget or false if none.
     *    @access public
     */
    function getDefault() {
        $widgets = $this->getWidgets();
        for ($i = 0, $count = count($widgets); $i < $count; $i++) {
            if ($widgets[$i]->getDefault() !== false) {
                return $widgets[$i]->getDefault();
            }
        }
        return false;
    }
}

/**
 *    Tag to keep track of labels.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleLabelTag extends SimpleTag {

    /**
     *    Starts with a named tag with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('label', $attributes);
    }

    /**
     *    Access for the ID to attach the label to.
     *    @return string        For attribute.
     *    @access public
     */
    function getFor() {
        return $this->getAttribute('for');
    }
}

/**
 *    Tag to aid parsing the form.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleFormTag extends SimpleTag {

    /**
     *    Starts with a named tag with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('form', $attributes);
    }
}

/**
 *    Tag to aid parsing the frames in a page.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleFrameTag extends SimpleTag {

    /**
     *    Starts with a named tag with attributes only.
     *    @param hash $attributes    Attribute names and
     *                               string values.
     */
    function __construct($attributes) {
        parent::__construct('frame', $attributes);
    }

    /**
     *    Tag contains no content.
     *    @return boolean        False.
     *    @access public
     */
    function expectEndTag() {
        return false;
    }
}
 /* .tmp\flat\test_case.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\test_case.php */ ?>
<?php
/**
 *  Base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 * Includes SimpleTest files and defined the root constant
 * for dependent libraries.
 */
//require_once(dirname(__FILE__) . '/invoker.php');
//require_once(dirname(__FILE__) . '/errors.php');
//require_once(dirname(__FILE__) . '/compatibility.php');
//require_once(dirname(__FILE__) . '/scorer.php');
//require_once(dirname(__FILE__) . '/expectation.php');
//require_once(dirname(__FILE__) . '/dumper.php');
//require_once(dirname(__FILE__) . '/simpletest.php');
//require_once(dirname(__FILE__) . '/exceptions.php');
//require_once(dirname(__FILE__) . '/reflection_php5.php');
/**#@-*/
if (! defined('SIMPLE_TEST')) {
    /**
     * @ignore
     */
    define('SIMPLE_TEST', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}

/**
 *    Basic test case. This is the smallest unit of a test
 *    suite. It searches for
 *    all methods that start with the the string "test" and
 *    runs them. Working test cases extend this class.
 *    @package      SimpleTest
 *    @subpackage   UnitTester
 */
class SimpleTestCase {
    private $label = false;
    protected $reporter;
    private $observers;
    private $should_skip = false;

    /**
     *    Sets up the test with no display.
     *    @param string $label    If no test name is given then
     *                            the class name is used.
     *    @access public
     */
    function __construct($label = false) {
        if ($label) {
            $this->label = $label;
        }
    }

    /**
     *    Accessor for the test name for subclasses.
     *    @return string           Name of the test.
     *    @access public
     */
    function getLabel() {
        return $this->label ? $this->label : get_class($this);
    }

    /**
     *    This is a placeholder for skipping tests. In this
     *    method you place skipIf() and skipUnless() calls to
     *    set the skipping state.
     *    @access public
     */
    function skip() {
    }

    /**
     *    Will issue a message to the reporter and tell the test
     *    case to skip if the incoming flag is true.
     *    @param string $should_skip    Condition causing the tests to be skipped.
     *    @param string $message        Text of skip condition.
     *    @access public
     */
    function skipIf($should_skip, $message = '%s') {
        if ($should_skip && ! $this->should_skip) {
            $this->should_skip = true;
            $message = sprintf($message, 'Skipping [' . get_class($this) . ']');
            $this->reporter->paintSkip($message . $this->getAssertionLine());
        }
    }

    /**
     *    Accessor for the private variable $_shoud_skip
     *    @access public
     */
    function shouldSkip() {
        return $this->should_skip;
    }

    /**
     *    Will issue a message to the reporter and tell the test
     *    case to skip if the incoming flag is false.
     *    @param string $shouldnt_skip  Condition causing the tests to be run.
     *    @param string $message        Text of skip condition.
     *    @access public
     */
    function skipUnless($shouldnt_skip, $message = false) {
        $this->skipIf(! $shouldnt_skip, $message);
    }

    /**
     *    Used to invoke the single tests.
     *    @return SimpleInvoker        Individual test runner.
     *    @access public
     */
    function createInvoker() {
        return new SimpleErrorTrappingInvoker(
                new SimpleExceptionTrappingInvoker(new SimpleInvoker($this)));
    }

    /**
     *    Uses reflection to run every method within itself
     *    starting with the string "test" unless a method
     *    is specified.
     *    @param SimpleReporter $reporter    Current test reporter.
     *    @return boolean                    True if all tests passed.
     *    @access public
     */
    function run($reporter) {
        $context = SimpleTest::getContext();
        $context->setTest($this);
        $context->setReporter($reporter);
        $this->reporter = $reporter;
        $started = false;
        foreach ($this->getTests() as $method) {
            if ($reporter->shouldInvoke($this->getLabel(), $method)) {
                $this->skip();
                if ($this->should_skip) {
                    break;
                }
                if (! $started) {
                    $reporter->paintCaseStart($this->getLabel());
                    $started = true;
                }
                $invoker = $this->reporter->createInvoker($this->createInvoker());
                $invoker->before($method);
                $invoker->invoke($method);
                $invoker->after($method);
            }
        }
        if ($started) {
            $reporter->paintCaseEnd($this->getLabel());
        }
        unset($this->reporter);
        return $reporter->getStatus();
    }

    /**
     *    Gets a list of test names. Normally that will
     *    be all internal methods that start with the
     *    name "test". This method should be overridden
     *    if you want a different rule.
     *    @return array        List of test names.
     *    @access public
     */
    function getTests() {
        $methods = array();
        foreach (get_class_methods(get_class($this)) as $method) {
            if ($this->isTest($method)) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     *    Tests to see if the method is a test that should
     *    be run. Currently any method that starts with 'test'
     *    is a candidate unless it is the constructor.
     *    @param string $method        Method name to try.
     *    @return boolean              True if test method.
     *    @access protected
     */
    protected function isTest($method) {
        if (strtolower(substr($method, 0, 4)) == 'test') {
            return ! SimpleTestCompatibility::isA($this, strtolower($method));
        }
        return false;
    }

    /**
     *    Announces the start of the test.
     *    @param string $method    Test method just started.
     *    @access public
     */
    function before($method) {
        $this->reporter->paintMethodStart($method);
        $this->observers = array();
    }

    /**
     *    Sets up unit test wide variables at the start
     *    of each test method. To be overridden in
     *    actual user test cases.
     *    @access public
     */
    function setUp() {
    }

    /**
     *    Clears the data set in the setUp() method call.
     *    To be overridden by the user in actual user test cases.
     *    @access public
     */
    function tearDown() {
    }

    /**
     *    Announces the end of the test. Includes private clean up.
     *    @param string $method    Test method just finished.
     *    @access public
     */
    function after($method) {
        for ($i = 0; $i < count($this->observers); $i++) {
            $this->observers[$i]->atTestEnd($method, $this);
        }
        $this->reporter->paintMethodEnd($method);
    }

    /**
     *    Sets up an observer for the test end.
     *    @param object $observer    Must have atTestEnd()
     *                               method.
     *    @access public
     */
    function tell($observer) {
        $this->observers[] = &$observer;
    }

    /**
     *    @deprecated
     */
    function pass($message = "Pass") {
        if (! isset($this->reporter)) {
            trigger_error('Can only make assertions within test methods');
        }
        $this->reporter->paintPass(
                $message . $this->getAssertionLine());
        return true;
    }

    /**
     *    Sends a fail event with a message.
     *    @param string $message        Message to send.
     *    @access public
     */
    function fail($message = "Fail") {
        if (! isset($this->reporter)) {
            trigger_error('Can only make assertions within test methods');
        }
        $this->reporter->paintFail(
                $message . $this->getAssertionLine());
        return false;
    }

    /**
     *    Formats a PHP error and dispatches it to the
     *    reporter.
     *    @param integer $severity  PHP error code.
     *    @param string $message    Text of error.
     *    @param string $file       File error occoured in.
     *    @param integer $line      Line number of error.
     *    @access public
     */
    function error($severity, $message, $file, $line) {
        if (! isset($this->reporter)) {
            trigger_error('Can only make assertions within test methods');
        }
        $this->reporter->paintError(
                "Unexpected PHP error [$message] severity [$severity] in [$file line $line]");
    }

    /**
     *    Formats an exception and dispatches it to the
     *    reporter.
     *    @param Exception $exception    Object thrown.
     *    @access public
     */
    function exception($exception) {
        $this->reporter->paintException($exception);
    }

    /**
     *    For user defined expansion of the available messages.
     *    @param string $type       Tag for sorting the signals.
     *    @param mixed $payload     Extra user specific information.
     */
    function signal($type, $payload) {
        if (! isset($this->reporter)) {
            trigger_error('Can only make assertions within test methods');
        }
        $this->reporter->paintSignal($type, $payload);
    }

    /**
     *    Runs an expectation directly, for extending the
     *    tests with new expectation classes.
     *    @param SimpleExpectation $expectation  Expectation subclass.
     *    @param mixed $compare               Value to compare.
     *    @param string $message                 Message to display.
     *    @return boolean                        True on pass
     *    @access public
     */
    function assert($expectation, $compare, $message = '%s') {
        if ($expectation->test($compare)) {
            return $this->pass(sprintf(
                    $message,
                    $expectation->overlayMessage($compare, $this->reporter->getDumper())));
        } else {
            return $this->fail(sprintf(
                    $message,
                    $expectation->overlayMessage($compare, $this->reporter->getDumper())));
        }
    }

    /**
     *    Uses a stack trace to find the line of an assertion.
     *    @return string           Line number of first assert*
     *                             method embedded in format string.
     *    @access public
     */
    function getAssertionLine() {
        $trace = new SimpleStackTrace(array('assert', 'expect', 'pass', 'fail', 'skip'));
        return $trace->traceMethod();
    }

    /**
     *    Sends a formatted dump of a variable to the
     *    test suite for those emergency debugging
     *    situations.
     *    @param mixed $variable    Variable to display.
     *    @param string $message    Message to display.
     *    @return mixed             The original variable.
     *    @access public
     */
    function dump($variable, $message = false) {
        $dumper = $this->reporter->getDumper();
        $formatted = $dumper->dump($variable);
        if ($message) {
            $formatted = $message . "\n" . $formatted;
        }
        $this->reporter->paintFormattedMessage($formatted);
        return $variable;
    }

    /**
     *    Accessor for the number of subtests including myelf.
     *    @return integer           Number of test cases.
     *    @access public
     */
    function getSize() {
        return 1;
    }
}

/**
 *  Helps to extract test cases automatically from a file.
 *    @package      SimpleTest
 *    @subpackage   UnitTester
 */
class SimpleFileLoader {

    /**
     *    Builds a test suite from a library of test cases.
     *    The new suite is composed into this one.
     *    @param string $test_file        File name of library with
     *                                    test case classes.
     *    @return TestSuite               The new test suite.
     *    @access public
     */
    function load($test_file) {
        $existing_classes = get_declared_classes();
        $existing_globals = get_defined_vars();
//        include_once($test_file);
        $new_globals = get_defined_vars();
        $this->makeFileVariablesGlobal($existing_globals, $new_globals);
        $new_classes = array_diff(get_declared_classes(), $existing_classes);
        if (empty($new_classes)) {
            $new_classes = $this->scrapeClassesFromFile($test_file);
        }
        $classes = $this->selectRunnableTests($new_classes);
        return $this->createSuiteFromClasses($test_file, $classes);
    }

    /**
     *    Imports new variables into the global namespace.
     *    @param hash $existing   Variables before the file was loaded.
     *    @param hash $new        Variables after the file was loaded.
     *    @access private
     */
    protected function makeFileVariablesGlobal($existing, $new) {
        $globals = array_diff(array_keys($new), array_keys($existing));
        foreach ($globals as $global) {
            $GLOBALS[$global] = $new[$global];
        }
    }

    /**
     *    Lookup classnames from file contents, in case the
     *    file may have been included before.
     *    Note: This is probably too clever by half. Figuring this
     *    out after a failed test case is going to be tricky for us,
     *    never mind the user. A test case should not be included
     *    twice anyway.
     *    @param string $test_file        File name with classes.
     *    @access private
     */
    protected function scrapeClassesFromFile($test_file) {
        preg_match_all('~^\s*class\s+(\w+)(\s+(extends|implements)\s+\w+)*\s*\{~mi',
                        file_get_contents($test_file),
                        $matches );
        return $matches[1];
    }

    /**
     *    Calculates the incoming test cases. Skips abstract
     *    and ignored classes.
     *    @param array $candidates   Candidate classes.
     *    @return array              New classes which are test
     *                               cases that shouldn't be ignored.
     *    @access public
     */
    function selectRunnableTests($candidates) {
        $classes = array();
        foreach ($candidates as $class) {
            if (TestSuite::getBaseTestCase($class)) {
                $reflection = new SimpleReflection($class);
                if ($reflection->isAbstract()) {
                    SimpleTest::ignore($class);
                } else {
                    $classes[] = $class;
                }
            }
        }
        return $classes;
    }

    /**
     *    Builds a test suite from a class list.
     *    @param string $title       Title of new group.
     *    @param array $classes      Test classes.
     *    @return TestSuite          Group loaded with the new
     *                               test cases.
     *    @access public
     */
    function createSuiteFromClasses($title, $classes) {
        if (count($classes) == 0) {
            $suite = new BadTestSuite($title, "No runnable test cases in [$title]");
            return $suite;
        }
        SimpleTest::ignoreParentsIfIgnored($classes);
        $suite = new TestSuite($title);
        foreach ($classes as $class) {
            if (! SimpleTest::isIgnored($class)) {
                $suite->add($class);
            }
        }
        return $suite;
    }
}

/**
 *    This is a composite test class for combining
 *    test cases and other RunnableTest classes into
 *    a group test.
 *    @package      SimpleTest
 *    @subpackage   UnitTester
 */
class TestSuite {
    private $label;
    private $test_cases;

    /**
     *    Sets the name of the test suite.
     *    @param string $label    Name sent at the start and end
     *                            of the test.
     *    @access public
     */
    function TestSuite($label = false) {
        $this->label = $label;
        $this->test_cases = array();
    }

    /**
     *    Accessor for the test name for subclasses. If the suite
     *    wraps a single test case the label defaults to the name of that test.
     *    @return string           Name of the test.
     *    @access public
     */
    function getLabel() {
        if (! $this->label) {
            return ($this->getSize() == 1) ?
                    get_class($this->test_cases[0]) : get_class($this);
        } else {
            return $this->label;
        }
    }

    /**
     *    Adds a test into the suite by instance or class. The class will
     *    be instantiated if it's a test suite.
     *    @param SimpleTestCase $test_case  Suite or individual test
     *                                      case implementing the
     *                                      runnable test interface.
     *    @access public
     */
    function add($test_case) {
        if (! is_string($test_case)) {
            $this->test_cases[] = $test_case;
        } elseif (TestSuite::getBaseTestCase($test_case) == 'testsuite') {
            $this->test_cases[] = new $test_case();
        } else {
            $this->test_cases[] = $test_case;
        }
    }

    /**
     *    Builds a test suite from a library of test cases.
     *    The new suite is composed into this one.
     *    @param string $test_file        File name of library with
     *                                    test case classes.
     *    @access public
     */
    function addFile($test_file) {
        $extractor = new SimpleFileLoader();
        $this->add($extractor->load($test_file));
    }

    /**
     *    Delegates to a visiting collector to add test
     *    files.
     *    @param string $path                  Path to scan from.
     *    @param SimpleCollector $collector    Directory scanner.
     *    @access public
     */
    function collect($path, $collector) {
        $collector->collect($this, $path);
    }

    /**
     *    Invokes run() on all of the held test cases, instantiating
     *    them if necessary.
     *    @param SimpleReporter $reporter    Current test reporter.
     *    @access public
     */
    function run($reporter) {
        $reporter->paintGroupStart($this->getLabel(), $this->getSize());
        for ($i = 0, $count = count($this->test_cases); $i < $count; $i++) {
            if (is_string($this->test_cases[$i])) {
                $class = $this->test_cases[$i];
                $test = new $class();
                $test->run($reporter);
                unset($test);
            } else {
                $this->test_cases[$i]->run($reporter);
            }
        }
        $reporter->paintGroupEnd($this->getLabel());
        return $reporter->getStatus();
    }

    /**
     *    Number of contained test cases.
     *    @return integer     Total count of cases in the group.
     *    @access public
     */
    function getSize() {
        $count = 0;
        foreach ($this->test_cases as $case) {
            if (is_string($case)) {
                if (! SimpleTest::isIgnored($case)) {
                    $count++;
                }
            } else {
                $count += $case->getSize();
            }
        }
        return $count;
    }

    /**
     *    Test to see if a class is derived from the
     *    SimpleTestCase class.
     *    @param string $class     Class name.
     *    @access public
     */
    static function getBaseTestCase($class) {
        while ($class = get_parent_class($class)) {
            $class = strtolower($class);
            if ($class == 'simpletestcase' || $class == 'testsuite') {
                return $class;
            }
        }
        return false;
    }
}

/**
 *    This is a failing group test for when a test suite hasn't
 *    loaded properly.
 *    @package      SimpleTest
 *    @subpackage   UnitTester
 */
class BadTestSuite {
    private $label;
    private $error;

    /**
     *    Sets the name of the test suite and error message.
     *    @param string $label    Name sent at the start and end
     *                            of the test.
     *    @access public
     */
    function BadTestSuite($label, $error) {
        $this->label = $label;
        $this->error = $error;
    }

    /**
     *    Accessor for the test name for subclasses.
     *    @return string           Name of the test.
     *    @access public
     */
    function getLabel() {
        return $this->label;
    }

    /**
     *    Sends a single error to the reporter.
     *    @param SimpleReporter $reporter    Current test reporter.
     *    @access public
     */
    function run($reporter) {
        $reporter->paintGroupStart($this->getLabel(), $this->getSize());
        $reporter->paintFail('Bad TestSuite [' . $this->getLabel() .
                '] with error [' . $this->error . ']');
        $reporter->paintGroupEnd($this->getLabel());
        return $reporter->getStatus();
    }

    /**
     *    Number of contained test cases. Always zero.
     *    @return integer     Total count of cases in the group.
     *    @access public
     */
    function getSize() {
        return 0;
    }
}
 /* .tmp\flat\tidy_parser.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\tidy_parser.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id: php_parser.php 1911 2009-07-29 16:38:04Z lastcraft $
 */

/**
 *    Builds the page object.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleTidyPageBuilder {
    private $page;
    private $forms = array();
    private $labels = array();
    private $widgets_by_id = array();

    public function __destruct() {
        $this->free();
    }

    /**
     *    Frees up any references so as to allow the PHP garbage
     *    collection from unset() to work.
     */
    private function free() {
        unset($this->page);
        $this->forms = array();
        $this->labels = array();
    }

    /**
     *    This builder is only available if the 'tidy' extension is loaded.
     *    @return boolean       True if available.
     */
    function can() {
        return extension_loaded('tidy');
    }

    /**
     *    Reads the raw content the page using HTML Tidy.
     *    @param $response SimpleHttpResponse  Fetched response.
     *    @return SimplePage                   Newly parsed page.
     */
    function parse($response) {
        $this->page = new SimplePage($response);
        $tidied = tidy_parse_string($input = $this->insertGuards($response->getContent()),
                                    array('output-xml' => false, 'wrap' => '0', 'indent' => 'no'),
                                    'latin1');
        $this->walkTree($tidied->html());
        $this->attachLabels($this->widgets_by_id, $this->labels);
        $this->page->setForms($this->forms);
        $page = $this->page;
        $this->free();
        return $page;
    }

    /**
     *    Stops HTMLTidy stripping content that we wish to preserve.
     *    @param string      The raw html.
     *    @return string     The html with guard tags inserted.
     */
    private function insertGuards($html) {
        return $this->insertEmptyTagGuards($this->insertTextareaSimpleWhitespaceGuards($html));
    }

    /**
     *    Removes the extra content added during the parse stage
     *    in order to preserve content we don't want stripped
     *    out by HTMLTidy.
     *    @param string      The raw html.
     *    @return string     The html with guard tags removed.
     */
    private function stripGuards($html) {
        return $this->stripTextareaWhitespaceGuards($this->stripEmptyTagGuards($html));
    }

    /**
     *    HTML tidy strips out empty tags such as <option> which we
     *    need to preserve. This method inserts an additional marker.
     *    @param string      The raw html.
     *    @return string     The html with guards inserted.
     */
    private function insertEmptyTagGuards($html) {
        return preg_replace('#<(option|textarea)([^>]*)>(\s*)</(option|textarea)>#is',
                            '<\1\2>___EMPTY___\3</\4>',
                            $html);
    }

    /**
     *    HTML tidy strips out empty tags such as <option> which we
     *    need to preserve. This method strips additional markers
     *    inserted by SimpleTest to the tidy output used to make the
     *    tags non-empty. This ensures their preservation.
     *    @param string      The raw html.
     *    @return string     The html with guards removed.
     */
    private function stripEmptyTagGuards($html) {
        return preg_replace('#(^|>)(\s*)___EMPTY___(\s*)(</|$)#i', '\2\3', $html);
    }

    /**
     *    By parsing the XML output of tidy, we lose some whitespace
     *    information in textarea tags. We temporarily recode this
     *    data ourselves so as not to lose it.
     *    @param string      The raw html.
     *    @return string     The html with guards inserted.
     */
    private function insertTextareaSimpleWhitespaceGuards($html) {
        return preg_replace_callback('#<textarea([^>]*)>(.*?)</textarea>#is',
                                     array($this, 'insertWhitespaceGuards'),
                                     $html);
    }

    /**
     *  Callback for insertTextareaSimpleWhitespaceGuards().
     *  @param array $matches       Result of preg_replace_callback().
     *  @return string              Guard tags now replace whitespace.
     */
    private function insertWhitespaceGuards($matches) {
        return '<textarea' . $matches[1] . '>' .
                str_replace(array("\n", "\r", "\t", ' '),
                            array('___NEWLINE___', '___CR___', '___TAB___', '___SPACE___'),
                            $matches[2]) .
                '</textarea>';
    }

    /**
     *    Removes the whitespace preserving guards we added
     *    before parsing.
     *    @param string      The raw html.
     *    @return string     The html with guards removed.
     */
    private function stripTextareaWhitespaceGuards($html) {
        return str_replace(array('___NEWLINE___', '___CR___', '___TAB___', '___SPACE___'),
                           array("\n", "\r", "\t", ' '),
                           $html);
    }

    /**
     *  Visits the given node and all children
     *  @param object $node      Tidy XML node.
     */
    private function walkTree($node) {
        if ($node->name == 'a') {
            $this->page->addLink($this->tags()->createTag($node->name, (array)$node->attribute)
                                        ->addContent($this->innerHtml($node)));
        } elseif ($node->name == 'base' and isset($node->attribute['href'])) {
            $this->page->setBase($node->attribute['href']);
        } elseif ($node->name == 'title') {
            $this->page->setTitle($this->tags()->createTag($node->name, (array)$node->attribute)
                                         ->addContent($this->innerHtml($node)));
        } elseif ($node->name == 'frameset') {
            $this->page->setFrames($this->collectFrames($node));
        } elseif ($node->name == 'form') {
            $this->forms[] = $this->walkForm($node, $this->createEmptyForm($node));
        } elseif ($node->name == 'label') {
            $this->labels[] = $this->tags()->createTag($node->name, (array)$node->attribute)
                                           ->addContent($this->innerHtml($node));
        } else {
            $this->walkChildren($node);
        }
    }

    /**
     *  Helper method for traversing the XML tree.
     *  @param object $node     Tidy XML node.
     */
    private function walkChildren($node) {
        if ($node->hasChildren()) {
            foreach ($node->child as $child) {
                $this->walkTree($child);
            }
        }
    }

    /**
     *  Facade for forms containing preparsed widgets.
     *  @param object $node     Tidy XML node.
     *  @return SimpleForm      Facade for SimpleBrowser.
     */
    private function createEmptyForm($node) {
        return new SimpleForm($this->tags()->createTag($node->name, (array)$node->attribute), $this->page);
    }

    /**
     *  Visits the given node and all children
     *  @param object $node      Tidy XML node.
     */
    private function walkForm($node, $form, $enclosing_label = '') {
        if ($node->name == 'a') {
            $this->page->addLink($this->tags()->createTag($node->name, (array)$node->attribute)
                                              ->addContent($this->innerHtml($node)));
        } elseif (in_array($node->name, array('input', 'button', 'textarea', 'select'))) {
            $this->addWidgetToForm($node, $form, $enclosing_label);
        } elseif ($node->name == 'label') {
            $this->labels[] = $this->tags()->createTag($node->name, (array)$node->attribute)
                                           ->addContent($this->innerHtml($node));
            if ($node->hasChildren()) {
                foreach ($node->child as $child) {
                    $this->walkForm($child, $form, SimplePage::normalise($this->innerHtml($node)));
                }
            }
        } elseif ($node->hasChildren()) {
            foreach ($node->child as $child) {
                $this->walkForm($child, $form);
            }
        }
        return $form;
    }

    /**
     *  Tests a node for a "for" atribute. Used for
     *  attaching labels.
     *  @param object $node      Tidy XML node.
     *  @return boolean          True if the "for" attribute exists.
     */
    private function hasFor($node) {
        return isset($node->attribute) and $node->attribute['for'];
    }

    /**
     *  Adds the widget into the form container.
     *  @param object $node             Tidy XML node of widget.
     *  @param SimpleForm $form         Form to add it to.
     *  @param string $enclosing_label  The label of any label
     *                                  tag we might be in.
     */
    private function addWidgetToForm($node, $form, $enclosing_label) {
        $widget = $this->tags()->createTag($node->name, $this->attributes($node));
        if (! $widget) {
            return;
        }
        $widget->setLabel($enclosing_label)
               ->addContent($this->innerHtml($node));
        if ($node->name == 'select') {
            $widget->addTags($this->collectSelectOptions($node));
        }
        $form->addWidget($widget);
        $this->indexWidgetById($widget);
    }

    /**
     *  Fills the widget cache to speed up searching.
     *  @param SimpleTag $widget    Parsed widget to cache.
     */
    private function indexWidgetById($widget) {
        $id = $widget->getAttribute('id');
        if (! $id) {
            return;
        }
        if (! isset($this->widgets_by_id[$id])) {
            $this->widgets_by_id[$id] = array();
        }
        $this->widgets_by_id[$id][] = $widget;
    }

    /**
     *  Parses the options from inside an XML select node.
     *  @param object $node      Tidy XML node.
     *  @return array            List of SimpleTag options.
     */
    private function collectSelectOptions($node) {
        $options = array();
        if ($node->name == 'option') {
            $options[] = $this->tags()->createTag($node->name, $this->attributes($node))
                                      ->addContent($this->innerHtml($node));
        }
        if ($node->hasChildren()) {
            foreach ($node->child as $child) {
                $options = array_merge($options, $this->collectSelectOptions($child));
            }
        }
        return $options;
    }

    /**
     *  Convenience method for collecting all the attributes
     *  of a tag. Not sure why Tidy does not have this.
     *  @param object $node      Tidy XML node.
     *  @return array            Hash of attribute strings.
     */
    private function attributes($node) {
        if (! preg_match('|<[^ ]+\s(.*?)/?>|s', $node->value, $first_tag_contents)) {
            return array();
        }
        $attributes = array();
        preg_match_all('/\S+\s*=\s*\'[^\']*\'|(\S+\s*=\s*"[^"]*")|([^ =]+\s*=\s*[^ "\']+?)|[^ "\']+/', $first_tag_contents[1], $matches);
        foreach($matches[0] as $unparsed) {
            $attributes = $this->mergeAttribute($attributes, $unparsed);
        }
        return $attributes;
    }

    /**
     *  Overlay an attribute into the attributes hash.
     *  @param array $attributes        Current attribute list.
     *  @param string $raw              Raw attribute string with
     *                                  both key and value.
     *  @return array                   New attribute hash.
     */
    private function mergeAttribute($attributes, $raw) {
        $parts = explode('=', $raw);
        list($name, $value) = count($parts) == 1 ? array($parts[0], $parts[0]) : $parts;
        $attributes[trim($name)] = html_entity_decode($this->dequote(trim($value)), ENT_QUOTES);
        return $attributes;
    }

    /**
     *  Remove start and end quotes.
     *  @param string $quoted    A quoted string.
     *  @return string           Quotes are gone.
     */
    private function dequote($quoted) {
        if (preg_match('/^(\'([^\']*)\'|"([^"]*)")$/', $quoted, $matches)) {
            return isset($matches[3]) ? $matches[3] : $matches[2];
        }
        return $quoted;
    }

    /**
     *  Collects frame information inside a frameset tag.
     *  @param object $node     Tidy XML node.
     *  @return array           List of SimpleTag frame descriptions.
     */
    private function collectFrames($node) {
        $frames = array();
        if ($node->name == 'frame') {
            $frames = array($this->tags()->createTag($node->name, (array)$node->attribute));
        } else if ($node->hasChildren()) {
            $frames = array();
            foreach ($node->child as $child) {
                $frames = array_merge($frames, $this->collectFrames($child));
            }
        }
        return $frames;
    }

    /**
     *  Extracts the XML node text.
     *  @param object $node     Tidy XML node.
     *  @return string          The text only.
     */
    private function innerHtml($node) {
        $raw = '';
        if ($node->hasChildren()) {
            foreach ($node->child as $child) {
                $raw .= $child->value;
            }
        }
        return $this->stripGuards($raw);
    }

    /**
     *  Factory for parsed content holders.
     *  @return SimpleTagBuilder    Factory.
     */
    private function tags() {
        return new SimpleTagBuilder();
    }

    /**
     *  Called at the end of a parse run. Attaches any
     *  non-wrapping labels to their form elements.
     *  @param array $widgets_by_id     Cached SimpleTag hash.
     *  @param array $labels            SimpleTag label elements.
     */
    private function attachLabels($widgets_by_id, $labels) {
        foreach ($labels as $label) {
            $for = $label->getFor();
            if ($for and isset($widgets_by_id[$for])) {
                $text = $label->getText();
                foreach ($widgets_by_id[$for] as $widget) {
                    $widget->setLabel($text);
                }
            }
        }
    }
}
 /* .tmp\flat\url.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\url.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/encoding.php');
/**#@-*/

/**
 *    URL parser to replace parse_url() PHP function which
 *    got broken in PHP 4.3.0. Adds some browser specific
 *    functionality such as expandomatics.
 *    Guesses a bit trying to separate the host from
 *    the path and tries to keep a raw, possibly unparsable,
 *    request string as long as possible.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleUrl {
    private $scheme;
    private $username;
    private $password;
    private $host;
    private $port;
    public $path;
    private $request;
    private $fragment;
    private $x;
    private $y;
    private $target;
    private $raw = false;
    
    /**
     *    Constructor. Parses URL into sections.
     *    @param string $url        Incoming URL.
     *    @access public
     */
    function __construct($url = '') {
        list($x, $y) = $this->chompCoordinates($url);
        $this->setCoordinates($x, $y);
        $this->scheme = $this->chompScheme($url);
        if ($this->scheme === 'file') {
            // Unescaped backslashes not used in directory separator context
            // will get caught by this, but they should have been urlencoded
            // anyway so we don't care. If this ends up being a problem, the
            // host regexp must be modified to match for backslashes when
            // the scheme is file.
            $url = str_replace('\\', '/', $url);
        }
        list($this->username, $this->password) = $this->chompLogin($url);
        $this->host = $this->chompHost($url);
        $this->port = false;
        if (preg_match('/(.*?):(.*)/', $this->host, $host_parts)) {
            if ($this->scheme === 'file' && strlen($this->host) === 2) {
                // DOS drive was placed in authority; promote it to path.
                $url = '/' . $this->host . $url;
                $this->host = false;
            } else {
                $this->host = $host_parts[1];
                $this->port = (integer)$host_parts[2];
            }
        }
        $this->path = $this->chompPath($url);
        $this->request = $this->parseRequest($this->chompRequest($url));
        $this->fragment = (strncmp($url, "#", 1) == 0 ? substr($url, 1) : false);
        $this->target = false;
    }
    
    /**
     *    Extracts the X, Y coordinate pair from an image map.
     *    @param string $url   URL so far. The coordinates will be
     *                         removed.
     *    @return array        X, Y as a pair of integers.
     *    @access private
     */
    protected function chompCoordinates(&$url) {
        if (preg_match('/(.*)\?(\d+),(\d+)$/', $url, $matches)) {
            $url = $matches[1];
            return array((integer)$matches[2], (integer)$matches[3]);
        }
        return array(false, false);
    }
    
    /**
     *    Extracts the scheme part of an incoming URL.
     *    @param string $url   URL so far. The scheme will be
     *                         removed.
     *    @return string       Scheme part or false.
     *    @access private
     */
    protected function chompScheme(&$url) {
        if (preg_match('#^([^/:]*):(//)(.*)#', $url, $matches)) {
            $url = $matches[2] . $matches[3];
            return $matches[1];
        }
        return false;
    }
    
    /**
     *    Extracts the username and password from the
     *    incoming URL. The // prefix will be reattached
     *    to the URL after the doublet is extracted.
     *    @param string $url    URL so far. The username and
     *                          password are removed.
     *    @return array         Two item list of username and
     *                          password. Will urldecode() them.
     *    @access private
     */
    protected function chompLogin(&$url) {
        $prefix = '';
        if (preg_match('#^(//)(.*)#', $url, $matches)) {
            $prefix = $matches[1];
            $url = $matches[2];
        }
        if (preg_match('#^([^/]*)@(.*)#', $url, $matches)) {
            $url = $prefix . $matches[2];
            $parts = explode(":", $matches[1]);
            return array(
                    urldecode($parts[0]),
                    isset($parts[1]) ? urldecode($parts[1]) : false);
        }
        $url = $prefix . $url;
        return array(false, false);
    }
    
    /**
     *    Extracts the host part of an incoming URL.
     *    Includes the port number part. Will extract
     *    the host if it starts with // or it has
     *    a top level domain or it has at least two
     *    dots.
     *    @param string $url    URL so far. The host will be
     *                          removed.
     *    @return string        Host part guess or false.
     *    @access private
     */
    protected function chompHost(&$url) {
        if (preg_match('!^(//)(.*?)(/.*|\?.*|#.*|$)!', $url, $matches)) {
            $url = $matches[3];
            return $matches[2];
        }
        if (preg_match('!(.*?)(\.\./|\./|/|\?|#|$)(.*)!', $url, $matches)) {
            $tlds = SimpleUrl::getAllTopLevelDomains();
            if (preg_match('/[a-z0-9\-]+\.(' . $tlds . ')/i', $matches[1])) {
                $url = $matches[2] . $matches[3];
                return $matches[1];
            } elseif (preg_match('/[a-z0-9\-]+\.[a-z0-9\-]+\.[a-z0-9\-]+/i', $matches[1])) {
                $url = $matches[2] . $matches[3];
                return $matches[1];
            }
        }
        return false;
    }
    
    /**
     *    Extracts the path information from the incoming
     *    URL. Strips this path from the URL.
     *    @param string $url     URL so far. The host will be
     *                           removed.
     *    @return string         Path part or '/'.
     *    @access private
     */
    protected function chompPath(&$url) {
        if (preg_match('/(.*?)(\?|#|$)(.*)/', $url, $matches)) {
            $url = $matches[2] . $matches[3];
            return ($matches[1] ? $matches[1] : '');
        }
        return '';
    }
    
    /**
     *    Strips off the request data.
     *    @param string $url  URL so far. The request will be
     *                        removed.
     *    @return string      Raw request part.
     *    @access private
     */
    protected function chompRequest(&$url) {
        if (preg_match('/\?(.*?)(#|$)(.*)/', $url, $matches)) {
            $url = $matches[2] . $matches[3];
            return $matches[1];
        }
        return '';
    }
        
    /**
     *    Breaks the request down into an object.
     *    @param string $raw           Raw request.
     *    @return SimpleFormEncoding    Parsed data.
     *    @access private
     */
    protected function parseRequest($raw) {
        $this->raw = $raw;
        $request = new SimpleGetEncoding();
        foreach (explode("&", $raw) as $pair) {
            if (preg_match('/(.*?)=(.*)/', $pair, $matches)) {
                $request->add($matches[1], urldecode($matches[2]));
            } elseif ($pair) {
                $request->add($pair, '');
            }
        }
        return $request;
    }
    
    /**
     *    Accessor for protocol part.
     *    @param string $default    Value to use if not present.
     *    @return string            Scheme name, e.g "http".
     *    @access public
     */
    function getScheme($default = false) {
        return $this->scheme ? $this->scheme : $default;
    }
    
    /**
     *    Accessor for user name.
     *    @return string    Username preceding host.
     *    @access public
     */
    function getUsername() {
        return $this->username;
    }
    
    /**
     *    Accessor for password.
     *    @return string    Password preceding host.
     *    @access public
     */
    function getPassword() {
        return $this->password;
    }
    
    /**
     *    Accessor for hostname and port.
     *    @param string $default    Value to use if not present.
     *    @return string            Hostname only.
     *    @access public
     */
    function getHost($default = false) {
        return $this->host ? $this->host : $default;
    }
    
    /**
     *    Accessor for top level domain.
     *    @return string       Last part of host.
     *    @access public
     */
    function getTld() {
        $path_parts = pathinfo($this->getHost());
        return (isset($path_parts['extension']) ? $path_parts['extension'] : false);
    }
    
    /**
     *    Accessor for port number.
     *    @return integer    TCP/IP port number.
     *    @access public
     */
    function getPort() {
        return $this->port;
    }        
            
    /**
     *    Accessor for path.
     *    @return string    Full path including leading slash if implied.
     *    @access public
     */
    function getPath() {
        if (! $this->path && $this->host) {
            return '/';
        }
        return $this->path;
    }
    
    /**
     *    Accessor for page if any. This may be a
     *    directory name if ambiguious.
     *    @return            Page name.
     *    @access public
     */
    function getPage() {
        if (! preg_match('/([^\/]*?)$/', $this->getPath(), $matches)) {
            return false;
        }
        return $matches[1];
    }
    
    /**
     *    Gets the path to the page.
     *    @return string       Path less the page.
     *    @access public
     */
    function getBasePath() {
        if (! preg_match('/(.*\/)[^\/]*?$/', $this->getPath(), $matches)) {
            return false;
        }
        return $matches[1];
    }
    
    /**
     *    Accessor for fragment at end of URL after the "#".
     *    @return string    Part after "#".
     *    @access public
     */
    function getFragment() {
        return $this->fragment;
    }
    
    /**
     *    Sets image coordinates. Set to false to clear
     *    them.
     *    @param integer $x    Horizontal position.
     *    @param integer $y    Vertical position.
     *    @access public
     */
    function setCoordinates($x = false, $y = false) {
        if (($x === false) || ($y === false)) {
            $this->x = $this->y = false;
            return;
        }
        $this->x = (integer)$x;
        $this->y = (integer)$y;
    }
    
    /**
     *    Accessor for horizontal image coordinate.
     *    @return integer        X value.
     *    @access public
     */
    function getX() {
        return $this->x;
    }
        
    /**
     *    Accessor for vertical image coordinate.
     *    @return integer        Y value.
     *    @access public
     */
    function getY() {
        return $this->y;
    }
    
    /**
     *    Accessor for current request parameters
     *    in URL string form. Will return teh original request
     *    if at all possible even if it doesn't make much
     *    sense.
     *    @return string   Form is string "?a=1&b=2", etc.
     *    @access public
     */
    function getEncodedRequest() {
        if ($this->raw) {
            $encoded = $this->raw;
        } else {
            $encoded = $this->request->asUrlRequest();
        }
        if ($encoded) {
            return '?' . preg_replace('/^\?/', '', $encoded);
        }
        return '';
    }
    
    /**
     *    Adds an additional parameter to the request.
     *    @param string $key            Name of parameter.
     *    @param string $value          Value as string.
     *    @access public
     */
    function addRequestParameter($key, $value) {
        $this->raw = false;
        $this->request->add($key, $value);
    }
    
    /**
     *    Adds additional parameters to the request.
     *    @param hash/SimpleFormEncoding $parameters   Additional
     *                                                parameters.
     *    @access public
     */
    function addRequestParameters($parameters) {
        $this->raw = false;
        $this->request->merge($parameters);
    }
    
    /**
     *    Clears down all parameters.
     *    @access public
     */
    function clearRequest() {
        $this->raw = false;
        $this->request = new SimpleGetEncoding();
    }
    
    /**
     *    Gets the frame target if present. Although
     *    not strictly part of the URL specification it
     *    acts as similarily to the browser.
     *    @return boolean/string    Frame name or false if none.
     *    @access public
     */
    function getTarget() {
        return $this->target;
    }
    
    /**
     *    Attaches a frame target.
     *    @param string $frame        Name of frame.
     *    @access public
     */
    function setTarget($frame) {
        $this->raw = false;
        $this->target = $frame;
    }
    
    /**
     *    Renders the URL back into a string.
     *    @return string        URL in canonical form.
     *    @access public
     */
    function asString() {
        $path = $this->path;
        $scheme = $identity = $host = $port = $encoded = $fragment = '';
        if ($this->username && $this->password) {
            $identity = $this->username . ':' . $this->password . '@';
        }
        if ($this->getHost()) {
            $scheme = $this->getScheme() ? $this->getScheme() : 'http';
            $scheme .= '://';
            $host = $this->getHost();
        } elseif ($this->getScheme() === 'file') {
            // Safest way; otherwise, file URLs on Windows have an extra
            // leading slash. It might be possible to convert file://
            // URIs to local file paths, but that requires more research.
            $scheme = 'file://';
        }
        if ($this->getPort() && $this->getPort() != 80 ) {
            $port = ':'.$this->getPort();
        }

        if (substr($this->path, 0, 1) == '/') {
            $path = $this->normalisePath($this->path);
        }
        $encoded = $this->getEncodedRequest();
        $fragment = $this->getFragment() ? '#'. $this->getFragment() : '';
        $coords = $this->getX() === false ? '' : '?' . $this->getX() . ',' . $this->getY();
        return "$scheme$identity$host$port$path$encoded$fragment$coords";
    }
    
    /**
     *    Replaces unknown sections to turn a relative
     *    URL into an absolute one. The base URL can
     *    be either a string or a SimpleUrl object.
     *    @param string/SimpleUrl $base       Base URL.
     *    @access public
     */
    function makeAbsolute($base) {
        if (! is_object($base)) {
            $base = new SimpleUrl($base);
        }
        if ($this->getHost()) {
            $scheme = $this->getScheme();
            $host = $this->getHost();
            $port = $this->getPort() ? ':' . $this->getPort() : '';
            $identity = $this->getIdentity() ? $this->getIdentity() . '@' : '';
            if (! $identity) {
                $identity = $base->getIdentity() ? $base->getIdentity() . '@' : '';
            }
        } else {
            $scheme = $base->getScheme();
            $host = $base->getHost();
            $port = $base->getPort() ? ':' . $base->getPort() : '';
            $identity = $base->getIdentity() ? $base->getIdentity() . '@' : '';
        }
        $path = $this->normalisePath($this->extractAbsolutePath($base));
        $encoded = $this->getEncodedRequest();
        $fragment = $this->getFragment() ? '#'. $this->getFragment() : '';
        $coords = $this->getX() === false ? '' : '?' . $this->getX() . ',' . $this->getY();
        return new SimpleUrl("$scheme://$identity$host$port$path$encoded$fragment$coords");
    }
    
    /**
     *    Replaces unknown sections of the path with base parts
     *    to return a complete absolute one.
     *    @param string/SimpleUrl $base       Base URL.
     *    @param string                       Absolute path.
     *    @access private
     */
    protected function extractAbsolutePath($base) {
        if ($this->getHost()) {
            return $this->path;
        }
        if (! $this->isRelativePath($this->path)) {
            return $this->path;
        }
        if ($this->path) {
            return $base->getBasePath() . $this->path;
        }
        return $base->getPath();
    }
    
    /**
     *    Simple test to see if a path part is relative.
     *    @param string $path        Path to test.
     *    @return boolean            True if starts with a "/".
     *    @access private
     */
    protected function isRelativePath($path) {
        return (substr($path, 0, 1) != '/');
    }
    
    /**
     *    Extracts the username and password for use in rendering
     *    a URL.
     *    @return string/boolean    Form of username:password or false.
     *    @access public
     */
    function getIdentity() {
        if ($this->username && $this->password) {
            return $this->username . ':' . $this->password;
        }
        return false;
    }
    
    /**
     *    Replaces . and .. sections of the path.
     *    @param string $path    Unoptimised path.
     *    @return string         Path with dots removed if possible.
     *    @access public
     */
    function normalisePath($path) {
        $path = preg_replace('|/\./|', '/', $path);
        return preg_replace('|/[^/]+/\.\./|', '/', $path);
    }
    
    /**
     *    A pipe seperated list of all TLDs that result in two part
     *    domain names.
     *    @return string        Pipe separated list.
     *    @access public
     */
    static function getAllTopLevelDomains() {
        return 'com|edu|net|org|gov|mil|int|biz|info|name|pro|aero|coop|museum';
    }
}
 /* .tmp\flat\user_agent.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\user_agent.php */ ?>
<?php
/**
 *  Base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/cookies.php');
//require_once(dirname(__FILE__) . '/http.php');
//require_once(dirname(__FILE__) . '/encoding.php');
//require_once(dirname(__FILE__) . '/authentication.php');
/**#@-*/

if (! defined('DEFAULT_MAX_REDIRECTS')) {
    define('DEFAULT_MAX_REDIRECTS', 3);
}
if (! defined('DEFAULT_CONNECTION_TIMEOUT')) {
    define('DEFAULT_CONNECTION_TIMEOUT', 15);
}

/**
 *    Fetches web pages whilst keeping track of
 *    cookies and authentication.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleUserAgent {
    private $cookie_jar;
    private $cookies_enabled = true;
    private $authenticator;
    private $max_redirects = DEFAULT_MAX_REDIRECTS;
    private $proxy = false;
    private $proxy_username = false;
    private $proxy_password = false;
    private $connection_timeout = DEFAULT_CONNECTION_TIMEOUT;
    private $additional_headers = array();
    
    /**
     *    Starts with no cookies, realms or proxies.
     *    @access public
     */
    function __construct() {
        $this->cookie_jar = new SimpleCookieJar();
        $this->authenticator = new SimpleAuthenticator();
    }
    
    /**
     *    Removes expired and temporary cookies as if
     *    the browser was closed and re-opened. Authorisation
     *    has to be obtained again as well.
     *    @param string/integer $date   Time when session restarted.
     *                                  If omitted then all persistent
     *                                  cookies are kept.
     *    @access public
     */
    function restart($date = false) {
        $this->cookie_jar->restartSession($date);
        $this->authenticator->restartSession();
    }
    
    /**
     *    Adds a header to every fetch.
     *    @param string $header       Header line to add to every
     *                                request until cleared.
     *    @access public
     */
    function addHeader($header) {
        $this->additional_headers[] = $header;
    }
    
    /**
     *    Ages the cookies by the specified time.
     *    @param integer $interval    Amount in seconds.
     *    @access public
     */
    function ageCookies($interval) {
        $this->cookie_jar->agePrematurely($interval);
    }
    
    /**
     *    Sets an additional cookie. If a cookie has
     *    the same name and path it is replaced.
     *    @param string $name            Cookie key.
     *    @param string $value           Value of cookie.
     *    @param string $host            Host upon which the cookie is valid.
     *    @param string $path            Cookie path if not host wide.
     *    @param string $expiry          Expiry date.
     *    @access public
     */
    function setCookie($name, $value, $host = false, $path = '/', $expiry = false) {
        $this->cookie_jar->setCookie($name, $value, $host, $path, $expiry);
    }
    
    /**
     *    Reads the most specific cookie value from the
     *    browser cookies.
     *    @param string $host        Host to search.
     *    @param string $path        Applicable path.
     *    @param string $name        Name of cookie to read.
     *    @return string             False if not present, else the
     *                               value as a string.
     *    @access public
     */
    function getCookieValue($host, $path, $name) {
        return $this->cookie_jar->getCookieValue($host, $path, $name);
    }
    
    /**
     *    Reads the current cookies within the base URL.
     *    @param string $name     Key of cookie to find.
     *    @param SimpleUrl $base  Base URL to search from.
     *    @return string/boolean  Null if there is no base URL, false
     *                            if the cookie is not set.
     *    @access public
     */
    function getBaseCookieValue($name, $base) {
        if (! $base) {
            return null;
        }
        return $this->getCookieValue($base->getHost(), $base->getPath(), $name);
    }
    
    /**
     *    Switches off cookie sending and recieving.
     *    @access public
     */
    function ignoreCookies() {
        $this->cookies_enabled = false;
    }
    
    /**
     *    Switches back on the cookie sending and recieving.
     *    @access public
     */
    function useCookies() {
        $this->cookies_enabled = true;
    }
    
    /**
     *    Sets the socket timeout for opening a connection.
     *    @param integer $timeout      Maximum time in seconds.
     *    @access public
     */
    function setConnectionTimeout($timeout) {
        $this->connection_timeout = $timeout;
    }
    
    /**
     *    Sets the maximum number of redirects before
     *    a page will be loaded anyway.
     *    @param integer $max        Most hops allowed.
     *    @access public
     */
    function setMaximumRedirects($max) {
        $this->max_redirects = $max;
    }
    
    /**
     *    Sets proxy to use on all requests for when
     *    testing from behind a firewall. Set URL
     *    to false to disable.
     *    @param string $proxy        Proxy URL.
     *    @param string $username     Proxy username for authentication.
     *    @param string $password     Proxy password for authentication.
     *    @access public
     */
    function useProxy($proxy, $username, $password) {
        if (! $proxy) {
            $this->proxy = false;
            return;
        }
        if ((strncmp($proxy, 'http://', 7) != 0) && (strncmp($proxy, 'https://', 8) != 0)) {
            $proxy = 'http://'. $proxy;
        }
        $this->proxy = new SimpleUrl($proxy);
        $this->proxy_username = $username;
        $this->proxy_password = $password;
    }
    
    /**
     *    Test to see if the redirect limit is passed.
     *    @param integer $redirects        Count so far.
     *    @return boolean                  True if over.
     *    @access private
     */
    protected function isTooManyRedirects($redirects) {
        return ($redirects > $this->max_redirects);
    }
    
    /**
     *    Sets the identity for the current realm.
     *    @param string $host        Host to which realm applies.
     *    @param string $realm       Full name of realm.
     *    @param string $username    Username for realm.
     *    @param string $password    Password for realm.
     *    @access public
     */
    function setIdentity($host, $realm, $username, $password) {
        $this->authenticator->setIdentityForRealm($host, $realm, $username, $password);
    }
    
    /**
     *    Fetches a URL as a response object. Will keep trying if redirected.
     *    It will also collect authentication realm information.
     *    @param string/SimpleUrl $url      Target to fetch.
     *    @param SimpleEncoding $encoding   Additional parameters for request.
     *    @return SimpleHttpResponse        Hopefully the target page.
     *    @access public
     */
    function fetchResponse($url, $encoding) {
        if ($encoding->getMethod() != 'POST') {
            $url->addRequestParameters($encoding);
            $encoding->clear();
        }
        $response = $this->fetchWhileRedirected($url, $encoding);
        if ($headers = $response->getHeaders()) {
            if ($headers->isChallenge()) {
                $this->authenticator->addRealm(
                        $url,
                        $headers->getAuthentication(),
                        $headers->getRealm());
            }
        }
        return $response;
    }
    
    /**
     *    Fetches the page until no longer redirected or
     *    until the redirect limit runs out.
     *    @param SimpleUrl $url                  Target to fetch.
     *    @param SimpelFormEncoding $encoding    Additional parameters for request.
     *    @return SimpleHttpResponse             Hopefully the target page.
     *    @access private
     */
    protected function fetchWhileRedirected($url, $encoding) {
        $redirects = 0;
        do {
            $response = $this->fetch($url, $encoding);
            if ($response->isError()) {
                return $response;
            }
            $headers = $response->getHeaders();
            $location = new SimpleUrl($headers->getLocation());
            $url = $location->makeAbsolute($url);
            if ($this->cookies_enabled) {
                $headers->writeCookiesToJar($this->cookie_jar, $url);
            }
            if (! $headers->isRedirect()) {
                break;
            }
            $encoding = new SimpleGetEncoding();
        } while (! $this->isTooManyRedirects(++$redirects));
        return $response;
    }
    
    /**
     *    Actually make the web request.
     *    @param SimpleUrl $url                   Target to fetch.
     *    @param SimpleFormEncoding $encoding     Additional parameters for request.
     *    @return SimpleHttpResponse              Headers and hopefully content.
     *    @access protected
     */
    protected function fetch($url, $encoding) {
        $request = $this->createRequest($url, $encoding);
        return $request->fetch($this->connection_timeout);
    }
    
    /**
     *    Creates a full page request.
     *    @param SimpleUrl $url                 Target to fetch as url object.
     *    @param SimpleFormEncoding $encoding   POST/GET parameters.
     *    @return SimpleHttpRequest             New request.
     *    @access private
     */
    protected function createRequest($url, $encoding) {
        $request = $this->createHttpRequest($url, $encoding);
        $this->addAdditionalHeaders($request);
        if ($this->cookies_enabled) {
            $request->readCookiesFromJar($this->cookie_jar, $url);
        }
        $this->authenticator->addHeaders($request, $url);
        return $request;
    }
    
    /**
     *    Builds the appropriate HTTP request object.
     *    @param SimpleUrl $url                  Target to fetch as url object.
     *    @param SimpleFormEncoding $parameters  POST/GET parameters.
     *    @return SimpleHttpRequest              New request object.
     *    @access protected
     */
    protected function createHttpRequest($url, $encoding) {
        return new SimpleHttpRequest($this->createRoute($url), $encoding);
    }
    
    /**
     *    Sets up either a direct route or via a proxy.
     *    @param SimpleUrl $url   Target to fetch as url object.
     *    @return SimpleRoute     Route to take to fetch URL.
     *    @access protected
     */
    protected function createRoute($url) {
        if ($this->proxy) {
            return new SimpleProxyRoute(
                    $url,
                    $this->proxy,
                    $this->proxy_username,
                    $this->proxy_password);
        }
        return new SimpleRoute($url);
    }
    
    /**
     *    Adds additional manual headers.
     *    @param SimpleHttpRequest $request    Outgoing request.
     *    @access private
     */
    protected function addAdditionalHeaders(&$request) {
        foreach ($this->additional_headers as $header) {
            $request->addHeaderLine($header);
        }
    }
}
 /* .tmp\flat\1\default_reporter.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\1\default_reporter.php */ ?>
<?php
/**
 *  Optional include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/simpletest.php');
//require_once(dirname(__FILE__) . '/scorer.php');
//require_once(dirname(__FILE__) . '/reporter.php');
//require_once(dirname(__FILE__) . '/xml.php');
/**#@-*/

/**
 *    Parser for command line arguments. Extracts
 *    the a specific test to run and engages XML
 *    reporting when necessary.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleCommandLineParser {
    private $to_property = array(
            'case' => 'case', 'c' => 'case',
            'test' => 'test', 't' => 'test',
    );
    private $case = '';
    private $test = '';
    private $xml = false;
    private $help = false;
    private $no_skips = false;

    /**
     *    Parses raw command line arguments into object properties.
     *    @param string $arguments        Raw commend line arguments.
     */
    function __construct($arguments) {
        if (! is_array($arguments)) {
            return;
        }
        foreach ($arguments as $i => $argument) {
            if (preg_match('/^--?(test|case|t|c)=(.+)$/', $argument, $matches)) {
                $property = $this->to_property[$matches[1]];
                $this->$property = $matches[2];
            } elseif (preg_match('/^--?(test|case|t|c)$/', $argument, $matches)) {
                $property = $this->to_property[$matches[1]];
                if (isset($arguments[$i + 1])) {
                    $this->$property = $arguments[$i + 1];
                }
            } elseif (preg_match('/^--?(xml|x)$/', $argument)) {
                $this->xml = true;
            } elseif (preg_match('/^--?(no-skip|no-skips|s)$/', $argument)) {
                $this->no_skips = true;
            } elseif (preg_match('/^--?(help|h)$/', $argument)) {
                $this->help = true;
            }
        }
    }
    
    /**
     *    Run only this test.
     *    @return string        Test name to run.
     */
    function getTest() {
        return $this->test;
    }
    
    /**
     *    Run only this test suite.
     *    @return string        Test class name to run.
     */
    function getTestCase() {
        return $this->case;
    }
    
    /**
     *    Output should be XML or not.
     *    @return boolean        True if XML desired.
     */
    function isXml() {
        return $this->xml;
    }
    
    /**
     *    Output should suppress skip messages.
     *    @return boolean        True for no skips.
     */
    function noSkips() {
        return $this->no_skips;
    }
    
    /**
     *    Output should be a help message. Disabled during XML mode.
     *    @return boolean        True if help message desired.
     */
    function help() {
        return $this->help && ! $this->xml;
    }
    
    /**
     *    Returns plain-text help message for command line runner.
     *    @return string         String help message
     */
    function getHelpText() {
        return <<<HELP
SimpleTest command line default reporter (autorun)
Usage: php <test_file> [args...]

    -c <class>      Run only the test-case <class>
    -t <method>     Run only the test method <method>
    -s              Suppress skip messages
    -x              Return test results in XML
    -h              Display this help message

HELP;
    }
    
}

/**
 *    The default reporter used by SimpleTest's autorun
 *    feature. The actual reporters used are dependency
 *    injected and can be overridden.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class DefaultReporter extends SimpleReporterDecorator {
    
    /**
     *  Assembles the appropriate reporter for the environment.
     */
    function __construct() {
        if (SimpleReporter::inCli()) {
            $parser = new SimpleCommandLineParser($_SERVER['argv']);
            $interfaces = $parser->isXml() ? array('XmlReporter') : array('TextReporter');
            if ($parser->help()) {
                // I'm not sure if we should do the echo'ing here -- ezyang
                echo $parser->getHelpText();
                exit(1);
            }
            $reporter = new SelectiveReporter(
                    SimpleTest::preferred($interfaces),
                    $parser->getTestCase(),
                    $parser->getTest());
            if ($parser->noSkips()) {
                $reporter = new NoSkipsReporter($reporter);
            }
        } else {
            $reporter = new SelectiveReporter(
                    SimpleTest::preferred('HtmlReporter'),
                    @$_GET['c'],
                    @$_GET['t']);
            if (@$_GET['skips'] == 'no' || @$_GET['show-skips'] == 'no') {
                $reporter = new NoSkipsReporter($reporter);
            }
        }
        parent::__construct($reporter);
    }
}
 /* .tmp\flat\1\errors.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\1\errors.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 * Includes SimpleTest files.
 */
//require_once dirname(__FILE__) . '/invoker.php';
//require_once dirname(__FILE__) . '/test_case.php';
//require_once dirname(__FILE__) . '/expectation.php';
/**#@-*/

/**
 *    Extension that traps errors into an error queue.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleErrorTrappingInvoker extends SimpleInvokerDecorator {

    /**
     *    Stores the invoker to wrap.
     *    @param SimpleInvoker $invoker  Test method runner.
     */
    function __construct($invoker) {
        parent::__construct($invoker);
    }

    /**
     *    Invokes a test method and dispatches any
     *    untrapped errors. Called back from
     *    the visiting runner.
     *    @param string $method    Test method to call.
     *    @access public
     */
    function invoke($method) {
        $queue = $this->createErrorQueue();
        set_error_handler('SimpleTestErrorHandler');
        parent::invoke($method);
        restore_error_handler();
        $queue->tally();
    }
    
    /**
     *    Wires up the error queue for a single test.
     *    @return SimpleErrorQueue    Queue connected to the test.
     *    @access private
     */
    protected function createErrorQueue() {
        $context = SimpleTest::getContext();
        $test = $this->getTestCase();
        $queue = $context->get('SimpleErrorQueue');
        $queue->setTestCase($test);
        return $queue;
    }
}

/**
 *    Error queue used to record trapped
 *    errors.
 *    @package  SimpleTest
 *    @subpackage   UnitTester
 */
class SimpleErrorQueue {
    private $queue;
    private $expectation_queue;
    private $test;
    private $using_expect_style = false;

    /**
     *    Starts with an empty queue.
     */
    function __construct() {
        $this->clear();
    }

    /**
     *    Discards the contents of the error queue.
     *    @access public
     */
    function clear() {
        $this->queue = array();
        $this->expectation_queue = array();
    }

    /**
     *    Sets the currently running test case.
     *    @param SimpleTestCase $test    Test case to send messages to.
     *    @access public
     */
    function setTestCase($test) {
        $this->test = $test;
    }

    /**
     *    Sets up an expectation of an error. If this is
     *    not fulfilled at the end of the test, a failure
     *    will occour. If the error does happen, then this
     *    will cancel it out and send a pass message.
     *    @param SimpleExpectation $expected    Expected error match.
     *    @param string $message                Message to display.
     *    @access public
     */
    function expectError($expected, $message) {
        array_push($this->expectation_queue, array($expected, $message));
    }

    /**
     *    Adds an error to the front of the queue.
     *    @param integer $severity       PHP error code.
     *    @param string $content         Text of error.
     *    @param string $filename        File error occoured in.
     *    @param integer $line           Line number of error.
     *    @access public
     */
    function add($severity, $content, $filename, $line) {
        $content = str_replace('%', '%%', $content);
        $this->testLatestError($severity, $content, $filename, $line);
    }
    
    /**
     *    Any errors still in the queue are sent to the test
     *    case. Any unfulfilled expectations trigger failures.
     *    @access public
     */
    function tally() {
        while (list($severity, $message, $file, $line) = $this->extract()) {
            $severity = $this->getSeverityAsString($severity);
            $this->test->error($severity, $message, $file, $line);
        }
        while (list($expected, $message) = $this->extractExpectation()) {
            $this->test->assert($expected, false, "%s -> Expected error not caught");
        }
    }

    /**
     *    Tests the error against the most recent expected
     *    error.
     *    @param integer $severity       PHP error code.
     *    @param string $content         Text of error.
     *    @param string $filename        File error occoured in.
     *    @param integer $line           Line number of error.
     *    @access private
     */
    protected function testLatestError($severity, $content, $filename, $line) {
        if ($expectation = $this->extractExpectation()) {
            list($expected, $message) = $expectation;
            $this->test->assert($expected, $content, sprintf(
                    $message,
                    "%s -> PHP error [$content] severity [" .
                            $this->getSeverityAsString($severity) .
                            "] in [$filename] line [$line]"));
        } else {
            $this->test->error($severity, $content, $filename, $line);
        }
    }

    /**
     *    Pulls the earliest error from the queue.
     *    @return  mixed    False if none, or a list of error
     *                      information. Elements are: severity
     *                      as the PHP error code, the error message,
     *                      the file with the error, the line number
     *                      and a list of PHP super global arrays.
     *    @access public
     */
    function extract() {
        if (count($this->queue)) {
            return array_shift($this->queue);
        }
        return false;
    }

    /**
     *    Pulls the earliest expectation from the queue.
     *    @return     SimpleExpectation    False if none.
     *    @access private
     */
    protected function extractExpectation() {
        if (count($this->expectation_queue)) {
            return array_shift($this->expectation_queue);
        }
        return false;
    }

    /**
     *    Converts an error code into it's string
     *    representation.
     *    @param $severity  PHP integer error code.
     *    @return           String version of error code.
     *    @access public
     */
    static function getSeverityAsString($severity) {
        static $map = array(
                E_STRICT => 'E_STRICT',
                E_ERROR => 'E_ERROR',
                E_WARNING => 'E_WARNING',
                E_PARSE => 'E_PARSE',
                E_NOTICE => 'E_NOTICE',
                E_CORE_ERROR => 'E_CORE_ERROR',
                E_CORE_WARNING => 'E_CORE_WARNING',
                E_COMPILE_ERROR => 'E_COMPILE_ERROR',
                E_COMPILE_WARNING => 'E_COMPILE_WARNING',
                E_USER_ERROR => 'E_USER_ERROR',
                E_USER_WARNING => 'E_USER_WARNING',
                E_USER_NOTICE => 'E_USER_NOTICE');
        if (defined('E_RECOVERABLE_ERROR')) {
            $map[E_RECOVERABLE_ERROR] = 'E_RECOVERABLE_ERROR';
        }
        if (defined('E_DEPRECATED')) {
            $map[E_DEPRECATED] = 'E_DEPRECATED';
        }
        return $map[$severity];
    }
}

/**
 *    Error handler that simply stashes any errors into the global
 *    error queue. Simulates the existing behaviour with respect to
 *    logging errors, but this feature may be removed in future.
 *    @param $severity        PHP error code.
 *    @param $message         Text of error.
 *    @param $filename        File error occoured in.
 *    @param $line            Line number of error.
 *    @param $super_globals   Hash of PHP super global arrays.
 *    @access public
 */
function SimpleTestErrorHandler($severity, $message, $filename = null, $line = null, $super_globals = null, $mask = null) {
    $severity = $severity & error_reporting();
    if ($severity) {
        restore_error_handler();
        if (IsNotCausedBySimpleTest($message) && IsNotTimeZoneNag($message)) {
            if (ini_get('log_errors')) {
                $label = SimpleErrorQueue::getSeverityAsString($severity);
                error_log("$label: $message in $filename on line $line");
            }
            $queue = SimpleTest::getContext()->get('SimpleErrorQueue');
            $queue->add($severity, $message, $filename, $line);
        }
        set_error_handler('SimpleTestErrorHandler');
    }
    return true;
}

/**
 *  Certain messages can be caused by the unit tester itself.
 *  These have to be filtered.
 *  @param string $message      Message to filter.
 *  @return boolean             True if genuine failure.
 */
function IsNotCausedBySimpleTest($message) {
    return ! preg_match('/returned by reference/', $message);
}

/**
 *  Certain messages caused by PHP are just noise.
 *  These have to be filtered.
 *  @param string $message      Message to filter.
 *  @return boolean             True if genuine failure.
 */
function IsNotTimeZoneNag($message) {
    return ! preg_match('/not safe to rely .* timezone settings/', $message);
}
 /* .tmp\flat\1\exceptions.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\1\exceptions.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 * Include required SimpleTest files
 */
//require_once dirname(__FILE__) . '/invoker.php';
//require_once dirname(__FILE__) . '/expectation.php';
/**#@-*/

/**
 *    Extension that traps exceptions and turns them into
 *    an error message. PHP5 only.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleExceptionTrappingInvoker extends SimpleInvokerDecorator {

    /**
     *    Stores the invoker to be wrapped.
     *    @param SimpleInvoker $invoker   Test method runner.
     */
    function __construct($invoker) {
        parent::__construct($invoker);
    }

    /**
     *    Invokes a test method whilst trapping expected
     *    exceptions. Any left over unthrown exceptions
     *    are then reported as failures.
     *    @param string $method    Test method to call.
     */
    function invoke($method) {
        $trap = SimpleTest::getContext()->get('SimpleExceptionTrap');
        $trap->clear();
        try {
            $has_thrown = false;
            parent::invoke($method);
        } catch (Exception $exception) {
            $has_thrown = true;
            if (! $trap->isExpected($this->getTestCase(), $exception)) {
                $this->getTestCase()->exception($exception);
            }
            $trap->clear();
        }
        if ($message = $trap->getOutstanding()) {
            $this->getTestCase()->fail($message);
        }
        if ($has_thrown) {
            try {
                parent::getTestCase()->tearDown();
            } catch (Exception $e) { }
        }
    }
}

/**
 *    Tests exceptions either by type or the exact
 *    exception. This could be improved to accept
 *    a pattern expectation to test the error
 *    message, but that will have to come later.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class ExceptionExpectation extends SimpleExpectation {
    private $expected;

    /**
     *    Sets up the conditions to test against.
     *    If the expected value is a string, then
     *    it will act as a test of the class name.
     *    An exception as the comparison will
     *    trigger an identical match. Writing this
     *    down now makes it look doubly dumb. I hope
     *    come up with a better scheme later.
     *    @param mixed $expected   A class name or an actual
     *                             exception to compare with.
     *    @param string $message   Message to display.
     */
    function __construct($expected, $message = '%s') {
        $this->expected = $expected;
        parent::__construct($message);
    }

    /**
     *    Carry out the test.
     *    @param Exception $compare    Value to check.
     *    @return boolean              True if matched.
     */
    function test($compare) {
        if (is_string($this->expected)) {
            return ($compare instanceof $this->expected);
        }
        if (get_class($compare) != get_class($this->expected)) {
            return false;
        }
        return $compare->getMessage() == $this->expected->getMessage();
    }

    /**
     *    Create the message to display describing the test.
     *    @param Exception $compare     Exception to match.
     *    @return string                Final message.
     */
    function testMessage($compare) {
        if (is_string($this->expected)) {
            return "Exception [" . $this->describeException($compare) .
                    "] should be type [" . $this->expected . "]";
        }
        return "Exception [" . $this->describeException($compare) .
                "] should match [" .
                $this->describeException($this->expected) . "]";
    }

    /**
     *    Summary of an Exception object.
     *    @param Exception $compare     Exception to describe.
     *    @return string                Text description.
     */
    protected function describeException($exception) {
        return get_class($exception) . ": " . $exception->getMessage();
    }
}

/**
 *    Stores expected exceptions for when they
 *    get thrown. Saves the irritating try...catch
 *    block.
 *    @package  SimpleTest
 *    @subpackage   UnitTester
 */
class SimpleExceptionTrap {
    private $expected;
    private $ignored;
    private $message;

    /**
     *    Clears down the queue ready for action.
     */
    function __construct() {
        $this->clear();
    }

    /**
     *    Sets up an expectation of an exception.
     *    This has the effect of intercepting an
     *    exception that matches.
     *    @param SimpleExpectation $expected    Expected exception to match.
     *    @param string $message                Message to display.
     *    @access public
     */
    function expectException($expected = false, $message = '%s') {
        $this->expected = $this->coerceToExpectation($expected);
        $this->message = $message;
    }

    /**
     *    Adds an exception to the ignore list. This is the list
     *    of exceptions that when thrown do not affect the test.
     *    @param SimpleExpectation $ignored    Exception to skip.
     *    @access public
     */
    function ignoreException($ignored) {
        $this->ignored[] = $this->coerceToExpectation($ignored);
    }

    /**
     *    Compares the expected exception with any
     *    in the queue. Issues a pass or fail and
     *    returns the state of the test.
     *    @param SimpleTestCase $test    Test case to send messages to.
     *    @param Exception $exception    Exception to compare.
     *    @return boolean                False on no match.
     */
    function isExpected($test, $exception) {
        if ($this->expected) {
            return $test->assert($this->expected, $exception, $this->message);
        }
        foreach ($this->ignored as $ignored) {
            if ($ignored->test($exception)) {
                return true;
            }
        }
        return false;
    }

    /**
     *    Turns an expected exception into a SimpleExpectation object.
     *    @param mixed $exception      Exception, expectation or
     *                                 class name of exception.
     *    @return SimpleExpectation    Expectation that will match the
     *                                 exception.
     */
    private function coerceToExpectation($exception) {
        if ($exception === false) {
            return new AnythingExpectation();
        }
        if (! SimpleExpectation::isExpectation($exception)) {
            return new ExceptionExpectation($exception);
        }
        return $exception;
    }

    /**
     *    Tests for any left over exception.
     *    @return string/false     The failure message or false if none.
     */
    function getOutstanding() {
        return sprintf($this->message, 'Failed to trap exception');
    }

    /**
     *    Discards the contents of the error queue.
     */
    function clear() {
        $this->expected = false;
        $this->message = false;
        $this->ignored = array();
    }
}
 /* .tmp\flat\1\http.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\1\http.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/socket.php');
//require_once(dirname(__FILE__) . '/cookies.php');
//require_once(dirname(__FILE__) . '/url.php');
/**#@-*/

/**
 *    Creates HTTP headers for the end point of
 *    a HTTP request.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleRoute {
    private $url;
    
    /**
     *    Sets the target URL.
     *    @param SimpleUrl $url   URL as object.
     *    @access public
     */
    function __construct($url) {
        $this->url = $url;
    }
    
    /**
     *    Resource name.
     *    @return SimpleUrl        Current url.
     *    @access protected
     */
    function getUrl() {
        return $this->url;
    }
    
    /**
     *    Creates the first line which is the actual request.
     *    @param string $method   HTTP request method, usually GET.
     *    @return string          Request line content.
     *    @access protected
     */
    protected function getRequestLine($method) {
        return $method . ' ' . $this->url->getPath() .
                $this->url->getEncodedRequest() . ' HTTP/1.0';
    }
    
    /**
     *    Creates the host part of the request.
     *    @return string          Host line content.
     *    @access protected
     */
    protected function getHostLine() {
        $line = 'Host: ' . $this->url->getHost();
        if ($this->url->getPort()) {
            $line .= ':' . $this->url->getPort();
        }
        return $line;
    }
    
    /**
     *    Opens a socket to the route.
     *    @param string $method      HTTP request method, usually GET.
     *    @param integer $timeout    Connection timeout.
     *    @return SimpleSocket       New socket.
     *    @access public
     */
    function createConnection($method, $timeout) {
        $default_port = ('https' == $this->url->getScheme()) ? 443 : 80;
        $socket = $this->createSocket(
                $this->url->getScheme() ? $this->url->getScheme() : 'http',
                $this->url->getHost(),
                $this->url->getPort() ? $this->url->getPort() : $default_port,
                $timeout);
        if (! $socket->isError()) {
            $socket->write($this->getRequestLine($method) . "\r\n");
            $socket->write($this->getHostLine() . "\r\n");
            $socket->write("Connection: close\r\n");
        }
        return $socket;
    }
    
    /**
     *    Factory for socket.
     *    @param string $scheme                   Protocol to use.
     *    @param string $host                     Hostname to connect to.
     *    @param integer $port                    Remote port.
     *    @param integer $timeout                 Connection timeout.
     *    @return SimpleSocket/SimpleSecureSocket New socket.
     *    @access protected
     */
    protected function createSocket($scheme, $host, $port, $timeout) {
        if (in_array($scheme, array('file'))) {
            return new SimpleFileSocket($this->url);
        } elseif (in_array($scheme, array('https'))) {
            return new SimpleSecureSocket($host, $port, $timeout);
        } else {
            return new SimpleSocket($host, $port, $timeout);
        }
    }
}

/**
 *    Creates HTTP headers for the end point of
 *    a HTTP request via a proxy server.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleProxyRoute extends SimpleRoute {
    private $proxy;
    private $username;
    private $password;
    
    /**
     *    Stashes the proxy address.
     *    @param SimpleUrl $url     URL as object.
     *    @param string $proxy      Proxy URL.
     *    @param string $username   Username for autentication.
     *    @param string $password   Password for autentication.
     *    @access public
     */
    function __construct($url, $proxy, $username = false, $password = false) {
        parent::__construct($url);
        $this->proxy = $proxy;
        $this->username = $username;
        $this->password = $password;
    }
    
    /**
     *    Creates the first line which is the actual request.
     *    @param string $method   HTTP request method, usually GET.
     *    @param SimpleUrl $url   URL as object.
     *    @return string          Request line content.
     *    @access protected
     */
    function getRequestLine($method) {
        $url = $this->getUrl();
        $scheme = $url->getScheme() ? $url->getScheme() : 'http';
        $port = $url->getPort() ? ':' . $url->getPort() : '';
        return $method . ' ' . $scheme . '://' . $url->getHost() . $port .
                $url->getPath() . $url->getEncodedRequest() . ' HTTP/1.0';
    }
    
    /**
     *    Creates the host part of the request.
     *    @param SimpleUrl $url   URL as object.
     *    @return string          Host line content.
     *    @access protected
     */
    function getHostLine() {
        $host = 'Host: ' . $this->proxy->getHost();
        $port = $this->proxy->getPort() ? $this->proxy->getPort() : 8080;
        return "$host:$port";
    }
    
    /**
     *    Opens a socket to the route.
     *    @param string $method       HTTP request method, usually GET.
     *    @param integer $timeout     Connection timeout.
     *    @return SimpleSocket        New socket.
     *    @access public
     */
    function createConnection($method, $timeout) {
        $socket = $this->createSocket(
                $this->proxy->getScheme() ? $this->proxy->getScheme() : 'http',
                $this->proxy->getHost(),
                $this->proxy->getPort() ? $this->proxy->getPort() : 8080,
                $timeout);
        if ($socket->isError()) {
            return $socket;
        }
        $socket->write($this->getRequestLine($method) . "\r\n");
        $socket->write($this->getHostLine() . "\r\n");
        if ($this->username && $this->password) {
            $socket->write('Proxy-Authorization: Basic ' .
                    base64_encode($this->username . ':' . $this->password) .
                    "\r\n");
        }
        $socket->write("Connection: close\r\n");
        return $socket;
    }
}

/**
 *    HTTP request for a web page. Factory for
 *    HttpResponse object.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleHttpRequest {
    private $route;
    private $encoding;
    private $headers;
    private $cookies;
    
    /**
     *    Builds the socket request from the different pieces.
     *    These include proxy information, URL, cookies, headers,
     *    request method and choice of encoding.
     *    @param SimpleRoute $route              Request route.
     *    @param SimpleFormEncoding $encoding    Content to send with
     *                                           request.
     *    @access public
     */
    function __construct($route, $encoding) {
        $this->route = $route;
        $this->encoding = $encoding;
        $this->headers = array();
        $this->cookies = array();
    }
    
    /**
     *    Dispatches the content to the route's socket.
     *    @param integer $timeout      Connection timeout.
     *    @return SimpleHttpResponse   A response which may only have
     *                                 an error, but hopefully has a
     *                                 complete web page.
     *    @access public
     */
    function fetch($timeout) {
        $socket = $this->route->createConnection($this->encoding->getMethod(), $timeout);
        if (! $socket->isError()) {
            $this->dispatchRequest($socket, $this->encoding);
        }
        return $this->createResponse($socket);
    }
    
    /**
     *    Sends the headers.
     *    @param SimpleSocket $socket           Open socket.
     *    @param string $method                 HTTP request method,
     *                                          usually GET.
     *    @param SimpleFormEncoding $encoding   Content to send with request.
     *    @access private
     */
    protected function dispatchRequest($socket, $encoding) {
        foreach ($this->headers as $header_line) {
            $socket->write($header_line . "\r\n");
        }
        if (count($this->cookies) > 0) {
            $socket->write("Cookie: " . implode(";", $this->cookies) . "\r\n");
        }
        $encoding->writeHeadersTo($socket);
        $socket->write("\r\n");
        $encoding->writeTo($socket);
    }
    
    /**
     *    Adds a header line to the request.
     *    @param string $header_line    Text of full header line.
     *    @access public
     */
    function addHeaderLine($header_line) {
        $this->headers[] = $header_line;
    }
    
    /**
     *    Reads all the relevant cookies from the
     *    cookie jar.
     *    @param SimpleCookieJar $jar     Jar to read
     *    @param SimpleUrl $url           Url to use for scope.
     *    @access public
     */
    function readCookiesFromJar($jar, $url) {
        $this->cookies = $jar->selectAsPairs($url);
    }
    
    /**
     *    Wraps the socket in a response parser.
     *    @param SimpleSocket $socket   Responding socket.
     *    @return SimpleHttpResponse    Parsed response object.
     *    @access protected
     */
    protected function createResponse($socket) {
        $response = new SimpleHttpResponse(
                $socket,
                $this->route->getUrl(),
                $this->encoding);
        $socket->close();
        return $response;
    }
}

/**
 *    Collection of header lines in the response.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleHttpHeaders {
    private $raw_headers;
    private $response_code;
    private $http_version;
    private $mime_type;
    private $location;
    private $cookies;
    private $authentication;
    private $realm;
    
    /**
     *    Parses the incoming header block.
     *    @param string $headers     Header block.
     *    @access public
     */
    function __construct($headers) {
        $this->raw_headers = $headers;
        $this->response_code = false;
        $this->http_version = false;
        $this->mime_type = '';
        $this->location = false;
        $this->cookies = array();
        $this->authentication = false;
        $this->realm = false;
        foreach (explode("\r\n", $headers) as $header_line) {
            $this->parseHeaderLine($header_line);
        }
    }
    
    /**
     *    Accessor for parsed HTTP protocol version.
     *    @return integer           HTTP error code.
     *    @access public
     */
    function getHttpVersion() {
        return $this->http_version;
    }
    
    /**
     *    Accessor for raw header block.
     *    @return string        All headers as raw string.
     *    @access public
     */
    function getRaw() {
        return $this->raw_headers;
    }
    
    /**
     *    Accessor for parsed HTTP error code.
     *    @return integer           HTTP error code.
     *    @access public
     */
    function getResponseCode() {
        return (integer)$this->response_code;
    }
    
    /**
     *    Returns the redirected URL or false if
     *    no redirection.
     *    @return string      URL or false for none.
     *    @access public
     */
    function getLocation() {
        return $this->location;
    }
    
    /**
     *    Test to see if the response is a valid redirect.
     *    @return boolean       True if valid redirect.
     *    @access public
     */
    function isRedirect() {
        return in_array($this->response_code, array(301, 302, 303, 307)) &&
                (boolean)$this->getLocation();
    }
    
    /**
     *    Test to see if the response is an authentication
     *    challenge.
     *    @return boolean       True if challenge.
     *    @access public
     */
    function isChallenge() {
        return ($this->response_code == 401) &&
                (boolean)$this->authentication &&
                (boolean)$this->realm;
    }
    
    /**
     *    Accessor for MIME type header information.
     *    @return string           MIME type.
     *    @access public
     */
    function getMimeType() {
        return $this->mime_type;
    }
    
    /**
     *    Accessor for authentication type.
     *    @return string        Type.
     *    @access public
     */
    function getAuthentication() {
        return $this->authentication;
    }
    
    /**
     *    Accessor for security realm.
     *    @return string        Realm.
     *    @access public
     */
    function getRealm() {
        return $this->realm;
    }
    
    /**
     *    Writes new cookies to the cookie jar.
     *    @param SimpleCookieJar $jar   Jar to write to.
     *    @param SimpleUrl $url         Host and path to write under.
     *    @access public
     */
    function writeCookiesToJar($jar, $url) {
        foreach ($this->cookies as $cookie) {
            $jar->setCookie(
                    $cookie->getName(),
                    $cookie->getValue(),
                    $url->getHost(),
                    $cookie->getPath(),
                    $cookie->getExpiry());
        }
    }

    /**
     *    Called on each header line to accumulate the held
     *    data within the class.
     *    @param string $header_line        One line of header.
     *    @access protected
     */
    protected function parseHeaderLine($header_line) {
        if (preg_match('/HTTP\/(\d+\.\d+)\s+(\d+)/i', $header_line, $matches)) {
            $this->http_version = $matches[1];
            $this->response_code = $matches[2];
        }
        if (preg_match('/Content-type:\s*(.*)/i', $header_line, $matches)) {
            $this->mime_type = trim($matches[1]);
        }
        if (preg_match('/Location:\s*(.*)/i', $header_line, $matches)) {
            $this->location = trim($matches[1]);
        }
        if (preg_match('/Set-cookie:(.*)/i', $header_line, $matches)) {
            $this->cookies[] = $this->parseCookie($matches[1]);
        }
        if (preg_match('/WWW-Authenticate:\s+(\S+)\s+realm=\"(.*?)\"/i', $header_line, $matches)) {
            $this->authentication = $matches[1];
            $this->realm = trim($matches[2]);
        }
    }
    
    /**
     *    Parse the Set-cookie content.
     *    @param string $cookie_line    Text after "Set-cookie:"
     *    @return SimpleCookie          New cookie object.
     *    @access private
     */
    protected function parseCookie($cookie_line) {
        $parts = explode(";", $cookie_line);
        $cookie = array();
        preg_match('/\s*(.*?)\s*=(.*)/', array_shift($parts), $cookie);
        foreach ($parts as $part) {
            if (preg_match('/\s*(.*?)\s*=(.*)/', $part, $matches)) {
                $cookie[$matches[1]] = trim($matches[2]);
            }
        }
        return new SimpleCookie(
                $cookie[1],
                trim($cookie[2]),
                isset($cookie["path"]) ? $cookie["path"] : "",
                isset($cookie["expires"]) ? $cookie["expires"] : false);
    }
}

/**
 *    Basic HTTP response.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleHttpResponse extends SimpleStickyError {
    private $url;
    private $encoding;
    private $sent;
    private $content;
    private $headers;
    
    /**
     *    Constructor. Reads and parses the incoming
     *    content and headers.
     *    @param SimpleSocket $socket   Network connection to fetch
     *                                  response text from.
     *    @param SimpleUrl $url         Resource name.
     *    @param mixed $encoding        Record of content sent.
     *    @access public
     */
    function __construct($socket, $url, $encoding) {
        parent::__construct();
        $this->url = $url;
        $this->encoding = $encoding;
        $this->sent = $socket->getSent();
        $this->content = false;
        $raw = $this->readAll($socket);
        if ($socket->isError()) {
            $this->setError('Error reading socket [' . $socket->getError() . ']');
            return;
        }
        $this->parse($raw);
    }
    
    /**
     *    Splits up the headers and the rest of the content.
     *    @param string $raw    Content to parse.
     *    @access private
     */
    protected function parse($raw) {
        if (! $raw) {
            $this->setError('Nothing fetched');
            $this->headers = new SimpleHttpHeaders('');
        } elseif ('file' == $this->url->getScheme()) {
            $this->headers = new SimpleHttpHeaders('');
            $this->content = $raw;
        } elseif (! strstr($raw, "\r\n\r\n")) {
            $this->setError('Could not split headers from content');
            $this->headers = new SimpleHttpHeaders($raw);
        } else {
            list($headers, $this->content) = explode("\r\n\r\n", $raw, 2);
            $this->headers = new SimpleHttpHeaders($headers);
        }
    }
    
    /**
     *    Original request method.
     *    @return string        GET, POST or HEAD.
     *    @access public
     */
    function getMethod() {
        return $this->encoding->getMethod();
    }
    
    /**
     *    Resource name.
     *    @return SimpleUrl        Current url.
     *    @access public
     */
    function getUrl() {
        return $this->url;
    }
    
    /**
     *    Original request data.
     *    @return mixed              Sent content.
     *    @access public
     */
    function getRequestData() {
        return $this->encoding;
    }
    
    /**
     *    Raw request that was sent down the wire.
     *    @return string        Bytes actually sent.
     *    @access public
     */
    function getSent() {
        return $this->sent;
    }
    
    /**
     *    Accessor for the content after the last
     *    header line.
     *    @return string           All content.
     *    @access public
     */
    function getContent() {
        return $this->content;
    }
    
    /**
     *    Accessor for header block. The response is the
     *    combination of this and the content.
     *    @return SimpleHeaders        Wrapped header block.
     *    @access public
     */
    function getHeaders() {
        return $this->headers;
    }
    
    /**
     *    Accessor for any new cookies.
     *    @return array       List of new cookies.
     *    @access public
     */
    function getNewCookies() {
        return $this->headers->getNewCookies();
    }
    
    /**
     *    Reads the whole of the socket output into a
     *    single string.
     *    @param SimpleSocket $socket  Unread socket.
     *    @return string               Raw output if successful
     *                                 else false.
     *    @access private
     */
    protected function readAll($socket) {
        $all = '';
        while (! $this->isLastPacket($next = $socket->read())) {
            $all .= $next;
        }
        return $all;
    }
    
    /**
     *    Test to see if the packet from the socket is the
     *    last one.
     *    @param string $packet    Chunk to interpret.
     *    @return boolean          True if empty or EOF.
     *    @access private
     */
    protected function isLastPacket($packet) {
        if (is_string($packet)) {
            return $packet === '';
        }
        return ! $packet;
    }
}
 /* .tmp\flat\1\mock_objects.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\1\mock_objects.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage MockObjects
 *  @version    $Id$
 */

/**#@+
 * include SimpleTest files
 */
//require_once(dirname(__FILE__) . '/expectation.php');
//require_once(dirname(__FILE__) . '/simpletest.php');
//require_once(dirname(__FILE__) . '/dumper.php');
//require_once(dirname(__FILE__) . '/reflection_php5.php');
/**#@-*/

/**
 * Default character simpletest will substitute for any value
 */
if (! defined('MOCK_ANYTHING')) {
    define('MOCK_ANYTHING', '*');
}

/**
 *    Parameter comparison assertion.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class ParametersExpectation extends SimpleExpectation {
    private $expected;

    /**
     *    Sets the expected parameter list.
     *    @param array $parameters  Array of parameters including
     *                              those that are wildcarded.
     *                              If the value is not an array
     *                              then it is considered to match any.
     *    @param string $message    Customised message on failure.
     */
    function __construct($expected = false, $message = '%s') {
        parent::__construct($message);
        $this->expected = $expected;
    }

    /**
     *    Tests the assertion. True if correct.
     *    @param array $parameters     Comparison values.
     *    @return boolean              True if correct.
     */
    function test($parameters) {
        if (! is_array($this->expected)) {
            return true;
        }
        if (count($this->expected) != count($parameters)) {
            return false;
        }
        for ($i = 0; $i < count($this->expected); $i++) {
            if (! $this->testParameter($parameters[$i], $this->expected[$i])) {
                return false;
            }
        }
        return true;
    }

    /**
     *    Tests an individual parameter.
     *    @param mixed $parameter    Value to test.
     *    @param mixed $expected     Comparison value.
     *    @return boolean            True if expectation
     *                               fulfilled.
     */
    protected function testParameter($parameter, $expected) {
        $comparison = $this->coerceToExpectation($expected);
        return $comparison->test($parameter);
    }

    /**
     *    Returns a human readable test message.
     *    @param array $comparison   Incoming parameter list.
     *    @return string             Description of success
     *                               or failure.
     */
    function testMessage($parameters) {
        if ($this->test($parameters)) {
            return "Expectation of " . count($this->expected) .
                    " arguments of [" . $this->renderArguments($this->expected) .
                    "] is correct";
        } else {
            return $this->describeDifference($this->expected, $parameters);
        }
    }

    /**
     *    Message to display if expectation differs from
     *    the parameters actually received.
     *    @param array $expected      Expected parameters as list.
     *    @param array $parameters    Actual parameters received.
     *    @return string              Description of difference.
     */
    protected function describeDifference($expected, $parameters) {
        if (count($expected) != count($parameters)) {
            return "Expected " . count($expected) .
                    " arguments of [" . $this->renderArguments($expected) .
                    "] but got " . count($parameters) .
                    " arguments of [" . $this->renderArguments($parameters) . "]";
        }
        $messages = array();
        for ($i = 0; $i < count($expected); $i++) {
            $comparison = $this->coerceToExpectation($expected[$i]);
            if (! $comparison->test($parameters[$i])) {
                $messages[] = "parameter " . ($i + 1) . " with [" .
                        $comparison->overlayMessage($parameters[$i], $this->getDumper()) . "]";
            }
        }
        return "Parameter expectation differs at " . implode(" and ", $messages);
    }

    /**
     *    Creates an identical expectation if the
     *    object/value is not already some type
     *    of expectation.
     *    @param mixed $expected      Expected value.
     *    @return SimpleExpectation   Expectation object.
     */
    protected function coerceToExpectation($expected) {
        if (SimpleExpectation::isExpectation($expected)) {
            return $expected;
        }
        return new IdenticalExpectation($expected);
    }

    /**
     *    Renders the argument list as a string for
     *    messages.
     *    @param array $args    Incoming arguments.
     *    @return string        Simple description of type and value.
     */
    protected function renderArguments($args) {
        $descriptions = array();
        if (is_array($args)) {
            foreach ($args as $arg) {
                $dumper = new SimpleDumper();
                $descriptions[] = $dumper->describeValue($arg);
            }
        }
        return implode(', ', $descriptions);
    }
}

/**
 *    Confirms that the number of calls on a method is as expected.
 *  @package    SimpleTest
 *  @subpackage MockObjects
 */
class CallCountExpectation extends SimpleExpectation {
    private $method;
    private $count;

    /**
     *    Stashes the method and expected count for later
     *    reporting.
     *    @param string $method    Name of method to confirm against.
     *    @param integer $count    Expected number of calls.
     *    @param string $message   Custom error message.
     */
    function __construct($method, $count, $message = '%s') {
        $this->method = $method;
        $this->count = $count;
        parent::__construct($message);
    }

    /**
     *    Tests the assertion. True if correct.
     *    @param integer $compare     Measured call count.
     *    @return boolean             True if expected.
     */
    function test($compare) {
        return ($this->count == $compare);
    }

    /**
     *    Reports the comparison.
     *    @param integer $compare     Measured call count.
     *    @return string              Message to show.
     */
    function testMessage($compare) {
        return 'Expected call count for [' . $this->method .
                '] was [' . $this->count .
                '] got [' . $compare . ']';
    }
}

/**
 *    Confirms that the number of calls on a method is as expected.
 *  @package    SimpleTest
 *  @subpackage MockObjects
 */
class MinimumCallCountExpectation extends SimpleExpectation {
    private $method;
    private $count;

    /**
     *    Stashes the method and expected count for later
     *    reporting.
     *    @param string $method    Name of method to confirm against.
     *    @param integer $count    Minimum number of calls.
     *    @param string $message   Custom error message.
     */
    function __construct($method, $count, $message = '%s') {
        $this->method = $method;
        $this->count = $count;
        parent::__construct($message);
    }

    /**
     *    Tests the assertion. True if correct.
     *    @param integer $compare     Measured call count.
     *    @return boolean             True if enough.
     */
    function test($compare) {
        return ($this->count <= $compare);
    }

    /**
     *    Reports the comparison.
     *    @param integer $compare     Measured call count.
     *    @return string              Message to show.
     */
    function testMessage($compare) {
        return 'Minimum call count for [' . $this->method .
                '] was [' . $this->count .
                '] got [' . $compare . ']';
    }
}

/**
 *    Confirms that the number of calls on a method is as expected.
 *    @package      SimpleTest
 *    @subpackage   MockObjects
 */
class MaximumCallCountExpectation extends SimpleExpectation {
    private $method;
    private $count;

    /**
     *    Stashes the method and expected count for later
     *    reporting.
     *    @param string $method    Name of method to confirm against.
     *    @param integer $count    Minimum number of calls.
     *    @param string $message   Custom error message.
     */
    function __construct($method, $count, $message = '%s') {
        $this->method = $method;
        $this->count = $count;
        parent::__construct($message);
    }

    /**
     *    Tests the assertion. True if correct.
     *    @param integer $compare     Measured call count.
     *    @return boolean             True if not over.
     */
    function test($compare) {
        return ($this->count >= $compare);
    }

    /**
     *    Reports the comparison.
     *    @param integer $compare     Measured call count.
     *    @return string              Message to show.
     */
    function testMessage($compare) {
        return 'Maximum call count for [' . $this->method .
                '] was [' . $this->count .
                '] got [' . $compare . ']';
    }
}

/**
 *    Retrieves method actions by searching the
 *    parameter lists until an expected match is found.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class SimpleSignatureMap {
    private $map;

    /**
     *    Creates an empty call map.
     */
    function __construct() {
        $this->map = array();
    }

    /**
     *    Stashes a reference against a method call.
     *    @param array $parameters    Array of arguments (including wildcards).
     *    @param mixed $action        Reference placed in the map.
     */
    function add($parameters, $action) {
        $place = count($this->map);
        $this->map[$place] = array();
        $this->map[$place]['params'] = new ParametersExpectation($parameters);
        $this->map[$place]['content'] = $action;
    }

    /**
     *    Searches the call list for a matching parameter
     *    set. Returned by reference.
     *    @param array $parameters    Parameters to search by
     *                                without wildcards.
     *    @return object              Object held in the first matching
     *                                slot, otherwise null.
     */
    function &findFirstAction($parameters) {
        $slot = $this->findFirstSlot($parameters);
        if (isset($slot) && isset($slot['content'])) {
            return $slot['content'];
        }
        $null = null;
        return $null;
    }

    /**
     *    Searches the call list for a matching parameter
     *    set. True if successful.
     *    @param array $parameters    Parameters to search by
     *                                without wildcards.
     *    @return boolean             True if a match is present.
     */
    function isMatch($parameters) {
        return ($this->findFirstSlot($parameters) != null);
    }

    /**
     *    Compares the incoming parameters with the
     *    internal expectation. Uses the incoming $test
     *    to dispatch the test message.
     *    @param SimpleTestCase $test   Test to dispatch to.
     *    @param array $parameters      The actual calling arguments.
     *    @param string $message        The message to overlay.
     */
    function test($test, $parameters, $message) {
    }

    /**
     *    Searches the map for a matching item.
     *    @param array $parameters    Parameters to search by
     *                                without wildcards.
     *    @return array               Reference to slot or null.
     */
    function &findFirstSlot($parameters) {
        $count = count($this->map);
        for ($i = 0; $i < $count; $i++) {
            if ($this->map[$i]["params"]->test($parameters)) {
                return $this->map[$i];
            }
        }
        $null = null;
        return $null;
    }
}

/**
 *    Allows setting of actions against call signatures either
 *    at a specific time, or always. Specific time settings
 *    trump lasting ones, otherwise the most recently added
 *    will mask an earlier match.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class SimpleCallSchedule {
    private $wildcard = MOCK_ANYTHING;
    private $always;
    private $at;

    /**
     *    Sets up an empty response schedule.
     *    Creates an empty call map.
     */
    function __construct() {
        $this->always = array();
        $this->at = array();
    }

    /**
     *    Stores an action against a signature that
     *    will always fire unless masked by a time
     *    specific one.
     *    @param string $method        Method name.
     *    @param array $args           Calling parameters.
     *    @param SimpleAction $action  Actually simpleByValue, etc.
     */
    function register($method, $args, $action) {
        $args = $this->replaceWildcards($args);
        $method = strtolower($method);
        if (! isset($this->always[$method])) {
            $this->always[$method] = new SimpleSignatureMap();
        }
        $this->always[$method]->add($args, $action);
    }

    /**
     *    Stores an action against a signature that
     *    will fire at a specific time in the future.
     *    @param integer $step         delay of calls to this method,
     *                                 0 is next.
     *    @param string $method        Method name.
     *    @param array $args           Calling parameters.
     *    @param SimpleAction $action  Actually SimpleByValue, etc.
     */
    function registerAt($step, $method, $args, $action) {
        $args = $this->replaceWildcards($args);
        $method = strtolower($method);
        if (! isset($this->at[$method])) {
            $this->at[$method] = array();
        }
        if (! isset($this->at[$method][$step])) {
            $this->at[$method][$step] = new SimpleSignatureMap();
        }
        $this->at[$method][$step]->add($args, $action);
    }

    /**
     *  Sets up an expectation on the argument list.
     *  @param string $method       Method to test.
     *  @param array $args          Bare arguments or list of
     *                              expectation objects.
     *  @param string $message      Failure message.
     */
    function expectArguments($method, $args, $message) {
        $args = $this->replaceWildcards($args);
        $message .= Mock::getExpectationLine();
        $this->expected_args[strtolower($method)] =
                new ParametersExpectation($args, $message);

    }

    /**
     *    Actually carry out the action stored previously,
     *    if the parameters match.
     *    @param integer $step      Time of call.
     *    @param string $method     Method name.
     *    @param array $args        The parameters making up the
     *                              rest of the call.
     *    @return mixed             The result of the action.
     */
    function &respond($step, $method, $args) {
        $method = strtolower($method);
        if (isset($this->at[$method][$step])) {
            if ($this->at[$method][$step]->isMatch($args)) {
                $action = $this->at[$method][$step]->findFirstAction($args);
                if (isset($action)) {
                    return $action->act();
                }
            }
        }
        if (isset($this->always[$method])) {
            $action = $this->always[$method]->findFirstAction($args);
            if (isset($action)) {
                return $action->act();
            }
        }
        $null = null;
        return $null;
    }

    /**
     *    Replaces wildcard matches with wildcard
     *    expectations in the argument list.
     *    @param array $args      Raw argument list.
     *    @return array           Argument list with
     *                            expectations.
     */
    protected function replaceWildcards($args) {
        if ($args === false) {
            return false;
        }
        for ($i = 0; $i < count($args); $i++) {
            if ($args[$i] === $this->wildcard) {
                $args[$i] = new AnythingExpectation();
            }
        }
        return $args;
    }
}

/**
 *    A type of SimpleMethodAction.
 *    Stashes a value for returning later. Follows usual
 *    PHP5 semantics of objects being returned by reference.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class SimpleReturn {
    private $value;

    /**
     *    Stashes it for later.
     *    @param mixed $value     You need to clone objects
     *                            if you want copy semantics
     *                            for these.
     */
    function __construct($value) {
        $this->value = $value;
    }

    /**
     *    Returns the value stored earlier.
     *    @return mixed    Whatever was stashed.
     */
    function act() {
        return $this->value;
    }
}

/**
 *    A type of SimpleMethodAction.
 *    Stashes a reference for returning later.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class SimpleByReference {
    private $reference;

    /**
     *    Stashes it for later.
     *    @param mixed $reference     Actual PHP4 style reference.
     */
    function __construct(&$reference) {
        $this->reference = &$reference;
    }

    /**
     *    Returns the reference stored earlier.
     *    @return mixed    Whatever was stashed.
     */
    function &act() {
        return $this->reference;
    }
}

/**
 *    A type of SimpleMethodAction.
 *    Stashes a value for returning later.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class SimpleByValue {
    private $value;

    /**
     *    Stashes it for later.
     *    @param mixed $value     You need to clone objects
     *                            if you want copy semantics
     *                            for these.
     */
    function __construct($value) {
        $this->value = $value;
    }

    /**
     *    Returns the value stored earlier.
     *    @return mixed    Whatever was stashed.
     */
    function &act() {
        $dummy = $this->value;
        return $dummy;
    }
}

/**
 *    A type of SimpleMethodAction.
 *    Stashes an exception for throwing later.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class SimpleThrower {
    private $exception;

    /**
     *    Stashes it for later.
     *    @param Exception $exception    The exception object to throw.
     */
    function __construct($exception) {
        $this->exception = $exception;
    }

    /**
     *    Throws the exceptins stashed earlier.
     */
    function act() {
        throw $this->exception;
    }
}

/**
 *    A type of SimpleMethodAction.
 *    Stashes an error for emitting later.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class SimpleErrorThrower {
    private $error;
    private $severity;

    /**
     *    Stashes an error to throw later.
     *    @param string $error      Error message.
     *    @param integer $severity  PHP error constant, e.g E_USER_ERROR.
     */
    function __construct($error, $severity) {
        $this->error = $error;
        $this->severity = $severity;
    }

    /**
     *    Triggers the stashed error.
     */
    function &act() {
        trigger_error($this->error, $this->severity);
        $null = null;
        return $null;
    }
}

/**
 *    A base class or delegate that extends an
 *    empty collection of methods that can have their
 *    return values set and expectations made of the
 *    calls upon them. The mock will assert the
 *    expectations against it's attached test case in
 *    addition to the server stub behaviour or returning
 *    preprogrammed responses.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class SimpleMock {
    private $actions;
    private $expectations;
    private $wildcard = MOCK_ANYTHING;
    private $is_strict = true;
    private $call_counts;
    private $expected_counts;
    private $max_counts;
    private $expected_args;
    private $expected_args_at;

    /**
     *    Creates an empty action list and expectation list.
     *    All call counts are set to zero.
     */
    function SimpleMock() {
        $this->actions = new SimpleCallSchedule();
        $this->expectations = new SimpleCallSchedule();
        $this->call_counts = array();
        $this->expected_counts = array();
        $this->max_counts = array();
        $this->expected_args = array();
        $this->expected_args_at = array();
        $this->getCurrentTestCase()->tell($this);
    }

    /**
     *    Disables a name check when setting expectations.
     *    This hack is needed for the partial mocks.
     */
    function disableExpectationNameChecks() {
        $this->is_strict = false;
    }

    /**
     *    Finds currently running test.
     *    @return SimpeTestCase    Current test case.
     */
    protected function getCurrentTestCase() {
        return SimpleTest::getContext()->getTest();
    }

    /**
     *    Die if bad arguments array is passed.
     *    @param mixed $args     The arguments value to be checked.
     *    @param string $task    Description of task attempt.
     *    @return boolean        Valid arguments
     */
    protected function checkArgumentsIsArray($args, $task) {
        if (! is_array($args)) {
            trigger_error(
                "Cannot $task as \$args parameter is not an array",
                E_USER_ERROR);
        }
    }

    /**
     *    Triggers a PHP error if the method is not part
     *    of this object.
     *    @param string $method        Name of method.
     *    @param string $task          Description of task attempt.
     */
    protected function dieOnNoMethod($method, $task) {
        if ($this->is_strict && ! method_exists($this, $method)) {
            trigger_error(
                    "Cannot $task as no ${method}() in class " . get_class($this),
                    E_USER_ERROR);
        }
    }

    /**
     *    Replaces wildcard matches with wildcard
     *    expectations in the argument list.
     *    @param array $args      Raw argument list.
     *    @return array           Argument list with
     *                            expectations.
     */
    function replaceWildcards($args) {
        if ($args === false) {
            return false;
        }
        for ($i = 0; $i < count($args); $i++) {
            if ($args[$i] === $this->wildcard) {
                $args[$i] = new AnythingExpectation();
            }
        }
        return $args;
    }

    /**
     *    Adds one to the call count of a method.
     *    @param string $method        Method called.
     *    @param array $args           Arguments as an array.
     */
    protected function addCall($method, $args) {
        if (! isset($this->call_counts[$method])) {
            $this->call_counts[$method] = 0;
        }
        $this->call_counts[$method]++;
    }

    /**
     *    Fetches the call count of a method so far.
     *    @param string $method        Method name called.
     *    @return integer              Number of calls so far.
     */
    function getCallCount($method) {
        $this->dieOnNoMethod($method, "get call count");
        $method = strtolower($method);
        if (! isset($this->call_counts[$method])) {
            return 0;
        }
        return $this->call_counts[$method];
    }

    /**
     *    Sets a return for a parameter list that will
     *    be passed on by all calls to this method that match.
     *    @param string $method       Method name.
     *    @param mixed $value         Result of call by value/handle.
     *    @param array $args          List of parameters to match
     *                                including wildcards.
     */
    function returns($method, $value, $args = false) {
        $this->dieOnNoMethod($method, "set return");
        $this->actions->register($method, $args, new SimpleReturn($value));
    }

    /**
     *    Sets a return for a parameter list that will
     *    be passed only when the required call count
     *    is reached.
     *    @param integer $timing   Number of calls in the future
     *                             to which the result applies. If
     *                             not set then all calls will return
     *                             the value.
     *    @param string $method    Method name.
     *    @param mixed $value      Result of call passed.
     *    @param array $args       List of parameters to match
     *                             including wildcards.
     */
    function returnsAt($timing, $method, $value, $args = false) {
        $this->dieOnNoMethod($method, "set return value sequence");
        $this->actions->registerAt($timing, $method, $args, new SimpleReturn($value));
    }

    /**
     *    Sets a return for a parameter list that will
     *    be passed by value for all calls to this method.
     *    @param string $method       Method name.
     *    @param mixed $value         Result of call passed by value.
     *    @param array $args          List of parameters to match
     *                                including wildcards.
     */
    function returnsByValue($method, $value, $args = false) {
        $this->dieOnNoMethod($method, "set return value");
        $this->actions->register($method, $args, new SimpleByValue($value));
    }

    /** @deprecated */
    function setReturnValue($method, $value, $args = false) {
        $this->returnsByValue($method, $value, $args);
    }

    /**
     *    Sets a return for a parameter list that will
     *    be passed by value only when the required call count
     *    is reached.
     *    @param integer $timing   Number of calls in the future
     *                             to which the result applies. If
     *                             not set then all calls will return
     *                             the value.
     *    @param string $method    Method name.
     *    @param mixed $value      Result of call passed by value.
     *    @param array $args       List of parameters to match
     *                             including wildcards.
     */
    function returnsByValueAt($timing, $method, $value, $args = false) {
        $this->dieOnNoMethod($method, "set return value sequence");
        $this->actions->registerAt($timing, $method, $args, new SimpleByValue($value));
    }

    /** @deprecated */
    function setReturnValueAt($timing, $method, $value, $args = false) {
        $this->returnsByValueAt($timing, $method, $value, $args);
    }

    /**
     *    Sets a return for a parameter list that will
     *    be passed by reference for all calls.
     *    @param string $method       Method name.
     *    @param mixed $reference     Result of the call will be this object.
     *    @param array $args          List of parameters to match
     *                                including wildcards.
     */
    function returnsByReference($method, &$reference, $args = false) {
        $this->dieOnNoMethod($method, "set return reference");
        $this->actions->register($method, $args, new SimpleByReference($reference));
    }

    /** @deprecated */
    function setReturnReference($method, &$reference, $args = false) {
        $this->returnsByReference($method, $reference, $args);
    }

    /**
     *    Sets a return for a parameter list that will
     *    be passed by value only when the required call count
     *    is reached.
     *    @param integer $timing    Number of calls in the future
     *                              to which the result applies. If
     *                              not set then all calls will return
     *                              the value.
     *    @param string $method     Method name.
     *    @param mixed $reference   Result of the call will be this object.
     *    @param array $args        List of parameters to match
     *                              including wildcards.
     */
    function returnsByReferenceAt($timing, $method, &$reference, $args = false) {
        $this->dieOnNoMethod($method, "set return reference sequence");
        $this->actions->registerAt($timing, $method, $args, new SimpleByReference($reference));
    }

    /** @deprecated */
    function setReturnReferenceAt($timing, $method, &$reference, $args = false) {
        $this->returnsByReferenceAt($timing, $method, $reference, $args);
    }

    /**
     *    Sets up an expected call with a set of
     *    expected parameters in that call. All
     *    calls will be compared to these expectations
     *    regardless of when the call is made.
     *    @param string $method        Method call to test.
     *    @param array $args           Expected parameters for the call
     *                                 including wildcards.
     *    @param string $message       Overridden message.
     */
    function expect($method, $args, $message = '%s') {
        $this->dieOnNoMethod($method, 'set expected arguments');
        $this->checkArgumentsIsArray($args, 'set expected arguments');
        $this->expectations->expectArguments($method, $args, $message);
        $args = $this->replaceWildcards($args);
        $message .= Mock::getExpectationLine();
        $this->expected_args[strtolower($method)] =
                new ParametersExpectation($args, $message);
    }

    /**
     *    Sets up an expected call with a set of
     *    expected parameters in that call. The
     *    expected call count will be adjusted if it
     *    is set too low to reach this call.
     *    @param integer $timing    Number of calls in the future at
     *                              which to test. Next call is 0.
     *    @param string $method     Method call to test.
     *    @param array $args        Expected parameters for the call
     *                              including wildcards.
     *    @param string $message    Overridden message.
     */
    function expectAt($timing, $method, $args, $message = '%s') {
        $this->dieOnNoMethod($method, 'set expected arguments at time');
        $this->checkArgumentsIsArray($args, 'set expected arguments at time');
        $args = $this->replaceWildcards($args);
        if (! isset($this->expected_args_at[$timing])) {
            $this->expected_args_at[$timing] = array();
        }
        $method = strtolower($method);
        $message .= Mock::getExpectationLine();
        $this->expected_args_at[$timing][$method] =
                new ParametersExpectation($args, $message);
    }

    /**
     *    Sets an expectation for the number of times
     *    a method will be called. The tally method
     *    is used to check this.
     *    @param string $method        Method call to test.
     *    @param integer $count        Number of times it should
     *                                 have been called at tally.
     *    @param string $message       Overridden message.
     */
    function expectCallCount($method, $count, $message = '%s') {
        $this->dieOnNoMethod($method, 'set expected call count');
        $message .= Mock::getExpectationLine();
        $this->expected_counts[strtolower($method)] =
                new CallCountExpectation($method, $count, $message);
    }

    /**
     *    Sets the number of times a method may be called
     *    before a test failure is triggered.
     *    @param string $method        Method call to test.
     *    @param integer $count        Most number of times it should
     *                                 have been called.
     *    @param string $message       Overridden message.
     */
    function expectMaximumCallCount($method, $count, $message = '%s') {
        $this->dieOnNoMethod($method, 'set maximum call count');
        $message .= Mock::getExpectationLine();
        $this->max_counts[strtolower($method)] =
                new MaximumCallCountExpectation($method, $count, $message);
    }

    /**
     *    Sets the number of times to call a method to prevent
     *    a failure on the tally.
     *    @param string $method      Method call to test.
     *    @param integer $count      Least number of times it should
     *                               have been called.
     *    @param string $message     Overridden message.
     */
    function expectMinimumCallCount($method, $count, $message = '%s') {
        $this->dieOnNoMethod($method, 'set minimum call count');
        $message .= Mock::getExpectationLine();
        $this->expected_counts[strtolower($method)] =
                new MinimumCallCountExpectation($method, $count, $message);
    }

    /**
     *    Convenience method for barring a method
     *    call.
     *    @param string $method        Method call to ban.
     *    @param string $message       Overridden message.
     */
    function expectNever($method, $message = '%s') {
        $this->expectMaximumCallCount($method, 0, $message);
    }

    /**
     *    Convenience method for a single method
     *    call.
     *    @param string $method     Method call to track.
     *    @param array $args        Expected argument list or
     *                              false for any arguments.
     *    @param string $message    Overridden message.
     */
    function expectOnce($method, $args = false, $message = '%s') {
        $this->expectCallCount($method, 1, $message);
        if ($args !== false) {
            $this->expect($method, $args, $message);
        }
    }

    /**
     *    Convenience method for requiring a method
     *    call.
     *    @param string $method       Method call to track.
     *    @param array $args          Expected argument list or
     *                                false for any arguments.
     *    @param string $message      Overridden message.
     */
    function expectAtLeastOnce($method, $args = false, $message = '%s') {
        $this->expectMinimumCallCount($method, 1, $message);
        if ($args !== false) {
            $this->expect($method, $args, $message);
        }
    }

    /**
     *    Sets up a trigger to throw an exception upon the
     *    method call.
     *    @param string $method     Method name to throw on.
     *    @param object $exception  Exception object to throw.
     *                              If not given then a simple
     *                              Exception object is thrown.
     *    @param array $args        Optional argument list filter.
     *                              If given then the exception
     *                              will only be thrown if the
     *                              method call matches the arguments.
     */
    function throwOn($method, $exception = false, $args = false) {
        $this->dieOnNoMethod($method, "throw on");
        $this->actions->register($method, $args,
                new SimpleThrower($exception ? $exception : new Exception()));
    }

    /**
     *    Sets up a trigger to throw an exception upon the
     *    method call.
     *    @param integer $timing    When to throw the exception. A
     *                              value of 0 throws immediately.
     *                              A value of 1 actually allows one call
     *                              to this method before throwing. 2
     *                              will allow two calls before throwing
     *                              and so on.
     *    @param string $method     Method name to throw on.
     *    @param object $exception  Exception object to throw.
     *                              If not given then a simple
     *                              Exception object is thrown.
     *    @param array $args        Optional argument list filter.
     *                              If given then the exception
     *                              will only be thrown if the
     *                              method call matches the arguments.
     */
    function throwAt($timing, $method, $exception = false, $args = false) {
        $this->dieOnNoMethod($method, "throw at");
        $this->actions->registerAt($timing, $method, $args,
                new SimpleThrower($exception ? $exception : new Exception()));
    }

    /**
     *    Sets up a trigger to throw an error upon the
     *    method call.
     *    @param string $method     Method name to throw on.
     *    @param object $error      Error message to trigger.
     *    @param array $args        Optional argument list filter.
     *                              If given then the exception
     *                              will only be thrown if the
     *                              method call matches the arguments.
     *    @param integer $severity  The PHP severity level. Defaults
     *                              to E_USER_ERROR.
     */
    function errorOn($method, $error = 'A mock error', $args = false, $severity = E_USER_ERROR) {
        $this->dieOnNoMethod($method, "error on");
        $this->actions->register($method, $args, new SimpleErrorThrower($error, $severity));
    }

    /**
     *    Sets up a trigger to throw an error upon a specific
     *    method call.
     *    @param integer $timing    When to throw the exception. A
     *                              value of 0 throws immediately.
     *                              A value of 1 actually allows one call
     *                              to this method before throwing. 2
     *                              will allow two calls before throwing
     *                              and so on.
     *    @param string $method     Method name to throw on.
     *    @param object $error      Error message to trigger.
     *    @param array $args        Optional argument list filter.
     *                              If given then the exception
     *                              will only be thrown if the
     *                              method call matches the arguments.
     *    @param integer $severity  The PHP severity level. Defaults
     *                              to E_USER_ERROR.
     */
    function errorAt($timing, $method, $error = 'A mock error', $args = false, $severity = E_USER_ERROR) {
        $this->dieOnNoMethod($method, "error at");
        $this->actions->registerAt($timing, $method, $args, new SimpleErrorThrower($error, $severity));
    }

    /**
     *    Receives event from unit test that the current
     *    test method has finished. Totals up the call
     *    counts and triggers a test assertion if a test
     *    is present for expected call counts.
     *    @param string $test_method      Current method name.
     *    @param SimpleTestCase $test     Test to send message to.
     */
    function atTestEnd($test_method, &$test) {
        foreach ($this->expected_counts as $method => $expectation) {
            $test->assert($expectation, $this->getCallCount($method));
        }
        foreach ($this->max_counts as $method => $expectation) {
            if ($expectation->test($this->getCallCount($method))) {
                $test->assert($expectation, $this->getCallCount($method));
            }
        }
    }

    /**
     *    Returns the expected value for the method name
     *    and checks expectations. Will generate any
     *    test assertions as a result of expectations
     *    if there is a test present.
     *    @param string $method       Name of method to simulate.
     *    @param array $args          Arguments as an array.
     *    @return mixed               Stored return.
     */
    function &invoke($method, $args) {
        $method = strtolower($method);
        $step = $this->getCallCount($method);
        $this->addCall($method, $args);
        $this->checkExpectations($method, $args, $step);
        $was = $this->disableEStrict();
        try {
            $result = &$this->emulateCall($method, $args, $step);
        } catch (Exception $e) {
            $this->restoreEStrict($was);
            throw $e;
        }
        $this->restoreEStrict($was);
        return $result;
    }

    /**
     *    Finds the return value matching the incoming
     *    arguments. If there is no matching value found
     *    then an error is triggered.
     *    @param string $method      Method name.
     *    @param array $args         Calling arguments.
     *    @param integer $step       Current position in the
     *                               call history.
     *    @return mixed              Stored return or other action.
     */
    protected function &emulateCall($method, $args, $step) {
        return $this->actions->respond($step, $method, $args);
    }

    /**
     *    Tests the arguments against expectations.
     *    @param string $method        Method to check.
     *    @param array $args           Argument list to match.
     *    @param integer $timing       The position of this call
     *                                 in the call history.
     */
    protected function checkExpectations($method, $args, $timing) {
        $test = $this->getCurrentTestCase();
        if (isset($this->max_counts[$method])) {
            if (! $this->max_counts[$method]->test($timing + 1)) {
                $test->assert($this->max_counts[$method], $timing + 1);
            }
        }
        if (isset($this->expected_args_at[$timing][$method])) {
            $test->assert(
                    $this->expected_args_at[$timing][$method],
                    $args,
                    "Mock method [$method] at [$timing] -> %s");
        } elseif (isset($this->expected_args[$method])) {
            $test->assert(
                    $this->expected_args[$method],
                    $args,
                    "Mock method [$method] -> %s");
        }
    }

    /**
     *   Our mock has to be able to return anything, including
     *   variable references. To allow for these mixed returns
     *   we have to disable the E_STRICT warnings while the
     *   method calls are emulated.
     */
    private function disableEStrict() {
        $was = error_reporting();
        error_reporting($was & ~E_STRICT);
        return $was;
    }

    /**
     *  Restores the E_STRICT level if it was previously set.
     *  @param integer $was     Previous error reporting level.
     */
    private function restoreEStrict($was) {
        error_reporting($was);
    }
}

/**
 *    Static methods only service class for code generation of
 *    mock objects.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class Mock {

    /**
     *    Factory for mock object classes.
     */
    function __construct() {
        trigger_error('Mock factory methods are static.');
    }

    /**
     *    Clones a class' interface and creates a mock version
     *    that can have return values and expectations set.
     *    @param string $class         Class to clone.
     *    @param string $mock_class    New class name. Default is
     *                                 the old name with "Mock"
     *                                 prepended.
     *    @param array $methods        Additional methods to add beyond
     *                                 those in the cloned class. Use this
     *                                 to emulate the dynamic addition of
     *                                 methods in the cloned class or when
     *                                 the class hasn't been written yet.sta
     */
    static function generate($class, $mock_class = false, $methods = false) {
        $generator = new MockGenerator($class, $mock_class);
        return @$generator->generateSubclass($methods);
    }

    /**
     *    Generates a version of a class with selected
     *    methods mocked only. Inherits the old class
     *    and chains the mock methods of an aggregated
     *    mock object.
     *    @param string $class            Class to clone.
     *    @param string $mock_class       New class name.
     *    @param array $methods           Methods to be overridden
     *                                    with mock versions.
     */
    static function generatePartial($class, $mock_class, $methods) {
        $generator = new MockGenerator($class, $mock_class);
        return @$generator->generatePartial($methods);
    }

    /**
     *    Uses a stack trace to find the line of an assertion.
     */
    static function getExpectationLine() {
        $trace = new SimpleStackTrace(array('expect'));
        return $trace->traceMethod();
    }
}

/**
 *    Service class for code generation of mock objects.
 *    @package SimpleTest
 *    @subpackage MockObjects
 */
class MockGenerator {
    private $class;
    private $mock_class;
    private $mock_base;
    private $reflection;

    /**
     *    Builds initial reflection object.
     *    @param string $class        Class to be mocked.
     *    @param string $mock_class   New class with identical interface,
     *                                but no behaviour.
     */
    function __construct($class, $mock_class) {
        $this->class = $class;
        $this->mock_class = $mock_class;
        if (! $this->mock_class) {
            $this->mock_class = 'Mock' . $this->class;
        }
        $this->mock_base = SimpleTest::getMockBaseClass();
        $this->reflection = new SimpleReflection($this->class);
    }

    /**
     *    Clones a class' interface and creates a mock version
     *    that can have return values and expectations set.
     *    @param array $methods        Additional methods to add beyond
     *                                 those in th cloned class. Use this
     *                                 to emulate the dynamic addition of
     *                                 methods in the cloned class or when
     *                                 the class hasn't been written yet.
     */
    function generate($methods) {
        if (! $this->reflection->classOrInterfaceExists()) {
            return false;
        }
        $mock_reflection = new SimpleReflection($this->mock_class);
        if ($mock_reflection->classExistsSansAutoload()) {
            return false;
        }
        $code = $this->createClassCode($methods ? $methods : array());
        return eval("$code return \$code;");
    }

    /**
     *    Subclasses a class and overrides every method with a mock one
     *    that can have return values and expectations set. Chains
     *    to an aggregated SimpleMock.
     *    @param array $methods        Additional methods to add beyond
     *                                 those in the cloned class. Use this
     *                                 to emulate the dynamic addition of
     *                                 methods in the cloned class or when
     *                                 the class hasn't been written yet.
     */
    function generateSubclass($methods) {
        if (! $this->reflection->classOrInterfaceExists()) {
            return false;
        }
        $mock_reflection = new SimpleReflection($this->mock_class);
        if ($mock_reflection->classExistsSansAutoload()) {
            return false;
        }
        if ($this->reflection->isInterface() || $this->reflection->hasFinal()) {
            $code = $this->createClassCode($methods ? $methods : array());
            return eval("$code return \$code;");
        } else {
            $code = $this->createSubclassCode($methods ? $methods : array());
            return eval("$code return \$code;");
        }
    }

    /**
     *    Generates a version of a class with selected
     *    methods mocked only. Inherits the old class
     *    and chains the mock methods of an aggregated
     *    mock object.
     *    @param array $methods           Methods to be overridden
     *                                    with mock versions.
     */
    function generatePartial($methods) {
        if (! $this->reflection->classExists($this->class)) {
            return false;
        }
        $mock_reflection = new SimpleReflection($this->mock_class);
        if ($mock_reflection->classExistsSansAutoload()) {
            trigger_error('Partial mock class [' . $this->mock_class . '] already exists');
            return false;
        }
        $code = $this->extendClassCode($methods);
        return eval("$code return \$code;");
    }

    /**
     *    The new mock class code as a string.
     *    @param array $methods          Additional methods.
     *    @return string                 Code for new mock class.
     */
    protected function createClassCode($methods) {
        $implements = '';
        $interfaces = $this->reflection->getInterfaces();
        if (function_exists('spl_classes')) {
            $interfaces = array_diff($interfaces, array('Traversable'));
        }
        if (count($interfaces) > 0) {
            $implements = 'implements ' . implode(', ', $interfaces);
        }
        $code = "class " . $this->mock_class . " extends " . $this->mock_base . " $implements {\n";
        $code .= "    function " . $this->mock_class . "() {\n";
        $code .= "        \$this->" . $this->mock_base . "();\n";
        $code .= "    }\n";
        if (in_array('__construct', $this->reflection->getMethods())) {
            $code .= "    function __construct() {\n";
            $code .= "        \$this->" . $this->mock_base . "();\n";
            $code .= "    }\n";
        }
        $code .= $this->createHandlerCode($methods);
        $code .= "}\n";
        return $code;
    }

    /**
     *    The new mock class code as a string. The mock will
     *    be a subclass of the original mocked class.
     *    @param array $methods          Additional methods.
     *    @return string                 Code for new mock class.
     */
    protected function createSubclassCode($methods) {
        $code  = "class " . $this->mock_class . " extends " . $this->class . " {\n";
        $code .= "    public \$mock;\n";
        $code .= $this->addMethodList(array_merge($methods, $this->reflection->getMethods()));
        $code .= "\n";
        $code .= "    function " . $this->mock_class . "() {\n";
        $code .= "        \$this->mock = new " . $this->mock_base . "();\n";
        $code .= "        \$this->mock->disableExpectationNameChecks();\n";
        $code .= "    }\n";
        $code .= $this->chainMockReturns();
        $code .= $this->chainMockExpectations();
        $code .= $this->chainThrowMethods();
        $code .= $this->overrideMethods($this->reflection->getMethods());
        $code .= $this->createNewMethodCode($methods);
        $code .= "}\n";
        return $code;
    }

    /**
     *    The extension class code as a string. The class
     *    composites a mock object and chains mocked methods
     *    to it.
     *    @param array  $methods       Mocked methods.
     *    @return string               Code for a new class.
     */
    protected function extendClassCode($methods) {
        $code  = "class " . $this->mock_class . " extends " . $this->class . " {\n";
        $code .= "    protected \$mock;\n";
        $code .= $this->addMethodList($methods);
        $code .= "\n";
        $code .= "    function " . $this->mock_class . "() {\n";
        $code .= "        \$this->mock = new " . $this->mock_base . "();\n";
        $code .= "        \$this->mock->disableExpectationNameChecks();\n";
        $code .= "    }\n";
        $code .= $this->chainMockReturns();
        $code .= $this->chainMockExpectations();
        $code .= $this->chainThrowMethods();
        $code .= $this->overrideMethods($methods);
        $code .= "}\n";
        return $code;
    }

    /**
     *    Creates code within a class to generate replaced
     *    methods. All methods call the invoke() handler
     *    with the method name and the arguments in an
     *    array.
     *    @param array $methods    Additional methods.
     */
    protected function createHandlerCode($methods) {
        $code = '';
        $methods = array_merge($methods, $this->reflection->getMethods());
        foreach ($methods as $method) {
            if ($this->isConstructor($method)) {
                continue;
            }
            $mock_reflection = new SimpleReflection($this->mock_base);
            if (in_array($method, $mock_reflection->getMethods())) {
                continue;
            }
            $code .= "    " . $this->reflection->getSignature($method) . " {\n";
            $code .= "        \$args = func_get_args();\n";
            $code .= "        \$result = &\$this->invoke(\"$method\", \$args);\n";
            $code .= "        return \$result;\n";
            $code .= "    }\n";
        }
        return $code;
    }

    /**
     *    Creates code within a class to generate a new
     *    methods. All methods call the invoke() handler
     *    on the internal mock with the method name and
     *    the arguments in an array.
     *    @param array $methods    Additional methods.
     */
    protected function createNewMethodCode($methods) {
        $code = '';
        foreach ($methods as $method) {
            if ($this->isConstructor($method)) {
                continue;
            }
            $mock_reflection = new SimpleReflection($this->mock_base);
            if (in_array($method, $mock_reflection->getMethods())) {
                continue;
            }
            $code .= "    " . $this->reflection->getSignature($method) . " {\n";
            $code .= "        \$args = func_get_args();\n";
            $code .= "        \$result = &\$this->mock->invoke(\"$method\", \$args);\n";
            $code .= "        return \$result;\n";
            $code .= "    }\n";
        }
        return $code;
    }

    /**
     *    Tests to see if a special PHP method is about to
     *    be stubbed by mistake.
     *    @param string $method    Method name.
     *    @return boolean          True if special.
     */
    protected function isConstructor($method) {
        return in_array(
                strtolower($method),
                array('__construct', '__destruct'));
    }

    /**
     *    Creates a list of mocked methods for error checking.
     *    @param array $methods       Mocked methods.
     *    @return string              Code for a method list.
     */
    protected function addMethodList($methods) {
        return "    protected \$mocked_methods = array('" .
                implode("', '", array_map('strtolower', $methods)) .
                "');\n";
    }

    /**
     *    Creates code to abandon the expectation if not mocked.
     *    @param string $alias       Parameter name of method name.
     *    @return string             Code for bail out.
     */
    protected function bailOutIfNotMocked($alias) {
        $code  = "        if (! in_array(strtolower($alias), \$this->mocked_methods)) {\n";
        $code .= "            trigger_error(\"Method [$alias] is not mocked\");\n";
        $code .= "            \$null = null;\n";
        $code .= "            return \$null;\n";
        $code .= "        }\n";
        return $code;
    }

    /**
     *    Creates source code for chaining to the composited
     *    mock object.
     *    @return string           Code for mock set up.
     */
    protected function chainMockReturns() {
        $code  = "    function returns(\$method, \$value, \$args = false) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->returns(\$method, \$value, \$args);\n";
        $code .= "    }\n";
        $code .= "    function returnsAt(\$timing, \$method, \$value, \$args = false) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->returnsAt(\$timing, \$method, \$value, \$args);\n";
        $code .= "    }\n";
        $code .= "    function returnsByValue(\$method, \$value, \$args = false) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->setReturnValue(\$method, \$value, \$args);\n";
        $code .= "    }\n";
        $code .= "    function returnsByValueAt(\$timing, \$method, \$value, \$args = false) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->setReturnValueAt(\$timing, \$method, \$value, \$args);\n";
        $code .= "    }\n";
        $code .= "    function returnsByReference(\$method, &\$ref, \$args = false) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->setReturnReference(\$method, \$ref, \$args);\n";
        $code .= "    }\n";
        $code .= "    function returnsByReferenceAt(\$timing, \$method, &\$ref, \$args = false) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->setReturnReferenceAt(\$timing, \$method, \$ref, \$args);\n";
        $code .= "    }\n";
        $code .= "    function setReturnValue(\$method, \$value, \$args = false) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->setReturnValue(\$method, \$value, \$args);\n";
        $code .= "    }\n";
        $code .= "    function setReturnValueAt(\$timing, \$method, \$value, \$args = false) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->setReturnValueAt(\$timing, \$method, \$value, \$args);\n";
        $code .= "    }\n";
        $code .= "    function setReturnReference(\$method, &\$ref, \$args = false) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->setReturnReference(\$method, \$ref, \$args);\n";
        $code .= "    }\n";
        $code .= "    function setReturnReferenceAt(\$timing, \$method, &\$ref, \$args = false) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->setReturnReferenceAt(\$timing, \$method, \$ref, \$args);\n";
        $code .= "    }\n";
        return $code;
    }

    /**
     *    Creates source code for chaining to an aggregated
     *    mock object.
     *    @return string                 Code for expectations.
     */
    protected function chainMockExpectations() {
        $code  = "    function expect(\$method, \$args = false, \$msg = '%s') {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->expect(\$method, \$args, \$msg);\n";
        $code .= "    }\n";
        $code .= "    function expectAt(\$timing, \$method, \$args = false, \$msg = '%s') {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->expectAt(\$timing, \$method, \$args, \$msg);\n";
        $code .= "    }\n";
        $code .= "    function expectCallCount(\$method, \$count) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->expectCallCount(\$method, \$count, \$msg = '%s');\n";
        $code .= "    }\n";
        $code .= "    function expectMaximumCallCount(\$method, \$count, \$msg = '%s') {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->expectMaximumCallCount(\$method, \$count, \$msg = '%s');\n";
        $code .= "    }\n";
        $code .= "    function expectMinimumCallCount(\$method, \$count, \$msg = '%s') {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->expectMinimumCallCount(\$method, \$count, \$msg = '%s');\n";
        $code .= "    }\n";
        $code .= "    function expectNever(\$method) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->expectNever(\$method);\n";
        $code .= "    }\n";
        $code .= "    function expectOnce(\$method, \$args = false, \$msg = '%s') {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->expectOnce(\$method, \$args, \$msg);\n";
        $code .= "    }\n";
        $code .= "    function expectAtLeastOnce(\$method, \$args = false, \$msg = '%s') {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->expectAtLeastOnce(\$method, \$args, \$msg);\n";
        $code .= "    }\n";
        return $code;
    }

    /**
     *    Adds code for chaining the throw methods.
     *    @return string           Code for chains.
     */
    protected function chainThrowMethods() {
        $code  = "    function throwOn(\$method, \$exception = false, \$args = false) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->throwOn(\$method, \$exception, \$args);\n";
        $code .= "    }\n";
        $code .= "    function throwAt(\$timing, \$method, \$exception = false, \$args = false) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->throwAt(\$timing, \$method, \$exception, \$args);\n";
        $code .= "    }\n";
        $code .= "    function errorOn(\$method, \$error = 'A mock error', \$args = false, \$severity = E_USER_ERROR) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->errorOn(\$method, \$error, \$args, \$severity);\n";
        $code .= "    }\n";
        $code .= "    function errorAt(\$timing, \$method, \$error = 'A mock error', \$args = false, \$severity = E_USER_ERROR) {\n";
        $code .= $this->bailOutIfNotMocked("\$method");
        $code .= "        \$this->mock->errorAt(\$timing, \$method, \$error, \$args, \$severity);\n";
        $code .= "    }\n";
        return $code;
    }

    /**
     *    Creates source code to override a list of methods
     *    with mock versions.
     *    @param array $methods    Methods to be overridden
     *                             with mock versions.
     *    @return string           Code for overridden chains.
     */
    protected function overrideMethods($methods) {
        $code = "";
        foreach ($methods as $method) {
            if ($this->isConstructor($method)) {
                continue;
            }
            $code .= "    " . $this->reflection->getSignature($method) . " {\n";
            $code .= "        \$args = func_get_args();\n";
            $code .= "        \$result = &\$this->mock->invoke(\"$method\", \$args);\n";
            $code .= "        return \$result;\n";
            $code .= "    }\n";
        }
        return $code;
    }
}
 /* .tmp\flat\1\recorder.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\1\recorder.php */ ?>
<?php
/**
 *	base include file for SimpleTest
 *	@package	SimpleTest
 *	@subpackage	Extensions
 *  @author Rene vd O (original code)
 *  @author Perrick Penet
 *  @author Marcus Baker
 *	@version	$Id$
 */

/**
 *	include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/scorer.php');

/**
 *	A single test result.
 */
abstract class SimpleResult {
	public $time;
	public $breadcrumb;
	public $message;
	
	/**
	 * Records the test result as public members.
	 * @param array $breadcrumb		Test stack at the time of the event.
	 * @param string $message       The messsage to the human.
	 */
	function __construct($breadcrumb, $message) {
		list($this->time, $this->breadcrumb, $this->message) =
				array(time(), $breadcrumb, $message);
	}
}

/** A single pass captured for later. */
class SimpleResultOfPass extends SimpleResult { }

/** A single failure captured for later. */
class SimpleResultOfFail extends SimpleResult { }

/** A single exception captured for later. */
class SimpleResultOfException extends SimpleResult { }

/**
 *    Array-based test recorder. Returns an array
 *    with timestamp, status, test name and message for each pass and failure.
 */
class Recorder extends SimpleReporterDecorator {
    public $results = array();
	
	/**
	 *    Stashes the pass as a SimpleResultOfPass
	 *    for later retrieval.
     *    @param string $message    Pass message to be displayed
     *    							eventually.
	 */
	function paintPass($message) {
        parent::paintPass($message);
        $this->results[] = new SimpleResultOfPass(parent::getTestList(), $message);
	}
	
	/**
	 * 	  Stashes the fail as a SimpleResultOfFail
	 * 	  for later retrieval.
     *    @param string $message    Failure message to be displayed
     *    							eventually.
	 */
	function paintFail($message) {
        parent::paintFail($message);
        $this->results[] = new SimpleResultOfFail(parent::getTestList(), $message);
	}
	
	/**
	 * 	  Stashes the exception as a SimpleResultOfException
	 * 	  for later retrieval.
     *    @param string $message    Exception message to be displayed
     *    							eventually.
	 */
	function paintException($message) {
        parent::paintException($message);
        $this->results[] = new SimpleResultOfException(parent::getTestList(), $message);
	}
}
 /* .tmp\flat\1\reporter.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\1\reporter.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/scorer.php');
//require_once(dirname(__FILE__) . '/arguments.php');
/**#@-*/

/**
 *    Sample minimal test displayer. Generates only
 *    failure messages and a pass count.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class HtmlReporter extends SimpleReporter {
    private $character_set;

    /**
     *    Does nothing yet. The first output will
     *    be sent on the first test start. For use
     *    by a web browser.
     *    @access public
     */
    function __construct($character_set = 'ISO-8859-1') {
        parent::__construct();
        $this->character_set = $character_set;
    }

    /**
     *    Paints the top of the web page setting the
     *    title to the name of the starting test.
     *    @param string $test_name      Name class of test.
     *    @access public
     */
    function paintHeader($test_name) {
        $this->sendNoCacheHeaders();
        print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
        print "<html>\n<head>\n<title>$test_name</title>\n";
        print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" .
                $this->character_set . "\">\n";
        print "<style type=\"text/css\">\n";
        print $this->getCss() . "\n";
        print "</style>\n";
        print "</head>\n<body>\n";
        print "<h1>$test_name</h1>\n";
        flush();
    }

    /**
     *    Send the headers necessary to ensure the page is
     *    reloaded on every request. Otherwise you could be
     *    scratching your head over out of date test data.
     *    @access public
     */
    static function sendNoCacheHeaders() {
        if (! headers_sent()) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }
    }

    /**
     *    Paints the CSS. Add additional styles here.
     *    @return string            CSS code as text.
     *    @access protected
     */
    protected function getCss() {
        return ".fail { background-color: inherit; color: red; }" .
                ".pass { background-color: inherit; color: green; }" .
                " pre { background-color: lightgray; color: inherit; }";
    }

    /**
     *    Paints the end of the test with a summary of
     *    the passes and failures.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintFooter($test_name) {
        $colour = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "red" : "green");
        print "<div style=\"";
        print "padding: 8px; margin-top: 1em; background-color: $colour; color: white;";
        print "\">";
        print $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
        print " test cases complete:\n";
        print "<strong>" . $this->getPassCount() . "</strong> passes, ";
        print "<strong>" . $this->getFailCount() . "</strong> fails and ";
        print "<strong>" . $this->getExceptionCount() . "</strong> exceptions.";
        print "</div>\n";
        print "</body>\n</html>\n";
    }

    /**
     *    Paints the test failure with a breadcrumbs
     *    trail of the nesting test suites below the
     *    top level test.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     */
    function paintFail($message) {
        parent::paintFail($message);
        print "<span class=\"fail\">Fail</span>: ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode(" -&gt; ", $breadcrumb);
        print " -&gt; " . $this->htmlEntities($message) . "<br />\n";
    }

    /**
     *    Paints a PHP error.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintError($message) {
        parent::paintError($message);
        print "<span class=\"fail\">Exception</span>: ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode(" -&gt; ", $breadcrumb);
        print " -&gt; <strong>" . $this->htmlEntities($message) . "</strong><br />\n";
    }

    /**
     *    Paints a PHP exception.
     *    @param Exception $exception        Exception to display.
     *    @access public
     */
    function paintException($exception) {
        parent::paintException($exception);
        print "<span class=\"fail\">Exception</span>: ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode(" -&gt; ", $breadcrumb);
        $message = 'Unexpected exception of type [' . get_class($exception) .
                '] with message ['. $exception->getMessage() .
                '] in ['. $exception->getFile() .
                ' line ' . $exception->getLine() . ']';
        print " -&gt; <strong>" . $this->htmlEntities($message) . "</strong><br />\n";
    }
    
    /**
     *    Prints the message for skipping tests.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) {
        parent::paintSkip($message);
        print "<span class=\"pass\">Skipped</span>: ";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print implode(" -&gt; ", $breadcrumb);
        print " -&gt; " . $this->htmlEntities($message) . "<br />\n";
    }

    /**
     *    Paints formatted text such as dumped privateiables.
     *    @param string $message        Text to show.
     *    @access public
     */
    function paintFormattedMessage($message) {
        print '<pre>' . $this->htmlEntities($message) . '</pre>';
    }

    /**
     *    Character set adjusted entity conversion.
     *    @param string $message    Plain text or Unicode message.
     *    @return string            Browser readable message.
     *    @access protected
     */
    protected function htmlEntities($message) {
        return htmlentities($message, ENT_COMPAT, $this->character_set);
    }
}

/**
 *    Sample minimal test displayer. Generates only
 *    failure messages and a pass count. For command
 *    line use. I've tried to make it look like JUnit,
 *    but I wanted to output the errors as they arrived
 *    which meant dropping the dots.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class TextReporter extends SimpleReporter {

    /**
     *    Does nothing yet. The first output will
     *    be sent on the first test start.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     *    Paints the title only.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintHeader($test_name) {
        if (! SimpleReporter::inCli()) {
            header('Content-type: text/plain');
        }
        print "$test_name\n";
        flush();
    }

    /**
     *    Paints the end of the test with a summary of
     *    the passes and failures.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintFooter($test_name) {
        if ($this->getFailCount() + $this->getExceptionCount() == 0) {
            print "OK\n";
        } else {
            print "FAILURES!!!\n";
        }
        print "Test cases run: " . $this->getTestCaseProgress() .
                "/" . $this->getTestCaseCount() .
                ", Passes: " . $this->getPassCount() .
                ", Failures: " . $this->getFailCount() .
                ", Exceptions: " . $this->getExceptionCount() . "\n";
    }

    /**
     *    Paints the test failure as a stack trace.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     *    @access public
     */
    function paintFail($message) {
        parent::paintFail($message);
        print $this->getFailCount() . ") $message\n";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
        print "\n";
    }

    /**
     *    Paints a PHP error or exception.
     *    @param string $message        Message to be shown.
     *    @access public
     *    @abstract
     */
    function paintError($message) {
        parent::paintError($message);
        print "Exception " . $this->getExceptionCount() . "!\n$message\n";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
        print "\n";
    }

    /**
     *    Paints a PHP error or exception.
     *    @param Exception $exception      Exception to describe.
     *    @access public
     *    @abstract
     */
    function paintException($exception) {
        parent::paintException($exception);
        $message = 'Unexpected exception of type [' . get_class($exception) .
                '] with message ['. $exception->getMessage() .
                '] in ['. $exception->getFile() .
                ' line ' . $exception->getLine() . ']';
        print "Exception " . $this->getExceptionCount() . "!\n$message\n";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
        print "\n";
    }
    
    /**
     *    Prints the message for skipping tests.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) {
        parent::paintSkip($message);
        print "Skip: $message\n";
    }

    /**
     *    Paints formatted text such as dumped privateiables.
     *    @param string $message        Text to show.
     *    @access public
     */
    function paintFormattedMessage($message) {
        print "$message\n";
        flush();
    }
}

/**
 *    Runs just a single test group, a single case or
 *    even a single test within that case.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SelectiveReporter extends SimpleReporterDecorator {
    private $just_this_case = false;
    private $just_this_test = false;
    private $on;
    
    /**
     *    Selects the test case or group to be run,
     *    and optionally a specific test.
     *    @param SimpleScorer $reporter    Reporter to receive events.
     *    @param string $just_this_case    Only this case or group will run.
     *    @param string $just_this_test    Only this test method will run.
     */
    function __construct($reporter, $just_this_case = false, $just_this_test = false) {
        if (isset($just_this_case) && $just_this_case) {
            $this->just_this_case = strtolower($just_this_case);
            $this->off();
        } else {
            $this->on();
        }
        if (isset($just_this_test) && $just_this_test) {
            $this->just_this_test = strtolower($just_this_test);
        }
        parent::__construct($reporter);
    }

    /**
     *    Compares criteria to actual the case/group name.
     *    @param string $test_case    The incoming test.
     *    @return boolean             True if matched.
     *    @access protected
     */
    protected function matchesTestCase($test_case) {
        return $this->just_this_case == strtolower($test_case);
    }

    /**
     *    Compares criteria to actual the test name. If no
     *    name was specified at the beginning, then all tests
     *    can run.
     *    @param string $method       The incoming test method.
     *    @return boolean             True if matched.
     *    @access protected
     */
    protected function shouldRunTest($test_case, $method) {
        if ($this->isOn() || $this->matchesTestCase($test_case)) {
            if ($this->just_this_test) {
                return $this->just_this_test == strtolower($method);
            } else {
                return true;
            }
        }
        return false;
    }
    
    /**
     *    Switch on testing for the group or subgroup.
     *    @access private
     */
    protected function on() {
        $this->on = true;
    }
    
    /**
     *    Switch off testing for the group or subgroup.
     *    @access private
     */
    protected function off() {
        $this->on = false;
    }
    
    /**
     *    Is this group actually being tested?
     *    @return boolean     True if the current test group is active.
     *    @access private
     */
    protected function isOn() {
        return $this->on;
    }

    /**
     *    Veto everything that doesn't match the method wanted.
     *    @param string $test_case       Name of test case.
     *    @param string $method          Name of test method.
     *    @return boolean                True if test should be run.
     *    @access public
     */
    function shouldInvoke($test_case, $method) {
        if ($this->shouldRunTest($test_case, $method)) {
            return $this->reporter->shouldInvoke($test_case, $method);
        }
        return false;
    }

    /**
     *    Paints the start of a group test.
     *    @param string $test_case     Name of test or other label.
     *    @param integer $size         Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_case, $size) {
        if ($this->just_this_case && $this->matchesTestCase($test_case)) {
            $this->on();
        }
        $this->reporter->paintGroupStart($test_case, $size);
    }

    /**
     *    Paints the end of a group test.
     *    @param string $test_case     Name of test or other label.
     *    @access public
     */
    function paintGroupEnd($test_case) {
        $this->reporter->paintGroupEnd($test_case);
        if ($this->just_this_case && $this->matchesTestCase($test_case)) {
            $this->off();
        }
    }
}

/**
 *    Suppresses skip messages.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NoSkipsReporter extends SimpleReporterDecorator {
    
    /**
     *    Does nothing.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) { }
}
 /* .tmp\flat\1\shell_tester.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\1\shell_tester.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/test_case.php');
/**#@-*/

/**
 *    Wrapper for exec() functionality.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleShell {
    private $output;

    /**
     *    Executes the shell comand and stashes the output.
     *    @access public
     */
    function __construct() {
        $this->output = false;
    }

    /**
     *    Actually runs the command. Does not trap the
     *    error stream output as this need PHP 4.3+.
     *    @param string $command    The actual command line
     *                              to run.
     *    @return integer           Exit code.
     *    @access public
     */
    function execute($command) {
        $this->output = false;
        exec($command, $this->output, $ret);
        return $ret;
    }

    /**
     *    Accessor for the last output.
     *    @return string        Output as text.
     *    @access public
     */
    function getOutput() {
        return implode("\n", $this->output);
    }

    /**
     *    Accessor for the last output.
     *    @return array         Output as array of lines.
     *    @access public
     */
    function getOutputAsList() {
        return $this->output;
    }
}

/**
 *    Test case for testing of command line scripts and
 *    utilities. Usually scripts that are external to the
 *    PHP code, but support it in some way.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class ShellTestCase extends SimpleTestCase {
    private $current_shell;
    private $last_status;
    private $last_command;

    /**
     *    Creates an empty test case. Should be subclassed
     *    with test methods for a functional test case.
     *    @param string $label     Name of test case. Will use
     *                             the class name if none specified.
     *    @access public
     */
    function __construct($label = false) {
        parent::__construct($label);
        $this->current_shell = $this->createShell();
        $this->last_status = false;
        $this->last_command = '';
    }

    /**
     *    Executes a command and buffers the results.
     *    @param string $command     Command to run.
     *    @return boolean            True if zero exit code.
     *    @access public
     */
    function execute($command) {
        $shell = $this->getShell();
        $this->last_status = $shell->execute($command);
        $this->last_command = $command;
        return ($this->last_status === 0);
    }

    /**
     *    Dumps the output of the last command.
     *    @access public
     */
    function dumpOutput() {
        $this->dump($this->getOutput());
    }

    /**
     *    Accessor for the last output.
     *    @return string        Output as text.
     *    @access public
     */
    function getOutput() {
        $shell = $this->getShell();
        return $shell->getOutput();
    }

    /**
     *    Accessor for the last output.
     *    @return array         Output as array of lines.
     *    @access public
     */
    function getOutputAsList() {
        $shell = $this->getShell();
        return $shell->getOutputAsList();
    }

    /**
     *    Called from within the test methods to register
     *    passes and failures.
     *    @param boolean $result    Pass on true.
     *    @param string $message    Message to display describing
     *                              the test state.
     *    @return boolean           True on pass
     *    @access public
     */
    function assertTrue($result, $message = false) {
        return $this->assert(new TrueExpectation(), $result, $message);
    }

    /**
     *    Will be true on false and vice versa. False
     *    is the PHP definition of false, so that null,
     *    empty strings, zero and an empty array all count
     *    as false.
     *    @param boolean $result    Pass on false.
     *    @param string $message    Message to display.
     *    @return boolean           True on pass
     *    @access public
     */
    function assertFalse($result, $message = '%s') {
        return $this->assert(new FalseExpectation(), $result, $message);
    }
    
    /**
     *    Will trigger a pass if the two parameters have
     *    the same value only. Otherwise a fail. This
     *    is for testing hand extracted text, etc.
     *    @param mixed $first          Value to compare.
     *    @param mixed $second         Value to compare.
     *    @param string $message       Message to display.
     *    @return boolean              True on pass
     *    @access public
     */
    function assertEqual($first, $second, $message = "%s") {
        return $this->assert(
                new EqualExpectation($first),
                $second,
                $message);
    }
    
    /**
     *    Will trigger a pass if the two parameters have
     *    a different value. Otherwise a fail. This
     *    is for testing hand extracted text, etc.
     *    @param mixed $first           Value to compare.
     *    @param mixed $second          Value to compare.
     *    @param string $message        Message to display.
     *    @return boolean               True on pass
     *    @access public
     */
    function assertNotEqual($first, $second, $message = "%s") {
        return $this->assert(
                new NotEqualExpectation($first),
                $second,
                $message);
    }

    /**
     *    Tests the last status code from the shell.
     *    @param integer $status   Expected status of last
     *                             command.
     *    @param string $message   Message to display.
     *    @return boolean          True if pass.
     *    @access public
     */
    function assertExitCode($status, $message = "%s") {
        $message = sprintf($message, "Expected status code of [$status] from [" .
                $this->last_command . "], but got [" .
                $this->last_status . "]");
        return $this->assertTrue($status === $this->last_status, $message);
    }

    /**
     *    Attempt to exactly match the combined STDERR and
     *    STDOUT output.
     *    @param string $expected  Expected output.
     *    @param string $message   Message to display.
     *    @return boolean          True if pass.
     *    @access public
     */
    function assertOutput($expected, $message = "%s") {
        $shell = $this->getShell();
        return $this->assert(
                new EqualExpectation($expected),
                $shell->getOutput(),
                $message);
    }

    /**
     *    Scans the output for a Perl regex. If found
     *    anywhere it passes, else it fails.
     *    @param string $pattern    Regex to search for.
     *    @param string $message    Message to display.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertOutputPattern($pattern, $message = "%s") {
        $shell = $this->getShell();
        return $this->assert(
                new PatternExpectation($pattern),
                $shell->getOutput(),
                $message);
    }

    /**
     *    If a Perl regex is found anywhere in the current
     *    output then a failure is generated, else a pass.
     *    @param string $pattern    Regex to search for.
     *    @param $message           Message to display.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertNoOutputPattern($pattern, $message = "%s") {
        $shell = $this->getShell();
        return $this->assert(
                new NoPatternExpectation($pattern),
                $shell->getOutput(),
                $message);
    }

    /**
     *    File existence check.
     *    @param string $path      Full filename and path.
     *    @param string $message   Message to display.
     *    @return boolean          True if pass.
     *    @access public
     */
    function assertFileExists($path, $message = "%s") {
        $message = sprintf($message, "File [$path] should exist");
        return $this->assertTrue(file_exists($path), $message);
    }

    /**
     *    File non-existence check.
     *    @param string $path      Full filename and path.
     *    @param string $message   Message to display.
     *    @return boolean          True if pass.
     *    @access public
     */
    function assertFileNotExists($path, $message = "%s") {
        $message = sprintf($message, "File [$path] should not exist");
        return $this->assertFalse(file_exists($path), $message);
    }

    /**
     *    Scans a file for a Perl regex. If found
     *    anywhere it passes, else it fails.
     *    @param string $pattern    Regex to search for.
     *    @param string $path       Full filename and path.
     *    @param string $message    Message to display.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertFilePattern($pattern, $path, $message = "%s") {
        return $this->assert(
                new PatternExpectation($pattern),
                implode('', file($path)),
                $message);
    }

    /**
     *    If a Perl regex is found anywhere in the named
     *    file then a failure is generated, else a pass.
     *    @param string $pattern    Regex to search for.
     *    @param string $path       Full filename and path.
     *    @param string $message    Message to display.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertNoFilePattern($pattern, $path, $message = "%s") {
        return $this->assert(
                new NoPatternExpectation($pattern),
                implode('', file($path)),
                $message);
    }

    /**
     *    Accessor for current shell. Used for testing the
     *    the tester itself.
     *    @return Shell        Current shell.
     *    @access protected
     */
    protected function getShell() {
        return $this->current_shell;
    }

    /**
     *    Factory for the shell to run the command on.
     *    @return Shell        New shell object.
     *    @access protected
     */
    protected function createShell() {
        return new SimpleShell();
    }
}
 /* .tmp\flat\1\unit_tester.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\1\unit_tester.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/test_case.php');
//require_once(dirname(__FILE__) . '/dumper.php');
/**#@-*/

/**
 *    Standard unit test class for day to day testing
 *    of PHP code XP style. Adds some useful standard
 *    assertions.
 *    @package  SimpleTest
 *    @subpackage   UnitTester
 */
class UnitTestCase extends SimpleTestCase {

    /**
     *    Creates an empty test case. Should be subclassed
     *    with test methods for a functional test case.
     *    @param string $label     Name of test case. Will use
     *                             the class name if none specified.
     *    @access public
     */
    function __construct($label = false) {
        if (! $label) {
            $label = get_class($this);
        }
        parent::__construct($label);
    }

    /**
     *    Called from within the test methods to register
     *    passes and failures.
     *    @param boolean $result    Pass on true.
     *    @param string $message    Message to display describing
     *                              the test state.
     *    @return boolean           True on pass
     *    @access public
     */
    function assertTrue($result, $message = '%s') {
        return $this->assert(new TrueExpectation(), $result, $message);
    }

    /**
     *    Will be true on false and vice versa. False
     *    is the PHP definition of false, so that null,
     *    empty strings, zero and an empty array all count
     *    as false.
     *    @param boolean $result    Pass on false.
     *    @param string $message    Message to display.
     *    @return boolean           True on pass
     *    @access public
     */
    function assertFalse($result, $message = '%s') {
        return $this->assert(new FalseExpectation(), $result, $message);
    }

    /**
     *    Will be true if the value is null.
     *    @param null $value       Supposedly null value.
     *    @param string $message   Message to display.
     *    @return boolean                        True on pass
     *    @access public
     */
    function assertNull($value, $message = '%s') {
        $dumper = new SimpleDumper();
        $message = sprintf(
                $message,
                '[' . $dumper->describeValue($value) . '] should be null');
        return $this->assertTrue(! isset($value), $message);
    }

    /**
     *    Will be true if the value is set.
     *    @param mixed $value           Supposedly set value.
     *    @param string $message        Message to display.
     *    @return boolean               True on pass.
     *    @access public
     */
    function assertNotNull($value, $message = '%s') {
        $dumper = new SimpleDumper();
        $message = sprintf(
                $message,
                '[' . $dumper->describeValue($value) . '] should not be null');
        return $this->assertTrue(isset($value), $message);
    }

    /**
     *    Type and class test. Will pass if class
     *    matches the type name or is a subclass or
     *    if not an object, but the type is correct.
     *    @param mixed $object         Object to test.
     *    @param string $type          Type name as string.
     *    @param string $message       Message to display.
     *    @return boolean              True on pass.
     *    @access public
     */
    function assertIsA($object, $type, $message = '%s') {
        return $this->assert(
                new IsAExpectation($type),
                $object,
                $message);
    }

    /**
     *    Type and class mismatch test. Will pass if class
     *    name or underling type does not match the one
     *    specified.
     *    @param mixed $object         Object to test.
     *    @param string $type          Type name as string.
     *    @param string $message       Message to display.
     *    @return boolean              True on pass.
     *    @access public
     */
    function assertNotA($object, $type, $message = '%s') {
        return $this->assert(
                new NotAExpectation($type),
                $object,
                $message);
    }

    /**
     *    Will trigger a pass if the two parameters have
     *    the same value only. Otherwise a fail.
     *    @param mixed $first          Value to compare.
     *    @param mixed $second         Value to compare.
     *    @param string $message       Message to display.
     *    @return boolean              True on pass
     *    @access public
     */
    function assertEqual($first, $second, $message = '%s') {
        return $this->assert(
                new EqualExpectation($first),
                $second,
                $message);
    }

    /**
     *    Will trigger a pass if the two parameters have
     *    a different value. Otherwise a fail.
     *    @param mixed $first           Value to compare.
     *    @param mixed $second          Value to compare.
     *    @param string $message        Message to display.
     *    @return boolean               True on pass
     *    @access public
     */
    function assertNotEqual($first, $second, $message = '%s') {
        return $this->assert(
                new NotEqualExpectation($first),
                $second,
                $message);
    }

    /**
     *    Will trigger a pass if the if the first parameter
     *    is near enough to the second by the margin.
     *    @param mixed $first          Value to compare.
     *    @param mixed $second         Value to compare.
     *    @param mixed $margin         Fuzziness of match.
     *    @param string $message       Message to display.
     *    @return boolean              True on pass
     *    @access public
     */
    function assertWithinMargin($first, $second, $margin, $message = '%s') {
        return $this->assert(
                new WithinMarginExpectation($first, $margin),
                $second,
                $message);
    }

    /**
     *    Will trigger a pass if the two parameters differ
     *    by more than the margin.
     *    @param mixed $first          Value to compare.
     *    @param mixed $second         Value to compare.
     *    @param mixed $margin         Fuzziness of match.
     *    @param string $message       Message to display.
     *    @return boolean              True on pass
     *    @access public
     */
    function assertOutsideMargin($first, $second, $margin, $message = '%s') {
        return $this->assert(
                new OutsideMarginExpectation($first, $margin),
                $second,
                $message);
    }

    /**
     *    Will trigger a pass if the two parameters have
     *    the same value and same type. Otherwise a fail.
     *    @param mixed $first           Value to compare.
     *    @param mixed $second          Value to compare.
     *    @param string $message        Message to display.
     *    @return boolean               True on pass
     *    @access public
     */
    function assertIdentical($first, $second, $message = '%s') {
        return $this->assert(
                new IdenticalExpectation($first),
                $second,
                $message);
    }

    /**
     *    Will trigger a pass if the two parameters have
     *    the different value or different type.
     *    @param mixed $first           Value to compare.
     *    @param mixed $second          Value to compare.
     *    @param string $message        Message to display.
     *    @return boolean               True on pass
     *    @access public
     */
    function assertNotIdentical($first, $second, $message = '%s') {
        return $this->assert(
                new NotIdenticalExpectation($first),
                $second,
                $message);
    }

    /**
     *    Will trigger a pass if both parameters refer
     *    to the same object or value. Fail otherwise.
     *    This will cause problems testing objects under
     *    E_STRICT.
     *    TODO: Replace with expectation.
     *    @param mixed $first           Reference to check.
     *    @param mixed $second          Hopefully the same variable.
     *    @param string $message        Message to display.
     *    @return boolean               True on pass
     *    @access public
     */
    function assertReference(&$first, &$second, $message = '%s') {
        $dumper = new SimpleDumper();
        $message = sprintf(
                $message,
                '[' . $dumper->describeValue($first) .
                        '] and [' . $dumper->describeValue($second) .
                        '] should reference the same object');
        return $this->assertTrue(
                SimpleTestCompatibility::isReference($first, $second),
                $message);
    }

    /**
     *    Will trigger a pass if both parameters refer
     *    to the same object. Fail otherwise. This has
     *    the same semantics at the PHPUnit assertSame.
     *    That is, if values are passed in it has roughly
     *    the same affect as assertIdentical.
     *    TODO: Replace with expectation.
     *    @param mixed $first           Object reference to check.
     *    @param mixed $second          Hopefully the same object.
     *    @param string $message        Message to display.
     *    @return boolean               True on pass
     *    @access public
     */
    function assertSame($first, $second, $message = '%s') {
        $dumper = new SimpleDumper();
        $message = sprintf(
                $message,
                '[' . $dumper->describeValue($first) .
                        '] and [' . $dumper->describeValue($second) .
                        '] should reference the same object');
        return $this->assertTrue($first === $second, $message);
    }

    /**
     *    Will trigger a pass if both parameters refer
     *    to different objects. Fail otherwise. The objects
     *    have to be identical though.
     *    @param mixed $first           Object reference to check.
     *    @param mixed $second          Hopefully not the same object.
     *    @param string $message        Message to display.
     *    @return boolean               True on pass
     *    @access public
     */
    function assertClone($first, $second, $message = '%s') {
        $dumper = new SimpleDumper();
        $message = sprintf(
                $message,
                '[' . $dumper->describeValue($first) .
                        '] and [' . $dumper->describeValue($second) .
                        '] should not be the same object');
        $identical = new IdenticalExpectation($first);
        return $this->assertTrue(
                $identical->test($second) && ! ($first === $second),
                $message);
    }

    /**
     *    Will trigger a pass if both parameters refer
     *    to different variables. Fail otherwise. The objects
     *    have to be identical references though.
     *    This will fail under E_STRICT with objects. Use
     *    assertClone() for this.
     *    @param mixed $first           Object reference to check.
     *    @param mixed $second          Hopefully not the same object.
     *    @param string $message        Message to display.
     *    @return boolean               True on pass
     *    @access public
     */
    function assertCopy(&$first, &$second, $message = "%s") {
        $dumper = new SimpleDumper();
        $message = sprintf(
                $message,
                "[" . $dumper->describeValue($first) .
                        "] and [" . $dumper->describeValue($second) .
                        "] should not be the same object");
        return $this->assertFalse(
                SimpleTestCompatibility::isReference($first, $second),
                $message);
    }

    /**
     *    Will trigger a pass if the Perl regex pattern
     *    is found in the subject. Fail otherwise.
     *    @param string $pattern    Perl regex to look for including
     *                              the regex delimiters.
     *    @param string $subject    String to search in.
     *    @param string $message    Message to display.
     *    @return boolean           True on pass
     *    @access public
     */
    function assertPattern($pattern, $subject, $message = '%s') {
        return $this->assert(
                new PatternExpectation($pattern),
                $subject,
                $message);
    }

    /**
     *    Will trigger a pass if the perl regex pattern
     *    is not present in subject. Fail if found.
     *    @param string $pattern    Perl regex to look for including
     *                              the regex delimiters.
     *    @param string $subject    String to search in.
     *    @param string $message    Message to display.
     *    @return boolean           True on pass
     *    @access public
     */
    function assertNoPattern($pattern, $subject, $message = '%s') {
        return $this->assert(
                new NoPatternExpectation($pattern),
                $subject,
                $message);
    }

    /**
     *    Prepares for an error. If the error mismatches it
     *    passes through, otherwise it is swallowed. Any
     *    left over errors trigger failures.
     *    @param SimpleExpectation/string $expected   The error to match.
     *    @param string $message                      Message on failure.
     *    @access public
     */
    function expectError($expected = false, $message = '%s') {
        $queue = SimpleTest::getContext()->get('SimpleErrorQueue');
        $queue->expectError($this->coerceExpectation($expected), $message);
    }

    /**
     *    Prepares for an exception. If the error mismatches it
     *    passes through, otherwise it is swallowed. Any
     *    left over errors trigger failures.
     *    @param SimpleExpectation/Exception $expected  The error to match.
     *    @param string $message                        Message on failure.
     *    @access public
     */
    function expectException($expected = false, $message = '%s') {
        $queue = SimpleTest::getContext()->get('SimpleExceptionTrap');
        $line = $this->getAssertionLine();
        $queue->expectException($expected, $message . $line);
    }

    /**
     *    Tells SimpleTest to ignore an upcoming exception as not relevant
     *    to the current test. It doesn't affect the test, whether thrown or
     *    not.
     *    @param SimpleExpectation/Exception $ignored  The error to ignore.
     *    @access public
     */
    function ignoreException($ignored = false) {
        SimpleTest::getContext()->get('SimpleExceptionTrap')->ignoreException($ignored);
    }

    /**
     *    Creates an equality expectation if the
     *    object/value is not already some type
     *    of expectation.
     *    @param mixed $expected      Expected value.
     *    @return SimpleExpectation   Expectation object.
     *    @access private
     */
    protected function coerceExpectation($expected) {
        if ($expected == false) {
            return new TrueExpectation();
        }
        if (SimpleTestCompatibility::isA($expected, 'SimpleExpectation')) {
            return $expected;
        }
        return new EqualExpectation(
                is_string($expected) ? str_replace('%', '%%', $expected) : $expected);
    }
}
 /* .tmp\flat\1\web_tester.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\1\web_tester.php */ ?>
<?php
/**
 *  Base include file for SimpleTest.
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/test_case.php');
//require_once(dirname(__FILE__) . '/browser.php');
//require_once(dirname(__FILE__) . '/page.php');
//require_once(dirname(__FILE__) . '/expectation.php');
/**#@-*/

/**
 *    Test for an HTML widget value match.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class FieldExpectation extends SimpleExpectation {
    private $value;

    /**
     *    Sets the field value to compare against.
     *    @param mixed $value     Test value to match. Can be an
     *                            expectation for say pattern matching.
     *    @param string $message  Optiona message override. Can use %s as
     *                            a placeholder for the original message.
     *    @access public
     */
    function __construct($value, $message = '%s') {
        parent::__construct($message);
        if (is_array($value)) {
            sort($value);
        }
        $this->value = $value;
    }

    /**
     *    Tests the expectation. True if it matches
     *    a string value or an array value in any order.
     *    @param mixed $compare        Comparison value. False for
     *                                 an unset field.
     *    @return boolean              True if correct.
     *    @access public
     */
    function test($compare) {
        if ($this->value === false) {
            return ($compare === false);
        }
        if ($this->isSingle($this->value)) {
            return $this->testSingle($compare);
        }
        if (is_array($this->value)) {
            return $this->testMultiple($compare);
        }
        return false;
    }

    /**
     *    Tests for valid field comparisons with a single option.
     *    @param mixed $value       Value to type check.
     *    @return boolean           True if integer, string or float.
     *    @access private
     */
    protected function isSingle($value) {
        return is_string($value) || is_integer($value) || is_float($value);
    }

    /**
     *    String comparison for simple field with a single option.
     *    @param mixed $compare    String to test against.
     *    @returns boolean         True if matching.
     *    @access private
     */
    protected function testSingle($compare) {
        if (is_array($compare) && count($compare) == 1) {
            $compare = $compare[0];
        }
        if (! $this->isSingle($compare)) {
            return false;
        }
        return ($this->value == $compare);
    }

    /**
     *    List comparison for multivalue field.
     *    @param mixed $compare    List in any order to test against.
     *    @returns boolean         True if matching.
     *    @access private
     */
    protected function testMultiple($compare) {
        if (is_string($compare)) {
            $compare = array($compare);
        }
        if (! is_array($compare)) {
            return false;
        }
        sort($compare);
        return ($this->value === $compare);
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        $dumper = $this->getDumper();
        if (is_array($compare)) {
            sort($compare);
        }
        if ($this->test($compare)) {
            return "Field expectation [" . $dumper->describeValue($this->value) . "]";
        } else {
            return "Field expectation [" . $dumper->describeValue($this->value) .
                    "] fails with [" .
                    $dumper->describeValue($compare) . "] " .
                    $dumper->describeDifference($this->value, $compare);
        }
    }
}

/**
 *    Test for a specific HTTP header within a header block.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class HttpHeaderExpectation extends SimpleExpectation {
    private $expected_header;
    private $expected_value;

    /**
     *    Sets the field and value to compare against.
     *    @param string $header   Case insenstive trimmed header name.
     *    @param mixed $value     Optional value to compare. If not
     *                            given then any value will match. If
     *                            an expectation object then that will
     *                            be used instead.
     *    @param string $message  Optiona message override. Can use %s as
     *                            a placeholder for the original message.
     */
    function __construct($header, $value = false, $message = '%s') {
        parent::__construct($message);
        $this->expected_header = $this->normaliseHeader($header);
        $this->expected_value = $value;
    }

    /**
     *    Accessor for aggregated object.
     *    @return mixed        Expectation set in constructor.
     *    @access protected
     */
    protected function getExpectation() {
        return $this->expected_value;
    }

    /**
     *    Removes whitespace at ends and case variations.
     *    @param string $header    Name of header.
     *    @param string            Trimmed and lowecased header
     *                             name.
     *    @access private
     */
    protected function normaliseHeader($header) {
        return strtolower(trim($header));
    }

    /**
     *    Tests the expectation. True if it matches
     *    a string value or an array value in any order.
     *    @param mixed $compare   Raw header block to search.
     *    @return boolean         True if header present.
     *    @access public
     */
    function test($compare) {
        return is_string($this->findHeader($compare));
    }

    /**
     *    Searches the incoming result. Will extract the matching
     *    line as text.
     *    @param mixed $compare   Raw header block to search.
     *    @return string          Matching header line.
     *    @access protected
     */
    protected function findHeader($compare) {
        $lines = explode("\r\n", $compare);
        foreach ($lines as $line) {
            if ($this->testHeaderLine($line)) {
                return $line;
            }
        }
        return false;
    }

    /**
     *    Compares a single header line against the expectation.
     *    @param string $line      A single line to compare.
     *    @return boolean          True if matched.
     *    @access private
     */
    protected function testHeaderLine($line) {
        if (count($parsed = explode(':', $line, 2)) < 2) {
            return false;
        }
        list($header, $value) = $parsed;
        if ($this->normaliseHeader($header) != $this->expected_header) {
            return false;
        }
        return $this->testHeaderValue($value, $this->expected_value);
    }

    /**
     *    Tests the value part of the header.
     *    @param string $value        Value to test.
     *    @param mixed $expected      Value to test against.
     *    @return boolean             True if matched.
     *    @access protected
     */
    protected function testHeaderValue($value, $expected) {
        if ($expected === false) {
            return true;
        }
        if (SimpleExpectation::isExpectation($expected)) {
            return $expected->test(trim($value));
        }
        return (trim($value) == trim($expected));
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Raw header block to search.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        if (SimpleExpectation::isExpectation($this->expected_value)) {
            $message = $this->expected_value->overlayMessage($compare, $this->getDumper());
        } else {
            $message = $this->expected_header .
                    ($this->expected_value ? ': ' . $this->expected_value : '');
        }
        if (is_string($line = $this->findHeader($compare))) {
            return "Searching for header [$message] found [$line]";
        } else {
            return "Failed to find header [$message]";
        }
    }
}

/**
 *    Test for a specific HTTP header within a header block that
 *    should not be found.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class NoHttpHeaderExpectation extends HttpHeaderExpectation {
    private $expected_header;
    private $expected_value;

    /**
     *    Sets the field and value to compare against.
     *    @param string $unwanted   Case insenstive trimmed header name.
     *    @param string $message    Optiona message override. Can use %s as
     *                              a placeholder for the original message.
     */
    function __construct($unwanted, $message = '%s') {
        parent::__construct($unwanted, false, $message);
    }

    /**
     *    Tests that the unwanted header is not found.
     *    @param mixed $compare   Raw header block to search.
     *    @return boolean         True if header present.
     *    @access public
     */
    function test($compare) {
        return ($this->findHeader($compare) === false);
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Raw header block to search.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        $expectation = $this->getExpectation();
        if (is_string($line = $this->findHeader($compare))) {
            return "Found unwanted header [$expectation] with [$line]";
        } else {
            return "Did not find unwanted header [$expectation]";
        }
    }
}

/**
 *    Test for a text substring.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class TextExpectation extends SimpleExpectation {
    private $substring;

    /**
     *    Sets the value to compare against.
     *    @param string $substring  Text to search for.
     *    @param string $message    Customised message on failure.
     *    @access public
     */
    function __construct($substring, $message = '%s') {
        parent::__construct($message);
        $this->substring = $substring;
    }

    /**
     *    Accessor for the substring.
     *    @return string       Text to match.
     *    @access protected
     */
    protected function getSubstring() {
        return $this->substring;
    }

    /**
     *    Tests the expectation. True if the text contains the
     *    substring.
     *    @param string $compare        Comparison value.
     *    @return boolean               True if correct.
     *    @access public
     */
    function test($compare) {
        return (strpos($compare, $this->substring) !== false);
    }

    /**
     *    Returns a human readable test message.
     *    @param mixed $compare      Comparison value.
     *    @return string             Description of success
     *                               or failure.
     *    @access public
     */
    function testMessage($compare) {
        if ($this->test($compare)) {
            return $this->describeTextMatch($this->getSubstring(), $compare);
        } else {
            $dumper = $this->getDumper();
            return "Text [" . $this->getSubstring() .
                    "] not detected in [" .
                    $dumper->describeValue($compare) . "]";
        }
    }

    /**
     *    Describes a pattern match including the string
     *    found and it's position.
     *    @param string $substring      Text to search for.
     *    @param string $subject        Subject to search.
     *    @access protected
     */
    protected function describeTextMatch($substring, $subject) {
        $position = strpos($subject, $substring);
        $dumper = $this->getDumper();
        return "Text [$substring] detected at character [$position] in [" .
                $dumper->describeValue($subject) . "] in region [" .
                $dumper->clipString($subject, 100, $position) . "]";
    }
}

/**
 *    Fail if a substring is detected within the
 *    comparison text.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NoTextExpectation extends TextExpectation {

    /**
     *    Sets the reject pattern
     *    @param string $substring  Text to search for.
     *    @param string $message    Customised message on failure.
     *    @access public
     */
    function __construct($substring, $message = '%s') {
        parent::__construct($substring, $message);
    }

    /**
     *    Tests the expectation. False if the substring appears
     *    in the text.
     *    @param string $compare        Comparison value.
     *    @return boolean               True if correct.
     *    @access public
     */
    function test($compare) {
        return ! parent::test($compare);
    }

    /**
     *    Returns a human readable test message.
     *    @param string $compare      Comparison value.
     *    @return string              Description of success
     *                                or failure.
     *    @access public
     */
    function testMessage($compare) {
        if ($this->test($compare)) {
            $dumper = $this->getDumper();
            return "Text [" . $this->getSubstring() .
                    "] not detected in [" .
                    $dumper->describeValue($compare) . "]";
        } else {
            return $this->describeTextMatch($this->getSubstring(), $compare);
        }
    }
}

/**
 *    Test case for testing of web pages. Allows
 *    fetching of pages, parsing of HTML and
 *    submitting forms.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class WebTestCase extends SimpleTestCase {
    private $browser;
    private $ignore_errors = false;

    /**
     *    Creates an empty test case. Should be subclassed
     *    with test methods for a functional test case.
     *    @param string $label     Name of test case. Will use
     *                             the class name if none specified.
     *    @access public
     */
    function __construct($label = false) {
        parent::__construct($label);
    }

    /**
     *    Announces the start of the test.
     *    @param string $method    Test method just started.
     *    @access public
     */
    function before($method) {
        parent::before($method);
        $this->setBrowser($this->createBrowser());
    }

    /**
     *    Announces the end of the test. Includes private clean up.
     *    @param string $method    Test method just finished.
     *    @access public
     */
    function after($method) {
        $this->unsetBrowser();
        parent::after($method);
    }

    /**
     *    Gets a current browser reference for setting
     *    special expectations or for detailed
     *    examination of page fetches.
     *    @return SimpleBrowser     Current test browser object.
     *    @access public
     */
    function getBrowser() {
        return $this->browser;
    }

    /**
     *    Gets a current browser reference for setting
     *    special expectations or for detailed
     *    examination of page fetches.
     *    @param SimpleBrowser $browser    New test browser object.
     *    @access public
     */
    function setBrowser($browser) {
        return $this->browser = $browser;
    }

    /**
     *    Sets the HTML parser to use within this browser.
     *    @param object         The parser, one of SimplePHPPageBuilder or
     *                          SimpleTidyPageBuilder.
     */
    function setParser($parser) {
        $this->browser->setParser($parser);
    }

    /**
     *    Clears the current browser reference to help the
     *    PHP garbage collector.
     *    @access public
     */
    function unsetBrowser() {
        unset($this->browser);
    }

    /**
     *    Creates a new default web browser object.
     *    Will be cleared at the end of the test method.
     *    @return TestBrowser           New browser.
     *    @access public
     */
    function createBrowser() {
        return new SimpleBrowser();
    }

    /**
     *    Gets the last response error.
     *    @return string    Last low level HTTP error.
     *    @access public
     */
    function getTransportError() {
        return $this->browser->getTransportError();
    }

    /**
     *    Accessor for the currently selected URL.
     *    @return string        Current location or false if
     *                          no page yet fetched.
     *    @access public
     */
    function getUrl() {
        return $this->browser->getUrl();
    }

    /**
     *    Dumps the current request for debugging.
     *    @access public
     */
    function showRequest() {
        $this->dump($this->browser->getRequest());
    }

    /**
     *    Dumps the current HTTP headers for debugging.
     *    @access public
     */
    function showHeaders() {
        $this->dump($this->browser->getHeaders());
    }

    /**
     *    Dumps the current HTML source for debugging.
     *    @access public
     */
    function showSource() {
        $this->dump($this->browser->getContent());
    }

    /**
     *    Dumps the visible text only for debugging.
     *    @access public
     */
    function showText() {
        $this->dump(wordwrap($this->browser->getContentAsText(), 80));
    }

    /**
     *    Simulates the closing and reopening of the browser.
     *    Temporary cookies will be discarded and timed
     *    cookies will be expired if later than the
     *    specified time.
     *    @param string/integer $date Time when session restarted.
     *                                If ommitted then all persistent
     *                                cookies are kept. Time is either
     *                                Cookie format string or timestamp.
     *    @access public
     */
    function restart($date = false) {
        if ($date === false) {
            $date = time();
        }
        $this->browser->restart($date);
    }

    /**
     *    Moves cookie expiry times back into the past.
     *    Useful for testing timeouts and expiries.
     *    @param integer $interval    Amount to age in seconds.
     *    @access public
     */
    function ageCookies($interval) {
        $this->browser->ageCookies($interval);
    }

    /**
     *    Disables frames support. Frames will not be fetched
     *    and the frameset page will be used instead.
     *    @access public
     */
    function ignoreFrames() {
        $this->browser->ignoreFrames();
    }

    /**
     *    Switches off cookie sending and recieving.
     *    @access public
     */
    function ignoreCookies() {
        $this->browser->ignoreCookies();
    }

    /**
     *    Skips errors for the next request only. You might
     *    want to confirm that a page is unreachable for
     *    example.
     *    @access public
     */
    function ignoreErrors() {
        $this->ignore_errors = true;
    }

    /**
     *    Issues a fail if there is a transport error anywhere
     *    in the current frameset. Only one such error is
     *    reported.
     *    @param string/boolean $result   HTML or failure.
     *    @return string/boolean $result  Passes through result.
     *    @access private
     */
    protected function failOnError($result) {
        if (! $this->ignore_errors) {
            if ($error = $this->browser->getTransportError()) {
                $this->fail($error);
            }
        }
        $this->ignore_errors = false;
        return $result;
    }

    /**
     *    Adds a header to every fetch.
     *    @param string $header       Header line to add to every
     *                                request until cleared.
     *    @access public
     */
    function addHeader($header) {
        $this->browser->addHeader($header);
    }

    /**
     *    Sets the maximum number of redirects before
     *    the web page is loaded regardless.
     *    @param integer $max        Maximum hops.
     *    @access public
     */
    function setMaximumRedirects($max) {
        if (! $this->browser) {
            trigger_error(
                    'Can only set maximum redirects in a test method, setUp() or tearDown()');
        }
        $this->browser->setMaximumRedirects($max);
    }

    /**
     *    Sets the socket timeout for opening a connection and
     *    receiving at least one byte of information.
     *    @param integer $timeout      Maximum time in seconds.
     *    @access public
     */
    function setConnectionTimeout($timeout) {
        $this->browser->setConnectionTimeout($timeout);
    }

    /**
     *    Sets proxy to use on all requests for when
     *    testing from behind a firewall. Set URL
     *    to false to disable.
     *    @param string $proxy        Proxy URL.
     *    @param string $username     Proxy username for authentication.
     *    @param string $password     Proxy password for authentication.
     *    @access public
     */
    function useProxy($proxy, $username = false, $password = false) {
        $this->browser->useProxy($proxy, $username, $password);
    }

    /**
     *    Fetches a page into the page buffer. If
     *    there is no base for the URL then the
     *    current base URL is used. After the fetch
     *    the base URL reflects the new location.
     *    @param string $url          URL to fetch.
     *    @param hash $parameters     Optional additional GET data.
     *    @return boolean/string      Raw page on success.
     *    @access public
     */
    function get($url, $parameters = false) {
        return $this->failOnError($this->browser->get($url, $parameters));
    }

    /**
     *    Fetches a page by POST into the page buffer.
     *    If there is no base for the URL then the
     *    current base URL is used. After the fetch
     *    the base URL reflects the new location.
     *    @param string $url          URL to fetch.
     *    @param mixed $parameters    Optional POST parameters or content body to send
     *    @param string $content_type Content type of provided body
     *    @return boolean/string      Raw page on success.
     *    @access public
     */
    function post($url, $parameters = false, $content_type = false) {
        return $this->failOnError($this->browser->post($url, $parameters, $content_type));
    }

    /**
     *    Fetches a page by PUT into the page buffer.
     *    If there is no base for the URL then the
     *    current base URL is used. After the fetch
     *    the base URL reflects the new location.
     *    @param string $url          URL to fetch.
     *    @param mixed $body          Optional content body to send
     *    @param string $content_type Content type of provided body
     *    @return boolean/string      Raw page on success.
     *    @access public
     */
    function put($url, $body = false, $content_type = false) {
        return $this->failOnError($this->browser->put($url, $body, $content_type));
    }
    
    /**
     *    Fetches a page by a DELETE request
     *    @param string $url          URL to fetch.
     *    @param hash $parameters     Optional additional parameters.
     *    @return boolean/string      Raw page on success.
     *    @access public
     */
    function delete($url, $parameters = false) {
        return $this->failOnError($this->browser->delete($url, $parameters));
    }
    
    
    /**
     *    Does a HTTP HEAD fetch, fetching only the page
     *    headers. The current base URL is unchanged by this.
     *    @param string $url          URL to fetch.
     *    @param hash $parameters     Optional additional GET data.
     *    @return boolean             True on success.
     *    @access public
     */
    function head($url, $parameters = false) {
        return $this->failOnError($this->browser->head($url, $parameters));
    }

    /**
     *    Equivalent to hitting the retry button on the
     *    browser. Will attempt to repeat the page fetch.
     *    @return boolean     True if fetch succeeded.
     *    @access public
     */
    function retry() {
        return $this->failOnError($this->browser->retry());
    }

    /**
     *    Equivalent to hitting the back button on the
     *    browser.
     *    @return boolean     True if history entry and
     *                        fetch succeeded.
     *    @access public
     */
    function back() {
        return $this->failOnError($this->browser->back());
    }

    /**
     *    Equivalent to hitting the forward button on the
     *    browser.
     *    @return boolean     True if history entry and
     *                        fetch succeeded.
     *    @access public
     */
    function forward() {
        return $this->failOnError($this->browser->forward());
    }

    /**
     *    Retries a request after setting the authentication
     *    for the current realm.
     *    @param string $username    Username for realm.
     *    @param string $password    Password for realm.
     *    @return boolean/string     HTML on successful fetch. Note
     *                               that authentication may still have
     *                               failed.
     *    @access public
     */
    function authenticate($username, $password) {
        return $this->failOnError(
                $this->browser->authenticate($username, $password));
    }

    /**
     *    Gets the cookie value for the current browser context.
     *    @param string $name          Name of cookie.
     *    @return string               Value of cookie or false if unset.
     *    @access public
     */
    function getCookie($name) {
        return $this->browser->getCurrentCookieValue($name);
    }

    /**
     *    Sets a cookie in the current browser.
     *    @param string $name          Name of cookie.
     *    @param string $value         Cookie value.
     *    @param string $host          Host upon which the cookie is valid.
     *    @param string $path          Cookie path if not host wide.
     *    @param string $expiry        Expiry date.
     *    @access public
     */
    function setCookie($name, $value, $host = false, $path = '/', $expiry = false) {
        $this->browser->setCookie($name, $value, $host, $path, $expiry);
    }

    /**
     *    Accessor for current frame focus. Will be
     *    false if no frame has focus.
     *    @return integer/string/boolean    Label if any, otherwise
     *                                      the position in the frameset
     *                                      or false if none.
     *    @access public
     */
    function getFrameFocus() {
        return $this->browser->getFrameFocus();
    }

    /**
     *    Sets the focus by index. The integer index starts from 1.
     *    @param integer $choice    Chosen frame.
     *    @return boolean           True if frame exists.
     *    @access public
     */
    function setFrameFocusByIndex($choice) {
        return $this->browser->setFrameFocusByIndex($choice);
    }

    /**
     *    Sets the focus by name.
     *    @param string $name    Chosen frame.
     *    @return boolean        True if frame exists.
     *    @access public
     */
    function setFrameFocus($name) {
        return $this->browser->setFrameFocus($name);
    }

    /**
     *    Clears the frame focus. All frames will be searched
     *    for content.
     *    @access public
     */
    function clearFrameFocus() {
        return $this->browser->clearFrameFocus();
    }

    /**
     *    Clicks a visible text item. Will first try buttons,
     *    then links and then images.
     *    @param string $label        Visible text or alt text.
     *    @return string/boolean      Raw page or false.
     *    @access public
     */
    function click($label) {
        return $this->failOnError($this->browser->click($label));
    }

    /**
     *    Checks for a click target.
     *    @param string $label        Visible text or alt text.
     *    @return boolean             True if click target.
     *    @access public
     */
    function assertClickable($label, $message = '%s') {
        return $this->assertTrue(
                $this->browser->isClickable($label),
                sprintf($message, "Click target [$label] should exist"));
    }

    /**
     *    Clicks the submit button by label. The owning
     *    form will be submitted by this.
     *    @param string $label    Button label. An unlabeled
     *                            button can be triggered by 'Submit'.
     *    @param hash $additional Additional form values.
     *    @return boolean/string  Page on success, else false.
     *    @access public
     */
    function clickSubmit($label = 'Submit', $additional = false) {
        return $this->failOnError(
                $this->browser->clickSubmit($label, $additional));
    }

    /**
     *    Clicks the submit button by name attribute. The owning
     *    form will be submitted by this.
     *    @param string $name     Name attribute of button.
     *    @param hash $additional Additional form values.
     *    @return boolean/string  Page on success.
     *    @access public
     */
    function clickSubmitByName($name, $additional = false) {
        return $this->failOnError(
                $this->browser->clickSubmitByName($name, $additional));
    }

    /**
     *    Clicks the submit button by ID attribute. The owning
     *    form will be submitted by this.
     *    @param string $id       ID attribute of button.
     *    @param hash $additional Additional form values.
     *    @return boolean/string  Page on success.
     *    @access public
     */
    function clickSubmitById($id, $additional = false) {
        return $this->failOnError(
                $this->browser->clickSubmitById($id, $additional));
    }

    /**
     *    Checks for a valid button label.
     *    @param string $label        Visible text.
     *    @return boolean             True if click target.
     *    @access public
     */
    function assertSubmit($label, $message = '%s') {
        return $this->assertTrue(
                $this->browser->isSubmit($label),
                sprintf($message, "Submit button [$label] should exist"));
    }

    /**
     *    Clicks the submit image by some kind of label. Usually
     *    the alt tag or the nearest equivalent. The owning
     *    form will be submitted by this. Clicking outside of
     *    the boundary of the coordinates will result in
     *    a failure.
     *    @param string $label    Alt attribute of button.
     *    @param integer $x       X-coordinate of imaginary click.
     *    @param integer $y       Y-coordinate of imaginary click.
     *    @param hash $additional Additional form values.
     *    @return boolean/string  Page on success.
     *    @access public
     */
    function clickImage($label, $x = 1, $y = 1, $additional = false) {
        return $this->failOnError(
                $this->browser->clickImage($label, $x, $y, $additional));
    }

    /**
     *    Clicks the submit image by the name. Usually
     *    the alt tag or the nearest equivalent. The owning
     *    form will be submitted by this. Clicking outside of
     *    the boundary of the coordinates will result in
     *    a failure.
     *    @param string $name     Name attribute of button.
     *    @param integer $x       X-coordinate of imaginary click.
     *    @param integer $y       Y-coordinate of imaginary click.
     *    @param hash $additional Additional form values.
     *    @return boolean/string  Page on success.
     *    @access public
     */
    function clickImageByName($name, $x = 1, $y = 1, $additional = false) {
        return $this->failOnError(
                $this->browser->clickImageByName($name, $x, $y, $additional));
    }

    /**
     *    Clicks the submit image by ID attribute. The owning
     *    form will be submitted by this. Clicking outside of
     *    the boundary of the coordinates will result in
     *    a failure.
     *    @param integer/string $id   ID attribute of button.
     *    @param integer $x           X-coordinate of imaginary click.
     *    @param integer $y           Y-coordinate of imaginary click.
     *    @param hash $additional     Additional form values.
     *    @return boolean/string      Page on success.
     *    @access public
     */
    function clickImageById($id, $x = 1, $y = 1, $additional = false) {
        return $this->failOnError(
                $this->browser->clickImageById($id, $x, $y, $additional));
    }

    /**
     *    Checks for a valid image with atht alt text or title.
     *    @param string $label        Visible text.
     *    @return boolean             True if click target.
     *    @access public
     */
    function assertImage($label, $message = '%s') {
        return $this->assertTrue(
                $this->browser->isImage($label),
                sprintf($message, "Image with text [$label] should exist"));
    }

    /**
     *    Submits a form by the ID.
     *    @param string $id       Form ID. No button information
     *                            is submitted this way.
     *    @return boolean/string  Page on success.
     *    @access public
     */
    function submitFormById($id) {
        return $this->failOnError($this->browser->submitFormById($id));
    }

    /**
     *    Follows a link by name. Will click the first link
     *    found with this link text by default, or a later
     *    one if an index is given. Match is case insensitive
     *    with normalised space.
     *    @param string $label     Text between the anchor tags.
     *    @param integer $index    Link position counting from zero.
     *    @return boolean/string   Page on success.
     *    @access public
     */
    function clickLink($label, $index = 0) {
        return $this->failOnError($this->browser->clickLink($label, $index));
    }

    /**
     *    Follows a link by id attribute.
     *    @param string $id        ID attribute value.
     *    @return boolean/string   Page on success.
     *    @access public
     */
    function clickLinkById($id) {
        return $this->failOnError($this->browser->clickLinkById($id));
    }

    /**
     *    Tests for the presence of a link label. Match is
     *    case insensitive with normalised space.
     *    @param string $label     Text between the anchor tags.
     *    @param mixed $expected   Expected URL or expectation object.
     *    @param string $message   Message to display. Default
     *                             can be embedded with %s.
     *    @return boolean          True if link present.
     *    @access public
     */
    function assertLink($label, $expected = true, $message = '%s') {
        $url = $this->browser->getLink($label);
        if ($expected === true || ($expected !== true && $url === false)) {
            return $this->assertTrue($url !== false, sprintf($message, "Link [$label] should exist"));
        }
        if (! SimpleExpectation::isExpectation($expected)) {
            $expected = new IdenticalExpectation($expected);
        }
        return $this->assert($expected, $url->asString(), sprintf($message, "Link [$label] should match"));
    }

    /**
     *    Tests for the non-presence of a link label. Match is
     *    case insensitive with normalised space.
     *    @param string/integer $label    Text between the anchor tags
     *                                    or ID attribute.
     *    @param string $message          Message to display. Default
     *                                    can be embedded with %s.
     *    @return boolean                 True if link missing.
     *    @access public
     */
    function assertNoLink($label, $message = '%s') {
        return $this->assertTrue(
                $this->browser->getLink($label) === false,
                sprintf($message, "Link [$label] should not exist"));
    }

    /**
     *    Tests for the presence of a link id attribute.
     *    @param string $id        Id attribute value.
     *    @param mixed $expected   Expected URL or expectation object.
     *    @param string $message   Message to display. Default
     *                             can be embedded with %s.
     *    @return boolean          True if link present.
     *    @access public
     */
    function assertLinkById($id, $expected = true, $message = '%s') {
        $url = $this->browser->getLinkById($id);
        if ($expected === true) {
            return $this->assertTrue($url !== false, sprintf($message, "Link ID [$id] should exist"));
        }
        if (! SimpleExpectation::isExpectation($expected)) {
            $expected = new IdenticalExpectation($expected);
        }
        return $this->assert($expected, $url->asString(), sprintf($message, "Link ID [$id] should match"));
    }

    /**
     *    Tests for the non-presence of a link label. Match is
     *    case insensitive with normalised space.
     *    @param string $id        Id attribute value.
     *    @param string $message   Message to display. Default
     *                             can be embedded with %s.
     *    @return boolean          True if link missing.
     *    @access public
     */
    function assertNoLinkById($id, $message = '%s') {
        return $this->assertTrue(
                $this->browser->getLinkById($id) === false,
                sprintf($message, "Link ID [$id] should not exist"));
    }

    /**
     *    Sets all form fields with that label, or name if there
     *    is no label attached.
     *    @param string $name    Name of field in forms.
     *    @param string $value   New value of field.
     *    @return boolean        True if field exists, otherwise false.
     *    @access public
     */
    function setField($label, $value, $position=false) {
        return $this->browser->setField($label, $value, $position);
    }

    /**
     *    Sets all form fields with that name.
     *    @param string $name    Name of field in forms.
     *    @param string $value   New value of field.
     *    @return boolean        True if field exists, otherwise false.
     *    @access public
     */
    function setFieldByName($name, $value, $position=false) {
        return $this->browser->setFieldByName($name, $value, $position);
    }

    /**
     *    Sets all form fields with that id.
     *    @param string/integer $id   Id of field in forms.
     *    @param string $value        New value of field.
     *    @return boolean             True if field exists, otherwise false.
     *    @access public
     */
    function setFieldById($id, $value) {
        return $this->browser->setFieldById($id, $value);
    }

    /**
     *    Confirms that the form element is currently set
     *    to the expected value. A missing form will always
     *    fail. If no value is given then only the existence
     *    of the field is checked.
     *    @param string $name       Name of field in forms.
     *    @param mixed $expected    Expected string/array value or
     *                              false for unset fields.
     *    @param string $message    Message to display. Default
     *                              can be embedded with %s.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertField($label, $expected = true, $message = '%s') {
        $value = $this->browser->getField($label);
        return $this->assertFieldValue($label, $value, $expected, $message);
    }

    /**
     *    Confirms that the form element is currently set
     *    to the expected value. A missing form element will always
     *    fail. If no value is given then only the existence
     *    of the field is checked.
     *    @param string $name       Name of field in forms.
     *    @param mixed $expected    Expected string/array value or
     *                              false for unset fields.
     *    @param string $message    Message to display. Default
     *                              can be embedded with %s.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertFieldByName($name, $expected = true, $message = '%s') {
        $value = $this->browser->getFieldByName($name);
        return $this->assertFieldValue($name, $value, $expected, $message);
    }

    /**
     *    Confirms that the form element is currently set
     *    to the expected value. A missing form will always
     *    fail. If no ID is given then only the existence
     *    of the field is checked.
     *    @param string/integer $id  Name of field in forms.
     *    @param mixed $expected     Expected string/array value or
     *                               false for unset fields.
     *    @param string $message     Message to display. Default
     *                               can be embedded with %s.
     *    @return boolean            True if pass.
     *    @access public
     */
    function assertFieldById($id, $expected = true, $message = '%s') {
        $value = $this->browser->getFieldById($id);
        return $this->assertFieldValue($id, $value, $expected, $message);
    }

    /**
     *    Tests the field value against the expectation.
     *    @param string $identifier      Name, ID or label.
     *    @param mixed $value            Current field value.
     *    @param mixed $expected         Expected value to match.
     *    @param string $message         Failure message.
     *    @return boolean                True if pass
     *    @access protected
     */
    protected function assertFieldValue($identifier, $value, $expected, $message) {
        if ($expected === true) {
            return $this->assertTrue(
                    isset($value),
                    sprintf($message, "Field [$identifier] should exist"));
        }
        if (! SimpleExpectation::isExpectation($expected)) {
            $identifier = str_replace('%', '%%', $identifier);
            $expected = new FieldExpectation(
                    $expected,
                    "Field [$identifier] should match with [%s]");
        }
        return $this->assert($expected, $value, $message);
    }

    /**
     *    Checks the response code against a list
     *    of possible values.
     *    @param array $responses    Possible responses for a pass.
     *    @param string $message     Message to display. Default
     *                               can be embedded with %s.
     *    @return boolean            True if pass.
     *    @access public
     */
    function assertResponse($responses, $message = '%s') {
        $responses = (is_array($responses) ? $responses : array($responses));
        $code = $this->browser->getResponseCode();
        $message = sprintf($message, "Expecting response in [" .
                implode(", ", $responses) . "] got [$code]");
        return $this->assertTrue(in_array($code, $responses), $message);
    }

    /**
     *    Checks the mime type against a list
     *    of possible values.
     *    @param array $types      Possible mime types for a pass.
     *    @param string $message   Message to display.
     *    @return boolean          True if pass.
     *    @access public
     */
    function assertMime($types, $message = '%s') {
        $types = (is_array($types) ? $types : array($types));
        $type = $this->browser->getMimeType();
        $message = sprintf($message, "Expecting mime type in [" .
                implode(", ", $types) . "] got [$type]");
        return $this->assertTrue(in_array($type, $types), $message);
    }

    /**
     *    Attempt to match the authentication type within
     *    the security realm we are currently matching.
     *    @param string $authentication   Usually basic.
     *    @param string $message          Message to display.
     *    @return boolean                 True if pass.
     *    @access public
     */
    function assertAuthentication($authentication = false, $message = '%s') {
        if (! $authentication) {
            $message = sprintf($message, "Expected any authentication type, got [" .
                    $this->browser->getAuthentication() . "]");
            return $this->assertTrue(
                    $this->browser->getAuthentication(),
                    $message);
        } else {
            $message = sprintf($message, "Expected authentication [$authentication] got [" .
                    $this->browser->getAuthentication() . "]");
            return $this->assertTrue(
                    strtolower($this->browser->getAuthentication()) == strtolower($authentication),
                    $message);
        }
    }

    /**
     *    Checks that no authentication is necessary to view
     *    the desired page.
     *    @param string $message     Message to display.
     *    @return boolean            True if pass.
     *    @access public
     */
    function assertNoAuthentication($message = '%s') {
        $message = sprintf($message, "Expected no authentication type, got [" .
                $this->browser->getAuthentication() . "]");
        return $this->assertFalse($this->browser->getAuthentication(), $message);
    }

    /**
     *    Attempts to match the current security realm.
     *    @param string $realm     Name of security realm.
     *    @param string $message   Message to display.
     *    @return boolean          True if pass.
     *    @access public
     */
    function assertRealm($realm, $message = '%s') {
        if (! SimpleExpectation::isExpectation($realm)) {
            $realm = new EqualExpectation($realm);
        }
        return $this->assert(
                $realm,
                $this->browser->getRealm(),
                "Expected realm -> $message");
    }

    /**
     *    Checks each header line for the required value. If no
     *    value is given then only an existence check is made.
     *    @param string $header    Case insensitive header name.
     *    @param mixed $value      Case sensitive trimmed string to
     *                             match against. An expectation object
     *                             can be used for pattern matching.
     *    @return boolean          True if pass.
     *    @access public
     */
    function assertHeader($header, $value = false, $message = '%s') {
        return $this->assert(
                new HttpHeaderExpectation($header, $value),
                $this->browser->getHeaders(),
                $message);
    }

    /**
     *    Confirms that the header type has not been received.
     *    Only the landing page is checked. If you want to check
     *    redirect pages, then you should limit redirects so
     *    as to capture the page you want.
     *    @param string $header    Case insensitive header name.
     *    @return boolean          True if pass.
     *    @access public
     */
    function assertNoHeader($header, $message = '%s') {
        return $this->assert(
                new NoHttpHeaderExpectation($header),
                $this->browser->getHeaders(),
                $message);
    }

    /**
     *    Tests the text between the title tags.
     *    @param string/SimpleExpectation $title    Expected title.
     *    @param string $message                    Message to display.
     *    @return boolean                           True if pass.
     *    @access public
     */
    function assertTitle($title = false, $message = '%s') {
        if (! SimpleExpectation::isExpectation($title)) {
            $title = new EqualExpectation($title);
        }
        return $this->assert($title, $this->browser->getTitle(), $message);
    }

    /**
     *    Will trigger a pass if the text is found in the plain
     *    text form of the page.
     *    @param string $text       Text to look for.
     *    @param string $message    Message to display.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertText($text, $message = '%s') {
        return $this->assert(
                new TextExpectation($text),
                $this->browser->getContentAsText(),
                $message);
    }

    /**
     *    Will trigger a pass if the text is not found in the plain
     *    text form of the page.
     *    @param string $text       Text to look for.
     *    @param string $message    Message to display.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertNoText($text, $message = '%s') {
        return $this->assert(
                new NoTextExpectation($text),
                $this->browser->getContentAsText(),
                $message);
    }

    /**
     *    Will trigger a pass if the Perl regex pattern
     *    is found in the raw content.
     *    @param string $pattern    Perl regex to look for including
     *                              the regex delimiters.
     *    @param string $message    Message to display.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertPattern($pattern, $message = '%s') {
        return $this->assert(
                new PatternExpectation($pattern),
                $this->browser->getContent(),
                $message);
    }

    /**
     *    Will trigger a pass if the perl regex pattern
     *    is not present in raw content.
     *    @param string $pattern    Perl regex to look for including
     *                              the regex delimiters.
     *    @param string $message    Message to display.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertNoPattern($pattern, $message = '%s') {
        return $this->assert(
                new NoPatternExpectation($pattern),
                $this->browser->getContent(),
                $message);
    }

    /**
     *    Checks that a cookie is set for the current page
     *    and optionally checks the value.
     *    @param string $name        Name of cookie to test.
     *    @param string $expected    Expected value as a string or
     *                               false if any value will do.
     *    @param string $message     Message to display.
     *    @return boolean            True if pass.
     *    @access public
     */
    function assertCookie($name, $expected = false, $message = '%s') {
        $value = $this->getCookie($name);
        if (! $expected) {
            return $this->assertTrue(
                    $value,
                    sprintf($message, "Expecting cookie [$name]"));
        }
        if (! SimpleExpectation::isExpectation($expected)) {
            $expected = new EqualExpectation($expected);
        }
        return $this->assert($expected, $value, "Expecting cookie [$name] -> $message");
    }

    /**
     *    Checks that no cookie is present or that it has
     *    been successfully cleared.
     *    @param string $name        Name of cookie to test.
     *    @param string $message     Message to display.
     *    @return boolean            True if pass.
     *    @access public
     */
    function assertNoCookie($name, $message = '%s') {
        return $this->assertTrue(
                $this->getCookie($name) === null or $this->getCookie($name) === false,
                sprintf($message, "Not expecting cookie [$name]"));
    }

    /**
     *    Called from within the test methods to register
     *    passes and failures.
     *    @param boolean $result    Pass on true.
     *    @param string $message    Message to display describing
     *                              the test state.
     *    @return boolean           True on pass
     *    @access public
     */
    function assertTrue($result, $message = false) {
        return $this->assert(new TrueExpectation(), $result, $message);
    }

    /**
     *    Will be true on false and vice versa. False
     *    is the PHP definition of false, so that null,
     *    empty strings, zero and an empty array all count
     *    as false.
     *    @param boolean $result    Pass on false.
     *    @param string $message    Message to display.
     *    @return boolean           True on pass
     *    @access public
     */
    function assertFalse($result, $message = '%s') {
        return $this->assert(new FalseExpectation(), $result, $message);
    }

    /**
     *    Will trigger a pass if the two parameters have
     *    the same value only. Otherwise a fail. This
     *    is for testing hand extracted text, etc.
     *    @param mixed $first          Value to compare.
     *    @param mixed $second         Value to compare.
     *    @param string $message       Message to display.
     *    @return boolean              True on pass
     *    @access public
     */
    function assertEqual($first, $second, $message = '%s') {
        return $this->assert(
                new EqualExpectation($first),
                $second,
                $message);
    }

    /**
     *    Will trigger a pass if the two parameters have
     *    a different value. Otherwise a fail. This
     *    is for testing hand extracted text, etc.
     *    @param mixed $first           Value to compare.
     *    @param mixed $second          Value to compare.
     *    @param string $message        Message to display.
     *    @return boolean               True on pass
     *    @access public
     */
    function assertNotEqual($first, $second, $message = '%s') {
        return $this->assert(
                new NotEqualExpectation($first),
                $second,
                $message);
    }

    /**
     *    Uses a stack trace to find the line of an assertion.
     *    @return string           Line number of first assert*
     *                             method embedded in format string.
     *    @access public
     */
    function getAssertionLine() {
        $trace = new SimpleStackTrace(array('assert', 'click', 'pass', 'fail'));
        return $trace->traceMethod();
    }
}
 /* .tmp\flat\1\xml.php */ ?>
<?php /* zenmagick/plugins/general/unitTests/simpletest-1.1b.packed.php.prep\\1\xml.php */ ?>
<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
//require_once(dirname(__FILE__) . '/scorer.php');
/**#@-*/

/**
 *    Creates the XML needed for remote communication
 *    by SimpleTest.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class XmlReporter extends SimpleReporter {
    private $indent;
    private $namespace;

    /**
     *    Sets up indentation and namespace.
     *    @param string $namespace        Namespace to add to each tag.
     *    @param string $indent           Indenting to add on each nesting.
     *    @access public
     */
    function __construct($namespace = false, $indent = '  ') {
        parent::__construct();
        $this->namespace = ($namespace ? $namespace . ':' : '');
        $this->indent = $indent;
    }

    /**
     *    Calculates the pretty printing indent level
     *    from the current level of nesting.
     *    @param integer $offset  Extra indenting level.
     *    @return string          Leading space.
     *    @access protected
     */
    protected function getIndent($offset = 0) {
        return str_repeat(
                $this->indent,
                count($this->getTestList()) + $offset);
    }

    /**
     *    Converts character string to parsed XML
     *    entities string.
     *    @param string text        Unparsed character data.
     *    @return string            Parsed character data.
     *    @access public
     */
    function toParsedXml($text) {
        return str_replace(
                array('&', '<', '>', '"', '\''),
                array('&amp;', '&lt;', '&gt;', '&quot;', '&apos;'),
                $text);
    }

    /**
     *    Paints the start of a group test.
     *    @param string $test_name   Name of test that is starting.
     *    @param integer $size       Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_name, $size) {
        parent::paintGroupStart($test_name, $size);
        print $this->getIndent();
        print "<" . $this->namespace . "group size=\"$size\">\n";
        print $this->getIndent(1);
        print "<" . $this->namespace . "name>" .
                $this->toParsedXml($test_name) .
                "</" . $this->namespace . "name>\n";
    }

    /**
     *    Paints the end of a group test.
     *    @param string $test_name   Name of test that is ending.
     *    @access public
     */
    function paintGroupEnd($test_name) {
        print $this->getIndent();
        print "</" . $this->namespace . "group>\n";
        parent::paintGroupEnd($test_name);
    }

    /**
     *    Paints the start of a test case.
     *    @param string $test_name   Name of test that is starting.
     *    @access public
     */
    function paintCaseStart($test_name) {
        parent::paintCaseStart($test_name);
        print $this->getIndent();
        print "<" . $this->namespace . "case>\n";
        print $this->getIndent(1);
        print "<" . $this->namespace . "name>" .
                $this->toParsedXml($test_name) .
                "</" . $this->namespace . "name>\n";
    }

    /**
     *    Paints the end of a test case.
     *    @param string $test_name   Name of test that is ending.
     *    @access public
     */
    function paintCaseEnd($test_name) {
        print $this->getIndent();
        print "</" . $this->namespace . "case>\n";
        parent::paintCaseEnd($test_name);
    }

    /**
     *    Paints the start of a test method.
     *    @param string $test_name   Name of test that is starting.
     *    @access public
     */
    function paintMethodStart($test_name) {
        parent::paintMethodStart($test_name);
        print $this->getIndent();
        print "<" . $this->namespace . "test>\n";
        print $this->getIndent(1);
        print "<" . $this->namespace . "name>" .
                $this->toParsedXml($test_name) .
                "</" . $this->namespace . "name>\n";
    }

    /**
     *    Paints the end of a test method.
     *    @param string $test_name   Name of test that is ending.
     *    @param integer $progress   Number of test cases ending.
     *    @access public
     */
    function paintMethodEnd($test_name) {
        print $this->getIndent();
        print "</" . $this->namespace . "test>\n";
        parent::paintMethodEnd($test_name);
    }

    /**
     *    Paints pass as XML.
     *    @param string $message        Message to encode.
     *    @access public
     */
    function paintPass($message) {
        parent::paintPass($message);
        print $this->getIndent(1);
        print "<" . $this->namespace . "pass>";
        print $this->toParsedXml($message);
        print "</" . $this->namespace . "pass>\n";
    }

    /**
     *    Paints failure as XML.
     *    @param string $message        Message to encode.
     *    @access public
     */
    function paintFail($message) {
        parent::paintFail($message);
        print $this->getIndent(1);
        print "<" . $this->namespace . "fail>";
        print $this->toParsedXml($message);
        print "</" . $this->namespace . "fail>\n";
    }

    /**
     *    Paints error as XML.
     *    @param string $message        Message to encode.
     *    @access public
     */
    function paintError($message) {
        parent::paintError($message);
        print $this->getIndent(1);
        print "<" . $this->namespace . "exception>";
        print $this->toParsedXml($message);
        print "</" . $this->namespace . "exception>\n";
    }

    /**
     *    Paints exception as XML.
     *    @param Exception $exception    Exception to encode.
     *    @access public
     */
    function paintException($exception) {
        parent::paintException($exception);
        print $this->getIndent(1);
        print "<" . $this->namespace . "exception>";
        $message = 'Unexpected exception of type [' . get_class($exception) .
                '] with message ['. $exception->getMessage() .
                '] in ['. $exception->getFile() .
                ' line ' . $exception->getLine() . ']';
        print $this->toParsedXml($message);
        print "</" . $this->namespace . "exception>\n";
    }

    /**
     *    Paints the skipping message and tag.
     *    @param string $message        Text to display in skip tag.
     *    @access public
     */
    function paintSkip($message) {
        parent::paintSkip($message);
        print $this->getIndent(1);
        print "<" . $this->namespace . "skip>";
        print $this->toParsedXml($message);
        print "</" . $this->namespace . "skip>\n";
    }

    /**
     *    Paints a simple supplementary message.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintMessage($message) {
        parent::paintMessage($message);
        print $this->getIndent(1);
        print "<" . $this->namespace . "message>";
        print $this->toParsedXml($message);
        print "</" . $this->namespace . "message>\n";
    }

    /**
     *    Paints a formatted ASCII message such as a
     *    privateiable dump.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintFormattedMessage($message) {
        parent::paintFormattedMessage($message);
        print $this->getIndent(1);
        print "<" . $this->namespace . "formatted>";
        print "<![CDATA[$message]]>";
        print "</" . $this->namespace . "formatted>\n";
    }

    /**
     *    Serialises the event object.
     *    @param string $type        Event type as text.
     *    @param mixed $payload      Message or object.
     *    @access public
     */
    function paintSignal($type, $payload) {
        parent::paintSignal($type, $payload);
        print $this->getIndent(1);
        print "<" . $this->namespace . "signal type=\"$type\">";
        print "<![CDATA[" . serialize($payload) . "]]>";
        print "</" . $this->namespace . "signal>\n";
    }

    /**
     *    Paints the test document header.
     *    @param string $test_name     First test top level
     *                                 to start.
     *    @access public
     *    @abstract
     */
    function paintHeader($test_name) {
        if (! SimpleReporter::inCli()) {
            header('Content-type: text/xml');
        }
        print "<?xml version=\"1.0\"";
        if ($this->namespace) {
            print " xmlns:" . $this->namespace .
                    "=\"www.lastcraft.com/SimpleTest/Beta3/Report\"";
        }
        print "?>\n";
        print "<" . $this->namespace . "run>\n";
    }

    /**
     *    Paints the test document footer.
     *    @param string $test_name        The top level test.
     *    @access public
     *    @abstract
     */
    function paintFooter($test_name) {
        print "</" . $this->namespace . "run>\n";
    }
}

/**
 *    Accumulator for incoming tag. Holds the
 *    incoming test structure information for
 *    later dispatch to the reporter.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NestingXmlTag {
    private $name;
    private $attributes;

    /**
     *    Sets the basic test information except
     *    the name.
     *    @param hash $attributes   Name value pairs.
     *    @access public
     */
    function NestingXmlTag($attributes) {
        $this->name = false;
        $this->attributes = $attributes;
    }

    /**
     *    Sets the test case/method name.
     *    @param string $name        Name of test.
     *    @access public
     */
    function setName($name) {
        $this->name = $name;
    }

    /**
     *    Accessor for name.
     *    @return string        Name of test.
     *    @access public
     */
    function getName() {
        return $this->name;
    }

    /**
     *    Accessor for attributes.
     *    @return hash        All attributes.
     *    @access protected
     */
    protected function getAttributes() {
        return $this->attributes;
    }
}

/**
 *    Accumulator for incoming method tag. Holds the
 *    incoming test structure information for
 *    later dispatch to the reporter.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NestingMethodTag extends NestingXmlTag {

    /**
     *    Sets the basic test information except
     *    the name.
     *    @param hash $attributes   Name value pairs.
     *    @access public
     */
    function NestingMethodTag($attributes) {
        $this->NestingXmlTag($attributes);
    }

    /**
     *    Signals the appropriate start event on the
     *    listener.
     *    @param SimpleReporter $listener    Target for events.
     *    @access public
     */
    function paintStart(&$listener) {
        $listener->paintMethodStart($this->getName());
    }

    /**
     *    Signals the appropriate end event on the
     *    listener.
     *    @param SimpleReporter $listener    Target for events.
     *    @access public
     */
    function paintEnd(&$listener) {
        $listener->paintMethodEnd($this->getName());
    }
}

/**
 *    Accumulator for incoming case tag. Holds the
 *    incoming test structure information for
 *    later dispatch to the reporter.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NestingCaseTag extends NestingXmlTag {

    /**
     *    Sets the basic test information except
     *    the name.
     *    @param hash $attributes   Name value pairs.
     *    @access public
     */
    function NestingCaseTag($attributes) {
        $this->NestingXmlTag($attributes);
    }

    /**
     *    Signals the appropriate start event on the
     *    listener.
     *    @param SimpleReporter $listener    Target for events.
     *    @access public
     */
    function paintStart(&$listener) {
        $listener->paintCaseStart($this->getName());
    }

    /**
     *    Signals the appropriate end event on the
     *    listener.
     *    @param SimpleReporter $listener    Target for events.
     *    @access public
     */
    function paintEnd(&$listener) {
        $listener->paintCaseEnd($this->getName());
    }
}

/**
 *    Accumulator for incoming group tag. Holds the
 *    incoming test structure information for
 *    later dispatch to the reporter.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NestingGroupTag extends NestingXmlTag {

    /**
     *    Sets the basic test information except
     *    the name.
     *    @param hash $attributes   Name value pairs.
     *    @access public
     */
    function NestingGroupTag($attributes) {
        $this->NestingXmlTag($attributes);
    }

    /**
     *    Signals the appropriate start event on the
     *    listener.
     *    @param SimpleReporter $listener    Target for events.
     *    @access public
     */
    function paintStart(&$listener) {
        $listener->paintGroupStart($this->getName(), $this->getSize());
    }

    /**
     *    Signals the appropriate end event on the
     *    listener.
     *    @param SimpleReporter $listener    Target for events.
     *    @access public
     */
    function paintEnd(&$listener) {
        $listener->paintGroupEnd($this->getName());
    }

    /**
     *    The size in the attributes.
     *    @return integer     Value of size attribute or zero.
     *    @access public
     */
    function getSize() {
        $attributes = $this->getAttributes();
        if (isset($attributes['SIZE'])) {
            return (integer)$attributes['SIZE'];
        }
        return 0;
    }
}

/**
 *    Parser for importing the output of the XmlReporter.
 *    Dispatches that output to another reporter.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleTestXmlParser {
    private $listener;
    private $expat;
    private $tag_stack;
    private $in_content_tag;
    private $content;
    private $attributes;

    /**
     *    Loads a listener with the SimpleReporter
     *    interface.
     *    @param SimpleReporter $listener   Listener of tag events.
     *    @access public
     */
    function SimpleTestXmlParser(&$listener) {
        $this->listener = &$listener;
        $this->expat = &$this->createParser();
        $this->tag_stack = array();
        $this->in_content_tag = false;
        $this->content = '';
        $this->attributes = array();
    }

    /**
     *    Parses a block of XML sending the results to
     *    the listener.
     *    @param string $chunk        Block of text to read.
     *    @return boolean             True if valid XML.
     *    @access public
     */
    function parse($chunk) {
        if (! xml_parse($this->expat, $chunk)) {
            trigger_error('XML parse error with ' .
                    xml_error_string(xml_get_error_code($this->expat)));
            return false;
        }
        return true;
    }

    /**
     *    Sets up expat as the XML parser.
     *    @return resource        Expat handle.
     *    @access protected
     */
    protected function &createParser() {
        $expat = xml_parser_create();
        xml_set_object($expat, $this);
        xml_set_element_handler($expat, 'startElement', 'endElement');
        xml_set_character_data_handler($expat, 'addContent');
        xml_set_default_handler($expat, 'defaultContent');
        return $expat;
    }

    /**
     *    Opens a new test nesting level.
     *    @return NestedXmlTag     The group, case or method tag
     *                             to start.
     *    @access private
     */
    protected function pushNestingTag($nested) {
        array_unshift($this->tag_stack, $nested);
    }

    /**
     *    Accessor for current test structure tag.
     *    @return NestedXmlTag     The group, case or method tag
     *                             being parsed.
     *    @access private
     */
    protected function &getCurrentNestingTag() {
        return $this->tag_stack[0];
    }

    /**
     *    Ends a nesting tag.
     *    @return NestedXmlTag     The group, case or method tag
     *                             just finished.
     *    @access private
     */
    protected function popNestingTag() {
        return array_shift($this->tag_stack);
    }

    /**
     *    Test if tag is a leaf node with only text content.
     *    @param string $tag        XML tag name.
     *    @return @boolean          True if leaf, false if nesting.
     *    @private
     */
    protected function isLeaf($tag) {
        return in_array($tag, array(
                'NAME', 'PASS', 'FAIL', 'EXCEPTION', 'SKIP', 'MESSAGE', 'FORMATTED', 'SIGNAL'));
    }

    /**
     *    Handler for start of event element.
     *    @param resource $expat     Parser handle.
     *    @param string $tag         Element name.
     *    @param hash $attributes    Name value pairs.
     *                               Attributes without content
     *                               are marked as true.
     *    @access protected
     */
    protected function startElement($expat, $tag, $attributes) {
        $this->attributes = $attributes;
        if ($tag == 'GROUP') {
            $this->pushNestingTag(new NestingGroupTag($attributes));
        } elseif ($tag == 'CASE') {
            $this->pushNestingTag(new NestingCaseTag($attributes));
        } elseif ($tag == 'TEST') {
            $this->pushNestingTag(new NestingMethodTag($attributes));
        } elseif ($this->isLeaf($tag)) {
            $this->in_content_tag = true;
            $this->content = '';
        }
    }

    /**
     *    End of element event.
     *    @param resource $expat     Parser handle.
     *    @param string $tag         Element name.
     *    @access protected
     */
    protected function endElement($expat, $tag) {
        $this->in_content_tag = false;
        if (in_array($tag, array('GROUP', 'CASE', 'TEST'))) {
            $nesting_tag = $this->popNestingTag();
            $nesting_tag->paintEnd($this->listener);
        } elseif ($tag == 'NAME') {
            $nesting_tag = &$this->getCurrentNestingTag();
            $nesting_tag->setName($this->content);
            $nesting_tag->paintStart($this->listener);
        } elseif ($tag == 'PASS') {
            $this->listener->paintPass($this->content);
        } elseif ($tag == 'FAIL') {
            $this->listener->paintFail($this->content);
        } elseif ($tag == 'EXCEPTION') {
            $this->listener->paintError($this->content);
        } elseif ($tag == 'SKIP') {
            $this->listener->paintSkip($this->content);
        } elseif ($tag == 'SIGNAL') {
            $this->listener->paintSignal(
                    $this->attributes['TYPE'],
                    unserialize($this->content));
        } elseif ($tag == 'MESSAGE') {
            $this->listener->paintMessage($this->content);
        } elseif ($tag == 'FORMATTED') {
            $this->listener->paintFormattedMessage($this->content);
        }
    }

    /**
     *    Content between start and end elements.
     *    @param resource $expat     Parser handle.
     *    @param string $text        Usually output messages.
     *    @access protected
     */
    protected function addContent($expat, $text) {
        if ($this->in_content_tag) {
            $this->content .= $text;
        }
        return true;
    }

    /**
     *    XML and Doctype handler. Discards all such content.
     *    @param resource $expat     Parser handle.
     *    @param string $default     Text of default content.
     *    @access protected
     */
    protected function defaultContent($expat, $default) {
    }
}
?>