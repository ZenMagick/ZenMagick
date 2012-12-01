<?php
/**
 * Navigation_history Class.
 *
 * @package classes
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: navigation_history.php 19328 2011-08-06 22:53:47Z drbyte $
 */

namespace ZenMagick\ZenCartBundle\Compat;

use Symfony\Component\HttpFoundation\Request;

/**
 * Somewhat cleaner version of ZenCart navigation_history class.
 *
 * This class is used to manage navigation snapshots
 *
 * CHANGES:
 * uses <code>Symfony\Component\HttpFoundation\Request</code> instead of globals
 * serializes internal data via <code>Serializable</code> interface
 *
 * @todo avoiding storing the request as we can't guarantee it
 * has the correct values if activated in a sub request.
 */
class NavigationHistory extends Base implements \Serializable
{
    public $path, $snapshot;

    protected $request;

    public function __construct()
    {
        $this->reset();
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Check if request is SSL or NONSSL
     *
     * @return string SSL|NONSSL
     */
    public function getRequestType()
    {
        return $this->request->isSecure() ? 'SSL' : 'NONSSL';
    }

    /**
     * Get Current active route (main_page)
     *
     * @return string route id
     */
    public function getMainPage()
    {
        return $this->request->attributes->get('_route');
    }

    public function reset()
    {
        $this->path = array();
        $this->snapshot = array();
    }

    public function add_current_page()
    {
        $get_vars = array();

        $cPath = $this->request->query->get('cPath');
        foreach($this->request->query->all() as $key => $value) {
            if ($key != 'main_page') {
                $get_vars[$key] = $value;
            }
        }

        $set = 'true';
        for ($i=0, $n=sizeof($this->path); $i<$n; $i++) {
            if ( ($this->path[$i]['page'] == $this->getMainPage()) ) {
                if (!empty($cPath)) {
                    if (!isset($this->path[$i]['get']['cPath'])) {
                        continue;
                    } else {
                        if ($this->path[$i]['get']['cPath'] == $cPath) {
                            array_splice($this->path, ($i+1));
                            $set = 'false';
                            break;
                        } else {
                            $old_cPath = explode('_', $this->path[$i]['get']['cPath']);
                            $new_cPath = explode('_', $cPath);

                            $exit_loop = false;
                            for ($j=0, $n2=sizeof($old_cPath); $j<$n2; $j++) {
                                if ($old_cPath[$j] != $new_cPath[$j]) {
                                    array_splice($this->path, ($i));
                                    $set = 'true';
                                    $exit_loop = true;
                                    break;
                                }
                            }
                            if ($exit_loop == true) break;
                        }
                    }
                } else {
                    array_splice($this->path, ($i));
                    $set = 'true';
                    break;
                }
            }
        }

        if ($set == 'true') {
            if ($this->getMainPage()) {
                $page = $this->getMainPage();
            } else {
                $page = 'index';
            }
            $this->path[] = array('page' => $page,
                'mode' => $this->getRequestType(),
                'get' => $get_vars,
                'post' => array() /*$_POST*/);
        }
    }

    public function remove_current_page()
    {
        $last_entry_position = sizeof($this->path) - 1;
        if ($this->path[$last_entry_position]['page'] == $this->getMainPage()) {
            unset($this->path[$last_entry_position]);
        }
    }

    public function set_snapshot($page = '')
    {
        $get_vars = array();
        if (is_array($page)) {
            $this->snapshot = array('page' => $page['page'],
                'mode' => $page['mode'],
                'get' => $page['get'],
                'post' => $page['post']);
        } else {
            foreach($this->request->query->all() as $key => $value) {
                if ($key != 'main_page') {
                    $get_vars[$key] = $value;
                }
             }
            if ($this->getMainPage()) {
                $page = $this->getMainPage();
            } else {
                $page = 'index';
            }
            $this->snapshot = array('page' => $page,
                'mode' => $this->getRequestType(),
                'get' => $get_vars,
                'post' => array()/*$_POST*/);
        }
    }

    public function clear_snapshot()
    {
        $this->snapshot = array();
    }

    public function set_path_as_snapshot($history = 0)
    {
        $pos = (sizeof($this->path)-1-$history);
        $this->snapshot = array('page' => $this->path[$pos]['page'],
            'mode' => $this->path[$pos]['mode'],
            'get' => $this->path[$pos]['get'],
            'post' => $this->path[$pos]['post']);
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(array($this->path, $this->snapshot));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        list($this->path, $this->snapshot) = unserialize($serialized);
    }
}
