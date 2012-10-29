<?php
/**
* LiftSuggest Recommendations v1.0
*
* @author Tatvic Interactive
* Email : info@liftsuggest.com
* URL : http://www.liftsuggest.com
* @version 1.0
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see license.txt
* LiftSuggest Recommendations is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace ZenMagick\plugins\liftSuggest;

/**
 * Lift Suggest Recommendations interface.
 *
 * <p>The configuration supports (and requires) the following options:</p>
 * <dl>
 *   <dt>token</dt>
 *   <dd>The Lift Suggest Token.</dd>
 *   <dt>customerId</dt>
 *   <dd>The Lift Suggest customer ID.</dd>
 *   <dt>limit</dt>
 *   <dd>The recommendation limit.</dd>
 *   <dt>domain</dt>
 *   <dd>The domain for suggestions.</dd>
 * </dl>
 */
class LiftSuggestLookup {
    private $token;
    private $customerId;
    private $limit;
    private $domain;

    /**
     * Create new instance.
     *
     * @param array config The configuration.
     */
    public function __construct($config) {
        $this->token = $config['token'];
        $this->customerId = $config['customerId'];
        $this->limit = $config['limit'];
        $this->domain = $config['domain'];
    }

    /**
     * Simple logger.
     *
     * @param string message The log message.
     * @param Exception e Optional exception; default is <code>null</code>.
     */
    public function log($message, $e=null) {
        // swallow
    }

    /**
     * Store a value in session.
     *
     * @param string key The key.
     * @param mixed value The value.
     */
    public function storeInSession($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a value from session.
     *
     * @param string key The key.
     * @param mixed default A default value; default is <code>null</code>.
     */
    public function getFromSession($key, $default=null) {
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        return $default;
    }

    /**
     * Populate the raw recommendation data.
     *
     * <p>The data returned by this method is the data eventually returned by the
     *  <code>getProductRecommendations()</code> method.</p>
     *
     * @param array raw The raw Lift Suggest recommendation data.
     * @return array Corresponding product data.
     */
    public function populate($raw) {
        return $raw;
    }

    /**
     * Get recommendations for the given product (id).
     *
     * @param mixed productIds Either a single productId or a list of product Ids.
     * @param int limit Optional limit to override the globally configured limit; default is <code>null</code> to use the global limit.
     * @return array List of maps with product recommendation details and global popularity (%), or <code>null</code> on failure.
     */
    public function getProductRecommendations($productIds, $limit=null) {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }
        $limit = null === $limit ? $this->limit : $limit;
        $url = sprintf("http://www.liftsuggest.com/index.php/rest_c/user/token/%s/custid/%s/prodsku/%s/limit/%s/format/json/domain/%s",
                $this->token, $this->customerId, implode(',', $productIds), $limit, $this->domain);

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $jsonResponse = curl_exec($curl);
            curl_close($curl);
        } catch (Exception $e) {
            $this->log('JSON call failed', $e);
            return null;
        }

        try {
            $raw = json_decode($jsonResponse, true);
        } catch (Exception $e) {
            $this->log('invalid JSON', $e);
            return null;
        }

        $recommendations = array();
        if (!is_array($raw)) {
            $this->log('invalid JSON', $e);
            return null;
        }

        if (array_key_exists('error', $raw)) {
            $this->log(sprintf('call failed %s', $raw['error']));
            return null;
        }

        foreach ($raw as $value) {
            if (is_array($value)) {
                foreach ($value as $key => $details) {
                    $recommendations[$key] = $details;
                }
            } else {
                $this->log(sprintf('invalid recommendation: "%s"', $value));
            }
        }

        // custom code
        return $this->populate($recommendations);
    }

}
