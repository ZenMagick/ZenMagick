<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_secure_form} function plugin
 *
 * Type:     function<br>
 * Name:     zms_secure_form<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_secure_form($params, &$smarty) {
    return zm_secure_form(_zms_dv($params['page']), _zms_ad($params, 'params', ''), _zms_ad($params, 'id', null)
      , _zms_ad($params, 'method', 'post'), _zms_ad($params, 'onsubmit', null), false);
}

/* vim: set expandtab: */

?>
