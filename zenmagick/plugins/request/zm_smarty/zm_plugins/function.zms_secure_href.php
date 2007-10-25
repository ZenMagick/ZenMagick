<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_secure_href} function plugin
 *
 * Type:     function<br>
 * Name:     zms_secure_href<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_secure_href($params, &$smarty) {
    return zm_secure_href(_zms_dv($params['page']), _zms_ad($params, 'params', false), false);
}

/* vim: set expandtab: */

?>
