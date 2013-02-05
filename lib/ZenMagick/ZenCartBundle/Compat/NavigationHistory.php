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
 * ignore all XmlHttpRequest
 *
 * @todo avoiding storing the request as we can't guarantee it
 * has the correct values if activated in a sub request.
 */
class NavigationHistory extends Base implements \Serializable
{
    public $path;
    public $snapshot;

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
        if ($this->request->isXmlHttpRequest()) return;
        $get_vars = $this->request->query->all();
        $cPath = $get_vars['cPath'];
        unset($get_vars['main_page']);

        $set = true;
        $n = sizeof($this->path);
        for ($i = 0; $i < $n; $i++) {
            if ($this->path[$i]['page'] == $this->getMainPage()) {
                continue;
            }
            if (!empty($cPath)) {
                $stored_cPath = '';
                if (isset($this->path[$i]['get']['cPath'])) {
                    $stored_cPath = $this->path[$i]['get']['cPath'];
                }
                if (empty($stored_cPath)) {
                    continue;
                } else {
                    if ($stored_cPath == $cPath) {
                        array_splice($this->path, ($i+1));
                        $set = false;
                        break;
                    } else {
                        $old_cPath = explode('_', $stored_cPath);
                        $new_cPath = explode('_', $cPath);

                        $exit_loop = false;
                        for ($j=0, $n2=sizeof($old_cPath); $j<$n2; $j++) {
                            if ($old_cPath[$j] != $new_cPath[$j]) {
                                array_splice($this->path, ($i));
                                $set = true;
                                $exit_loop = true;
                                break;
                            }
                        }
                        if ($exit_loop) break;
                    }
                }
            } else {
                array_splice($this->path, ($i));
                $set = true;
                break;
            }
        }

        if ($set) {
            $this->path[] = array(
                'page' => $this->getMainPage() ?: 'index',
                'mode' => $this->getRequestType(),
                'get' => $get_vars
            );
        }
    }

    public function remove_current_page()
    {
        if ($this->request->isXmlHttpRequest()) return;
        $pos = count($this->path) - 1;
        if ($this->path[$pos]['page'] == $this->getMainPage()) {
            unset($this->path[$pos]);
        }
    }

    public function set_snapshot($page = null)
    {
         if ($this->request->isXmlHttpRequest()) return;
        if (is_array($page)) {
            $this->snapshot = $page;
        } else {
            $get_vars = $this->request->query->all();
            unset($get_vars['main_page']);
            $this->snapshot = array(
                'page' => $this->getMainPage() ?: 'index',
                'mode' => $this->getRequestType(),
                'get' => $get_vars
            );
        }
    }

    public function clear_snapshot()
    {
        if ($this->request->isXmlHttpRequest()) return;
        $this->snapshot = array();
    }

    public function set_path_as_snapshot($history = 0)
    {
        if ($this->request->isXmlHttpRequest()) return;
        $pos = (count($this->path) - 1) - $history;
        $this->snapshot = $this->path[$pos];
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
