<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {zms_ezpage_link} function plugin
 *
 * Type:     function<br>
 * Name:     zms_ezpage_link<br>
 * Purpose:  Wrapper
 * @author   DerManoMann
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_zms_ezpage_link($params, &$smarty) {
    return zm_ezpage_link($params['id'], isset($params['text']) ? $params['text'] : '', false);
}

/* vim: set expandtab: */

?>
